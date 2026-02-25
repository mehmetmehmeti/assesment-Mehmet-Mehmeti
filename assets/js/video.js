// video.js - Complete video player with bookmarks and annotations

// DOM Elements
const video = document.getElementById('videoPlayer');
const canvas = document.getElementById('annotationCanvas');
const ctx = canvas?.getContext('2d');
const progressBar = document.getElementById('progressBar');
const progressFill = document.getElementById('progressFill');
const playPauseBtn = document.getElementById('playPauseBtn');
const timeDisplay = document.getElementById('timeDisplay');
const bookmarksPanel = document.getElementById('bookmarksPanel');
const annotationsPanel = document.getElementById('annotationsPanel');
const addBookmarkBtn = document.getElementById('addBookmarkBtn');
const addAnnotationBtn = document.getElementById('addAnnotationBtn');
const deleteSelectedAnnotationsBtn = document.getElementById('deleteSelectedAnnotationsBtn');
const annotationPopup = document.getElementById('annotationPopup');
const annotationPopupText = document.getElementById('annotationPopupText');

// State
let isDrawing = false;
let currentTool = 'freehand';
let currentColor = '#ef4444';
let startX, startY;
let annotations = [];
let bookmarks = [];
let lastPlaybackAnnotationSecond = -1;
let annotationPopupTimer = null;
let pendingAnnotationSession = null;
let pendingAnnotationDraft = null;
let isSavingPendingAnnotation = false;
let skipNextPlayIntercept = false;
let selectedAnnotationIds = new Set();

// ✅ NEW: store freehand points while drawing
let freehandPoints = [];

// ============================================
// VIDEO PLAYER CONTROLS
// ============================================

// Play/Pause
playPauseBtn?.addEventListener('click', togglePlay);

async function togglePlay() {
    if (video.paused) {
        await savePendingAnnotationDraftIfNeeded();
        video.play();
        playPauseBtn.textContent = '⏸ Pause';
    } else {
        video.pause();
        playPauseBtn.textContent = '▶ Play';
    }
}

// Update progress bar and time
video?.addEventListener('timeupdate', () => {
    if (video.duration) {
        const percent = (video.currentTime / video.duration) * 100;
        if (progressFill) progressFill.style.width = percent + '%';

        // Update time display
        const current = formatTime(video.currentTime);
        const duration = formatTime(video.duration);
        if (timeDisplay) timeDisplay.textContent = `${current} / ${duration}`;
    }

    handlePlaybackAnnotations();
});

video?.addEventListener('seeked', () => {
    lastPlaybackAnnotationSecond = -1;
    clearCanvasAndPopup();
});

video?.addEventListener('play', async () => {
    if (skipNextPlayIntercept) {
        skipNextPlayIntercept = false;
        return;
    }

    if (!pendingAnnotationDraft || isSavingPendingAnnotation) return;

    video.pause();
    const saved = await savePendingAnnotationDraftIfNeeded();

    if (saved !== false) {
        skipNextPlayIntercept = true;
        video.play();
        if (playPauseBtn) playPauseBtn.textContent = '⏸ Pause';
    }
});

// Click on progress bar to seek
progressBar?.addEventListener('click', (e) => {
    const rect = progressBar.getBoundingClientRect();
    const pos = (e.clientX - rect.left) / rect.width;
    video.currentTime = pos * video.duration;
});

// Format time helper
function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

// ============================================
// LOAD BOOKMARKS & ANNOTATIONS
// ============================================

// Load video details and set player source
async function loadVideoDetails() {
    if (!videoId || !video) return;

    try {
        const response = await fetch(`../api/get_single_video.php?id=${videoId}`);
        const data = await response.json();

        if (data.success && data.video?.filename) {
            // Uploaded files are stored in /public/uploads/videos/
            video.src = `uploads/videos/${data.video.filename}`;
        } else {
            alert(data.error || 'Video not found');
        }
    } catch (error) {
        console.error('Failed to load video:', error);
        alert('Failed to load video');
    }
}

// Load bookmarks when page loads
async function loadBookmarks() {
    if (!videoId) return;

    try {
        const response = await fetch(`../api/get_bookmarks.php?video_id=${videoId}`);
        const data = await response.json();

        if (data.success) {
            bookmarks = data.bookmarks;
            displayBookmarks();
        }
    } catch (error) {
        console.error('Failed to load bookmarks:', error);
    }
}

// Load annotations when page loads
async function loadAnnotations() {
    if (!videoId) return;

    try {
        const response = await fetch(`../api/get_annotations.php?video_id=${videoId}`);
        const data = await response.json();

        if (data.success) {
            annotations = data.annotations || [];
            annotations.sort((a, b) => Number(a.timestamp) - Number(b.timestamp));
            selectedAnnotationIds = new Set(
                [...selectedAnnotationIds].filter(id => annotations.some(a => Number(a.id) === Number(id)))
            );
            if (document.getElementById('annotationsTab')?.classList.contains('active')) {
                displayAnnotations();
            }
            updateDeleteSelectedAnnotationsUI();
        }
    } catch (error) {
        console.error('Failed to load annotations:', error);
    }
}

// Display bookmarks in sidebar
function displayBookmarks() {
    if (!bookmarksPanel) return;

    if (!bookmarks || bookmarks.length === 0) {
        bookmarksPanel.innerHTML = `
            <div style="text-align: center; padding: var(--space-lg); color: var(--gray);">
                No bookmarks yet<br>
                <small>Click "Add Bookmark" while watching</small>
            </div>
        `;
        return;
    }

    let html = '';
    bookmarks.forEach(bookmark => {
        html += `
            <div class="bookmark-item" onclick="jumpToTime(${bookmark.timestamp})">
                <div>
                    <strong>${escapeHtml(bookmark.title)}</strong>
                </div>
                <span class="bookmark-time">${bookmark.time_formatted || formatTime(bookmark.timestamp)}</span>
            </div>
        `;
    });

    bookmarksPanel.innerHTML = html;
}

// Jump to bookmark time
window.jumpToTime = function(seconds) {
    video.currentTime = seconds;
    video.play();
}

// ============================================
// ADD BOOKMARK
// ============================================

addBookmarkBtn?.addEventListener('click', async () => {
    const currentTime = Math.floor(video.currentTime);
    const title = prompt('Enter bookmark title:', `Bookmark at ${formatTime(currentTime)}`);

    if (!title) return;

    try {
        const response = await fetch('../api/add_bookmark.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                video_id: videoId,
                timestamp: currentTime,
                title: title
            })
        });

        const data = await response.json();

        if (data.success) {
            // Reload bookmarks
            await loadBookmarks();
            alert('Bookmark added!');
        } else {
            alert(data.error || 'Failed to add bookmark');
        }
    } catch (error) {
        alert('Connection error');
        console.error('Add bookmark error:', error);
    }
});

async function openAddAnnotationPrompt() {
    if (!video) {
        alert('Video player not found on this page.');
        return;
    }

    const currentTime = Math.floor(video.currentTime || 0);
    const defaultText = `Annotation at ${formatTime(currentTime)}`;
    const description = prompt('Enter annotation text:', defaultText);

    if (!description || !description.trim()) return;

    pendingAnnotationSession = {
        timestamp: currentTime,
        description: description.trim()
    };
    pendingAnnotationDraft = null;

    // Annotation drawing is easier while paused.
    video.pause();
    if (playPauseBtn) playPauseBtn.textContent = '▶ Play';

    showAnnotationPopup(`Annotation started: ${pendingAnnotationSession.description}`);
}

window.openAddAnnotationPrompt = openAddAnnotationPrompt;

function bindAddAnnotationButton() {
    const btn = document.getElementById('addAnnotationBtn');
    if (!btn) return;

    btn.onclick = (e) => {
        e.preventDefault();
        window.openAddAnnotationPrompt();
    };
}

bindAddAnnotationButton();

// ============================================
// ANNOTATION DRAWING
// ============================================

// Setup canvas
function setupCanvas() {
    if (!canvas || !video) return;

    canvas.width = video.offsetWidth;
    canvas.height = video.offsetHeight;
    canvas.style.position = 'absolute';
    canvas.style.top = video.offsetTop + 'px';
    canvas.style.left = video.offsetLeft + 'px';
    canvas.style.pointerEvents = 'none'; // Let clicks pass through when not drawing
}

window.addEventListener('resize', setupCanvas);
video?.addEventListener('loadedmetadata', setupCanvas);

// Drawing tools
document.querySelectorAll('.tool-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTool = btn.dataset.tool;

        // Enable drawing on canvas
        if (canvas) {
            canvas.style.pointerEvents = 'auto';
        }
    });
});

// Color picker
document.querySelectorAll('.color-option').forEach(color => {
    color.addEventListener('click', () => {
        document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
        color.classList.add('selected');
        currentColor = color.dataset.color;
    });
});

// Mouse events for drawing
canvas?.addEventListener('mousedown', startDrawing);
canvas?.addEventListener('mousemove', draw);
canvas?.addEventListener('mouseup', stopDrawing);
canvas?.addEventListener('mouseleave', stopDrawing);

function startDrawing(e) {
    if (!pendingAnnotationSession) {
        showAnnotationPopup('Click "Add Annotation" first, then draw, then press Play to save.');
        return;
    }

    isDrawing = true;
    renderPendingDraftOnCanvas();

    const rect = canvas.getBoundingClientRect();
    startX = e.clientX - rect.left;
    startY = e.clientY - rect.top;

    ctx.strokeStyle = currentColor;
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';

    // ✅ NEW: reset points and store first point for freehand
    if (currentTool === 'freehand') {
        freehandPoints = [{ x: startX, y: startY }];
        ctx.beginPath();
        ctx.moveTo(startX, startY);
    }
}

function draw(e) {
    if (!isDrawing) return;

    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    ctx.strokeStyle = currentColor;
    ctx.lineWidth = 3;

    if (currentTool === 'freehand') {
        // ✅ NEW: store points as user draws
        freehandPoints.push({ x, y });

        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
    } else if (currentTool === 'rectangle') {
        // Clear and redraw for preview (still simplified)
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        renderPendingDraftOnCanvas(false);
        ctx.strokeRect(startX, startY, x - startX, y - startY);
    } else if (currentTool === 'circle') {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        renderPendingDraftOnCanvas(false);
        const radius = Math.sqrt(Math.pow(x - startX, 2) + Math.pow(y - startY, 2));
        ctx.beginPath();
        ctx.arc(startX, startY, radius, 0, 2 * Math.PI);
        ctx.stroke();
    } else if (currentTool === 'arrow') {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        renderPendingDraftOnCanvas(false);
        drawArrow(startX, startY, x, y, currentColor);
    }
}

function stopDrawing(e) {
    if (!isDrawing) return;

    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    ctx.strokeStyle = currentColor;
    ctx.lineWidth = 3;

    // ✅ Build coordinates + include style
    let coordinates = {};
    let shapeType = currentTool;

    switch(currentTool) {
        case 'rectangle':
            coordinates = {
                x: Math.min(startX, x),
                y: Math.min(startY, y),
                width: Math.abs(x - startX),
                height: Math.abs(y - startY),
                color: currentColor,
                lineWidth: 3
            };
            ctx.strokeRect(coordinates.x, coordinates.y, coordinates.width, coordinates.height);
            break;

        case 'circle':
            const radius = Math.sqrt(Math.pow(x - startX, 2) + Math.pow(y - startY, 2));
            coordinates = { x: startX, y: startY, radius, color: currentColor, lineWidth: 3 };
            ctx.beginPath();
            ctx.arc(startX, startY, radius, 0, 2 * Math.PI);
            ctx.stroke();
            break;

        case 'arrow':
            coordinates = { startX, startY, endX: x, endY: y, color: currentColor, lineWidth: 3 };
            drawArrow(startX, startY, x, y, currentColor);
            break;

        case 'freehand':
            shapeType = 'freehand';
            coordinates = { points: freehandPoints, color: currentColor, lineWidth: 3 };
            break;
    }

    const annotationData = {
        tool: shapeType,
        ...coordinates
    };

    appendToPendingAnnotationDraft(annotationData);
    renderPendingDraftOnCanvas();
    showAnnotationPopup('Drawing captured. Keep drawing or click Play to save annotation.');

    isDrawing = false;
}

// ✅ Helper: draw arrow
function drawArrow(x1, y1, x2, y2, color) {
    // Draw line
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.strokeStyle = color;
    ctx.stroke();

    // Draw arrow head
    const angle = Math.atan2(y2 - y1, x2 - x1);
    ctx.beginPath();
    ctx.moveTo(x2, y2);
    ctx.lineTo(x2 - 15 * Math.cos(angle - 0.3), y2 - 15 * Math.sin(angle - 0.3));
    ctx.lineTo(x2 - 15 * Math.cos(angle + 0.3), y2 - 15 * Math.sin(angle + 0.3));
    ctx.closePath();
    ctx.fillStyle = color;
    ctx.fill();
}

async function createAnnotation({ timestamp, description, data }) {
    try {
        const response = await fetch('../api/add_annotation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                video_id: videoId,
                timestamp,
                description,
                data
            })
        });

        const result = await response.json();

        if (result.success) {
            await loadAnnotations();
            if (description) {
                showAnnotationPopup(description);
            }
            return true;
        } else {
            alert(result.error || 'Failed to add annotation');
            console.error(result.error || 'Failed to save annotation');
            return false;
        }
    } catch (error) {
        alert('Connection error');
        console.error('Failed to save annotation:', error);
        return false;
    }
}

function appendToPendingAnnotationDraft(annotationData) {
    if (!pendingAnnotationSession) return;

    const items = pendingAnnotationDraft?.data?.tool === 'group' && Array.isArray(pendingAnnotationDraft.data.items)
        ? [...pendingAnnotationDraft.data.items, annotationData]
        : [annotationData];

    pendingAnnotationDraft = {
        timestamp: pendingAnnotationSession.timestamp,
        description: pendingAnnotationSession.description,
        data: {
            tool: 'group',
            items
        }
    };
}

function renderPendingDraftOnCanvas(clearFirst = true) {
    if (!canvas || !ctx || !pendingAnnotationDraft?.data) return;
    if (clearFirst) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    drawParsedAnnotation(pendingAnnotationDraft.data);
}

async function savePendingAnnotationDraftIfNeeded() {
    if (isSavingPendingAnnotation) return false;
    if (!pendingAnnotationDraft) return true;

    isSavingPendingAnnotation = true;
    const draftToSave = { ...pendingAnnotationDraft };

    const saved = await createAnnotation(draftToSave);

    if (saved) {
        pendingAnnotationDraft = null;
        pendingAnnotationSession = null;
    }

    isSavingPendingAnnotation = false;
    return saved;
}

// ============================================
// TABS (Bookmarks/Annotations)
// ============================================

document.getElementById('bookmarksTab')?.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('bookmarksTab').classList.add('active');
    document.getElementById('bookmarksPanel').style.display = 'block';
    document.getElementById('annotationsPanel').style.display = 'none';
    updateDeleteSelectedAnnotationsUI();
});

document.getElementById('annotationsTab')?.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('annotationsTab').classList.add('active');
    document.getElementById('annotationsPanel').style.display = 'block';
    document.getElementById('bookmarksPanel').style.display = 'none';

    // Display annotations
    displayAnnotations();
    updateDeleteSelectedAnnotationsUI();
});

function displayAnnotations() {
    if (!annotationsPanel) return;

    if (!annotations || annotations.length === 0) {
        annotationsPanel.innerHTML = `
            <div style="text-align: center; padding: var(--space-lg); color: var(--gray);">
                No annotations yet<br>
                <small>Use drawing tools while watching</small>
            </div>
        `;
        return;
    }

    let html = '';
    annotations.forEach(ann => {
        const annId = Number(ann.id);
        const isChecked = selectedAnnotationIds.has(annId) ? 'checked' : '';
        html += `
            <div class="bookmark-item" onclick="jumpToTime(${ann.timestamp}); previewAnnotation(${ann.id});">
                <div style="display:flex; align-items:flex-start; gap:8px; width:100%;">
                    <input type="checkbox" ${isChecked}
                        onclick="event.stopPropagation()"
                        onchange="toggleAnnotationSelection(${ann.id}, this.checked)">
                    <div style="flex:1;">
                        <strong>${escapeHtml(ann.description)}</strong>
                        <div style="font-size: 0.8rem; color: var(--gray);">${ann.time_formatted || formatTime(ann.timestamp)}</div>
                    </div>
                </div>
            </div>
        `;
    });

    annotationsPanel.innerHTML = html;
}

window.toggleAnnotationSelection = function(annotationId, checked) {
    const id = Number(annotationId);
    if (checked) {
        selectedAnnotationIds.add(id);
    } else {
        selectedAnnotationIds.delete(id);
    }
    updateDeleteSelectedAnnotationsUI();
};

function updateDeleteSelectedAnnotationsUI() {
    if (!deleteSelectedAnnotationsBtn) return;

    const annotationsTabActive = document.getElementById('annotationsTab')?.classList.contains('active');
    deleteSelectedAnnotationsBtn.style.display = annotationsTabActive ? 'block' : 'none';

    const count = selectedAnnotationIds.size;
    deleteSelectedAnnotationsBtn.disabled = count === 0;
    deleteSelectedAnnotationsBtn.textContent = count > 0
        ? `Delete Selected Annotations (${count})`
        : 'Delete Selected Annotations';
}

deleteSelectedAnnotationsBtn?.addEventListener('click', async () => {
    if (selectedAnnotationIds.size === 0) return;

    const ids = [...selectedAnnotationIds];
    const confirmed = confirm(`Delete ${ids.length} selected annotation(s)?`);
    if (!confirmed) return;

    deleteSelectedAnnotationsBtn.disabled = true;

    let failed = 0;
    for (const id of ids) {
        try {
            const response = await fetch(`../api/delete_annotation.php?id=${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();
            if (!result.success) failed += 1;
        } catch (error) {
            failed += 1;
            console.error(`Failed to delete annotation ${id}:`, error);
        }
    }

    if (failed > 0) {
        alert(`${failed} annotation(s) could not be deleted.`);
    }

    selectedAnnotationIds.clear();
    clearCanvasAndPopup();
    await loadAnnotations();
    displayAnnotations();
    updateDeleteSelectedAnnotationsUI();
});

window.previewAnnotation = function(annotationId) {
    const ann = annotations.find(a => Number(a.id) === Number(annotationId));
    if (!ann) return;

    renderSavedAnnotation(annotationId);
    showAnnotationPopup(ann.description || 'Annotation');
};

// ✅ NEW: Render saved annotation from DB `data`
window.renderSavedAnnotation = function(annotationId) {
    if (!canvas || !ctx) return;

    const ann = annotations.find(a => Number(a.id) === Number(annotationId));
    if (!ann) return;

    const parsed = parseAnnotationData(ann.data);

    // Clear canvas and redraw only that annotation
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    drawParsedAnnotation(parsed);
};

function drawParsedAnnotation(parsed) {
    if (!canvas || !ctx || !parsed) return;

    if (parsed.tool === 'note') return;

    if (parsed.tool === 'group' && Array.isArray(parsed.items)) {
        parsed.items.forEach(drawParsedAnnotation);
        return;
    }

    ctx.strokeStyle = parsed.color || '#ef4444';
    ctx.lineWidth = parsed.lineWidth || 3;

    if (parsed.tool === 'freehand' && Array.isArray(parsed.points)) {
        ctx.beginPath();
        parsed.points.forEach((p, i) => {
            if (i === 0) ctx.moveTo(p.x, p.y);
            else ctx.lineTo(p.x, p.y);
        });
        ctx.stroke();
        return;
    }

    if (parsed.tool === 'rectangle') {
        ctx.strokeRect(parsed.x, parsed.y, parsed.width, parsed.height);
        return;
    }

    if (parsed.tool === 'circle') {
        ctx.beginPath();
        ctx.arc(parsed.x, parsed.y, parsed.radius, 0, 2 * Math.PI);
        ctx.stroke();
        return;
    }

    if (parsed.tool === 'arrow') {
        drawArrow(parsed.startX, parsed.startY, parsed.endX, parsed.endY, parsed.color || '#ef4444');
    }
}

function parseAnnotationData(rawData) {
    if (!rawData) return null;

    try {
        return typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
    } catch (e) {
        console.error('Invalid annotation JSON in DB:', e);
        return null;
    }
}

function handlePlaybackAnnotations() {
    if (!video) return;

    const currentSecond = Math.floor(video.currentTime);
    if (currentSecond === lastPlaybackAnnotationSecond) return;
    lastPlaybackAnnotationSecond = currentSecond;

    const matches = annotations.filter(a => Number(a.timestamp) === currentSecond);
    if (matches.length === 0) return;

    const annotationToShow = matches[matches.length - 1];
    renderSavedAnnotation(annotationToShow.id);
    showAnnotationPopup(annotationToShow.description || 'Annotation');
}

function showAnnotationPopup(text) {
    if (!annotationPopup || !annotationPopupText) return;

    annotationPopupText.textContent = text;
    annotationPopup.style.display = 'block';

    if (annotationPopupTimer) {
        clearTimeout(annotationPopupTimer);
    }

    annotationPopupTimer = setTimeout(() => {
        annotationPopup.style.display = 'none';
        if (canvas && ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    }, 3000);
}

function clearCanvasAndPopup() {
    if (canvas && ctx) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    if (annotationPopup) {
        annotationPopup.style.display = 'none';
    }

    if (annotationPopupTimer) {
        clearTimeout(annotationPopupTimer);
        annotationPopupTimer = null;
    }
}

// Helper to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
    bindAddAnnotationButton();

    if (document.getElementById('videoPlayer')) {
        await loadVideoDetails();
        await loadBookmarks();
        await loadAnnotations();
        setupCanvas();
    }
});
