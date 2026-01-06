# Dashboard de Disciplina

## Requisitos
- PHP 8.1+
- MySQL 5.7+ ou MariaDB 10+
- Apache com suporte a `.htaccess`

## Configuração do banco
Edite `app/config.php` e atualize:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

## Instalação
1. Faça upload dos arquivos para a hospedagem.
2. Crie o banco de dados e usuário no painel da hospedagem.
3. Atualize `app/config.php` com os dados do banco.
4. Acesse `https://seudominio.com/install.php` e crie o usuário admin inicial.
5. Após instalar, faça login em `/pages/login.php`.

## Login inicial
O login é o usuário criado no `install.php`.

## Hospedagem (HostGator)
1. Envie os arquivos para `public_html/`.
2. Certifique-se de que o `.htaccess` está presente.
3. Ajuste as credenciais no `app/config.php`.
4. Acesse `install.php` uma única vez e depois remova ou mova o arquivo.

## Estrutura
- `app/`: configuração, helpers, cálculo de score e regras do sargento.
- `pages/`: telas principais.
- `api/`: endpoints JSON.
- `schema.sql`: SQL completo.
- `install.php`: instalador.
