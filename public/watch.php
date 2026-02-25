<!-- public/watch.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Video - Video Annotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    require_once '../config/auth.php';
    checkAuth();
    $video_id = $_GET['id'] ?? '';
    if (empty($video_id) || !is_numeric($video_id)) {
        header('Location: dashboard.php');
        exit;
    }
    ?>
    
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">← Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="player-container">
            <!-- Video Section -->
            <div class="video-section" style="position: relative;">
                <video id="videoPlayer" class="video-player" controls>
                    <!-- Source is set dynamically in ../assets/js/video.js -->
                </video>
                
                <!-- Annotation Canvas -->
                <canvas id="annotationCanvas"></canvas>
                <div id="annotationPopup" style="display:none; position:absolute; top:12px; left:12px; right:12px; z-index:20; pointer-events:none;">
                    <div id="annotationPopupText" style="display:inline-block; max-width:100%; background:rgba(17,24,39,0.9); color:#fff; padding:10px 14px; border-radius:8px; font-weight:600; box-shadow:0 6px 20px rgba(0,0,0,0.25);"></div>
                </div>
                
                <!-- Custom Controls -->
                <div class="video-controls">
                    <div class="progress-bar" id="progressBar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    
                    <div class="control-buttons">
                        <button class="btn btn-secondary" id="playPauseBtn">▶ Play</button>
                        <span id="timeDisplay">0:00 / 0:00</span>
                    </div>
                </div>
                
                <!-- Annotation Tools -->
                <div class="annotation-tools">
                    <button class="tool-btn active" data-tool="rectangle">⬜ Rectangle</button>
                    <button class="tool-btn" data-tool="circle">⚪ Circle</button>
                    <button class="tool-btn" data-tool="arrow">➡️ Arrow</button>
                    <button class="tool-btn" data-tool="freehand">✏️ Freehand</button>
                    
                    <div class="color-picker">
                        <div class="color-option red selected" data-color="#ef4444"></div>
                        <div class="color-option blue" data-color="#3b82f6"></div>
                        <div class="color-option green" data-color="#10b981"></div>
                        <div class="color-option yellow" data-color="#f59e0b"></div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-tabs">
                    <button class="tab-btn active" id="bookmarksTab">📌 Bookmarks</button>
                    <button class="tab-btn" id="annotationsTab">✏️ Annotations</button>
                </div>
                
                <div id="bookmarksPanel" class="bookmarks-list">
                    <!-- Bookmarks loaded via JS -->
                    <div class="loading">Loading bookmarks...</div>
                </div>
                
                <div id="annotationsPanel" class="annotations-list" style="display: none;">
                    <!-- Annotations loaded via JS -->
                    <div class="loading">Loading annotations...</div>
                </div>
                
                <button type="button" class="btn btn-secondary" id="addBookmarkBtn" style="margin-top: var(--space-md); width: 100%;">
                    + Add Bookmark at Current Time
                </button>
                <button type="button" class="btn btn-secondary" id="addAnnotationBtn" style="margin-top: var(--space-sm); width: 100%;">
                    + Add Annotation
                </button>
                <button type="button" class="btn btn-danger" id="deleteSelectedAnnotationsBtn" style="margin-top: var(--space-sm); width: 100%; display: none;" disabled>
                    Delete Selected Annotations
                </button>
            </div>
        </div>
    </div>
    
    <script>
        const videoId = <?php echo $video_id; ?>;
    </script>
    <script src="../assets/js/video.js?v=20260225-1"></script>
</body>
</html>
