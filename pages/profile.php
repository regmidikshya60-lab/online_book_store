<?php
$pageTitle = "My Profile";

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php?page=logout');
    exit();
}

// Include header (NO session_start() here)
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4"><i class="fas fa-user"></i> My Profile</h1>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="uploads/users/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($user['full_name']); ?>"
                         class="rounded-circle mb-3"
                         style="width: 150px; height: 150px; object-fit: cover;"
                         onerror="this.src='assets/images/placeholder.jpg'">
                    
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="mt-3">
                        <span class="badge bg-<?php echo $user['user_type'] === 'admin' ? 'danger' : 'primary'; ?>">
                            <?php echo ucfirst($user['user_type']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Full Name</strong></label>
                            <p><?php echo htmlspecialchars($user['full_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Username</strong></label>
                            <p><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Email Address</strong></label>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Phone Number</strong></label>
                            <p><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Address</strong></label>
                        <p>
                            <?php if ($user['address']): ?>
                                <?php echo htmlspecialchars($user['address']); ?><br>
                                <?php echo htmlspecialchars($user['city']); ?>, 
                                <?php echo htmlspecialchars($user['state']); ?> 
                                <?php echo htmlspecialchars($user['zip_code']); ?>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Member Since</strong></label>
                            <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Last Updated</strong></label>
                            <p><?php echo date('F j, Y', strtotime($user['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
?>