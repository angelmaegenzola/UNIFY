/* ══════════════════════════════════════════════════════════════
   UPLOAD: Avatar / Profile Picture
══════════════════════════════════════════════════════════════ */
async function uploadAvatar(input) {
  if (!input.files || !input.files[0]) return;

  const file = input.files[0];

  if (file.size > 3 * 1024 * 1024) {
    alert('Image must be under 3 MB.');
    input.value = '';
    return;
  }

  const formData = new FormData();
  formData.append('avatar', file);

  // Instant preview
  const reader = new FileReader();
  reader.onload = e => {
    const circle = document.getElementById('of-avatar-circle');
    if (circle) {
      circle.innerHTML =
        `<img src="${e.target.result}" alt="Preview" class="avatar-photo" id="of-avatar-img" />`;
    }
  };
  reader.readAsDataURL(file);

  showToast('Uploading…');

  try {
    const res  = await fetch('index.php?page=upload_avatar', {
      method: 'POST',
      body:   formData,
    });
    const data = await res.json();

    if (data.success) {
      showToast('Profile picture updated!');

      // Update sidebar avatar
      const sidebar = document.getElementById('of-sidebar-avatar');
      if (sidebar) {
        const img     = document.createElement('img');
        img.src       = data.url;
        img.alt       = 'Avatar';
        img.className = 'profile-avatar-img';
        img.id        = 'of-sidebar-avatar';
        sidebar.replaceWith(img);
      }

      // Update topbar avatar
      const topbar = document.getElementById('of-topbar-avatar');
      if (topbar) {
        topbar.innerHTML =
          `<img src="${data.url}" alt="Avatar"
                style="width:100%;height:100%;border-radius:50%;object-fit:cover;object-position:center;display:block;" />`;
      }

    } else {
      alert(data.message || 'Upload failed.');
      setTimeout(() => window.location.reload(), 1500);
    }
  } catch (err) {
    alert('Network error: ' + err.message);
    setTimeout(() => window.location.reload(), 1500);
  } finally {
    input.value = '';
  }
}

/* ── Toast for officer profile ── */
function showToast(msg) {
  const t   = document.getElementById('toast');
  const msg_el = document.getElementById('toast-msg');
  if (!t || !msg_el) return;
  msg_el.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3000);
}

const twofa = (() => {
    const ENDPOINT = window.location.href.split('?')[0] + '?page=setup_2fa';

    async function generate() {
        const btn = document.getElementById('btnGenerate');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

        try {
            const res = await fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=generate'
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('qrImage').src = data.qr_code;
                document.getElementById('manualSecret').textContent = data.secret;
                document.getElementById('qrStep').style.display = 'block';
                btn.style.display = 'none';
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shield-halved"></i> Enable 2FA';
            }
        } catch (e) {
            alert('Network error: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-shield-halved"></i> Enable 2FA';
        }
    }

    async function enable() {
        const otp = document.getElementById('confirmOtp').value.trim();
        const btn = document.querySelector('#qrStep .btn-primary');

        if (!/^\d{6}$/.test(otp)) {
            alert('Please enter a valid 6-digit code.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

        try {
            const res = await fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=enable&otp_code=${encodeURIComponent(otp)}`
            });
            const data = await res.json();

            if (data.success) {
                alert('✅ 2FA Enabled successfully!');
                window.location.reload();
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Confirm & Enable';
                document.getElementById('confirmOtp').value = '';
            }
        } catch (e) {
            alert('Network error: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Confirm & Enable';
        }
    }

    async function disable() {
        if (!confirm('Disable Two-Factor Authentication?')) return;

        const btn = document.getElementById('btnDisable');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Disabling...';

        try {
            const res = await fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=disable'
            });
            const data = await res.json();

            if (data.success) {
                alert('2FA has been disabled.');
                window.location.reload();
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shield-xmark"></i> Disable 2FA';
            }
        } catch (e) {
            alert('Network error: ' + e.message);
            btn.disabled = false;
        }
    }

    return { generate, enable, disable };
})();