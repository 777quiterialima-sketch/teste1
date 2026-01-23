from datetime import date, datetime
from calendar import monthrange
from app.models import Transaction, CardInvoice
from app.extensions import db


def get_month_range(year, month):
    start_date = date(year, month, 1)
    end_date = date(year, month, monthrange(year, month)[1])
    return start_date, end_date


def get_account_balance(account_id, opening_balance):
    income = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.account_id == account_id,
        Transaction.type == "income",
        Transaction.payment_method != "card",
    ).scalar()

    expenses = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.account_id == account_id,
        Transaction.type == "expense",
        Transaction.payment_method != "card",
    ).scalar()

    transfers_out = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.account_id == account_id,
        Transaction.type == "transfer",
    ).scalar()

    transfers_in = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.account_to_id == account_id,
        Transaction.type == "transfer",
    ).scalar()

    return float(opening_balance) + float(income) - float(expenses) - float(transfers_out) + float(transfers_in)


def get_month_summary(user_id, year, month):
    start_date, end_date = get_month_range(year, month)
    income = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.user_id == user_id,
        Transaction.type == "income",
        Transaction.date.between(start_date, end_date),
    ).scalar()

    expenses = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.user_id == user_id,
        Transaction.type == "expense",
        Transaction.date.between(start_date, end_date),
    ).scalar()

    return float(income), float(expenses)


def get_card_cycle(card, year, month):
    closing_day = card.closing_day
    if month == 1:
        prev_month = 12
        prev_year = year - 1
    else:
        prev_month = month - 1
        prev_year = year
    start_date = date(prev_year, prev_month, closing_day + 1) if closing_day < 28 else date(prev_year, prev_month, closing_day)
    end_day = min(closing_day, monthrange(year, month)[1])
    end_date = date(year, month, end_day)
    return start_date, end_date


def get_card_invoice(card, year, month):
    start_date, end_date = get_card_cycle(card, year, month)
    items = Transaction.query.filter(
        Transaction.card_id == card.id,
        Transaction.payment_method == "card",
        Transaction.date.between(start_date, end_date),
    ).order_by(Transaction.date.asc()).all()

    total = sum(float(item.amount) for item in items)
    invoice = CardInvoice.query.filter_by(card_id=card.id, year=year, month=month).first()
    return invoice, items, total


def register_invoice_payment(card, user_id, year, month, payment_account_id):
    invoice, items, total = get_card_invoice(card, year, month)
    if not invoice:
        invoice = CardInvoice(
            card_id=card.id,
            user_id=user_id,
            year=year,
            month=month,
            total_amount=total,
        )
        db.session.add(invoice)
    invoice.total_amount = total
    invoice.paid_at = datetime.utcnow()
    invoice.payment_account_id = payment_account_id
    db.session.commit()
    return invoice
