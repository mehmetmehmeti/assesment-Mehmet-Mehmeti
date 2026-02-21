<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../config/auth.php';

// Check authentication
requireAuth();

// Only accept DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$bookmark_id = $_GET['id'] ?? '';

if (empty($bookmark_id) || !is_numeric($bookmark_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid bookmark ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Check if bookmark exists and belongs to user (or user is admin)
    $stmt = $db->prepare("
        SELECT id FROM bookmarks 
        WHERE id = ? AND (user_id = ? OR ? = 'admin')
    ");
    $stmt->execute([$bookmark_id, $_SESSION['user_id'], $_SESSION['role']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Bookmark not found or access denied']);
        exit;
    }
    
    // Delete bookmark
    $stmt = $db->prepare("DELETE FROM bookmarks WHERE id = ?");
    $stmt->execute([$bookmark_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Bookmark deleted successfully'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>