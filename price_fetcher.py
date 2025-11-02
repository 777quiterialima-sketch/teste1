"""Utilities to download and extract product prices from supported stores."""
from __future__ import annotations

import json
import re
from dataclasses import dataclass
from decimal import Decimal
from typing import Callable, Optional

import requests
from bs4 import BeautifulSoup

PRICE_PATTERN = re.compile(r"([0-9]{1,3}(?:\.[0-9]{3})*,[0-9]{2}|[0-9]+(?:\.[0-9]{2}))")


@dataclass
class PriceResult:
    amount: Decimal
    currency: str
    raw_value: str


class PriceFetcherError(RuntimeError):
    """Raised when it is not possible to determine a price for a product."""


def normalise_price(value: str) -> Decimal:
    value = value.strip()
    if value.count(",") == 1 and value.count(".") >= 1:
        value = value.replace(".", "").replace(",", ".")
    elif "," in value and "." not in value:
        value = value.replace(".", "").replace(",", ".")
    return Decimal(value)


def download(url: str, timeout: float = 15) -> str:
    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Safari/537.36"
        )
    }
    response = requests.get(url, headers=headers, timeout=timeout, allow_redirects=True)
    response.raise_for_status()
    response.encoding = response.apparent_encoding or response.encoding
    return response.text


def extract_from_json_ld(soup: BeautifulSoup) -> Optional[str]:
    for script in soup.find_all("script", type="application/ld+json"):
        try:
            data = json.loads(script.string or "{}")
        except json.JSONDecodeError:
            continue
        nodes = data if isinstance(data, list) else [data]
        for node in nodes:
            if not isinstance(node, dict):
                continue
            offers = node.get("offers")
            if isinstance(offers, list):
                for offer in offers:
                    price = extract_price_from_offer(offer)
                    if price:
                        return price
            elif isinstance(offers, dict):
                price = extract_price_from_offer(offers)
                if price:
                    return price
    return None


def extract_price_from_offer(offer: dict) -> Optional[str]:
    if not isinstance(offer, dict):
        return None
    price = offer.get("price") or offer.get("priceSpecification", {}).get("price")
    if isinstance(price, (int, float)):
        return f"{price:.2f}"
    if isinstance(price, str):
        return price
    return None


def extract_from_meta(soup: BeautifulSoup) -> Optional[str]:
    meta_props = [
        ("meta", {"property": "og:price:amount"}),
        ("meta", {"property": "product:price:amount"}),
        ("meta", {"itemprop": "price"}),
    ]
    for tag_name, attrs in meta_props:
        tag = soup.find(tag_name, attrs=attrs)
        if tag and tag.get("content"):
            return tag["content"].strip()
    return None


def extract_from_script_state(soup: BeautifulSoup) -> Optional[str]:
    for script in soup.find_all("script"):
        contents = script.string or ""
        if "product-price" in contents or "sellingPrice" in contents:
            match = PRICE_PATTERN.search(contents)
            if match:
                return match.group(1)
    return None


def extract_casas_bahia_price(html: str) -> str:
    soup = BeautifulSoup(html, "html.parser")

    element = soup.find(id="product-price")
    if element:
        text = element.get_text(strip=True)
        if text:
            match = PRICE_PATTERN.search(text)
            if match:
                return match.group(1)
        if element.has_attr("data-price"):
            return element["data-price"].strip()

    for extractor in (extract_from_meta, extract_from_json_ld, extract_from_script_state):
        price = extractor(soup)
        if price:
            return price

    raise PriceFetcherError("Não foi possível identificar o preço na Casas Bahia.")


STORE_HANDLERS: dict[str, Callable[[str], str]] = {
    "casasbahia": extract_casas_bahia_price,
}


def fetch_price(url: str, store: str) -> PriceResult:
    if store not in STORE_HANDLERS:
        raise PriceFetcherError(f"Loja '{store}' não suportada.")

    html = download(url)
    extractor = STORE_HANDLERS[store]
    raw_price = extractor(html)
    amount = normalise_price(raw_price)
    return PriceResult(amount=amount, currency="R$", raw_value=raw_price)
