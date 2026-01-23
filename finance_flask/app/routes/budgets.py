from datetime import date
from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from app.extensions import db
from app.models import Budget, Category


budgets_bp = Blueprint("budgets", __name__, url_prefix="/budgets")


@budgets_bp.route("")
@login_required
def index():
    today = date.today()
    month = request.args.get("month", f"{today.year}-{today.month:02d}")
    year, month_num = map(int, month.split("-"))

    categories = Category.query.filter_by(user_id=current_user.id).all()
    budgets = Budget.query.filter_by(user_id=current_user.id, year=year, month=month_num).all()
    budget_map = {budget.category_id: budget for budget in budgets}
    total_budget = next((b for b in budgets if b.category_id is None), None)

    return render_template(
        "budgets/index.html",
        month=month,
        categories=categories,
        budget_map=budget_map,
        total_budget=total_budget,
    )


@budgets_bp.route("/save", methods=["POST"])
@login_required
def save():
    month = request.form.get("month")
    year, month_num = map(int, month.split("-"))

    for key, value in request.form.items():
        if not key.startswith("category_") or not value:
            continue
        category_id = int(key.replace("category_", ""))
        budget = Budget.query.filter_by(
            user_id=current_user.id,
            year=year,
            month=month_num,
            category_id=category_id,
        ).first()
        if not budget:
            budget = Budget(
                user_id=current_user.id,
                year=year,
                month=month_num,
                category_id=category_id,
                limit_amount=value,
            )
            db.session.add(budget)
        else:
            budget.limit_amount = value

    total_value = request.form.get("total_budget")
    if total_value:
        total_budget = Budget.query.filter_by(
            user_id=current_user.id,
            year=year,
            month=month_num,
            category_id=None,
        ).first()
        if not total_budget:
            total_budget = Budget(
                user_id=current_user.id,
                year=year,
                month=month_num,
                category_id=None,
                limit_amount=total_value,
            )
            db.session.add(total_budget)
        else:
            total_budget.limit_amount = total_value

    db.session.commit()
    flash("Limites atualizados.", "success")
    return redirect(url_for("budgets.index", month=month))
