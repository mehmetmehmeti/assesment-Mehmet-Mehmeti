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

// Check if file was uploaded
if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No video file uploaded or upload error']);
    exit;
}

$video = $_FILES['video'];
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

// Backwards compatibility with older UI
$original_name = $_POST['original_name'] ?? $video['name'];

if ($title === '') {
    // If no title provided, use original filename
    $title = $original_name;
}

// Validate file type
$allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $video['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only MP4, WebM, OGG, and MOV are allowed']);
    exit;
}

// Validate file size (100MB max)
$max_size = 100 * 1024 * 1024;
if ($video['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size is 100MB']);
    exit;
}

// Generate unique filename
$extension = pathinfo($video['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$upload_path = '../public/uploads/videos/' . $filename;

// Ensure upload directory exists
if (!is_dir('../public/uploads/videos')) {
    mkdir('../public/uploads/videos', 0755, true);
}

// Move uploaded file
if (move_uploaded_file($video['tmp_name'], $upload_path)) {
    try {
        $db = Database::getInstance();
        
        // Save to database
       $stmt = $db->prepare("
    INSERT INTO videos (user_id, filename, original_name, title, description)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $_SESSION['user_id'],
    $filename,
    $original_name,
    $title,
    $description
]);
        
        $video_id = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'video' => [
                'id' => $video_id,
                'filename' => $filename,
                'original_name' => $original_name,
                'title' => $title,
                'description' => $description
               
            ]
        ]);
        
    } catch (PDOException $e) {
        // Delete uploaded file if database insert fails
        unlink($upload_path);
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save uploaded file']);
}
?>