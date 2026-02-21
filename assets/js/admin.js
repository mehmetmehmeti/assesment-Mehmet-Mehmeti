// admin.js - Admin dashboard (stats + all videos table)

document.addEventListener('DOMContentLoaded', () => {
  loadStats();
  loadAllVideos();
});

async function loadStats() {
  try {
    const res = await fetch('../api/admin/get_stats.php');
    const data = await res.json();
    if (!data.success) return;

    setText('totalVideos', data.stats.total_videos);
    setText('totalUsers', data.stats.total_users);
    setText('totalBookmarks', data.stats.total_bookmarks);
    setText('totalAnnotations', data.stats.total_annotations);
  } catch (e) {
    console.error('Stats error:', e);
  }
}

async function loadAllVideos() {
  const tbody = document.getElementById('videosTableBody');
  if (!tbody) return;

  try {
    const res = await fetch('../api/admin/get_all_videos.php');
    const data = await res.json();
    if (!data.success) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center">Failed to load</td></tr>';
      return;
    }

    const videos = data.videos || [];
    if (!videos.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center">No videos</td></tr>';
      return;
    }

    tbody.innerHTML = videos.map(v => {
      const d = v.created_at ? new Date(v.created_at).toLocaleDateString() : '-';
      const title = escapeHtml(v.title || v.original_name || v.filename);
      const email = escapeHtml(v.user_email || '-');
      const b = v.bookmark_count || 0;
      const a = v.annotation_count || 0;
      return `
        <tr>
          <td>${title}</td>
          <td>${email}</td>
          <td>${d}</td>
          <td>${b}</td>
          <td>${a}</td>
          <td><a class="btn btn-secondary" href="watch.php?id=${v.id}">View</a></td>
        </tr>
      `;
    }).join('');
  } catch (e) {
    console.error('Videos error:', e);
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Connection error</td></tr>';
  }
}

function setText(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
