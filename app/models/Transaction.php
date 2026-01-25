<?php

class Transaction
{
    public static function listByUser(int $userId, array $filters = []): array
    {
        $sql = "SELECT t.*, c.name AS category_name, a.name AS account_name, ad.name AS account_dest_name
                FROM transactions t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN accounts a ON a.id = t.account_id
                LEFT JOIN accounts ad ON ad.id = t.account_dest_id
                WHERE t.user_id = :user_id";
        $params = ['user_id' => $userId];

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['account_id'])) {
            $sql .= " AND (t.account_id = :account_id OR t.account_dest_id = :account_id)";
            $params['account_id'] = $filters['account_id'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND t.date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND t.date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND t.description LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY t.date DESC, t.id DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO transactions (user_id, type, date, description, category_id, amount, account_id, account_dest_id, payment_method, card_id, invoice_month, tags, notes)
             VALUES (:user_id, :type, :date, :description, :category_id, :amount, :account_id, :account_dest_id, :payment_method, :card_id, :invoice_month, :tags, :notes)'
        );
        $stmt->execute($data);
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, int $userId, array $data): void
    {
        $data['id'] = $id;
        $data['user_id'] = $userId;
        $stmt = Database::connection()->prepare(
            'UPDATE transactions SET type = :type, date = :date, description = :description, category_id = :category_id, amount = :amount, account_id = :account_id, account_dest_id = :account_dest_id, payment_method = :payment_method, card_id = :card_id, invoice_month = :invoice_month, tags = :tags, notes = :notes WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute($data);
    }

    public static function find(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM transactions WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $transaction = $stmt->fetch();
        return $transaction ?: null;
    }

    public static function delete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM transactions WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public static function monthlyTotals(int $userId, string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
             FROM transactions
             WHERE user_id = :user_id AND DATE_FORMAT(date, '%Y-%m') = :month"
        );
        $stmt->execute(['user_id' => $userId, 'month' => $month]);
        return $stmt->fetch() ?: ['income' => 0, 'expense' => 0];
    }

    public static function expensesByCategory(int $userId, string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT c.name, SUM(t.amount) as total
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user_id AND t.type = 'expense' AND DATE_FORMAT(t.date, '%Y-%m') = :month
             GROUP BY c.name
             ORDER BY total DESC"
        );
        $stmt->execute(['user_id' => $userId, 'month' => $month]);
        return $stmt->fetchAll();
    }

    public static function incomeExpenseByMonth(int $userId, int $months = 6): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT DATE_FORMAT(date, '%Y-%m') as month,
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
             FROM transactions
             WHERE user_id = :user_id AND date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month"
        );
        $stmt->execute(['user_id' => $userId, 'months' => $months]);
        return $stmt->fetchAll();
    }
}
