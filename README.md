# Monitor de preços (Python)

Aplicação web simples para cadastrar produtos, buscar o preço atual diretamente do site escolhido e acompanhar o histórico de variação. Foi reescrita em Python com Flask para oferecer mais flexibilidade e facilidade de manutenção.

## Recursos

- Cadastro de produtos com URL e loja alvo (atualmente Casas Bahia).
- Atualização manual do preço com captura automática do valor na página.
- Histórico de preços armazenado em SQLite com visualização tabular.
- Interface responsiva e amigável para desktop e mobile.
- Script de linha de comando para atualizar todos os preços em lote.

## Requisitos

- Python 3.11 ou superior.
- Dependências listadas em `requirements.txt`.

Instale-as com:

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

## Como usar

1. Inicialize o banco e execute a aplicação:
   ```bash
   flask --app app run --debug
   ```
   Por padrão ela ficará disponível em `http://127.0.0.1:5000`.

2. Abra o endereço no navegador, cadastre um produto (por exemplo, um link da Casas Bahia) e clique em **Atualizar agora** para capturar o preço.

3. Consulte o histórico completo clicando em **Ver histórico**.

### Atualização pela linha de comando

O script `fetch_price_cli.py` permite atualizar todos os produtos de uma vez ou apenas um produto específico:

```bash
python fetch_price_cli.py           # atualiza todos
python fetch_price_cli.py --product-id 1  # atualiza apenas o produto 1
```

Os valores capturados são salvos automaticamente no histórico.

## Sobre a captura de preços

Para a loja Casas Bahia o sistema tenta, nesta ordem:

1. Ler o conteúdo do elemento com `id="product-price"`.
2. Buscar metatags (`og:price:amount`, `product:price:amount`, `itemprop=price`).
3. Ler objetos JSON-LD com ofertas e preço numérico.
4. Examinar scripts embutidos que contenham valores como `sellingPrice`.

Sempre que um valor bruto é encontrado ele é normalizado para o formato numérico brasileiro e armazenado junto com o histórico.

> **Observação:** Este ambiente não possui acesso à internet. Para testar a captura de preços utilize a aplicação em uma máquina com conexão externa.

## Estrutura do projeto

```
app.py               # Aplicação Flask
price_fetcher.py     # Download e extração de preços por loja
fetch_price_cli.py   # Script de linha de comando
database.py          # Funções utilitárias para o SQLite
templates/           # HTML renderizado pelo Flask
static/              # Arquivos de estilo
requirements.txt     # Dependências Python
```

O banco SQLite (`price_monitor.db`) é criado automaticamente ao iniciar a aplicação.
