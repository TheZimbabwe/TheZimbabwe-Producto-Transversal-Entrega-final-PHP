<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'Please login to access your profile.';
    $_SESSION['flash_type'] = 'warning';
    redirect('login.php');
}

// Get user information
$userId = getCurrentUserId();
$user = getUserById($userId);

$errors = [];
$success = '';

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize input
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $bio = sanitizeInput($_POST['bio'] ?? '');
        $website = sanitizeInput($_POST['website'] ?? '');
        
        // Validate website URL if provided
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = 'Please enter a valid website URL.';
        }
        
        // Update profile if no errors
        if (empty($errors)) {
            $profileData = [
                'full_name' => $fullName,
                'bio' => $bio,
                'website' => $website
            ];
            
            $result = updateProfile($userId, $profileData);
            
            if ($result['success']) {
                $success = $result['message'];
                // Refresh user data
                $user = getUserById($userId);
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

// Process password change form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get password inputs
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'New password is required.';
        } elseif (!validatePassword($newPassword)) {
            $errors[] = 'New password must be at least 8 characters long.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }
        
        // Change password if no errors
        if (empty($errors)) {
            $result = changePassword($userId, $currentPassword, $newPassword);
            
            if ($result['success']) {
                $success = $result['message'];
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

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body profile-header">
                <div class="text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p>Member since <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 order-lg-1">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Profile Information</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name'] ?? 'Not set'); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Website:</strong> 
                        <?php if (!empty($user['website'])): ?>
                            <a href="<?php echo htmlspecialchars($user['website']); ?>" target="_blank" rel="noopener">
                                <?php echo htmlspecialchars($user['website']); ?>
                            </a>
                        <?php else: ?>
                            Not set
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Account Actions</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="#" class="list-group-item list-group-item-action disabled">
                        <i class="fas fa-cog me-2"></i> Account Settings
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8 order-lg-0">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Edit Profile</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="profile.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card" id="password">
            <div class="card-header">
                <h4 class="mb-0">Change Password</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="profile.php#password" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="new_password" minlength="8" required>
                        <div class="password-strength-meter mt-2">
                            <div id="password-strength-meter-fill" class="password-strength-meter-fill"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-text">Password must be at least 8 characters long</div>
                            <div id="password-strength-text"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div id="password-match-message" class="form-text"></div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
