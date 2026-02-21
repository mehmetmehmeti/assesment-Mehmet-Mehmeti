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

$annotation_id = $_GET['id'] ?? '';

if (empty($annotation_id) || !is_numeric($annotation_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid annotation ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Check if annotation exists and belongs to user (or user is admin)
    $stmt = $db->prepare("
        SELECT id FROM annotations 
        WHERE id = ? AND (user_id = ? OR ? = 'admin')
    ");
    $stmt->execute([$annotation_id, $_SESSION['user_id'], $_SESSION['role']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Annotation not found or access denied']);
        exit;
    }
    
    // Delete annotation
    $stmt = $db->prepare("DELETE FROM annotations WHERE id = ?");
    $stmt->execute([$annotation_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Annotation deleted successfully'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>