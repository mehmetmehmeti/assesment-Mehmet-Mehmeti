<!-- public/admin.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Video Annotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  
    
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🎥 VideoAnnotate Admin</a>
            
            <div class="nav-links">
                <span style="color: var(--primary);">👑 Admin</span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Stats Cards -->
        <div class="admin-stats" id="statsContainer">
            <div class="stat-card">
                <h3>Total Videos</h3>
                <div class="number" id="totalVideos">-</div>
            </div>
            
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number" id="totalUsers">-</div>
            </div>
            
            <div class="stat-card">
                <h3>Bookmarks</h3>
                <div class="number" id="totalBookmarks">-</div>
            </div>
            
            <div class="stat-card">
                <h3>Annotations</h3>
                <div class="number" id="totalAnnotations">-</div>
            </div>
        </div>
        
        <!-- All Videos Table -->
        <h2 style="margin: var(--space-xl) 0 var(--space-lg);">All Videos</h2>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>Bookmarks</th>
                    <th>Annotations</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="videosTableBody">
                <tr><td colspan="6" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>