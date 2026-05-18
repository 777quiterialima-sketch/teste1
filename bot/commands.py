"""
Interpreta comandos e mensagens de texto enviados no DM do Instagram
e retorna a resposta textual + persiste no banco.
"""

from datetime import date
import database as db
import parser as p
from config import RENDA_MENSAL, META_ECONOMIA_PCT


AJUDA = """💚 *Organizador Financeiro* — Comandos:

💸 *Registrar gasto:*
  "mercado 150"
  "uber 22,50"
  "gastei 80 no almoço"
  "netflix 45 lazer"

📋 *Consultas:*
  /saldo     → saldo do mês
  /relatorio → gastos por categoria
  /metas     → progresso das metas
  /ultimos   → últimos 10 gastos

🎯 *Metas:*
  /novameta  → adicionar meta

❓ /ajuda    → este menu"""


def _barra_progresso(pct: float, largura: int = 15) -> str:
    pct = max(0, min(100, pct))
    preenchido = round(pct / 100 * largura)
    return "█" * preenchido + "░" * (largura - preenchido)


def cmd_saldo() -> str:
    total = db.total_gasto_mes()
    renda = RENDA_MENSAL
    hoje = date.today()

    if renda > 0:
        pct_gasto = total / renda * 100
        saldo = renda - total
        meta_economia = renda * META_ECONOMIA_PCT / 100
        barra = _barra_progresso(pct_gasto)
        return (
            f"📊 *{hoje.strftime('%B/%Y').capitalize()}*\n\n"
            f"💼 Renda:   R$ {renda:,.2f}\n"
            f"💸 Gastado: R$ {total:,.2f} ({pct_gasto:.1f}%)\n"
            f"💰 Saldo:   R$ {saldo:,.2f}\n\n"
            f"{barra} {pct_gasto:.0f}%\n\n"
            f"🎯 Meta de economia: R$ {meta_economia:,.2f}/mês"
        )
    else:
        return (
            f"📊 *{hoje.strftime('%B/%Y').capitalize()}*\n\n"
            f"💸 Total gasto este mês: R$ {total:,.2f}\n\n"
            f"💡 Dica: configure RENDA_MENSAL no .env para ver % de gasto."
        )


def cmd_relatorio() -> str:
    por_cat = db.total_por_categoria()
    if not por_cat:
        return "📋 Nenhum gasto registrado este mês ainda."

    total = sum(por_cat.values())
    renda = RENDA_MENSAL or total

    linhas = [f"📋 *Relatório — {date.today().strftime('%B/%Y').capitalize()}*\n"]
    for cat, valor in sorted(por_cat.items(), key=lambda x: -x[1]):
        pct = valor / renda * 100
        barra = _barra_progresso(pct, 10)
        linhas.append(f"{barra} {cat}\n   R$ {valor:,.2f} ({pct:.1f}%)")

    linhas.append(f"\n💸 Total: R$ {total:,.2f}")
    return "\n".join(linhas)


def cmd_ultimos() -> str:
    gastos = db.ultimos_gastos(10)
    if not gastos:
        return "📝 Nenhum gasto registrado ainda."

    linhas = ["📝 *Últimos gastos:*\n"]
    for g in gastos:
        data_fmt = g["data"][8:10] + "/" + g["data"][5:7]
        linhas.append(f"• {data_fmt} — {g['descricao']} ({g['categoria']}): R$ {g['valor']:,.2f}")
    return "\n".join(linhas)


def cmd_metas() -> str:
    metas = db.listar_metas()
    if not metas:
        return "🎯 Nenhuma meta cadastrada. Use /novameta para criar uma."

    linhas = ["🎯 *Suas metas:*\n"]
    for m in metas:
        pct = m["valor_acumulado"] / m["valor_total"] * 100 if m["valor_total"] > 0 else 0
        faltam = m["valor_total"] - m["valor_acumulado"]
        barra = _barra_progresso(pct)
        linhas.append(
            f"*{m['nome']}*\n"
            f"{barra} {pct:.0f}%\n"
            f"  Acumulado: R$ {m['valor_acumulado']:,.2f} / R$ {m['valor_total']:,.2f}\n"
            f"  Faltam: R$ {faltam:,.2f} ({m['prazo_meses']}x R$ {m['economia_mensal']:,.2f}/mês)"
        )
    return "\n".join(linhas)


# Estado de conversa simples por usuário (em memória)
_estado_usuario: dict[str, dict] = {}


def processar(sender_id: str, texto: str) -> str:
    texto = texto.strip()
    estado = _estado_usuario.get(sender_id, {})

    # --- Fluxo de criação de meta ---
    if estado.get("aguardando") == "meta_nome":
        _estado_usuario[sender_id] = {"aguardando": "meta_valor", "nome": texto}
        return f"✅ Nome: *{texto}*\n\nQual o valor total da meta? (ex: 3000)"

    if estado.get("aguardando") == "meta_valor":
        gasto = p.parsear(texto)
        if not gasto:
            return "❌ Não entendi o valor. Digite só o número, ex: 3000"
        _estado_usuario[sender_id] = {**estado, "aguardando": "meta_prazo", "valor": gasto.valor}
        return f"✅ Valor: R$ {gasto.valor:,.2f}\n\nEm quantos meses quer atingir? (ex: 6)"

    if estado.get("aguardando") == "meta_prazo":
        try:
            meses = int(re.sub(r"\D", "", texto))
        except Exception:
            return "❌ Digite só o número de meses, ex: 6"
        nome = estado["nome"]
        valor = estado["valor"]
        economia = valor / meses
        db.inserir_meta(nome, valor, economia, meses)
        _estado_usuario.pop(sender_id, None)
        return (
            f"🎯 Meta criada!\n\n"
            f"*{nome}*\n"
            f"Valor: R$ {valor:,.2f}\n"
            f"Prazo: {meses} meses\n"
            f"Economize: R$ {economia:,.2f}/mês"
        )

    # --- Comandos ---
    cmd = texto.lower().strip()

    if cmd in ("/ajuda", "/help", "ajuda", "help", "oi", "olá", "ola"):
        return AJUDA

    if cmd == "/saldo":
        return cmd_saldo()

    if cmd in ("/relatorio", "/relatório", "/gastos"):
        return cmd_relatorio()

    if cmd in ("/ultimos", "/últimos", "/recentes"):
        return cmd_ultimos()

    if cmd in ("/metas", "/meta"):
        return cmd_metas()

    if cmd in ("/novameta", "/nova meta"):
        _estado_usuario[sender_id] = {"aguardando": "meta_nome"}
        return "🎯 Vamos criar uma nova meta!\n\nQual o nome da meta? (ex: Férias no Nordeste)"

    # --- Tentativa de parsear gasto ---
    gasto = p.parsear(texto)
    if gasto:
        gasto_id = db.inserir_gasto(
            valor=gasto.valor,
            descricao=gasto.descricao,
            categoria=gasto.categoria,
            essencial=gasto.essencial,
        )
        emoji = "🏠" if gasto.categoria == "Habitação" else \
                "🍽️" if gasto.categoria == "Alimentação" else \
                "🚌" if gasto.categoria == "Transporte" else \
                "💊" if gasto.categoria == "Saúde" else \
                "⚡" if gasto.categoria == "Contas" else \
                "🎬" if gasto.categoria == "Lazer" else \
                "📚" if gasto.categoria == "Educação" else \
                "🛍️" if gasto.categoria == "Compras" else "💸"

        total_mes = db.total_gasto_mes()
        renda = RENDA_MENSAL

        resposta = (
            f"✅ Gasto registrado! #{gasto_id}\n\n"
            f"{emoji} {gasto.descricao}\n"
            f"💰 R$ {gasto.valor:,.2f}\n"
            f"📂 {gasto.categoria}"
        )

        if renda > 0:
            pct = total_mes / renda * 100
            resposta += f"\n\n📊 Total do mês: R$ {total_mes:,.2f} ({pct:.1f}% da renda)"

        resposta += "\n\n💡 /saldo para ver o resumo"
        return resposta

    # --- Não entendeu ---
    return (
        "🤔 Não entendi. Para registrar um gasto, tente:\n\n"
        "  \"mercado 150\"\n"
        "  \"uber 22,50\"\n"
        "  \"gastei 80 no almoço\"\n\n"
        "Digite /ajuda para ver todos os comandos."
    )


# Importação atrasada para evitar circular
import re  # noqa: E402 — usado em cmd_novameta acima
