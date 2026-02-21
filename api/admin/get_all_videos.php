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
    
    // Get all videos with user info
    $stmt = $db->query("
        SELECT v.*, u.email as user_email 
        FROM videos v
        JOIN users u ON v.user_id = u.id
        ORDER BY v.created_at DESC
    ");
    
    $videos = $stmt->fetchAll();
    
    // Get counts for each video
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