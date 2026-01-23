<?php
class Transaction
{
    public static function list(int $userId, array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT t.*, c.name AS category_name, a.name AS account_name, ad.name AS dest_name, ca.name AS card_name
                FROM transactions t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN accounts a ON a.id = t.account_id
                LEFT JOIN accounts ad ON ad.id = t.account_dest_id
                LEFT JOIN cards ca ON ca.id = t.card_id
                WHERE t.user_id = ?';
        $params = [$userId];

        if (!empty($filters['type'])) {
            $sql .= ' AND t.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['account_id'])) {
            $sql .= ' AND (t.account_id = ? OR t.account_dest_id = ?)';
            $params[] = $filters['account_id'];
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['category_id'])) {
            $sql .= ' AND t.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= ' AND t.date >= ?';
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= ' AND t.date <= ?';
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND t.description LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY t.date DESC, t.id DESC LIMIT ? OFFSET ?';
        $stmt = db()->prepare($sql);
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(int $userId, array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) as total FROM transactions t WHERE t.user_id = ?';
        $params = [$userId];

        if (!empty($filters['type'])) {
            $sql .= ' AND t.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['account_id'])) {
            $sql .= ' AND (t.account_id = ? OR t.account_dest_id = ?)';
            $params[] = $filters['account_id'];
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['category_id'])) {
            $sql .= ' AND t.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= ' AND t.date >= ?';
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= ' AND t.date <= ?';
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND t.description LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? (int)$row['total'] : 0;
    }

    public static function find(int $userId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM transactions WHERE user_id = ? AND id = ?');
        $stmt->execute([$userId, $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $stmt = db()->prepare('INSERT INTO transactions (user_id, type, date, description, category_id, amount, account_id, account_dest_id, payment_method, card_id, tags, notes, installment_group, installment_number, installment_total, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $userId,
            $data['type'],
            $data['date'],
            $data['description'],
            $data['category_id'],
            $data['amount'],
            $data['account_id'],
            $data['account_dest_id'],
            $data['payment_method'],
            $data['card_id'],
            $data['tags'],
            $data['notes'],
            $data['installment_group'],
            $data['installment_number'],
            $data['installment_total'],
        ]);
        return (int)db()->lastInsertId();
    }

    public static function update(int $userId, int $id, array $data): void
    {
        $stmt = db()->prepare('UPDATE transactions SET type = ?, date = ?, description = ?, category_id = ?, amount = ?, account_id = ?, account_dest_id = ?, payment_method = ?, card_id = ?, tags = ?, notes = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([
            $data['type'],
            $data['date'],
            $data['description'],
            $data['category_id'],
            $data['amount'],
            $data['account_id'],
            $data['account_dest_id'],
            $data['payment_method'],
            $data['card_id'],
            $data['tags'],
            $data['notes'],
            $id,
            $userId,
        ]);
    }

    public static function delete(int $userId, int $id): void
    {
        $stmt = db()->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public static function sumByTypeForMonth(int $userId, string $type, string $month): float
    {
        $stmt = db()->prepare('SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = ? AND DATE_FORMAT(date, "%Y-%m") = ?');
        $stmt->execute([$userId, $type, $month]);
        $row = $stmt->fetch();
        return $row ? (float)$row['total'] : 0.0;
    }

    public static function totalByCategory(int $userId, string $start, string $end): array
    {
        $stmt = db()->prepare('SELECT c.name, COALESCE(SUM(t.amount),0) AS total
            FROM transactions t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE t.user_id = ? AND t.type = "despesa" AND t.date BETWEEN ? AND ?
            GROUP BY c.name
            ORDER BY total DESC');
        $stmt->execute([$userId, $start, $end]);
        return $stmt->fetchAll();
    }

    public static function totalsLastMonths(int $userId, int $months = 6): array
    {
        $stmt = db()->prepare('SELECT DATE_FORMAT(date, "%Y-%m") as ym,
            SUM(CASE WHEN type = "receita" THEN amount ELSE 0 END) as receita,
            SUM(CASE WHEN type = "despesa" THEN amount ELSE 0 END) as despesa
            FROM transactions
            WHERE user_id = ?
            GROUP BY ym
            ORDER BY ym DESC
            LIMIT ?');
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $months, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }
}
