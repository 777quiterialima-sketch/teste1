# Monitor de preços em PHP

Aplicação simples em PHP e SQLite para acompanhar o histórico de preços de produtos. Permite cadastrar páginas de produto, definir a loja onde o preço será capturado automaticamente e visualizar a evolução em tabela e gráfico.

## Requisitos

- PHP 8.0 ou superior com extensões `pdo_sqlite` e `sqlite3` habilitadas.
- Permissão de escrita no diretório `data/` (criado automaticamente na primeira execução).

## Como usar

1. Coloque todos os arquivos em um servidor compatível com PHP (Apache, Nginx + PHP-FPM ou o servidor embutido do PHP).
2. Acesse `index.php` no navegador.
3. Cadastre um produto informando:
   - Nome amigável para identificação.
   - URL completa da página de produto.
   - Loja onde a coleta acontecerá (por enquanto, **Casas Bahia**, que usa o elemento com id `product-price`).
4. Clique em **Atualizar agora** para que o sistema faça o download da página, extraia o preço de acordo com o identificador configurado para a loja e salve no histórico.
5. Abra **Ver histórico** para consultar a lista de coletas e um gráfico com a evolução do preço.

## Extensões futuras

- Outras lojas podem ser adicionadas definindo seletores específicos para cada página.
- É possível habilitar novamente o campo de expressão regular para cenários personalizados.

## Segurança

- As expressões regulares são validadas antes de salvar.
- O agente de coleta usa um `User-Agent` comum para reduzir bloqueios, mas recomenda-se respeitar os termos de uso do site monitorado.
