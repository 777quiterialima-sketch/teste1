from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from flask_wtf import FlaskForm
from wtforms import StringField, SelectField, DecimalField, SubmitField
from wtforms.validators import DataRequired
from app.extensions import db
from app.models import Account
from app.services.finance import get_account_balance


accounts_bp = Blueprint("accounts", __name__, url_prefix="/accounts")


class AccountForm(FlaskForm):
    name = StringField("Nome", validators=[DataRequired()])
    type = SelectField(
        "Tipo",
        choices=[("corrente", "Corrente"), ("poupança", "Poupança"), ("carteira", "Carteira")],
        validators=[DataRequired()],
    )
    opening_balance = DecimalField("Saldo inicial", default=0)
    submit = SubmitField("Salvar")


@accounts_bp.route("")
@login_required
def list_accounts():
    accounts = Account.query.filter_by(user_id=current_user.id).all()
    account_data = []
    for account in accounts:
        balance = get_account_balance(account.id, account.opening_balance)
        account_data.append({"account": account, "balance": balance})
    return render_template("accounts/list.html", accounts=account_data)


@accounts_bp.route("/new", methods=["GET", "POST"])
@login_required
def new_account():
    form = AccountForm()
    if form.validate_on_submit():
        account = Account(
            user_id=current_user.id,
            name=form.name.data,
            type=form.type.data,
            opening_balance=form.opening_balance.data or 0,
        )
        db.session.add(account)
        db.session.commit()
        flash("Conta criada com sucesso.", "success")
        return redirect(url_for("accounts.list_accounts"))
    return render_template("accounts/form.html", form=form, title="Nova Conta")


@accounts_bp.route("/<int:account_id>/edit", methods=["GET", "POST"])
@login_required
def edit_account(account_id):
    account = Account.query.filter_by(id=account_id, user_id=current_user.id).first_or_404()
    form = AccountForm(obj=account)
    if form.validate_on_submit():
        account.name = form.name.data
        account.type = form.type.data
        account.opening_balance = form.opening_balance.data or 0
        db.session.commit()
        flash("Conta atualizada.", "success")
        return redirect(url_for("accounts.list_accounts"))
    return render_template("accounts/form.html", form=form, title="Editar Conta")


@accounts_bp.route("/<int:account_id>/delete", methods=["POST"])
@login_required
def delete_account(account_id):
    account = Account.query.filter_by(id=account_id, user_id=current_user.id).first_or_404()
    db.session.delete(account)
    db.session.commit()
    flash("Conta removida.", "info")
    return redirect(url_for("accounts.list_accounts"))
