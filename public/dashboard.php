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
    <?php
    // Include auth check
    require_once '../config/auth.php';
    checkAuth();
    ?>
    
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🎥 VideoAnnotate</a>
            
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="upload.php">Upload</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
                
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_email'], 0, 2)); ?></div>
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
        <div class="video-grid" id="videoGrid">
            <!-- Videos will be loaded here via JavaScript -->
            <div class="loading">Loading videos...</div>
        </div>
    </div>
    
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>