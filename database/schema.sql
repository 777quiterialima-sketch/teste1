CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    type VARCHAR(40) NOT NULL,
    initial_balance DECIMAL(10,2) DEFAULT 0,
    color VARCHAR(20) DEFAULT '#2ebd59',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    brand VARCHAR(60) NOT NULL,
    limit_total DECIMAL(10,2) DEFAULT 0,
    close_day TINYINT NOT NULL,
    due_day TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(120) NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'ambos',
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    category_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    account_id INT NULL,
    account_dest_id INT NULL,
    payment_method VARCHAR(40) NULL,
    card_id INT NULL,
    invoice_month VARCHAR(7) NULL,
    tags VARCHAR(120) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (account_dest_id) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, date),
    INDEX idx_card_month (card_id, invoice_month)
);

CREATE TABLE card_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_id INT NOT NULL,
    month VARCHAR(7) NOT NULL,
    status VARCHAR(20) DEFAULT 'open',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_invoice (card_id, month),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE
);

CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NULL,
    month VARCHAR(7) NOT NULL,
    limit_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_budget (user_id, category_id, month),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE bank_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    bank_name VARCHAR(120) NOT NULL,
    type VARCHAR(40) DEFAULT 'manual',
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

CREATE TABLE imports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
