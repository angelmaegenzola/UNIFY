/* ============================================================
   UNIFY — Sign Up Page JS
   signup.js
============================================================ */

/* ── Real-time confirm password match ─────────────────────── */
function checkConfirm() {
  const pw  = document.getElementById('password');
  const cfm = document.getElementById('confirm_password');
  const msg = document.getElementById('confirmMsg');
  if (!pw || !cfm || !msg) return;

  if (cfm.value.length === 0) {
    msg.textContent = '';
    cfm.style.borderColor = '';
    return;
  }

  if (pw.value === cfm.value) {
    msg.textContent  = '✓ Passwords match';
    msg.style.color  = '#16a34a';
    cfm.style.borderColor = '#16a34a';
  } else {
    msg.textContent  = '✗ Passwords do not match';
    msg.style.color  = '#dc2626';
    cfm.style.borderColor = '#dc2626';
  }
}

/* ── Submit button loading state ──────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('signupForm');
  const btn  = document.getElementById('signupBtn');

  if (form && btn) {
    form.addEventListener('submit', function () {
      btn.classList.add('loading');
      btn.disabled = true;
    });
  }
});