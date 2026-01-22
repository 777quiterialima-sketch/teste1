INSERT INTO users (id, name, email, password_hash, created_at)
VALUES (1, 'Admin', 'admin@demo.com', '$2y$12$ldFqznv5Xp5mzRCK7qjju.OOl6ShB7bKXmJolGdz7KSDil1hlRbeK', NOW());

INSERT INTO categories (user_id, name, type) VALUES
(NULL, 'Moradia', 'despesa'),
(NULL, 'Alimentação', 'despesa'),
(NULL, 'Transporte', 'despesa'),
(NULL, 'Saúde', 'despesa'),
(NULL, 'Lazer', 'despesa'),
(NULL, 'Educação', 'despesa'),
(NULL, 'Assinaturas', 'despesa'),
(NULL, 'Salário', 'receita'),
(NULL, 'Extras', 'ambos');

INSERT INTO accounts (user_id, name, type, initial_balance, color, icon, created_at) VALUES
(1, 'Nubank', 'corrente', 2500.00, '#8e44ad', '💳', NOW()),
(1, 'Caixa', 'poupanca', 1500.00, '#27ae60', '🏦', NOW()),
(1, 'Carteira', 'carteira', 320.00, '#f39c12', '👛', NOW());

INSERT INTO cards (user_id, name, brand, limit_total, closing_day, due_day, created_at) VALUES
(1, 'Nubank Ultravioleta', 'Mastercard', 5000.00, 5, 15, NOW()),
(1, 'Inter Black', 'Visa', 8000.00, 8, 18, NOW());

INSERT INTO transactions (user_id, type, date, description, category_id, amount, account_id, account_dest_id, payment_method, card_id, tags, notes, created_at)
VALUES
(1, 'receita', CURDATE(), 'Salário', (SELECT id FROM categories WHERE name='Salário' LIMIT 1), 6500.00, 1, NULL, 'pix', NULL, 'mensal', 'Recebimento mensal', NOW()),
(1, 'despesa', CURDATE(), 'Supermercado', (SELECT id FROM categories WHERE name='Alimentação' LIMIT 1), 420.00, 1, NULL, 'debito', NULL, 'mercado', 'Compra semanal', NOW()),
(1, 'despesa', CURDATE(), 'Assinatura streaming', (SELECT id FROM categories WHERE name='Assinaturas' LIMIT 1), 55.90, 1, NULL, 'cartao', 1, 'streaming', 'Plano mensal', NOW());

INSERT INTO budgets (user_id, category_id, month, limit_amount) VALUES
(1, (SELECT id FROM categories WHERE name='Alimentação' LIMIT 1), DATE_FORMAT(CURDATE(), '%Y-%m'), 1200.00),
(1, NULL, DATE_FORMAT(CURDATE(), '%Y-%m'), 4500.00);

INSERT INTO bank_connections (user_id, account_id, name, type, created_at) VALUES
(1, 1, 'Nubank Manual', 'manual', NOW());
