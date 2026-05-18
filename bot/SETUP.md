# Setup — Bot Financeiro no Instagram

## O que você vai precisar

| Item | Onde criar |
|------|-----------|
| Conta Instagram Business ou Creator | App do Instagram → Configurações → Conta |
| Página do Facebook | facebook.com/pages/create |
| App no Meta Developer Portal | developers.facebook.com |
| ngrok (para testes locais) | ngrok.com/download |
| Python 3.11+ | python.org |

---

## Passo 1 — Converter conta Instagram para Business

1. No app do Instagram: **Configurações → Conta → Mudar para conta profissional**
2. Escolha **Criador** ou **Empresa**
3. Conecte a uma **Página do Facebook** (crie uma se não tiver)

---

## Passo 2 — Criar App no Meta Developer Portal

1. Acesse [developers.facebook.com](https://developers.facebook.com)
2. Clique em **Meus apps → Criar app**
3. Escolha tipo: **Empresa**
4. Adicione o produto **Instagram**
5. Em **Instagram → Configurações básicas**, conecte sua Página do Facebook

---

## Passo 3 — Gerar o Page Access Token

1. No portal: **Instagram → Gerar token de acesso**
2. Autorize as permissões: `instagram_basic`, `instagram_manage_messages`, `pages_messaging`
3. Copie o token gerado

---

## Passo 4 — Configurar o projeto

```bash
# Clone / navegue até a pasta
cd bot/

# Instale dependências
pip install -r requirements.txt

# Copie e preencha o .env
cp .env.example .env
# edite .env com PAGE_ACCESS_TOKEN, INSTAGRAM_ACCOUNT_ID, RENDA_MENSAL
```

---

## Passo 5 — Subir o servidor + ngrok

```bash
# Terminal 1 — inicia o servidor
cd bot/
python main.py

# Terminal 2 — expõe para a internet
ngrok http 8000
```

O ngrok vai gerar uma URL assim:
```
https://abc123.ngrok-free.app
```

---

## Passo 6 — Registrar o webhook na Meta

1. No portal: **Instagram → Webhooks → Adicionar callback URL**
2. URL: `https://abc123.ngrok-free.app/webhook`
3. Token de verificação: o mesmo que está em `VERIFY_TOKEN` no seu `.env`
4. Clique em **Verificar e salvar**
5. Assine o evento: `messages`

---

## Passo 7 — Testar

Abra o DM da sua conta Instagram e mande:
```
oi
mercado 150
uber 22,50
/saldo
/relatorio
```

Acesse o dashboard: [http://localhost:8000](http://localhost:8000)

---

## Comandos disponíveis no DM

| Mensagem | O que faz |
|----------|-----------|
| `mercado 150` | Registra R$150 em Alimentação |
| `uber 22,50` | Registra R$22,50 em Transporte |
| `gastei 80 no almoço` | Registra com descrição |
| `netflix 45 lazer` | Registra com categoria explícita |
| `/saldo` | Resumo do mês |
| `/relatorio` | Gastos por categoria |
| `/ultimos` | Últimos 10 gastos |
| `/metas` | Progresso das metas |
| `/novameta` | Criar nova meta (guia interativo) |
| `/ajuda` | Menu de ajuda |

---

## Estrutura dos arquivos

```
bot/
  main.py        → servidor FastAPI (webhook + dashboard)
  commands.py    → lógica dos comandos do bot
  parser.py      → interpreta mensagens em português
  database.py    → SQLite (gastos, metas, configurações)
  instagram.py   → envia mensagens via Graph API
  config.py      → lê variáveis do .env
  templates/
    dashboard.html → painel web
  requirements.txt
  .env.example
```
