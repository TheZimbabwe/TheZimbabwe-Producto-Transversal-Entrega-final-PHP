<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'Please login to access the dashboard.';
    $_SESSION['flash_type'] = 'warning';
    redirect('login.php');
}

// Get user information
$userId = getCurrentUserId();
$user = getUserById($userId);

// Get all users (for demonstration)
$allUsers = getAllUsers();

// Include header
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                <p class="card-text">This is your dashboard where you can manage your account and view system information.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="fas fa-user dashboard-icon text-primary"></i>
                <h5 class="card-title">My Profile</h5>
                <p class="card-text">View and update your profile information</p>
                <a href="profile.php" class="btn btn-outline-primary">Manage Profile</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="fas fa-lock dashboard-icon text-warning"></i>
                <h5 class="card-title">Security</h5>
                <p class="card-text">Update your password and security settings</p>
                <a href="profile.php#password" class="btn btn-outline-warning">Update Password</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="fas fa-users dashboard-icon text-info"></i>
                <h5 class="card-title">User Account</h5>
                <p class="card-text">Member since: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                <button class="btn btn-outline-info" disabled>Account Active</button>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="fas fa-sign-out-alt dashboard-icon text-danger"></i>
                <h5 class="card-title">Session</h5>
                <p class="card-text">Logout from your current session</p>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">User Listing</h4>
                <span class="badge bg-primary"><?php echo count($allUsers); ?> Users</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name'] ?? 'Not set'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
