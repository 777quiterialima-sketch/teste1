# Organizador Financeiro (MVP)

Sistema web em PHP 8+ + MySQL para controle financeiro pessoal, pronto para hospedagem compartilhada (HostGator cPanel). Não utiliza Node, Docker ou serviços externos.

## ✅ Recursos do MVP
- Login/registro e perfil (alterar nome/senha).
- Dashboard com resumo de receitas, despesas, saldo, contas e cartões.
- Lançamentos (receitas, despesas, transferências e parcelas no cartão).
- Contas, cartões e faturas.
- Limite de gastos mensal (total e por categoria).
- Relatórios básicos com gráficos (JS puro).
- Conexão bancária MVP: manual + importação CSV/OFX.
- Multiusuário com isolamento de dados.

---

## 📂 Estrutura
```
/
  index.php
  .htaccess
  config/
    config.php
    config.php.example
  app/
    controllers/
    models/
    views/
    helpers/
    core/
  assets/
    css/
    js/
  storage/
    logs/
    uploads/
  database/
    schema.sql
    seed.sql
  README.md
```

---

## ⚙️ Configuração no cPanel (HostGator)

1. **Crie o banco de dados**
   - Acesse o cPanel > *MySQL Databases*.
   - Crie um banco (ex: `organizze_db`).
   - Crie usuário e senha e associe ao banco com **ALL PRIVILEGES**.

2. **Configure o arquivo `config/config.php`**
   - Copie `config/config.php.example` para `config/config.php`.
   - Preencha com o host, banco, usuário e senha.
   - Se a aplicação estiver em subpasta (ex: `https://seudominio.com/financeiro`), defina:
     - `base_url` como `https://seudominio.com/financeiro`
     - `base_path` como `/financeiro`

3. **Importe o schema**
   - No cPanel, acesse **phpMyAdmin**.
   - Selecione o banco e execute o conteúdo de `database/schema.sql`.

4. **Opcional: insira dados de seed**
   - Execute `database/seed.sql` para ter dados de demonstração.
   - Usuário seed: **admin@demo.com** / **admin123**.

5. **Upload via FTP**
   - Envie todos os arquivos para a pasta pública (ex: `public_html`).

---

## 🚀 Execução local (opcional)
```bash
php -S localhost:8000
```
Acesse: `http://localhost:8000`

---

## ✅ Segurança implementada
- PDO + prepared statements
- CSRF em formulários
- Hash de senha com `password_hash`
- Escapando output com `htmlspecialchars`
- Logs simples em `storage/logs/app.log`

---

## 📌 Observações
- A importação CSV permite informar o índice das colunas (data, descrição, valor e tipo).
- A importação OFX usa parser simples (MVP).
- O layout segue visual limpo inspirado no Organizze.

---

## 🛠️ Ajustes rápidos
- Para alterar cores: `assets/css/style.css`
- Para ajustar gráficos: `assets/js/charts.js`

---

Feito para rodar em hospedagem compartilhada com **PHP puro**.
