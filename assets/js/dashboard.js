// Dashboard.js - Loads and displays user's videos

// Load videos when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadVideos();
});

async function loadVideos() {
    const videoGrid = document.getElementById('videoGrid');
    
    if (!videoGrid) return;
    
    // Show loading state
    videoGrid.innerHTML = '<div class="loading">Loading your videos...</div>';
    
    try {
        // NOTE: this file is loaded from /public, so we use a relative path to /api
        const response = await fetch('../api/get_videos.php');
        const data = await response.json();
        
        if (data.success) {
            displayVideos(data.videos);
        } else {
            videoGrid.innerHTML = '<div class="error">Failed to load videos</div>';
        }
    } catch (error) {
        videoGrid.innerHTML = '<div class="error">Connection error</div>';
        console.error('Dashboard error:', error);
    }
}

function displayVideos(videos) {
    const videoGrid = document.getElementById('videoGrid');
    
    if (!videos || videos.length === 0) {
        videoGrid.innerHTML = `
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: var(--space-md);">📹</div>
                <h3>No videos yet</h3>
                <p style="color: var(--gray); margin-bottom: var(--space-lg);">Upload your first video to get started</p>
                <a href="upload.php" class="btn btn-primary">Upload Video</a>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    videos.forEach(video => {
        // Format date
        const uploadDate = new Date(video.created_at).toLocaleDateString();
        
        html += `
            <div class="video-card" onclick="window.location.href='watch.php?id=${video.id}'">
                <div class="video-thumbnail">
                    <span class="video-duration">--:--</span>
                </div>
                <div class="video-info">
                    <h3>${escapeHtml(video.title || video.original_name || video.filename)}</h3>
                    <div class="video-meta">
                        <span>📌 ${video.bookmark_count || 0} bookmarks</span>
                        <span>✏️ ${video.annotation_count || 0} annotations</span>
                    </div>
                    <small style="color: var(--gray);">Uploaded ${uploadDate}</small>
                </div>
            </div>
        `;
    });
    
    videoGrid.innerHTML = html;
}

// Helper to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}