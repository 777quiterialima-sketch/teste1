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

## Passo a passo no Windows

1. **Instale o Python 3.11** a partir de [python.org](https://www.python.org/downloads/windows/) e marque a opção para adicionar o Python ao `PATH` durante a instalação.
2. (Opcional) **Instale o Git para Windows** para facilitar o download do código.
3. **Abra o PowerShell** (ou Prompt de Comando) na pasta do projeto. Se ainda não clonou o repositório, execute `git clone <URL-do-repo>` e entre no diretório gerado.
4. **Crie o ambiente virtual** executando:
   ```powershell
   py -3.11 -m venv .venv
   ```
   (Se o comando `py` não estiver disponível, use `python -m venv .venv`.)
5. **Ative o ambiente virtual**:
   ```powershell
   .venv\Scripts\Activate
   ```
   O prompt deve indicar que você está dentro do ambiente (`(.venv)` à esquerda).
6. **Instale as dependências**:
   ```powershell
   pip install -r requirements.txt
   ```
7. **Execute a aplicação** com recarregamento automático:
   ```powershell
   flask --app app run --debug
   ```
   A aplicação ficará acessível em `http://127.0.0.1:5000`.
8. **Atualize preços pela linha de comando (opcional)** mantendo o ambiente virtual ativo:
   ```powershell
   python fetch_price_cli.py
   ```
   Use `python fetch_price_cli.py --product-id 1` para atualizar apenas um item específico.

Sempre que abrir um novo terminal, reative o ambiente virtual com `.venv\Scripts\Activate` antes de executar os comandos acima.

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
