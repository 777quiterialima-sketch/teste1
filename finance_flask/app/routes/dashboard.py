from datetime import date
from flask import Blueprint, render_template, redirect, url_for
from flask_login import login_required, current_user
from app.models import Account, Card, BankConnection
from app.services.finance import get_account_balance, get_month_summary, get_card_invoice


dashboard_bp = Blueprint("dashboard", __name__)


@dashboard_bp.route("/")
def home():
    return redirect(url_for("dashboard.index"))


@dashboard_bp.route("/dashboard")
@login_required
def index():
    today = date.today()
    income, expenses = get_month_summary(current_user.id, today.year, today.month)
    accounts = Account.query.filter_by(user_id=current_user.id).all()
    cards = Card.query.filter_by(user_id=current_user.id).all()
    connections = BankConnection.query.filter_by(user_id=current_user.id).all()

    account_data = []
    for account in accounts:
        balance = get_account_balance(account.id, account.opening_balance)
        account_data.append({"account": account, "balance": balance})

    card_data = []
    for card in cards:
        invoice, items, total = get_card_invoice(card, today.year, today.month)
        available_limit = float(card.limit_total) - total
        card_data.append(
            {
                "card": card,
                "invoice_total": total,
                "available_limit": available_limit,
            }
        )

    general_balance = sum(item["balance"] for item in account_data)

    return render_template(
        "dashboard/index.html",
        income=income,
        expenses=expenses,
        general_balance=general_balance,
        accounts=account_data,
        cards=card_data,
        connections=connections,
    )
