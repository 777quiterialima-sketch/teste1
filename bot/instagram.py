import httpx
from config import PAGE_ACCESS_TOKEN

GRAPH_API = "https://graph.facebook.com/v19.0"


async def enviar_mensagem(recipient_id: str, texto: str):
    """Envia uma mensagem via Instagram Graph API."""
    if not PAGE_ACCESS_TOKEN:
        print(f"[BOT → {recipient_id}]: {texto}")
        return

    url = f"{GRAPH_API}/me/messages"
    payload = {
        "recipient": {"id": recipient_id},
        "message": {"text": texto},
        "messaging_type": "RESPONSE",
    }
    params = {"access_token": PAGE_ACCESS_TOKEN}

    async with httpx.AsyncClient(timeout=10) as client:
        resp = await client.post(url, json=payload, params=params)

    if resp.status_code != 200:
        print(f"[Instagram API erro {resp.status_code}]: {resp.text}")
