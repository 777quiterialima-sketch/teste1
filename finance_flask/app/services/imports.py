import csv
import hashlib
from datetime import datetime
from app.extensions import db
from app.models import ImportBatch, ImportItem, Transaction


def normalize_row(date_value, description, amount):
    normalized = f"{date_value}|{description.strip().lower()}|{amount}"
    return hashlib.sha256(normalized.encode("utf-8")).hexdigest()


def parse_date(value):
    for fmt in ("%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y"):
        try:
            return datetime.strptime(value.strip(), fmt).date()
        except ValueError:
            continue
    raise ValueError("Formato de data inválido")


def create_import_batch(user_id, file_name, file_hash):
    batch = ImportBatch(user_id=user_id, file_name=file_name, file_hash=file_hash)
    db.session.add(batch)
    db.session.commit()
    return batch


def load_csv_items(batch_id, file_stream, mapping, account_id):
    reader = csv.DictReader(file_stream)
    items = []
    for row in reader:
        date_value = parse_date(row[mapping["date"]])
        description = row[mapping["description"]]
        amount = float(row[mapping["amount"]].replace(",", "."))
        suggested_type = "income" if amount >= 0 else "expense"
        items.append(
            {
                "date": date_value,
                "description": description,
                "amount": abs(amount),
                "account_id": account_id,
                "suggested_type": suggested_type,
            }
        )
    for item in items:
        import_item = ImportItem(
            batch_id=batch_id,
            date=item["date"],
            description=item["description"],
            amount=item["amount"],
            account_id=item["account_id"],
            suggested_type=item["suggested_type"],
        )
        db.session.add(import_item)
    db.session.commit()


def mark_duplicates(user_id, batch_id):
    items = ImportItem.query.filter_by(batch_id=batch_id).all()
    for item in items:
        exists = Transaction.query.filter_by(
            user_id=user_id,
            date=item.date,
            description=item.description,
            amount=item.amount,
        ).first()
        if exists:
            item.status = "duplicate"
            item.matched_transaction_id = exists.id
    db.session.commit()


def confirm_import(user_id, batch_id):
    items = ImportItem.query.filter_by(batch_id=batch_id, status="pending").all()
    for item in items:
        transaction = Transaction(
            user_id=user_id,
            type=item.suggested_type,
            date=item.date,
            description=item.description,
            amount=item.amount,
            account_id=item.account_id,
            payment_method="debit",
        )
        db.session.add(transaction)
        item.status = "imported"
        item.matched_transaction = transaction
    db.session.commit()
