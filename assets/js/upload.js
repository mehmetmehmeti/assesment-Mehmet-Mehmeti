// upload.js - Handles drag/drop and uploads to ../api/upload_video.php

document.addEventListener('DOMContentLoaded', () => {
  const uploadForm = document.getElementById('uploadForm');
  const uploadArea = document.getElementById('uploadArea');
  const videoFile = document.getElementById('videoFile');
  const titleEl = document.getElementById('title');
  const descEl = document.getElementById('description');

  if (!uploadForm || !uploadArea || !videoFile) return;

  // Click to choose file
  uploadArea.addEventListener('click', () => videoFile.click());

  // Drag & drop
  uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('drag-over');
  });

  uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('drag-over');
  });

  uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      videoFile.files = e.dataTransfer.files;
      showSelectedFile();
    }
  });

  videoFile.addEventListener('change', showSelectedFile);

  function showSelectedFile() {
    const f = videoFile.files?.[0];
    if (!f) return;
    uploadArea.innerHTML = `
      <div class="upload-icon">✅</div>
      <p><strong>${escapeHtml(f.name)}</strong></p>
      <p style="font-size: 0.9rem;">${Math.round(f.size / (1024 * 1024) * 10) / 10} MB</p>
      <p style="font-size: 0.85rem; color: var(--gray);">Click to change</p>
    `;
  }

  uploadForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const f = videoFile.files?.[0];
    if (!f) {
      alert('Please select a video file.');
      return;
    }

    const title = (titleEl?.value || '').trim();
    const description = (descEl?.value || '').trim();
    if (!title) {
      alert('Please enter a video title.');
      return;
    }

    const btn = uploadForm.querySelector('button[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Uploading…';
    }

    try {
      const fd = new FormData();
      fd.append('video', f);
      fd.append('title', title);
      fd.append('description', description);

      const res = await fetch('../api/upload_video.php', {
        method: 'POST',
        body: fd
      });

      const data = await res.json().catch(() => ({}));
      if (res.ok && data.success) {
        window.location.href = 'dashboard.php';
      } else {
        alert(data.error || 'Upload failed.');
      }
    } catch (err) {
      console.error(err);
      alert('Connection error.');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Upload Video';
      }
    }
  });
});

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
