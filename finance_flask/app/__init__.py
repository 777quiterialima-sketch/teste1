from flask import Flask, render_template
from config import Config
from app.extensions import db, migrate, login_manager, csrf


def create_app():
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_object(Config)

    db.init_app(app)
    migrate.init_app(app, db)
    login_manager.init_app(app)
    csrf.init_app(app)
    login_manager.login_view = "auth.login"

    from app.routes.auth import auth_bp
    from app.routes.dashboard import dashboard_bp
    from app.routes.accounts import accounts_bp
    from app.routes.cards import cards_bp
    from app.routes.transactions import transactions_bp
    from app.routes.reports import reports_bp
    from app.routes.budgets import budgets_bp
    from app.routes.connections import connections_bp
    from app.routes.categories import categories_bp

    app.register_blueprint(auth_bp)
    app.register_blueprint(dashboard_bp)
    app.register_blueprint(accounts_bp)
    app.register_blueprint(cards_bp)
    app.register_blueprint(transactions_bp)
    app.register_blueprint(reports_bp)
    app.register_blueprint(budgets_bp)
    app.register_blueprint(connections_bp)
    app.register_blueprint(categories_bp)

    @app.errorhandler(404)
    def not_found(error):
        return render_template("errors/404.html"), 404

    @app.errorhandler(500)
    def server_error(error):
        return render_template("errors/500.html"), 500

    with app.app_context():
        from app.models import seed_if_empty
        db.create_all()
        seed_if_empty()

    return app
