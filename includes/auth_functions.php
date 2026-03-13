<?php
// Authentication functions

function login($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];
            
            return true;
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
    }
    
    return false;
}

function register($data) {
    global $pdo;
    
    $errors = [];
    
    // Validate required fields
    if (empty($data['username'])) $errors[] = "Username is required";
    if (empty($data['email'])) $errors[] = "Email is required";
    if (empty($data['password'])) $errors[] = "Password is required";
    if (empty($data['full_name'])) $errors[] = "Full name is required";
    
    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($data['password']) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$data['email'], $data['username']]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $errors[] = "Email or username already exists";
            return ['success' => false, 'errors' => $errors];
        }
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address, city, state, zip_code) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashed_password,
            $data['full_name'],
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['zip_code'] ?? ''
        ]);
        
        // Auto-login after registration
        if ($stmt->rowCount() > 0) {
            if (login($data['email'], $data['password'])) {
                return ['success' => true, 'message' => 'Registration successful!'];
            }
        }
        
        $errors[] = "Registration failed. Please try again.";
        return ['success' => false, 'errors' => $errors];
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        $errors[] = "Database error. Please try again.";
        return ['success' => false, 'errors' => $errors];
    }
}

function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    if (session_id() != "") {
        session_destroy();
    }
    
    // Redirect to home
    header('Location: ../index.php');
    exit();
}
?>