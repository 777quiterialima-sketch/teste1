"""Command line helper to fetch prices for stored products."""
from __future__ import annotations

import argparse
from typing import Iterable

from database import get_connection
from price_fetcher import PriceFetcherError, fetch_price


def iter_products(product_id: int | None = None) -> Iterable[dict]:
    with get_connection() as conn:
        if product_id is None:
            rows = conn.execute("SELECT * FROM products ORDER BY id").fetchall()
        else:
            rows = conn.execute("SELECT * FROM products WHERE id = ?", (product_id,)).fetchall()
    for row in rows:
        yield dict(row)


def save_history(product_id: int, amount: float, currency: str, raw: str) -> None:
    with get_connection() as conn:
        conn.execute(
            "INSERT INTO price_history (product_id, price, currency, raw_value) VALUES (?, ?, ?, ?)",
            (product_id, amount, currency, raw),
        )
        conn.commit()


def main() -> None:
    parser = argparse.ArgumentParser(description="Atualiza preços dos produtos cadastrados")
    parser.add_argument("--product-id", type=int, help="ID do produto a atualizar", default=None)
    args = parser.parse_args()

    for product in iter_products(args.product_id):
        print(f"Buscando preço para {product['name']} ({product['store']})...")
        try:
            result = fetch_price(product["url"], product["store"])
        except PriceFetcherError as exc:
            print(f"  Erro: {exc}")
            continue
        save_history(product["id"], float(result.amount), result.currency, result.raw_value)
        print(f"  Preço salvo: {result.currency} {result.amount}")


if __name__ == "__main__":
    main()
