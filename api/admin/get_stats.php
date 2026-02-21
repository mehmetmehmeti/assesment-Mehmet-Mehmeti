<?php
header('Content-Type: application/json');
require_once '../../config/db.php';
require_once '../../config/auth.php';

// Check admin role
requireAdmin();

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $stats = [];
    
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total videos
    $stmt = $db->query("SELECT COUNT(*) as count FROM videos");
    $stats['total_videos'] = $stmt->fetch()['count'];
    
    // Total bookmarks
    $stmt = $db->query("SELECT COUNT(*) as count FROM bookmarks");
    $stats['total_bookmarks'] = $stmt->fetch()['count'];
    
    // Total annotations
    $stmt = $db->query("SELECT COUNT(*) as count FROM annotations");
    $stats['total_annotations'] = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>