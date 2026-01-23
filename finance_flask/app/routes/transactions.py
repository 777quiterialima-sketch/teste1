from datetime import date
from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from flask_wtf import FlaskForm
from wtforms import StringField, SelectField, DecimalField, DateField, TextAreaField, SubmitField
from wtforms.validators import DataRequired
from app.extensions import db
from app.models import Transaction, Category, Account, Card


transactions_bp = Blueprint("transactions", __name__, url_prefix="/transactions")


class TransactionForm(FlaskForm):
    type = SelectField(
        "Tipo",
        choices=[("income", "Receita"), ("expense", "Despesa"), ("transfer", "Transferência")],
        validators=[DataRequired()],
    )
    date = DateField("Data", validators=[DataRequired()], format="%Y-%m-%d")
    description = StringField("Descrição", validators=[DataRequired()])
    category_id = SelectField("Categoria", coerce=int, choices=[])
    amount = DecimalField("Valor", validators=[DataRequired()])
    account_id = SelectField("Conta", coerce=int, choices=[])
    account_to_id = SelectField("Conta destino", coerce=int, choices=[])
    payment_method = SelectField(
        "Forma de pagamento",
        choices=[("cash", "Dinheiro"), ("debit", "Débito"), ("pix", "PIX"), ("card", "Cartão")],
        validators=[DataRequired()],
    )
    card_id = SelectField("Cartão", coerce=int, choices=[])
    notes = TextAreaField("Observações")
    submit = SubmitField("Salvar")


@transactions_bp.route("")
@login_required
def list_transactions():
    query = Transaction.query.filter_by(user_id=current_user.id)
    month = request.args.get("month")
    account_id = request.args.get("account_id", type=int)
    category_id = request.args.get("category_id", type=int)
    tx_type = request.args.get("type")
    search = request.args.get("search")

    if month:
        year, month_num = map(int, month.split("-"))
        query = query.filter(db.extract("year", Transaction.date) == year)
        query = query.filter(db.extract("month", Transaction.date) == month_num)
    if account_id:
        query = query.filter(Transaction.account_id == account_id)
    if category_id:
        query = query.filter(Transaction.category_id == category_id)
    if tx_type:
        query = query.filter(Transaction.type == tx_type)
    if search:
        query = query.filter(Transaction.description.ilike(f"%{search}%"))

    transactions = query.order_by(Transaction.date.desc()).all()
    categories = Category.query.filter_by(user_id=current_user.id).all()
    accounts = Account.query.filter_by(user_id=current_user.id).all()
    return render_template(
        "transactions/list.html",
        transactions=transactions,
        categories=categories,
        accounts=accounts,
    )


def setup_form_choices(form):
    form.category_id.choices = [(0, "Sem categoria")] + [
        (c.id, c.name) for c in Category.query.filter_by(user_id=current_user.id).all()
    ]
    form.account_id.choices = [(0, "Selecione")] + [
        (a.id, a.name) for a in Account.query.filter_by(user_id=current_user.id).all()
    ]
    form.account_to_id.choices = [(0, "Selecione")] + [
        (a.id, a.name) for a in Account.query.filter_by(user_id=current_user.id).all()
    ]
    form.card_id.choices = [(0, "Sem cartão")] + [
        (c.id, c.name) for c in Card.query.filter_by(user_id=current_user.id).all()
    ]


@transactions_bp.route("/new", methods=["GET", "POST"])
@login_required
def new_transaction():
    form = TransactionForm(date=date.today())
    setup_form_choices(form)
    if form.validate_on_submit():
        transaction = Transaction(
            user_id=current_user.id,
            type=form.type.data,
            date=form.date.data,
            description=form.description.data,
            category_id=form.category_id.data or None,
            amount=form.amount.data,
            account_id=form.account_id.data or None,
            account_to_id=form.account_to_id.data or None,
            payment_method=form.payment_method.data,
            card_id=form.card_id.data or None,
            notes=form.notes.data,
        )
        db.session.add(transaction)
        db.session.commit()
        flash("Lançamento criado.", "success")
        return redirect(url_for("transactions.list_transactions"))
    return render_template("transactions/form.html", form=form, title="Novo Lançamento")


@transactions_bp.route("/<int:transaction_id>/edit", methods=["GET", "POST"])
@login_required
def edit_transaction(transaction_id):
    transaction = Transaction.query.filter_by(id=transaction_id, user_id=current_user.id).first_or_404()
    form = TransactionForm(obj=transaction)
    setup_form_choices(form)
    if form.validate_on_submit():
        transaction.type = form.type.data
        transaction.date = form.date.data
        transaction.description = form.description.data
        transaction.category_id = form.category_id.data or None
        transaction.amount = form.amount.data
        transaction.account_id = form.account_id.data or None
        transaction.account_to_id = form.account_to_id.data or None
        transaction.payment_method = form.payment_method.data
        transaction.card_id = form.card_id.data or None
        transaction.notes = form.notes.data
        db.session.commit()
        flash("Lançamento atualizado.", "success")
        return redirect(url_for("transactions.list_transactions"))
    return render_template("transactions/form.html", form=form, title="Editar Lançamento")


@transactions_bp.route("/<int:transaction_id>/delete", methods=["POST"])
@login_required
def delete_transaction(transaction_id):
    transaction = Transaction.query.filter_by(id=transaction_id, user_id=current_user.id).first_or_404()
    db.session.delete(transaction)
    db.session.commit()
    flash("Lançamento removido.", "info")
    return redirect(url_for("transactions.list_transactions"))
