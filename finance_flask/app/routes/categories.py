from flask import Blueprint, render_template, redirect, url_for, flash
from flask_login import login_required, current_user
from flask_wtf import FlaskForm
from wtforms import StringField, SelectField, SubmitField
from wtforms.validators import DataRequired
from app.extensions import db
from app.models import Category


categories_bp = Blueprint("categories", __name__, url_prefix="/categories")


class CategoryForm(FlaskForm):
    name = StringField("Nome", validators=[DataRequired()])
    type = SelectField(
        "Tipo",
        choices=[("income", "Receita"), ("expense", "Despesa"), ("both", "Ambos")],
        validators=[DataRequired()],
    )
    submit = SubmitField("Salvar")


@categories_bp.route("")
@login_required
def list_categories():
    categories = Category.query.filter_by(user_id=current_user.id).all()
    return render_template("categories/list.html", categories=categories)


@categories_bp.route("/new", methods=["GET", "POST"])
@login_required
def new_category():
    form = CategoryForm()
    if form.validate_on_submit():
        category = Category(user_id=current_user.id, name=form.name.data, type=form.type.data)
        db.session.add(category)
        db.session.commit()
        flash("Categoria criada.", "success")
        return redirect(url_for("categories.list_categories"))
    return render_template("categories/form.html", form=form, title="Nova Categoria")


@categories_bp.route("/<int:category_id>/edit", methods=["GET", "POST"])
@login_required
def edit_category(category_id):
    category = Category.query.filter_by(id=category_id, user_id=current_user.id).first_or_404()
    form = CategoryForm(obj=category)
    if form.validate_on_submit():
        category.name = form.name.data
        category.type = form.type.data
        db.session.commit()
        flash("Categoria atualizada.", "success")
        return redirect(url_for("categories.list_categories"))
    return render_template("categories/form.html", form=form, title="Editar Categoria")


@categories_bp.route("/<int:category_id>/delete", methods=["POST"])
@login_required
def delete_category(category_id):
    category = Category.query.filter_by(id=category_id, user_id=current_user.id).first_or_404()
    db.session.delete(category)
    db.session.commit()
    flash("Categoria removida.", "info")
    return redirect(url_for("categories.list_categories"))
