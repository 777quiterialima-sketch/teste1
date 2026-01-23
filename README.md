# OrganizzeLite (MVP)

Dashboard financeiro em PHP + MySQL inspirado no Organizze, pronto para hospedagem compartilhada (HostGator/cPanel).

## ✅ Requisitos
- PHP 8+
- MySQL 5.7+
- Apache com mod_rewrite habilitado

## ✅ Instalação no cPanel (HostGator)
1. **Crie o banco de dados:**
   - Acesse **MySQL® Databases** no cPanel.
   - Crie um banco (ex.: `organizze_lite`).
   - Crie um usuário e atribua **todas as permissões** ao banco.

2. **Envie os arquivos via FTP:**
   - Envie toda a pasta do projeto para `public_html/` (ou subpasta).
   - Se instalar em **subpasta**, ajuste o `app_url` para incluir o caminho (ex.: `https://seudominio.com/organizze`).

3. **Configure o arquivo de conexão:**
   - Copie `config/config.php.example` para `config/config.php`.
   - Preencha com as credenciais do banco e URL da aplicação:
     ```php
     'app_url' => 'https://seudominio.com/organizze',
     'db' => [
         'host' => 'localhost',
         'name' => 'seu_banco',
         'user' => 'seu_usuario',
         'pass' => 'sua_senha',
     ]
     ```

4. **Crie as tabelas:**
   - Importe `database/schema.sql` no phpMyAdmin.
   - (Opcional) Importe `database/seed.sql` para dados de demonstração.

5. **Acesse o sistema:**
   - URL de acesso: `https://seudominio.com/login`
   - Usuário demo (seed): `admin@demo.com` | senha: `admin123`

## Estrutura do projeto
```
/
  index.php
  .htaccess
  config/
  app/
    controllers/
    models/
    views/
    helpers/
  assets/
    css/
    js/
  storage/
    logs/
    uploads/
  database/
    schema.sql
    seed.sql
```

## Recursos do MVP
- Cadastro/login/logout
- Dashboard com cards, saldo geral, contas e cartões
- Lançamentos (receitas/despesas/transferências)
- Relatórios básicos com gráficos em JS
- Limites de gastos (por categoria e total)
- Conexão bancária manual + importação CSV/OFX
- Multiusuário (isolamento por `user_id`)

## Dicas de uso
- O saldo atual é calculado automaticamente a partir dos lançamentos.
- Importações CSV permitem mapear colunas via formulário.
- Logs simples são gravados em `storage/logs/app.log`.
