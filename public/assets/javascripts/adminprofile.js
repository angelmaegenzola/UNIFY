/* ============================================================
   UNIFY — Admin Profile JS  (adminprofile.js)
============================================================ */

/* ── Tab Switcher ─────────────────────────────────────────── */
function switchTab(name, btn) {
  // Hide all panels
  document.querySelectorAll('.tab-panel').forEach(p => {
    p.style.display = 'none';
  });

  // Show selected panel
  const panel = document.getElementById('tab-' + name);
  if (panel) {
    panel.style.display    = 'flex';
    panel.style.flexDirection = 'column';
    panel.style.gap        = '0';
  }

  // Update active nav button
  document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  // Scroll right panel to top
  const right = document.querySelector('.profile-right');
  if (right) right.scrollTop = 0;
}

/* ── Password Strength Meter ──────────────────────────────── */
function checkStrength(val) {
  const bars  = [1,2,3,4].map(i => document.getElementById('bar' + i));
  const label = document.getElementById('strengthLabel');
  if (!bars[0] || !label) return;

  let score = 0;
  if (val.length >= 8)          score++;
  if (/[A-Z]/.test(val))        score++;
  if (/[0-9]/.test(val))        score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levelClass = ['', 'weak', 'weak', 'medium', 'strong'];
  bars.forEach((bar, i) => {
    bar.className = 'strength-bar';
    if (i < score) bar.classList.add(levelClass[score]);
  });

  const labels  = ['', 'Weak', 'Fair', 'Good', 'Strong'];
  const classes = ['', 'weak', 'medium', 'medium', 'strong'];
  label.textContent = val.length ? labels[score] : 'Enter a password';
  label.className   = 'strength-label ' + (val.length ? classes[score] : '');
}

/* ── Password Match Checker ───────────────────────────────── */
function checkMatch() {
  const np   = document.getElementById('newPass');
  const cp   = document.getElementById('confirmPass');
  const hint = document.getElementById('matchHint');
  if (!np || !cp || !hint) return;

  if (!cp.value) { hint.textContent = ''; hint.className = 'input-hint'; return; }

  if (np.value === cp.value) {
    hint.textContent = '✓ Passwords match';
    hint.className   = 'input-hint success';
  } else {
    hint.textContent = '✗ Passwords do not match';
    hint.className   = 'input-hint error';
  }
}

/* ── Toast Notification ───────────────────────────────────── */
function showToast(msg, isError = false) {
  const toast = document.getElementById('toast');
  const msgEl = document.getElementById('toast-msg');
  if (!toast || !msgEl) return;

  msgEl.textContent = msg || 'Changes saved!';
  toast.style.background = isError ? 'var(--red-accent)' : 'var(--green-dark)';
  toast.classList.add('show');

  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), 3200);
}

/* ── Danger Zone Confirms ─────────────────────────────────── */
function confirmDeactivate() {
  if (confirm('Are you sure you want to deactivate your account?\nYou can reactivate it by logging in again.')) {
    showToast('Account deactivation requested.');
  }
}

function confirmDelete() {
  const input = prompt('Type DELETE to permanently remove your account:');
  if (input === 'DELETE') {
    return true; // allows the form to submit
  }
  showToast('Account deletion cancelled.', true);
  return false;
}

/* ── Alert auto-dismiss ───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {

  // Init first tab
  const firstPanel = document.getElementById('tab-personal');
  if (firstPanel) {
    firstPanel.style.display      = 'flex';
    firstPanel.style.flexDirection = 'column';
    firstPanel.style.gap          = '0';
  }

  // Auto-dismiss success/error alerts after 4s
  document.querySelectorAll('.alert-success, .alert-error').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity    = '0';
      setTimeout(() => el.remove(), 500);
    }, 4000);
  });

});
/* ── Avatar Upload ─────────────────────────────────────────── */
const AdminProfile = (() => {

  async function uploadAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    if (file.size > 3 * 1024 * 1024) {
      showToast('Image must be under 3 MB.', true);
      input.value = '';
      return;
    }

    // Instant preview
    const reader = new FileReader();
    reader.onload = e => {
      const circle = document.getElementById('ap-avatar-circle');
      if (circle) {
        circle.innerHTML =
          `<img src="${e.target.result}" alt="Preview" class="avatar-photo" id="ap-avatar-img" />` +
          `<input type="file" id="ap-avatar-input" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="AdminProfile.uploadAvatar(this)" />`;
      }
    };
    reader.readAsDataURL(file);

    showToast('Uploading…');

    const formData = new FormData();
    formData.append('avatar', file);

    try {
      const res  = await fetch('index.php?page=upload_avatar', { method: 'POST', body: formData });
      const data = await res.json();

      if (data.success) {
        showToast('Profile picture updated!');

        // Update sidebar avatar
        const wrap = document.querySelector('.profile-avatar-wrap');
        if (wrap) {
          const existing = wrap.querySelector('img.profile-avatar-img, span.profile-avatar-fallback');
          const img = document.createElement('img');
          img.src       = data.url;
          img.alt       = 'Avatar';
          img.className = 'profile-avatar-img';
          if (existing) existing.replaceWith(img);
          else wrap.prepend(img);
        }
      } else {
        showToast(data.message || 'Upload failed.', true);
        setTimeout(() => window.location.reload(), 1500);
      }
    } catch (err) {
      showToast('Network error: ' + err.message, true);
      setTimeout(() => window.location.reload(), 1500);
    } finally {
      input.value = '';
    }
  }

  return { uploadAvatar };
})();
