# Monitor de preços em PHP

Aplicação simples em PHP e SQLite para acompanhar o histórico de preços de produtos. Permite cadastrar páginas de produto, definir como extrair o valor via expressão regular, executar coletas pontuais e visualizar a evolução em tabela e gráfico.

## Requisitos

- PHP 8.0 ou superior com extensões `pdo_sqlite` e `sqlite3` habilitadas.
- Permissão de escrita no diretório `data/` (criado automaticamente na primeira execução).

## Como usar

1. Coloque todos os arquivos em um servidor compatível com PHP (Apache, Nginx + PHP-FPM ou o servidor embutido do PHP).
2. Acesse `index.php` no navegador.
3. Cadastre um produto informando:
   - Nome amigável para identificação.
   - URL completa da página de produto.
   - Expressão regular capaz de encontrar o preço na página (por exemplo: `/R\\$\s*([0-9.,]+)/`).
4. Clique em **Atualizar agora** para que o sistema faça o download da página, extraia o preço e salve no histórico.
5. Abra **Ver histórico** para consultar a lista de coletas e um gráfico com a evolução do preço.

## Dicas para as expressões regulares

- Use um grupo de captura para isolar apenas os números (`([0-9.,]+)`), facilitando a conversão para número.
- Adapte o padrão ao HTML da loja. Utilize o inspector do navegador para localizar o trecho que contém o preço.
- É possível monitorar valores em outras moedas (US$, €, £). O símbolo localizado na captura será exibido nos relatórios.

## Segurança

- As expressões regulares são validadas antes de salvar.
- O agente de coleta usa um `User-Agent` comum para reduzir bloqueios, mas recomenda-se respeitar os termos de uso do site monitorado.
