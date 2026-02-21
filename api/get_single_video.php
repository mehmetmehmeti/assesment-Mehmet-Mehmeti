<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../config/auth.php';

// Check authentication
requireAuth();

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$video_id = $_GET['id'] ?? '';

if (empty($video_id) || !is_numeric($video_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid video ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get video
    $stmt = $db->prepare("
        SELECT v.*, u.email as user_email 
        FROM videos v
        JOIN users u ON v.user_id = u.id
        WHERE v.id = ? AND (v.user_id = ? OR ? = 'admin')
    ");
    $stmt->execute([$video_id, $_SESSION['user_id'], $_SESSION['role']]);
    
    $video = $stmt->fetch();
    
    if (!$video) {
        http_response_code(404);
        echo json_encode(['error' => 'Video not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'video' => $video
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>