<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$username = '';
$email = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize and validate input
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate username
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 20) {
            $errors[] = 'Username must be between 3 and 20 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        } elseif (usernameExists($username)) {
            $errors[] = 'Username is already taken. Please choose another.';
        }
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (emailExists($email)) {
            $errors[] = 'Email address is already registered. Please use another.';
        }
        
        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (!validatePassword($password)) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        
        // Confirm passwords match
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }
        
        // If no errors, register the user
        if (empty($errors)) {
            $result = registerUser($username, $email, $password);
            
            if ($result['success']) {
                // Set flash message
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
                
                // Redirect to login page
                redirect('login.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Include header
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Register a New Account</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" pattern="^[a-zA-Z0-9_]+$" minlength="3" maxlength="20" required>
                        <div class="form-text">Username must be 3-20 characters and can contain letters, numbers, and underscores.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                        <div class="password-strength-meter mt-2">
                            <div id="password-strength-meter-fill" class="password-strength-meter-fill"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-text">Password must be at least 8 characters long</div>
                            <div id="password-strength-text"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div id="password-match-message" class="form-text"></div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
