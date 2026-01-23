from datetime import date
from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from flask_wtf import FlaskForm
from wtforms import StringField, DecimalField, IntegerField, SubmitField, SelectField
from wtforms.validators import DataRequired, NumberRange
from app.extensions import db
from app.models import Card, Account, Transaction
from app.services.finance import get_card_invoice, register_invoice_payment


cards_bp = Blueprint("cards", __name__, url_prefix="/cards")


class CardForm(FlaskForm):
    name = StringField("Nome", validators=[DataRequired()])
    brand = StringField("Bandeira", validators=[DataRequired()])
    limit_total = DecimalField("Limite total", default=0)
    closing_day = IntegerField("Dia de fechamento", validators=[DataRequired(), NumberRange(min=1, max=28)])
    due_day = IntegerField("Dia de vencimento", validators=[DataRequired(), NumberRange(min=1, max=28)])
    submit = SubmitField("Salvar")


class InvoicePayForm(FlaskForm):
    payment_account_id = SelectField("Conta para pagamento", coerce=int, validators=[DataRequired()])
    submit = SubmitField("Pagar fatura")


@cards_bp.route("")
@login_required
def list_cards():
    cards = Card.query.filter_by(user_id=current_user.id).all()
    return render_template("cards/list.html", cards=cards)


@cards_bp.route("/new", methods=["GET", "POST"])
@login_required
def new_card():
    form = CardForm()
    if form.validate_on_submit():
        card = Card(
            user_id=current_user.id,
            name=form.name.data,
            brand=form.brand.data,
            limit_total=form.limit_total.data or 0,
            closing_day=form.closing_day.data,
            due_day=form.due_day.data,
        )
        db.session.add(card)
        db.session.commit()
        flash("Cartão criado.", "success")
        return redirect(url_for("cards.list_cards"))
    return render_template("cards/form.html", form=form, title="Novo Cartão")


@cards_bp.route("/<int:card_id>/edit", methods=["GET", "POST"])
@login_required
def edit_card(card_id):
    card = Card.query.filter_by(id=card_id, user_id=current_user.id).first_or_404()
    form = CardForm(obj=card)
    if form.validate_on_submit():
        card.name = form.name.data
        card.brand = form.brand.data
        card.limit_total = form.limit_total.data or 0
        card.closing_day = form.closing_day.data
        card.due_day = form.due_day.data
        db.session.commit()
        flash("Cartão atualizado.", "success")
        return redirect(url_for("cards.list_cards"))
    return render_template("cards/form.html", form=form, title="Editar Cartão")


@cards_bp.route("/<int:card_id>/delete", methods=["POST"])
@login_required
def delete_card(card_id):
    card = Card.query.filter_by(id=card_id, user_id=current_user.id).first_or_404()
    db.session.delete(card)
    db.session.commit()
    flash("Cartão removido.", "info")
    return redirect(url_for("cards.list_cards"))


@cards_bp.route("/<int:card_id>/invoice")
@login_required
def view_invoice(card_id):
    card = Card.query.filter_by(id=card_id, user_id=current_user.id).first_or_404()
    month_param = request.args.get("month")
    if month_param:
        year, month = map(int, month_param.split("-"))
    else:
        today = date.today()
        year, month = today.year, today.month
    invoice, items, total = get_card_invoice(card, year, month)
    form = InvoicePayForm()
    form.payment_account_id.choices = [(a.id, a.name) for a in Account.query.filter_by(user_id=current_user.id).all()]
    return render_template(
        "cards/invoice.html",
        card=card,
        invoice=invoice,
        items=items,
        total=total,
        year=year,
        month=month,
        form=form,
    )


@cards_bp.route("/<int:card_id>/invoice/pay", methods=["POST"])
@login_required
def pay_invoice(card_id):
    card = Card.query.filter_by(id=card_id, user_id=current_user.id).first_or_404()
    form = InvoicePayForm()
    form.payment_account_id.choices = [(a.id, a.name) for a in Account.query.filter_by(user_id=current_user.id).all()]
    if form.validate_on_submit():
        today = date.today()
        invoice, items, total = get_card_invoice(card, today.year, today.month)
        transaction = Transaction(
            user_id=current_user.id,
            type="expense",
            date=today,
            description=f"Pagamento fatura {card.name} {today.month}/{today.year}",
            amount=total,
            account_id=form.payment_account_id.data,
            payment_method="debit",
        )
        db.session.add(transaction)
        db.session.commit()
        register_invoice_payment(card, current_user.id, today.year, today.month, form.payment_account_id.data)
        flash("Fatura paga com sucesso.", "success")
    else:
        flash("Selecione uma conta válida.", "danger")
    return redirect(url_for("cards.view_invoice", card_id=card.id, month=f"{date.today().year}-{date.today().month:02d}"))
