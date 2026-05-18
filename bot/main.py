"""
Servidor principal do bot financeiro do Instagram.

Endpoints:
  GET  /webhook   → verificação do webhook pela Meta
  POST /webhook   → recebimento de mensagens
  GET  /          → dashboard web
  GET  /api/dados → dados JSON para o dashboard
"""

from fastapi import FastAPI, Request, Response, HTTPException
from fastapi.responses import HTMLResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from datetime import date
import uvicorn

import database as db
import commands
from instagram import enviar_mensagem
from config import VERIFY_TOKEN

app = FastAPI(title="Bot Financeiro Instagram")
app.mount("/static", StaticFiles(directory="static"), name="static")
templates = Jinja2Templates(directory="templates")

db.init_db()


# ─── Webhook Instagram ────────────────────────────────────────────────────────

@app.get("/webhook")
async def verificar_webhook(
    hub_mode: str | None = None,
    hub_challenge: str | None = None,
    hub_verify_token: str | None = None,
):
    """Meta chama este endpoint para verificar o webhook."""
    if hub_mode == "subscribe" and hub_verify_token == VERIFY_TOKEN:
        return Response(content=hub_challenge, media_type="text/plain")
    raise HTTPException(status_code=403, detail="Token inválido")


@app.post("/webhook")
async def receber_mensagem(request: Request):
    """Recebe eventos do Instagram Messaging."""
    body = await request.json()

    if body.get("object") != "instagram":
        return JSONResponse({"status": "ignored"})

    for entry in body.get("entry", []):
        for evento in entry.get("messaging", []):

            # Ignora mensagens enviadas pelo próprio bot (echo)
            if evento.get("message", {}).get("is_echo"):
                continue

            sender_id = evento.get("sender", {}).get("id")
            texto = evento.get("message", {}).get("text", "")

            if not sender_id or not texto:
                continue

            resposta = commands.processar(sender_id, texto)
            await enviar_mensagem(sender_id, resposta)

    return JSONResponse({"status": "ok"})


# ─── Dashboard Web ────────────────────────────────────────────────────────────

@app.get("/", response_class=HTMLResponse)
async def dashboard(request: Request):
    return templates.TemplateResponse("dashboard.html", {"request": request})


@app.get("/api/dados")
async def dados_dashboard():
    hoje = date.today()
    por_cat = db.total_por_categoria()
    metas = db.listar_metas()
    ultimos = db.ultimos_gastos(15)
    total = sum(por_cat.values())

    from config import RENDA_MENSAL, META_ECONOMIA_PCT
    renda = RENDA_MENSAL
    meta_economia = renda * META_ECONOMIA_PCT / 100 if renda > 0 else 0

    return {
        "mes": hoje.strftime("%B/%Y"),
        "renda": renda,
        "total_gasto": total,
        "saldo": renda - total if renda > 0 else None,
        "pct_gasto": (total / renda * 100) if renda > 0 else None,
        "meta_economia": meta_economia,
        "por_categoria": por_cat,
        "metas": metas,
        "ultimos_gastos": ultimos,
    }


# ─── Entrypoint ───────────────────────────────────────────────────────────────

if __name__ == "__main__":
    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)
