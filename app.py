import os
import random
import string
import datetime

from flask import Flask, render_template, redirect, url_for, request, flash, session
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.orm import DeclarativeBase
from werkzeug.security import generate_password_hash, check_password_hash


class Base(DeclarativeBase):
    pass


db = SQLAlchemy(model_class=Base)
# create the app
app = Flask(__name__)
app.secret_key = os.environ.get("SESSION_SECRET", "dev-secret-key")

# configure the database, relative to the app instance folder
app.config["SQLALCHEMY_DATABASE_URI"] = os.environ.get("DATABASE_URL")
app.config["SQLALCHEMY_ENGINE_OPTIONS"] = {
    "pool_recycle": 300,
    "pool_pre_ping": True,
}
# initialize the app with the extension, flask-sqlalchemy >= 3.0.x
db.init_app(app)

# Import models and routes after app and db are defined
from models import User

# Create tables
with app.app_context():
    db.create_all()
    
# Context processor for templates
@app.context_processor
def inject_now():
    return {'now': datetime.datetime.utcnow()}

# Helper functions
def generate_token(length=32):
    """Generate a random token for remember me functionality"""
    chars = string.ascii_letters + string.digits
    return ''.join(random.choice(chars) for _ in range(length))


# Routes
@app.route('/')
def index():
    """Homepage route"""
    return render_template('index.html')


@app.route('/register', methods=['GET', 'POST'])
def register():
    """User registration route"""
    if 'user_id' in session:
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        email = request.form.get('email', '').strip()
        password = request.form.get('password', '')
        confirm_password = request.form.get('confirm_password', '')
        
        # Validation
        errors = []
        
        # Username validation
        if not username:
            errors.append('El nombre de usuario es obligatorio.')
        elif len(username) < 3 or len(username) > 20:
            errors.append('El nombre de usuario debe tener entre 3 y 20 caracteres.')
        elif not all(c.isalnum() or c == '_' for c in username):
            errors.append('El nombre de usuario solo puede contener letras, números y guiones bajos.')
        elif User.query.filter_by(username=username).first():
            errors.append('El nombre de usuario ya está en uso. Por favor, elige otro.')
        
        # Email validation
        if not email:
            errors.append('El correo electrónico es obligatorio.')
        elif '@' not in email or '.' not in email:
            errors.append('Por favor, introduce una dirección de correo electrónico válida.')
        elif User.query.filter_by(email=email).first():
            errors.append('La dirección de correo electrónico ya está registrada. Por favor, usa otra.')
        
        # Password validation
        if not password:
            errors.append('La contraseña es obligatoria.')
        elif len(password) < 8:
            errors.append('La contraseña debe tener al menos 8 caracteres.')
        
        # Confirm passwords match
        if password != confirm_password:
            errors.append('Las contraseñas no coinciden.')
        
        # Register user if no errors
        if not errors:
            password_hash = generate_password_hash(password)
            new_user = User(username=username, email=email, password_hash=password_hash)
            
            db.session.add(new_user)
            db.session.commit()
            
            flash('¡Registro exitoso! Ahora puedes iniciar sesión.', 'success')
            return redirect(url_for('login'))
        
        # If there are errors, flash them
        for error in errors:
            flash(error, 'danger')
    
    return render_template('register.html')


@app.route('/login', methods=['GET', 'POST'])
def login():
    """User login route"""
    if 'user_id' in session:
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '')
        remember_me = 'remember_me' in request.form
        
        # Validation
        errors = []
        
        # Find user
        user = User.query.filter_by(username=username).first()
        
        if not username or not password:
            errors.append('Se requieren nombre de usuario y contraseña.')
        elif not user or not check_password_hash(user.password_hash, password):
            errors.append('Nombre de usuario o contraseña inválidos.')
        
        # Log in user if no errors
        if not errors:
            session['user_id'] = user.id
            session['username'] = user.username
            
            # Set remember me cookie if requested
            if remember_me:
                token = generate_token()
                user.remember_token = token
                db.session.commit()
                
                # Set cookie that expires in 30 days
                remember_expiry = 30 * 24 * 60 * 60  # 30 days in seconds
                response = redirect(url_for('dashboard'))
                response.set_cookie('remember_token', token, max_age=remember_expiry, httponly=True)
                response.set_cookie('remember_user', str(user.id), max_age=remember_expiry, httponly=True)
                
                flash('¡Inicio de sesión exitoso! Tu sesión será recordada.', 'success')
                return response
            
            flash('¡Inicio de sesión exitoso!', 'success')
            return redirect(url_for('dashboard'))
        
        # If there are errors, flash them
        for error in errors:
            flash(error, 'danger')
    
    return render_template('login.html')


@app.route('/dashboard')
def dashboard():
    """User dashboard route"""
    if 'user_id' not in session:
        flash('Por favor, inicia sesión para acceder al panel principal.', 'warning')
        return redirect(url_for('login'))
    
    user = User.query.get(session['user_id'])
    all_users = User.query.all()
    
    return render_template('dashboard.html', user=user, all_users=all_users)


@app.route('/profile', methods=['GET', 'POST'])
def profile():
    """User profile route"""
    if 'user_id' not in session:
        flash('Por favor, inicia sesión para acceder a tu perfil.', 'warning')
        return redirect(url_for('login'))
    
    user = User.query.get(session['user_id'])
    
    # Process profile update
    if request.method == 'POST' and 'update_profile' in request.form:
        full_name = request.form.get('full_name', '').strip()
        bio = request.form.get('bio', '').strip()
        website = request.form.get('website', '').strip()
        
        # Validate website URL if provided
        if website and not website.startswith(('http://', 'https://')):
            website = 'https://' + website
        
        user.full_name = full_name
        user.bio = bio
        user.website = website
        
        db.session.commit()
        flash('¡Perfil actualizado correctamente!', 'success')
    
    # Process password change
    elif request.method == 'POST' and 'change_password' in request.form:
        current_password = request.form.get('current_password', '')
        new_password = request.form.get('new_password', '')
        confirm_password = request.form.get('confirm_password', '')
        
        # Validation
        errors = []
        
        if not current_password:
            errors.append('Se requiere la contraseña actual.')
        elif not check_password_hash(user.password_hash, current_password):
            errors.append('La contraseña actual es incorrecta.')
        
        if not new_password:
            errors.append('Se requiere una nueva contraseña.')
        elif len(new_password) < 8:
            errors.append('La nueva contraseña debe tener al menos 8 caracteres.')
        
        if new_password != confirm_password:
            errors.append('Las nuevas contraseñas no coinciden.')
        
        # Change password if no errors
        if not errors:
            user.password_hash = generate_password_hash(new_password)
            db.session.commit()
            flash('¡Contraseña cambiada correctamente!', 'success')
        else:
            for error in errors:
                flash(error, 'danger')
    
    return render_template('profile.html', user=user)


@app.route('/logout')
def logout():
    """User logout route"""
    # Clear the remember me cookies
    response = redirect(url_for('login'))
    response.delete_cookie('remember_token')
    response.delete_cookie('remember_user')
    
    # Clear session
    session.clear()
    
    flash('Has cerrado sesión exitosamente.', 'success')
    return response


# Before request handler to check remember me cookies
@app.before_request
def check_remember_me():
    if 'user_id' not in session:
        user_id = request.cookies.get('remember_user')
        token = request.cookies.get('remember_token')
        
        if user_id and token:
            user = User.query.filter_by(id=user_id, remember_token=token).first()
            
            if user:
                session['user_id'] = user.id
                session['username'] = user.username