"""Database utilities for the price monitor application."""
from __future__ import annotations

import sqlite3
from contextlib import contextmanager
from pathlib import Path
from typing import Iterator

DB_PATH = Path("price_monitor.db")

SCHEMA = """
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    url TEXT NOT NULL,
    store TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS price_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    price REAL NOT NULL,
    currency TEXT NOT NULL,
    fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    raw_value TEXT,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);
"""


def init_db(path: Path | None = None) -> None:
    """Initialise the SQLite database if required."""
    db_path = path or DB_PATH
    with sqlite3.connect(db_path) as conn:
        conn.executescript(SCHEMA)


@contextmanager
def get_connection(path: Path | None = None) -> Iterator[sqlite3.Connection]:
    """Yield a SQLite connection with row factory configured."""
    db_path = path or DB_PATH
    init_db(db_path)
    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row
    try:
        yield conn
    finally:
        conn.close()
