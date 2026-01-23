# Finance Flask - Dashboard Financeiro Local

Dashboard financeiro local (MVP) inspirado no Organizze, com Flask + SQLite.

## Requisitos
- Windows 10/11
- Python 3.11+

## Instalação
```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
```

## Inicializar banco de dados
### Opção 1: Migrações (recomendado)
```bash
flask --app app.py db init
flask --app app.py db migrate -m "init"
flask --app app.py db upgrade
```

### Opção 2: Criação automática (fallback)
Ao iniciar a aplicação pela primeira vez, o banco será criado automaticamente (com seeds iniciais) se não existir.

## Executar
```bash
flask --app app.py run
```
Ou:
```bash
python app.py
```
Acesse: http://127.0.0.1:5000

## Banco de dados
- Caminho padrão: `instance/finance.db`
- Para backup, copie o arquivo `instance/finance.db`.

## Usuário inicial
Ao registrar o primeiro usuário, o sistema cria categorias padrão e dados de exemplo.

## Estrutura do projeto
```
finance_flask/
  app.py
  config.py
  requirements.txt
  README.md
  instance/
    finance.db
  app/
    __init__.py
    extensions.py
    models.py
    services/
      finance.py
      imports.py
    routes/
      auth.py
      dashboard.py
      accounts.py
      cards.py
      transactions.py
      reports.py
      budgets.py
      connections.py
    templates/
      base.html
      auth/
        login.html
        register.html
      dashboard/
        index.html
      accounts/
        list.html
        form.html
      cards/
        list.html
        form.html
        invoice.html
      transactions/
        list.html
        form.html
      reports/
        index.html
      budgets/
        index.html
      connections/
        index.html
        import_review.html
    static/
      css/style.css
      js/main.js
```
