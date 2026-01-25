INSERT INTO users (name, email, password) VALUES
('Admin', 'admin@demo.com', '$2y$12$3p8MbrkMdmBXrD3h8LR2/OKic0T2zE7a2ZlGvuAJZpqT9gnLjd4BG');

INSERT INTO categories (user_id, name, type, is_default) VALUES
(NULL, 'Moradia', 'despesa', 1),
(NULL, 'Alimentação', 'despesa', 1),
(NULL, 'Transporte', 'despesa', 1),
(NULL, 'Saúde', 'despesa', 1),
(NULL, 'Lazer', 'despesa', 1),
(NULL, 'Educação', 'despesa', 1),
(NULL, 'Assinaturas', 'despesa', 1),
(NULL, 'Salário', 'receita', 1),
(NULL, 'Extras', 'receita', 1);

INSERT INTO accounts (user_id, name, type, initial_balance, color) VALUES
(1, 'Conta Principal', 'corrente', 2500.00, '#2ebd59'),
(1, 'Carteira', 'carteira', 320.00, '#1f9bcf');

INSERT INTO cards (user_id, name, brand, limit_total, close_day, due_day) VALUES
(1, 'Nubank', 'Mastercard', 4000.00, 5, 15),
(1, 'Inter', 'Visa', 2500.00, 10, 20);

INSERT INTO transactions (user_id, type, date, description, category_id, amount, account_id, payment_method) VALUES
(1, 'income', CURDATE(), 'Salário', 8, 3724.00, 1, 'pix'),
(1, 'expense', CURDATE(), 'Supermercado', 2, 480.00, 1, 'debito'),
(1, 'expense', CURDATE(), 'Streaming', 7, 59.90, 1, 'debito');

INSERT INTO budgets (user_id, category_id, month, limit_amount) VALUES
(1, NULL, DATE_FORMAT(CURDATE(), '%Y-%m'), 5000.00),
(1, 2, DATE_FORMAT(CURDATE(), '%Y-%m'), 1200.00);

INSERT INTO bank_connections (user_id, account_id, bank_name, type, status) VALUES
(1, 1, 'Nubank', 'manual', 'active');
