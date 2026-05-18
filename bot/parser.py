"""
Parser de mensagens de gastos em português brasileiro.

Exemplos aceitos:
  "gastei 50 no mercado"
  "almoço 35,90"
  "uber 18.50"
  "R$ 120 conta de luz"
  "netflix 45 lazer"
  "farmácia 32,00 saúde"
"""

import re
from dataclasses import dataclass

CATEGORIAS: dict[str, list[str]] = {
    "Alimentação": [
        "mercado", "supermercado", "almoço", "almoco", "jantar", "lanche",
        "feira", "padaria", "açougue", "acougue", "comida", "pizza", "sushi",
        "hamburguer", "hamburger", "restaurante", "ifood", "rappi", "delivery",
        "café", "cafe", "pão", "pao", "fruta", "hortifruti", "refeição",
        "refeicao", "marmita", "sorveteria", "sorvete", "lanchonete",
    ],
    "Transporte": [
        "uber", "99", "ônibus", "onibus", "metro", "metrô", "gasolina",
        "estacionamento", "pedágio", "pedagio", "passagem", "trem", "táxi",
        "taxi", "combustível", "combustivel", "moto", "bicicleta", "bike",
        "van", "transporte", "condução", "conducao",
    ],
    "Habitação": [
        "aluguel", "condomínio", "condominio", "reforma", "iptu", "imóvel",
        "imovel", "casa", "apartamento", "habitação", "habitacao",
    ],
    "Saúde": [
        "farmácia", "farmacia", "médico", "medico", "dentista", "consulta",
        "remédio", "remedio", "hospital", "exame", "plano", "saúde", "saude",
        "psicólogo", "psicologo", "academia", "nutricionista",
    ],
    "Contas": [
        "luz", "água", "agua", "internet", "telefone", "celular", "gás", "gas",
        "conta", "boleto", "tim", "claro", "vivo", "oi", "net", "fatura",
    ],
    "Lazer": [
        "cinema", "teatro", "show", "bar", "balada", "netflix", "spotify",
        "streaming", "disney", "hbo", "prime", "amazon", "jogos", "game",
        "viagem", "hotel", "passeio", "praia", "lazer", "entretenimento",
    ],
    "Educação": [
        "curso", "livro", "escola", "faculdade", "universidade", "apostila",
        "material", "udemy", "alura", "coursera", "mensalidade", "aula",
        "educação", "educacao",
    ],
    "Compras": [
        "roupa", "sapato", "shopping", "presente", "perfume", "cosmético",
        "cosmetico", "maquiagem", "eletrônico", "eletronico", "celular novo",
        "notebook", "acessório", "acessorio",
    ],
}

ESSENCIAIS = {"Alimentação", "Transporte", "Habitação", "Saúde", "Contas"}

_AMOUNT_RE = re.compile(
    r"R\$\s*(\d{1,6}(?:[.,]\d{1,2})?)"  # R$ 50,00
    r"|(\d{1,6}(?:[.,]\d{1,2})?)\s*(?:reais|real|r\$)?",
    re.IGNORECASE,
)


@dataclass
class GastoParseado:
    valor: float
    descricao: str
    categoria: str
    essencial: bool


def _parse_valor(texto: str) -> tuple[float | None, str]:
    """Extrai o primeiro valor monetário e retorna o resto do texto."""
    for m in _AMOUNT_RE.finditer(texto):
        raw = m.group(1) or m.group(2)
        if not raw:
            continue
        raw = raw.replace(",", ".")
        try:
            valor = float(raw)
        except ValueError:
            continue
        if valor <= 0:
            continue
        restante = (texto[: m.start()] + texto[m.end() :]).strip()
        restante = re.sub(r"\s+", " ", restante)
        return valor, restante
    return None, texto


def _detectar_categoria(texto: str) -> str:
    lower = texto.lower()
    # Conta pontuação de palavras-chave por categoria
    scores: dict[str, int] = {cat: 0 for cat in CATEGORIAS}
    for cat, keywords in CATEGORIAS.items():
        for kw in keywords:
            if kw in lower:
                scores[cat] += len(kw)  # palavras mais longas pesam mais
    melhor = max(scores, key=lambda c: scores[c])
    return melhor if scores[melhor] > 0 else "Outros"


def _limpar_descricao(texto: str) -> str:
    ruido = r"\b(gastei|gasto|paguei|pago|comprei|foi|de|no|na|num|numa|com|para|pro|pra|o|a|um|uma|em|por)\b"
    texto = re.sub(ruido, "", texto, flags=re.IGNORECASE)
    return re.sub(r"\s+", " ", texto).strip().title() or "Gasto"


def parsear(mensagem: str) -> GastoParseado | None:
    """Tenta parsear uma mensagem de gasto. Retorna None se não conseguir."""
    texto = mensagem.strip()

    valor, restante = _parse_valor(texto)
    if valor is None:
        return None

    categoria = _detectar_categoria(restante or texto)
    descricao = _limpar_descricao(restante) if restante else categoria
    if not descricao or descricao == "":
        descricao = categoria

    return GastoParseado(
        valor=valor,
        descricao=descricao,
        categoria=categoria,
        essencial=categoria in ESSENCIAIS,
    )
