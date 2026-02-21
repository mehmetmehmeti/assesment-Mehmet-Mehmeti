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
    $description = $_POST['description'] ?? '';
    $data = $_POST['data'] ?? null;
} else {
    $video_id = $input['video_id'] ?? '';
    $timestamp = $input['timestamp'] ?? '';
    $description = $input['description'] ?? '';
    $data = $input['data'] ?? null;
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

if (empty($description)) {
    http_response_code(400);
    echo json_encode(['error' => 'Annotation description is required']);
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
    
    // Insert annotation
    $stmt = $db->prepare(" 
        INSERT INTO annotations (user_id, video_id, timestamp, description, data) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    // Normalize data: allow array/object or JSON string
    $data_json = null;
    if ($data !== null && $data !== '') {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data_json = (json_last_error() === JSON_ERROR_NONE) ? json_encode($decoded) : json_encode(['value' => $data]);
        } else {
            $data_json = json_encode($data);
        }
    }

    $stmt->execute([
        $_SESSION['user_id'],
        $video_id,
        $timestamp,
        $description,
        $data_json
    ]);
    
    $annotation_id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Annotation added successfully',
        'annotation' => [
            'id' => $annotation_id,
            'video_id' => $video_id,
            'timestamp' => $timestamp,
            'description' => $description
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>