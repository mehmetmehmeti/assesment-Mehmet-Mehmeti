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

$video_id = $_GET['video_id'] ?? '';

if (empty($video_id) || !is_numeric($video_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid video ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get bookmarks for the video
    if ($_SESSION['role'] === 'admin') {
        $stmt = $db->prepare("
            SELECT b.*, u.email as user_email 
            FROM bookmarks b
            JOIN users u ON b.user_id = u.id
            WHERE b.video_id = ?
            ORDER BY b.timestamp ASC
        ");
        $stmt->execute([$video_id]);
    } else {
        $stmt = $db->prepare("
            SELECT * FROM bookmarks 
            WHERE video_id = ? AND user_id = ?
            ORDER BY timestamp ASC
        ");
        $stmt->execute([$video_id, $_SESSION['user_id']]);
    }
    
    $bookmarks = $stmt->fetchAll();
    
    // Format timestamps as time strings
    foreach ($bookmarks as &$bookmark) {
        $bookmark['time_formatted'] = gmdate("i:s", $bookmark['timestamp']);
    }
    
    echo json_encode([
        'success' => true,
        'bookmarks' => $bookmarks
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>