<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
} else {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
}

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get user from database
    $stmt = $db->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verify password:
    //  - if stored as hash => password_verify
    //  - if stored as plain text (older data) => fallback + auto-upgrade to hash
    $ok = false;
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $ok = true;
        } elseif ($password === $user['password']) {
            $ok = true;
            // upgrade legacy plain-text password to hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $up = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $up->execute([$newHash, $user['id']]);
        }
    }

    if ($ok) {
        // Start session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Don't send password back
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>