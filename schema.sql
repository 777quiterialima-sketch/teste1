CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    water_goal_cups INT NOT NULL DEFAULT 8,
    reading_goal_minutes INT NOT NULL DEFAULT 30,
    workout_goal_days_week INT NOT NULL DEFAULT 3,
    english_goal_minutes INT NOT NULL DEFAULT 15,
    nicotine_weekly_reduction_target INT NOT NULL DEFAULT 5,
    work_start_1 TIME NOT NULL DEFAULT '11:00:00',
    work_end_1 TIME NOT NULL DEFAULT '15:00:00',
    work_start_2 TIME NOT NULL DEFAULT '19:00:00',
    work_end_2 TIME NOT NULL DEFAULT '22:00:00',
    high_energy_start TIME NOT NULL DEFAULT '06:00:00',
    high_energy_end TIME NOT NULL DEFAULT '10:00:00',
    sergeant_tone VARCHAR(20) NOT NULL DEFAULT 'Firme',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS daily_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    log_date DATE NOT NULL,
    water_cups INT NOT NULL DEFAULT 0,
    reading_minutes INT NOT NULL DEFAULT 0,
    workout_done TINYINT(1) NOT NULL DEFAULT 0,
    workout_minutes INT NOT NULL DEFAULT 0,
    english_done TINYINT(1) NOT NULL DEFAULT 0,
    english_minutes INT NOT NULL DEFAULT 0,
    market_done TINYINT(1) NOT NULL DEFAULT 0,
    meal_prep_done TINYINT(1) NOT NULL DEFAULT 0,
    nicotine_puffs INT NOT NULL DEFAULT 0,
    notes TEXT,
    score INT NOT NULL DEFAULT 0,
    status_color ENUM('VERDE','AMARELO','VERMELHO') NOT NULL DEFAULT 'VERMELHO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_date (user_id, log_date),
    KEY idx_user_date (user_id, log_date),
    KEY idx_status (user_id, status_color, log_date),
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
