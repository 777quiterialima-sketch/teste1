import os
from dotenv import load_dotenv

load_dotenv()

VERIFY_TOKEN      = os.getenv("VERIFY_TOKEN", "meu_token_secreto_123")
PAGE_ACCESS_TOKEN = os.getenv("PAGE_ACCESS_TOKEN", "")
INSTAGRAM_ACCOUNT_ID = os.getenv("INSTAGRAM_ACCOUNT_ID", "")
DATABASE_PATH     = os.getenv("DATABASE_PATH", "financas.db")
RENDA_MENSAL      = float(os.getenv("RENDA_MENSAL", "0"))
META_ECONOMIA_PCT = float(os.getenv("META_ECONOMIA_PCT", "20"))
