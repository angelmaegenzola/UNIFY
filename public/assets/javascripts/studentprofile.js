/**
 * UNIFY — Student Profile JS
 * public/assets/javascripts/studentprofile.js
 *
 * Handles:
 *   - Modal open / close (personal, academic, password, twofa)
 *   - AJAX saves for personal & academic info
 *   - AJAX password change
 *   - Full 2FA setup / disable flow (generate QR, confirm OTP, disable)
 *   - Password strength meter & match checker
 *   - Toast notifications
 */

const StudentProfile = (() => {

  /* ── Config ──────────────────────────────────────────────────── */
  const SAVE_ENDPOINT  = window.SP_DATA?.saveEndpoint  || 'index.php?page=studentprofile_save';
  const TWOFA_ENDPOINT = window.SP_DATA?.twoFaEndpoint || 'index.php?page=setup_2fa';

  /* ══════════════════════════════════════════════════════════════
     TOAST
  ══════════════════════════════════════════════════════════════ */
  let _toastTimer = null;

  function showToast(msg, type = 'success') {
    const el  = document.getElementById('sp-toast');
    const txt = document.getElementById('sp-toast-msg');
    const ico = document.getElementById('sp-toast-icon');
    if (!el) return;

    clearTimeout(_toastTimer);

    const icons = {
      success: 'fa-circle-check',
      error:   'fa-circle-exclamation',
      info:    'fa-circle-info',
    };

    el.className = `crud-toast crud-toast-${type} crud-toast-show`;
    ico.className = `fas ${icons[type] || icons.success}`;
    txt.textContent = msg;

    _toastTimer = setTimeout(() => {
      el.classList.remove('crud-toast-show');
    }, 3500);
  }

  /* ══════════════════════════════════════════════════════════════
     MODAL HELPERS
  ══════════════════════════════════════════════════════════════ */
  function openModal(name) {
    const overlay = document.getElementById(`modal-${name}`);
    if (!overlay) return;
    overlay.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(name) {
    const overlay = document.getElementById(`modal-${name}`);
    if (!overlay) return;
    overlay.classList.remove('modal-open');
    document.body.style.overflow = '';
  }

  // Close on backdrop click
  document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
      e.target.classList.remove('modal-open');
      document.body.style.overflow = '';
    }
  });

  // ESC key closes modals
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.modal-open').forEach(m => {
        m.classList.remove('modal-open');
      });
      document.body.style.overflow = '';
    }
  });

  /* ══════════════════════════════════════════════════════════════
     HELPERS
  ══════════════════════════════════════════════════════════════ */
  function val(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : '';
  }

  async function postJSON(endpoint, body) {
    const params = new URLSearchParams(body);
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: params.toString(),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  function setBtnLoading(btn, loading, originalHTML) {
    if (loading) {
      btn.disabled = true;
      btn._original = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    } else {
      btn.disabled = false;
      btn.innerHTML = originalHTML || btn._original || 'Save';
    }
  }

  /* ══════════════════════════════════════════════════════════════
     SAVE: Personal Info
  ══════════════════════════════════════════════════════════════ */
  async function savePersonal() {
    const btn = document.querySelector('#modal-personal .modal-btn-save');

    const first_name = val('pi_first_name');
    const last_name  = val('pi_last_name');
    const email      = val('pi_email');

    if (!first_name || !last_name || !email) {
      showToast('First name, last name, and email are required.', 'error');
      return;
    }

    const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRx.test(email)) {
      showToast('Please enter a valid email address.', 'error');
      return;
    }

    setBtnLoading(btn, true);

    try {
      const data = await postJSON(SAVE_ENDPOINT, {
        action:      'save_personal',
        first_name,
        last_name,
        username:    val('pi_username'),
        email,
        phone:       val('pi_phone'),
        dob:         val('pi_dob'),
        gender:      val('pi_gender'),
        nationality: val('pi_nationality'),
        address:     val('pi_address'),
      });

      if (data.success) {
        showToast(data.message || 'Personal info updated!', 'success');
        closeModal('personal');
        // Live-update the displayed name
        const fn = document.getElementById('pi_first_name')?.value?.trim() || '';
        const ln = document.getElementById('pi_last_name')?.value?.trim()  || '';
        const fullName = `${fn} ${ln}`.trim();
        document.querySelectorAll('.avatar-name, .profile-name').forEach(el => el.textContent = fullName);
        // Update initials
        const init = (fn[0] || '') + (ln[0] || '');
        document.querySelectorAll('.avatar-circle').forEach(el => el.textContent = init.toUpperCase());
        document.querySelectorAll('.profile-avatar-fallback').forEach(el => el.textContent = (fn[0] || '').toUpperCase());
        // Reload after short delay to refresh all values
        setTimeout(() => window.location.reload(), 1200);
      } else {
        showToast(data.message || 'Failed to save. Please try again.', 'error');
      }
    } catch (err) {
      showToast('Network error: ' + err.message, 'error');
    } finally {
      setBtnLoading(btn, false);
    }
  }

  /* ══════════════════════════════════════════════════════════════
     SAVE: Academic Info
  ══════════════════════════════════════════════════════════════ */
  async function saveAcademic() {
    const btn = document.querySelector('#modal-academic .modal-btn-save');
    setBtnLoading(btn, true);

    try {
      const data = await postJSON(SAVE_ENDPOINT, {
        action:        'save_academic',
        student_id:    val('ac_student_id'),
        department:    val('ac_department'),
        course:        val('ac_course'),
        year_level:    val('ac_year_level'),
        section:       val('ac_section'),
        academic_year: val('ac_academic_year'),
        campus:        val('ac_campus'),
      });

      if (data.success) {
        showToast(data.message || 'Academic info updated!', 'success');
        closeModal('academic');
        setTimeout(() => window.location.reload(), 1200);
      } else {
        showToast(data.message || 'Failed to save. Please try again.', 'error');
      }
    } catch (err) {
      showToast('Network error: ' + err.message, 'error');
    } finally {
      setBtnLoading(btn, false);
    }
  }

  /* ══════════════════════════════════════════════════════════════
     SAVE: Password
  ══════════════════════════════════════════════════════════════ */
  async function savePassword() {
    const current = val('pw_current');
    const newPw   = val('pw_new');
    const confirm = val('pw_confirm');
    const btn     = document.querySelector('#modal-password .modal-btn-save');

    if (!current || !newPw || !confirm) {
      showToast('All password fields are required.', 'error');
      return;
    }
    if (newPw.length < 8) {
      showToast('New password must be at least 8 characters.', 'error');
      return;
    }
    if (newPw !== confirm) {
      showToast('Passwords do not match.', 'error');
      return;
    }

    setBtnLoading(btn, true);

    try {
      const data = await postJSON(SAVE_ENDPOINT, {
        action:           'save_password',
        current_password: current,
        new_password:     newPw,
        confirm_password: confirm,
      });

      if (data.success) {
        showToast(data.message || 'Password updated!', 'success');
        closeModal('password');
        // Reset fields
        ['pw_current','pw_new','pw_confirm'].forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });
        document.getElementById('pw-strength-fill').style.width = '0';
        document.getElementById('pw-strength-label').textContent = '—';
        document.getElementById('pw-match-msg').textContent = '';
      } else {
        showToast(data.message || 'Password update failed.', 'error');
      }
    } catch (err) {
      showToast('Network error: ' + err.message, 'error');
    } finally {
      setBtnLoading(btn, false);
    }
  }

  /* ══════════════════════════════════════════════════════════════
     PASSWORD STRENGTH & MATCH
  ══════════════════════════════════════════════════════════════ */
  function checkStrength(value) {
    const fill  = document.getElementById('pw-strength-fill');
    const label = document.getElementById('pw-strength-label');
    if (!fill || !label) return;

    let score = 0;
    if (value.length >= 8)           score++;
    if (/[A-Z]/.test(value))         score++;
    if (/[0-9]/.test(value))         score++;
    if (/[^A-Za-z0-9]/.test(value))  score++;

    const pct    = ['0%', '25%', '50%', '75%', '100%'];
    const colors = ['#aaa', '#ef4444', '#f97316', '#eab308', '#22c55e'];
    const labels = ['—', 'Weak', 'Fair', 'Good', 'Strong'];

    fill.style.width      = pct[score];
    fill.style.background = colors[score];
    label.textContent     = labels[score];
    label.style.color     = colors[score];
  }

  function checkMatch() {
    const np   = document.getElementById('pw_new')?.value    || '';
    const cp   = document.getElementById('pw_confirm')?.value || '';
    const hint = document.getElementById('pw-match-msg');
    if (!hint) return;

    if (!cp) { hint.textContent = ''; return; }
    if (np === cp) {
      hint.textContent  = '✓ Passwords match';
      hint.style.color  = '#16a34a';
    } else {
      hint.textContent  = '✗ Passwords do not match';
      hint.style.color  = '#c0291b';
    }
  }

  function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    const icon = btn.querySelector('i');
    if (icon) icon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
  }

  /* ══════════════════════════════════════════════════════════════
     TWO-FACTOR AUTHENTICATION
  ══════════════════════════════════════════════════════════════ */
  const twofa = (() => {

    async function generate() {
      const btn = document.getElementById('btn-twofa-generate');
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating…'; }

      try {
        const data = await postJSON(TWOFA_ENDPOINT, { action: 'generate' });

        if (data.success) {
          // Show QR step
          const qrImg  = document.getElementById('twofa-qr-img');
          const secret = document.getElementById('twofa-manual-secret');
          if (qrImg)  qrImg.src = data.qr_code;
          if (secret) secret.textContent = data.secret;

          const offState = document.getElementById('twofa-off-state');
          const qrStep   = document.getElementById('twofa-qr-step');
          if (offState) offState.style.display = 'none';
          if (qrStep)   qrStep.style.display   = 'block';
        } else {
          showToast(data.message || '2FA generation failed.', 'error');
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-shield-halved"></i> Enable Two-Factor Authentication'; }
        }
      } catch (err) {
        showToast('Network error: ' + err.message, 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-shield-halved"></i> Enable Two-Factor Authentication'; }
      }
    }

    async function enable() {
      const otp    = val('twofa-otp-input');
      const btn    = document.getElementById('btn-twofa-enable');
      const errEl  = document.getElementById('twofa-otp-err');

      if (!/^\d{6}$/.test(otp)) {
        if (errEl) errEl.textContent = 'Please enter a valid 6-digit code.';
        return;
      }
      if (errEl) errEl.textContent = '';

      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying…'; }

      try {
        const data = await postJSON(TWOFA_ENDPOINT, { action: 'enable', otp_code: otp });

        if (data.success) {
          showToast('✅ 2FA enabled successfully!', 'success');
          closeModal('twofa');
          setTimeout(() => window.location.reload(), 1200);
        } else {
          if (errEl) errEl.textContent = data.message || 'Invalid code. Try again.';
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Confirm & Enable'; }
          const otpInput = document.getElementById('twofa-otp-input');
          if (otpInput) { otpInput.value = ''; otpInput.focus(); }
        }
      } catch (err) {
        showToast('Network error: ' + err.message, 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Confirm & Enable'; }
      }
    }

    async function disable() {
      const confirmed = window.confirm(
        'Disable Two-Factor Authentication?\n\nYou will no longer be asked for a code when logging in.'
      );
      if (!confirmed) return;

      const btn = document.getElementById('btn-twofa-disable');
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Disabling…'; }

      try {
        const data = await postJSON(TWOFA_ENDPOINT, { action: 'disable' });

        if (data.success) {
          showToast('2FA has been disabled.', 'info');
          closeModal('twofa');
          setTimeout(() => window.location.reload(), 1200);
        } else {
          showToast(data.message || 'Failed to disable 2FA.', 'error');
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-shield-xmark"></i> Disable Two-Factor Authentication'; }
        }
      } catch (err) {
        showToast('Network error: ' + err.message, 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-shield-xmark"></i> Disable Two-Factor Authentication'; }
      }
    }

    return { generate, enable, disable };
  })();

  /* ══════════════════════════════════════════════════════════════
     UPLOAD: Avatar / Profile Picture
  ══════════════════════════════════════════════════════════════ */
  async function uploadAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];

    // Client-side size check (3 MB)
    if (file.size > 3 * 1024 * 1024) {
      showToast('Image must be under 3 MB.', 'error');
      input.value = '';
      return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    // Show a preview immediately
    const reader = new FileReader();
    reader.onload = e => {
      // Swap circle content to show preview while uploading
      const circle = document.getElementById('sp-avatar-circle');
      if (circle) {
        circle.innerHTML =
          `<img src="${e.target.result}" alt="Preview" class="avatar-photo" id="sp-avatar-img" />` +
          `<input type="file" id="sp-avatar-input" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="StudentProfile.uploadAvatar(this)" />` +
          `<button class="sp-avatar-edit-btn" title="Change photo" onclick="document.getElementById('sp-avatar-input').click()"><i class="fas fa-camera"></i></button>`;
      }
    };
    reader.readAsDataURL(file);

    showToast('Uploading…', 'info');

    try {
      const res = await fetch('index.php?page=upload_avatar', {
        method: 'POST',
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        showToast('Profile picture updated!', 'success');

        // Update sidebar avatar too (handle both img and fallback span)
        const sidebar = document.getElementById('sp-sidebar-avatar') ||
                        document.getElementById('sp-sidebar-avatar-fallback');
        if (sidebar) {
          const img = document.createElement('img');
          img.src       = data.url;
          img.alt       = 'Avatar';
          img.className = 'profile-avatar-img';
          img.id        = 'sp-sidebar-avatar';
          sidebar.replaceWith(img);
        }
      } else {
        showToast(data.message || 'Upload failed.', 'error');
        // Revert preview on failure
        setTimeout(() => window.location.reload(), 1500);
      }
    } catch (err) {
      showToast('Network error: ' + err.message, 'error');
      setTimeout(() => window.location.reload(), 1500);
    } finally {
      input.value = '';
    }
  }

  /* ── Public API ──────────────────────────────────────────────── */
  return {
    openModal,
    closeModal,
    savePersonal,
    saveAcademic,
    savePassword,
    checkStrength,
    checkMatch,
    togglePw,
    twofa,
    showToast,
    uploadAvatar,
  };

})();