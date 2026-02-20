<!-- public/dashboard.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Video Annotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🎥 VideoAnnotate</a>
            
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="upload.php">Upload</a>
                
                <div class="user-menu">
                    <span>John Doe</span>
                    <div class="user-avatar">JD</div>
                    <a href="logout.php" style="color: var(--gray);">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container">
        <div class="dashboard-header">
            <h1>My Videos</h1>
            <a href="upload.php" class="btn btn-primary">+ Upload New Video</a>
        </div>
        
        <!-- Video Grid -->
        <div class="video-grid">
            <!-- Video Card 1 -->
            <div class="video-card" onclick="window.location.href='watch.php?id=1'">
                <div class="video-thumbnail">
                    <span class="video-duration">5:32</span>
                </div>
                <div class="video-info">
                    <h3>Product Demo 2024</h3>
                    <div class="video-meta">
                        <span>📌 12 bookmarks</span>
                        <span>✏️ 8 annotations</span>
                    </div>
                    <small style="color: var(--gray);">Uploaded 2 days ago</small>
                </div>
            </div>
            
            <!-- Video Card 2 -->
            <div class="video-card" onclick="window.location.href='watch.php?id=2'">
                <div class="video-thumbnail">
                    <span class="video-duration">12:15</span>
                </div>
                <div class="video-info">
                    <h3>Tutorial: Getting Started</h3>
                    <div class="video-meta">
                        <span>📌 5 bookmarks</span>
                        <span>✏️ 3 annotations</span>
                    </div>
                    <small style="color: var(--gray);">Uploaded 5 days ago</small>
                </div>
            </div>
            
            <!-- Video Card 3 -->
            <div class="video-card" onclick="window.location.href='watch.php?id=3'">
                <div class="video-thumbnail">
                    <span class="video-duration">3:47</span>
                </div>
                <div class="video-info">
                    <h3>Team Meeting Recording</h3>
                    <div class="video-meta">
                        <span>📌 2 bookmarks</span>
                        <span>✏️ 1 annotation</span>
                    </div>
                    <small style="color: var(--gray);">Uploaded 1 week ago</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>