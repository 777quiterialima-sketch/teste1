import hashlib
from io import StringIO
from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from app.extensions import db
from app.models import BankConnection, Account, ImportBatch, ImportItem
from app.services.imports import create_import_batch, load_csv_items, mark_duplicates, confirm_import


connections_bp = Blueprint("connections", __name__, url_prefix="/connections")


@connections_bp.route("")
@login_required
def index():
    connections = BankConnection.query.filter_by(user_id=current_user.id).all()
    accounts = Account.query.filter_by(user_id=current_user.id).all()
    batches = ImportBatch.query.filter_by(user_id=current_user.id).order_by(ImportBatch.created_at.desc()).all()
    return render_template("connections/index.html", connections=connections, accounts=accounts, batches=batches)


@connections_bp.route("/add", methods=["POST"])
@login_required
def add_connection():
    provider_name = request.form.get("provider_name")
    account_id = request.form.get("account_id")
    if not provider_name:
        flash("Informe o nome da conexão.", "danger")
        return redirect(url_for("connections.index"))
    connection = BankConnection(
        user_id=current_user.id,
        provider_name=provider_name,
        account_id=account_id or None,
    )
    db.session.add(connection)
    db.session.commit()
    flash("Conexão adicionada.", "success")
    return redirect(url_for("connections.index"))


@connections_bp.route("/import", methods=["GET", "POST"])
@login_required
def import_csv():
    if request.method == "POST":
        file = request.files.get("file")
        account_id = request.form.get("account_id", type=int)
        if not file or not account_id:
            flash("Selecione o arquivo e a conta destino.", "danger")
            return redirect(url_for("connections.import_csv"))
        content = file.read()
        file_hash = hashlib.sha256(content).hexdigest()
        batch = create_import_batch(current_user.id, file.filename, file_hash)

        content_str = content.decode("utf-8")
        mapping = {
            "date": request.form.get("col_date"),
            "description": request.form.get("col_description"),
            "amount": request.form.get("col_amount"),
        }
        load_csv_items(batch.id, StringIO(content_str), mapping, account_id)
        mark_duplicates(current_user.id, batch.id)
        flash("Arquivo importado. Revise antes de confirmar.", "info")
        return redirect(url_for("connections.import_review", batch_id=batch.id))

    return render_template("connections/import_review.html", step="upload", accounts=Account.query.filter_by(user_id=current_user.id).all())


@connections_bp.route("/import/review/<int:batch_id>")
@login_required
def import_review(batch_id):
    batch = ImportBatch.query.filter_by(id=batch_id, user_id=current_user.id).first_or_404()
    items = ImportItem.query.filter_by(batch_id=batch_id).all()
    return render_template("connections/import_review.html", step="review", batch=batch, items=items)


@connections_bp.route("/import/confirm/<int:batch_id>", methods=["POST"])
@login_required
def import_confirm(batch_id):
    batch = ImportBatch.query.filter_by(id=batch_id, user_id=current_user.id).first_or_404()
    ignore_duplicates = request.form.get("ignore_duplicates")
    if ignore_duplicates:
        ImportItem.query.filter_by(batch_id=batch_id, status="duplicate").update({"status": "ignored"})
        db.session.commit()
    confirm_import(current_user.id, batch_id)
    flash("Importação concluída.", "success")
    return redirect(url_for("connections.index"))
