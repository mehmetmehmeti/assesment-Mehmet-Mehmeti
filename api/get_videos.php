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

try {
    $db = Database::getInstance();
    
    // Get videos for the logged-in user
    $stmt = $db->prepare("
        SELECT * FROM videos 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $videos = $stmt->fetchAll();
    
    // Get bookmark and annotation counts for each video
    foreach ($videos as &$video) {
        // Count bookmarks
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookmarks WHERE video_id = ?");
        $stmt->execute([$video['id']]);
        $video['bookmark_count'] = $stmt->fetch()['count'];
        
        // Count annotations
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM annotations WHERE video_id = ?");
        $stmt->execute([$video['id']]);
        $video['annotation_count'] = $stmt->fetch()['count'];
    }
    
    echo json_encode([
        'success' => true,
        'videos' => $videos
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>