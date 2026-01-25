<?php

class CardInvoice
{
    public static function findOrCreate(int $cardId, int $userId, string $month): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM card_invoices WHERE card_id = :card_id AND user_id = :user_id AND month = :month');
        $stmt->execute(['card_id' => $cardId, 'user_id' => $userId, 'month' => $month]);
        $invoice = $stmt->fetch();
        if ($invoice) {
            return $invoice;
        }

        $stmt = Database::connection()->prepare('INSERT INTO card_invoices (user_id, card_id, month, status) VALUES (:user_id, :card_id, :month, :status)');
        $stmt->execute([
            'user_id' => $userId,
            'card_id' => $cardId,
            'month' => $month,
            'status' => 'open',
        ]);

        return self::findOrCreate($cardId, $userId, $month);
    }

    public static function markPaid(int $invoiceId, int $userId): void
    {
        $stmt = Database::connection()->prepare('UPDATE card_invoices SET status = "paid", paid_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $invoiceId, 'user_id' => $userId]);
    }
}
