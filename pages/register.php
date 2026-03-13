<?php
$pageTitle = "Register";

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    
    // Validation
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($full_name)) $errors[] = "Full name is required";
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($errors)) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $errors[] = "Email or username already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address, city, state, zip_code) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $username, $email, $hashed_password, $full_name, 
                    $phone, $address, $city, $state, $zip_code
                ]);
                
                // Get the inserted user
                $user_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    $success = "Registration successful!";
                    echo '<script>
                        setTimeout(function() {
                            window.location.href = "index.php";
                        }, 2000);
                    </script>';
                } else {
                    $errors[] = "Registration failed. User not found after insertion.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error. Please try again.";
        }
    }
}

// Include header (NO session_start() here)
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-container">
                <h2 class="text-center mb-4"><i class="fas fa-user-plus"></i> Create Account</h2>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <p style="margin-top: 0.5rem;">Redirecting to home page...</p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div style="margin-top: 0.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" required 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Zip Code</label>
                                <input type="text" name="zip_code" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? 
                        <a href="index.php?page=login">Login here</a>
                    </p>
                    <p>
                        <a href="index.php">Back to Home</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
?>