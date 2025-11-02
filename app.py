"""Flask web application for monitoring product prices."""
from __future__ import annotations

from datetime import datetime
from decimal import Decimal
from typing import Any

from flask import Flask, flash, redirect, render_template, request, url_for

from database import DB_PATH, get_connection, init_db
from price_fetcher import PriceFetcherError, PriceResult, fetch_price

app = Flask(__name__)
app.secret_key = "change-me"  # Replace for production usage.

STORE_OPTIONS = {
    "casasbahia": {
        "label": "Casas Bahia",
        "helper": "O preço é buscado pelo id 'product-price' com fallbacks para metadados.",
    },
}


@app.before_first_request
def ensure_database() -> None:
    init_db(DB_PATH)


@app.route("/")
def index() -> str:
    with get_connection() as conn:
        products = conn.execute(
            "SELECT * FROM products ORDER BY name"
        ).fetchall()

        histories: dict[int, list[dict[str, Any]]] = {}
        for product in products:
            entries = conn.execute(
                """
                SELECT price, currency, fetched_at
                FROM price_history
                WHERE product_id = ?
                ORDER BY fetched_at DESC
                LIMIT 2
                """,
                (product["id"],),
            ).fetchall()
            histories[product["id"]] = [dict(row) for row in entries]

    return render_template(
        "index.html",
        products=products,
        histories=histories,
        stores=STORE_OPTIONS,
    )


@app.route("/products", methods=["POST"])
def create_product() -> Any:
    name = request.form.get("name", "").strip()
    url = request.form.get("url", "").strip()
    store = request.form.get("store", "").strip()

    errors = []
    if not name:
        errors.append("Informe um nome para o produto.")
    if not url:
        errors.append("Informe a URL do produto.")
    if store not in STORE_OPTIONS:
        errors.append("Selecione uma loja válida.")

    if errors:
        for error in errors:
            flash(error, "error")
        return redirect(url_for("index"))

    with get_connection() as conn:
        conn.execute(
            "INSERT INTO products (name, url, store) VALUES (?, ?, ?)",
            (name, url, store),
        )
        conn.commit()

    flash("Produto cadastrado com sucesso!", "success")
    return redirect(url_for("index"))


def save_price(product_id: int, result: PriceResult) -> None:
    with get_connection() as conn:
        conn.execute(
            "INSERT INTO price_history (product_id, price, currency, raw_value) VALUES (?, ?, ?, ?)",
            (product_id, float(result.amount), result.currency, result.raw_value),
        )
        conn.commit()


@app.route("/fetch/<int:product_id>")
def fetch_now(product_id: int) -> Any:
    with get_connection() as conn:
        product = conn.execute(
            "SELECT * FROM products WHERE id = ?",
            (product_id,),
        ).fetchone()

    if product is None:
        flash("Produto não encontrado.", "error")
        return redirect(url_for("index"))

    try:
        result = fetch_price(product["url"], product["store"])
    except PriceFetcherError as exc:
        flash(str(exc), "error")
        return redirect(url_for("index"))
    except Exception:
        flash("Ocorreu um erro ao buscar o preço.", "error")
        return redirect(url_for("index"))

    save_price(product_id, result)
    flash("Preço atualizado com sucesso!", "success")
    return redirect(url_for("index"))


@app.route("/history/<int:product_id>")
def history(product_id: int) -> Any:
    with get_connection() as conn:
        product = conn.execute(
            "SELECT * FROM products WHERE id = ?",
            (product_id,),
        ).fetchone()
        if product is None:
            flash("Produto não encontrado.", "error")
            return redirect(url_for("index"))

        entries = conn.execute(
            """
            SELECT price, currency, fetched_at, raw_value
            FROM price_history
            WHERE product_id = ?
            ORDER BY fetched_at DESC
            """,
            (product_id,),
        ).fetchall()

    def parse_datetime(value: str) -> datetime:
        return datetime.fromisoformat(value)

    store_meta = STORE_OPTIONS.get(product["store"], {"label": product["store"]})

    return render_template(
        "history.html",
        product=product,
        entries=entries,
        parse_datetime=parse_datetime,
        store_meta=store_meta,
    )


@app.template_filter("currency")
def currency_filter(value: Decimal | float | int | None) -> str:
    if value is None:
        return "--"
    amount = Decimal(value)
    return f"R$ {amount:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")


@app.template_filter("datetime_br")
def datetime_br_filter(value: str | None) -> str:
    if not value:
        return "--"
    try:
        return datetime.fromisoformat(value).strftime("%d/%m/%Y %H:%M")
    except ValueError:
        return value


if __name__ == "__main__":
    app.run(debug=True)
