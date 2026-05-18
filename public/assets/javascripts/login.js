/* ============================================================
   UNIFY — Login Page JS
   login.js
============================================================ */

/* ── Show/Hide Password ───────────────────────────────────── */
function togglePassword() {
  const input   = document.getElementById('passwordInput');
  const icon    = document.getElementById('eyeIcon');
  const isHidden = input.type === 'password';

  input.type = isHidden ? 'text' : 'password';

  // Swap icon between eye and eye-off
  icon.innerHTML = isHidden
    ? /* eye-off */
      `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
       <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
       <line x1="1" y1="1" x2="23" y2="23"/>`
    : /* eye */
      `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
       <circle cx="12" cy="12" r="3"/>`;
}

/* ── Submit loading state ─────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('loginForm');
  const btn  = document.getElementById('loginBtn');

  if (form && btn) {
    form.addEventListener('submit', function () {
      btn.classList.add('loading');
      btn.disabled = true;
    });
  }

  // Auto-dismiss success message after 4 seconds
  const successEl = document.querySelector('.success-msg');
  if (successEl) {
    setTimeout(() => {
      successEl.style.transition = 'opacity 0.5s';
      successEl.style.opacity    = '0';
      setTimeout(() => successEl.remove(), 500);
    }, 4000);
  }
});