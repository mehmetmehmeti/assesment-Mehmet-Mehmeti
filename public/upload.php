<!-- public/upload.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video - Video Annotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
   
    
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">← Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="upload-container">
            <h1 style="margin-bottom: var(--space-lg);">Upload New Video</h1>
            
            <form id="uploadForm" enctype="multipart/form-data">
                <!-- Drag & Drop Area -->
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📁</div>
                    <p><strong>Click to upload</strong> or drag and drop</p>
                    <p style="font-size: 0.9rem;">MP4, WebM, OGV (Max 100MB)</p>
                    <input type="file" id="videoFile" name="video" accept="video/*" style="display: none;">
                </div>
                
                <!-- Video Details -->
                <div class="form-group">
                    <label for="title">Video Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Product Demo 2024" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <textarea id="description" name="description" rows="3" placeholder="Brief description of the video"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Upload Video</button>
            </form>
        </div>
    </div>
    
    <script src="assets/js/upload.js"></script>
</body>
</html>