CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    type VARCHAR(40) NOT NULL,
    initial_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    color VARCHAR(20) DEFAULT '#2ecc71',
    icon VARCHAR(20) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_accounts_user (user_id),
    CONSTRAINT fk_accounts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    brand VARCHAR(60) NOT NULL,
    limit_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    closing_day INT NOT NULL DEFAULT 1,
    due_day INT NOT NULL DEFAULT 10,
    created_at DATETIME NOT NULL,
    INDEX idx_cards_user (user_id),
    CONSTRAINT fk_cards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(120) NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'ambos',
    INDEX idx_categories_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    category_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    account_id INT DEFAULT NULL,
    account_dest_id INT DEFAULT NULL,
    payment_method VARCHAR(20) DEFAULT NULL,
    card_id INT DEFAULT NULL,
    tags VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    installment_group VARCHAR(50) DEFAULT NULL,
    installment_number INT DEFAULT NULL,
    installment_total INT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_transactions_user (user_id),
    INDEX idx_transactions_date (date),
    CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_transactions_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_transactions_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    CONSTRAINT fk_transactions_account_dest FOREIGN KEY (account_dest_id) REFERENCES accounts(id) ON DELETE SET NULL,
    CONSTRAINT fk_transactions_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE card_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_id INT NOT NULL,
    month CHAR(7) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'aberta',
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_invoice (user_id, card_id, month),
    CONSTRAINT fk_invoice_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE card_invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    transaction_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES card_invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_items_transaction FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    month CHAR(7) NOT NULL,
    limit_amount DECIMAL(12,2) NOT NULL,
    INDEX idx_budgets_user (user_id),
    CONSTRAINT fk_budgets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_budgets_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bank_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    type VARCHAR(40) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_connections_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_connections_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE imports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_imports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
