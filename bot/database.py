import sqlite3
from datetime import datetime, date
from contextlib import contextmanager
from config import DATABASE_PATH


def init_db():
    with get_conn() as conn:
        conn.executescript("""
            CREATE TABLE IF NOT EXISTS gastos (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                valor       REAL    NOT NULL,
                descricao   TEXT    NOT NULL,
                categoria   TEXT    NOT NULL,
                essencial   INTEGER NOT NULL DEFAULT 1,
                data        TEXT    NOT NULL,
                criado_em   TEXT    NOT NULL
            );

            CREATE TABLE IF NOT EXISTS metas (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                nome            TEXT    NOT NULL,
                valor_total     REAL    NOT NULL,
                economia_mensal REAL    NOT NULL,
                prazo_meses     INTEGER NOT NULL,
                valor_acumulado REAL    NOT NULL DEFAULT 0,
                ativa           INTEGER NOT NULL DEFAULT 1,
                criada_em       TEXT    NOT NULL
            );

            CREATE TABLE IF NOT EXISTS config (
                chave TEXT PRIMARY KEY,
                valor TEXT NOT NULL
            );
        """)


@contextmanager
def get_conn():
    conn = sqlite3.connect(DATABASE_PATH)
    conn.row_factory = sqlite3.Row
    try:
        yield conn
        conn.commit()
    finally:
        conn.close()


def inserir_gasto(valor: float, descricao: str, categoria: str, essencial: bool,
                  data: str | None = None) -> int:
    hoje = data or date.today().isoformat()
    with get_conn() as conn:
        cur = conn.execute(
            "INSERT INTO gastos (valor, descricao, categoria, essencial, data, criado_em) "
            "VALUES (?, ?, ?, ?, ?, ?)",
            (valor, descricao, categoria, int(essencial), hoje, datetime.now().isoformat()),
        )
        return cur.lastrowid


def gastos_do_mes(ano: int | None = None, mes: int | None = None) -> list[dict]:
    hoje = date.today()
    ano = ano or hoje.year
    mes = mes or hoje.month
    prefixo = f"{ano:04d}-{mes:02d}"
    with get_conn() as conn:
        rows = conn.execute(
            "SELECT * FROM gastos WHERE data LIKE ? ORDER BY data DESC",
            (f"{prefixo}%",),
        ).fetchall()
    return [dict(r) for r in rows]


def total_por_categoria(ano: int | None = None, mes: int | None = None) -> dict[str, float]:
    rows = gastos_do_mes(ano, mes)
    totais: dict[str, float] = {}
    for r in rows:
        totais[r["categoria"]] = totais.get(r["categoria"], 0) + r["valor"]
    return totais


def ultimos_gastos(n: int = 10) -> list[dict]:
    with get_conn() as conn:
        rows = conn.execute(
            "SELECT * FROM gastos ORDER BY criado_em DESC LIMIT ?", (n,)
        ).fetchall()
    return [dict(r) for r in rows]


def total_gasto_mes() -> float:
    return sum(r["valor"] for r in gastos_do_mes())


def listar_metas() -> list[dict]:
    with get_conn() as conn:
        rows = conn.execute(
            "SELECT * FROM metas WHERE ativa = 1 ORDER BY id"
        ).fetchall()
    return [dict(r) for r in rows]


def atualizar_meta(meta_id: int, valor_adicional: float):
    with get_conn() as conn:
        conn.execute(
            "UPDATE metas SET valor_acumulado = valor_acumulado + ? WHERE id = ?",
            (valor_adicional, meta_id),
        )


def inserir_meta(nome: str, valor_total: float, economia_mensal: float, prazo_meses: int) -> int:
    with get_conn() as conn:
        cur = conn.execute(
            "INSERT INTO metas (nome, valor_total, economia_mensal, prazo_meses, criada_em) "
            "VALUES (?, ?, ?, ?, ?)",
            (nome, valor_total, economia_mensal, prazo_meses, datetime.now().isoformat()),
        )
        return cur.lastrowid


def get_config(chave: str, padrao: str = "") -> str:
    with get_conn() as conn:
        row = conn.execute("SELECT valor FROM config WHERE chave = ?", (chave,)).fetchone()
    return row["valor"] if row else padrao


def set_config(chave: str, valor: str):
    with get_conn() as conn:
        conn.execute(
            "INSERT INTO config (chave, valor) VALUES (?, ?) "
            "ON CONFLICT(chave) DO UPDATE SET valor = excluded.valor",
            (chave, valor),
        )
