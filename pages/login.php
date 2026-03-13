<?php
$pageTitle = "Login";

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: index.php?page=admin/dashboard');
    } else {
        header('Location: index.php');
    }
    exit();
}

require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if ($user['user_type'] === 'admin') {
                    header('Location: index.php?page=admin/dashboard');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Invalid password. Please try again.';
            }
        } else {
            $error = 'No account found with this email.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}

// Include header (NO session_start() here)
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-container">
                <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Login</h2>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Don't have an account? 
                        <a href="index.php?page=register">Register here</a>
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