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
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">← Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="player-container">
            <!-- Video Section -->
            <div class="video-section">
                <video id="videoPlayer" class="video-player" controls>
                    <source src="uploads/videos/sample.mp4" type="video/mp4">
                </video>
                
                <!-- Annotation Canvas -->
                <canvas id="annotationCanvas"></canvas>
                
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
                
                <button class="btn btn-secondary" id="addBookmarkBtn" style="margin-top: var(--space-md); width: 100%;">
                    + Add Bookmark at Current Time
                </button>
            </div>
        </div>
    </div>
    
    <script>
        const videoId = <?php echo $video_id; ?>;
    </script>
    <script src="assets/js/video.js"></script>
</body>
</html>