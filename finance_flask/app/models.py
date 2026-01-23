from datetime import datetime
from flask_login import UserMixin
from werkzeug.security import generate_password_hash, check_password_hash
from app.extensions import db, login_manager


@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))


class User(UserMixin, db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(120), nullable=False)
    email = db.Column(db.String(255), unique=True, nullable=False)
    password_hash = db.Column(db.String(255), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    accounts = db.relationship("Account", backref="user", lazy=True)
    cards = db.relationship("Card", backref="user", lazy=True)
    categories = db.relationship("Category", backref="user", lazy=True)
    transactions = db.relationship("Transaction", backref="user", lazy=True)
    budgets = db.relationship("Budget", backref="user", lazy=True)
    connections = db.relationship("BankConnection", backref="user", lazy=True)
    import_batches = db.relationship("ImportBatch", backref="user", lazy=True)

    def set_password(self, password):
        self.password_hash = generate_password_hash(password)

    def check_password(self, password):
        return check_password_hash(self.password_hash, password)


class Account(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    name = db.Column(db.String(120), nullable=False)
    type = db.Column(db.String(30), nullable=False)
    opening_balance = db.Column(db.Numeric(12, 2), default=0)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    transactions = db.relationship(
        "Transaction",
        backref="account",
        foreign_keys="Transaction.account_id",
        lazy=True,
    )


class Card(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    name = db.Column(db.String(120), nullable=False)
    brand = db.Column(db.String(50), nullable=False)
    limit_total = db.Column(db.Numeric(12, 2), default=0)
    closing_day = db.Column(db.Integer, nullable=False)
    due_day = db.Column(db.Integer, nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    invoices = db.relationship("CardInvoice", backref="card", lazy=True)


class Category(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    name = db.Column(db.String(120), nullable=False)
    type = db.Column(db.String(20), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)


class Transaction(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    type = db.Column(db.String(20), nullable=False)
    date = db.Column(db.Date, nullable=False)
    description = db.Column(db.String(255), nullable=False)
    category_id = db.Column(db.Integer, db.ForeignKey("category.id"), nullable=True)
    amount = db.Column(db.Numeric(12, 2), nullable=False)
    account_id = db.Column(db.Integer, db.ForeignKey("account.id"), nullable=True)
    account_to_id = db.Column(db.Integer, db.ForeignKey("account.id"), nullable=True)
    payment_method = db.Column(db.String(20), nullable=False)
    card_id = db.Column(db.Integer, db.ForeignKey("card.id"), nullable=True)
    installment_group_id = db.Column(db.String(64), nullable=True)
    installment_n = db.Column(db.Integer, nullable=True)
    notes = db.Column(db.Text, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    category = db.relationship("Category", backref="transactions", lazy=True)
    card = db.relationship("Card", backref="transactions", lazy=True)
    account_to = db.relationship("Account", foreign_keys=[account_to_id])


class Budget(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    month = db.Column(db.Integer, nullable=False)
    year = db.Column(db.Integer, nullable=False)
    category_id = db.Column(db.Integer, db.ForeignKey("category.id"), nullable=True)
    limit_amount = db.Column(db.Numeric(12, 2), nullable=False)

    category = db.relationship("Category", backref="budgets", lazy=True)


class BankConnection(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    provider_name = db.Column(db.String(120), nullable=False)
    account_id = db.Column(db.Integer, db.ForeignKey("account.id"), nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    account = db.relationship("Account", backref="connections", lazy=True)


class ImportBatch(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    file_name = db.Column(db.String(255), nullable=False)
    file_hash = db.Column(db.String(64), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    items = db.relationship("ImportItem", backref="batch", lazy=True)


class ImportItem(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    batch_id = db.Column(db.Integer, db.ForeignKey("import_batch.id"), nullable=False)
    date = db.Column(db.Date, nullable=False)
    description = db.Column(db.String(255), nullable=False)
    amount = db.Column(db.Numeric(12, 2), nullable=False)
    account_id = db.Column(db.Integer, db.ForeignKey("account.id"), nullable=False)
    suggested_type = db.Column(db.String(20), nullable=False)
    status = db.Column(db.String(20), nullable=False, default="pending")
    matched_transaction_id = db.Column(db.Integer, db.ForeignKey("transaction.id"), nullable=True)

    account = db.relationship("Account", backref="import_items", lazy=True)
    matched_transaction = db.relationship("Transaction")


class CardInvoice(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    card_id = db.Column(db.Integer, db.ForeignKey("card.id"), nullable=False)
    user_id = db.Column(db.Integer, db.ForeignKey("user.id"), nullable=False)
    month = db.Column(db.Integer, nullable=False)
    year = db.Column(db.Integer, nullable=False)
    total_amount = db.Column(db.Numeric(12, 2), default=0)
    paid_at = db.Column(db.DateTime, nullable=True)
    payment_account_id = db.Column(db.Integer, db.ForeignKey("account.id"), nullable=True)

    payment_account = db.relationship("Account")


def seed_if_empty():
    if User.query.count() > 0:
        return

    user = User(name="Usuário Demo", email="demo@local.dev")
    user.set_password("123456")
    db.session.add(user)
    db.session.flush()

    categories = [
        Category(user_id=user.id, name="Salário", type="income"),
        Category(user_id=user.id, name="Alimentação", type="expense"),
        Category(user_id=user.id, name="Transporte", type="expense"),
        Category(user_id=user.id, name="Lazer", type="expense"),
        Category(user_id=user.id, name="Investimentos", type="income"),
    ]
    db.session.add_all(categories)

    account = Account(user_id=user.id, name="Conta Principal", type="corrente", opening_balance=1500)
    db.session.add(account)

    card = Card(user_id=user.id, name="Cartão Principal", brand="Visa", limit_total=3000, closing_day=20, due_day=5)
    db.session.add(card)

    db.session.commit()
