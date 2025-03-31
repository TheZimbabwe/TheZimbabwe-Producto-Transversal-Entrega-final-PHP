<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Include header
include 'includes/header.php';
?>

<div class="jumbotron bg-dark text-white p-5 mb-4 rounded">
    <div class="container">
        <h1 class="display-4">Welcome to <?php echo SITE_NAME; ?></h1>
        <p class="lead">A complete PHP user management system with registration, login, profile management, and more.</p>
        <?php if (!isLoggedIn()): ?>
            <div class="mt-4">
                <a href="register.php" class="btn btn-primary btn-lg me-2">Register</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                    <h3 class="card-title">User Registration</h3>
                    <p class="card-text">Create a new account to access all features of our application.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-3x text-primary mb-3"></i>
                    <h3 class="card-title">Database Operations</h3>
                    <p class="card-text">Secure PDO-based database operations for user management.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h3 class="card-title">Secure Sessions</h3>
                    <p class="card-text">Secure session and cookie management for user authentication.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Features</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">User Registration with Validation</li>
                                <li class="list-group-item">Secure Login with Remember Me</li>
                                <li class="list-group-item">Session Management</li>
                                <li class="list-group-item">CSRF Protection</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Password Hashing</li>
                                <li class="list-group-item">Profile Management</li>
                                <li class="list-group-item">Form Validation</li>
                                <li class="list-group-item">PDO Database Operations</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
