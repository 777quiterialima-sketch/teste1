from datetime import date
from calendar import monthrange
from flask import Blueprint, render_template, request
from flask_login import login_required, current_user
from app.extensions import db
from app.models import Transaction, Category


reports_bp = Blueprint("reports", __name__, url_prefix="/reports")


@reports_bp.route("")
@login_required
def index():
    month_param = request.args.get("month")
    if month_param:
        year, month = map(int, month_param.split("-"))
    else:
        today = date.today()
        year, month = today.year, today.month

    start_date = date(year, month, 1)
    end_date = date(year, month, monthrange(year, month)[1])

    expenses = (
        db.session.query(Category.name, db.func.sum(Transaction.amount))
        .join(Category, Transaction.category_id == Category.id)
        .filter(
            Transaction.user_id == current_user.id,
            Transaction.type == "expense",
            Transaction.date.between(start_date, end_date),
        )
        .group_by(Category.name)
        .all()
    )

    income_total = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.user_id == current_user.id,
        Transaction.type == "income",
        Transaction.date.between(start_date, end_date),
    ).scalar()

    expense_total = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
        Transaction.user_id == current_user.id,
        Transaction.type == "expense",
        Transaction.date.between(start_date, end_date),
    ).scalar()

    months_labels = []
    income_series = []
    expense_series = []
    for i in range(5, -1, -1):
        ref_month = (month - i - 1) % 12 + 1
        ref_year = year if month - i > 0 else year - 1
        start = date(ref_year, ref_month, 1)
        end = date(ref_year, ref_month, monthrange(ref_year, ref_month)[1])
        months_labels.append(f"{ref_month:02d}/{ref_year}")
        income_val = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
            Transaction.user_id == current_user.id,
            Transaction.type == "income",
            Transaction.date.between(start, end),
        ).scalar()
        expense_val = db.session.query(db.func.coalesce(db.func.sum(Transaction.amount), 0)).filter(
            Transaction.user_id == current_user.id,
            Transaction.type == "expense",
            Transaction.date.between(start, end),
        ).scalar()
        income_series.append(float(income_val))
        expense_series.append(float(expense_val))

    return render_template(
        "reports/index.html",
        expenses=expenses,
        income_total=float(income_total),
        expense_total=float(expense_total),
        months_labels=months_labels,
        income_series=income_series,
        expense_series=expense_series,
        year=year,
        month=month,
    )
