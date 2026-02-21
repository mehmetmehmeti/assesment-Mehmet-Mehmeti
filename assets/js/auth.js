// auth.js - Handles login & registration using the PHP JSON APIs

function getEl(id) {
  return document.getElementById(id);
}

function showInlineMessage(container, msg, isError = false) {
  if (!container) {
    alert(msg);
    return;
  }
  container.textContent = msg;
  container.style.display = 'block';
  container.style.marginTop = '12px';
  container.style.padding = '10px 12px';
  container.style.borderRadius = '10px';
  container.style.background = isError ? 'rgba(239,68,68,0.12)' : 'rgba(16,185,129,0.12)';
  container.style.color = isError ? '#ef4444' : '#10b981';
}

document.addEventListener('DOMContentLoaded', () => {
  const loginForm = getEl('loginForm');
  const registerForm = getEl('registerForm');

  // Optional place to show messages (will be created if missing)
  let msgBox = document.querySelector('[data-auth-message]');
  if (!msgBox) {
    msgBox = document.createElement('div');
    msgBox.setAttribute('data-auth-message', '1');
    msgBox.style.display = 'none';
    // Put under the form if possible
    const host = loginForm || registerForm;
    host?.appendChild(msgBox);
  }

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      msgBox.style.display = 'none';

      const email = (getEl('email')?.value || '').trim();
      const password = getEl('password')?.value || '';

      if (!email || !password) {
        showInlineMessage(msgBox, 'Email and password are required.', true);
        return;
      }

      try {
        const res = await fetch('../api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });

        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
          window.location.href = 'dashboard.php';
        } else {
          showInlineMessage(msgBox, data.error || 'Login failed.', true);
        }
      } catch (err) {
        console.error(err);
        showInlineMessage(msgBox, 'Connection error. Check your server.', true);
      }
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      msgBox.style.display = 'none';

      const email = (getEl('email')?.value || '').trim();
      const password = getEl('password')?.value || '';
      const confirm_password = getEl('confirm_password')?.value || '';

      if (!email || !password || !confirm_password) {
        showInlineMessage(msgBox, 'All fields are required.', true);
        return;
      }

      try {
        const res = await fetch('../api/register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password, confirm_password })
        });

        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
          showInlineMessage(msgBox, 'Registration successful. Redirecting to login…', false);
          setTimeout(() => {
            window.location.href = 'index.php';
          }, 700);
        } else {
          const msg = (data.errors && Array.isArray(data.errors))
            ? data.errors.join('\n')
            : (data.error || 'Registration failed.');
          showInlineMessage(msgBox, msg, true);
        }
      } catch (err) {
        console.error(err);
        showInlineMessage(msgBox, 'Connection error. Check your server.', true);
      }
    });
  }
});
