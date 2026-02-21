<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../config/auth.php';

// Check authentication
requireAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $video_id = $_POST['video_id'] ?? '';
    $timestamp = $_POST['timestamp'] ?? '';
    $title = $_POST['title'] ?? '';
} else {
    $video_id = $input['video_id'] ?? '';
    $timestamp = $input['timestamp'] ?? '';
    $title = $input['title'] ?? '';
}

// Validate input
if (empty($video_id) || !is_numeric($video_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid video ID is required']);
    exit;
}

if (!isset($timestamp) || !is_numeric($timestamp)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid timestamp is required']);
    exit;
}

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['error' => 'Bookmark title is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Check if video exists and belongs to user (or user is admin)
    $stmt = $db->prepare("
        SELECT id FROM videos 
        WHERE id = ? AND (user_id = ? OR ? = 'admin')
    ");
    $stmt->execute([$video_id, $_SESSION['user_id'], $_SESSION['role']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Video not found or access denied']);
        exit;
    }
    
    // Insert bookmark
    $stmt = $db->prepare("
        INSERT INTO bookmarks (user_id, video_id, timestamp, title) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $video_id,
        $timestamp,
        $title
    ]);
    
    $bookmark_id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bookmark added successfully',
        'bookmark' => [
            'id' => $bookmark_id,
            'video_id' => $video_id,
            'timestamp' => $timestamp,
            'title' => $title
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>