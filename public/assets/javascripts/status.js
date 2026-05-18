/* ============================================================
   UNIFY — Application Status JS
   status.js
============================================================ */

/* ── Set dynamic dates & reference number on load ──────── */
document.addEventListener('DOMContentLoaded', () => {
  const now = new Date();

  // Submitted date — shown in hero card and timeline
  const dateStr = now.toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
  const timeStr = now.toLocaleTimeString('en-PH', {
    hour: '2-digit', minute: '2-digit'
  });

  const submittedDate = document.getElementById('submittedDate');
  const tlSubmitTime  = document.getElementById('tlSubmitTime');
  if (submittedDate) submittedDate.textContent = dateStr;
  if (tlSubmitTime)  tlSubmitTime.textContent  = `${dateStr} at ${timeStr}`;

  // Generate a reference number
  const refNo = document.getElementById('refNo');
  if (refNo) {
    const rand = Math.floor(100000 + Math.random() * 900000);
    refNo.textContent = `APP-${rand}`;
  }
});





/* ── Locked nav toast ─────────────────────────────────── */
function showLockedToast() {
  showToast('Join a club first to access this section.', 'info');
}

/* ── ESC to close modal ───────────────────────────────── */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeWithdraw();
});

/* ── status modal + withdraw logic (moved from inline) ── */
function openWithdraw()  { document.getElementById('withdrawOverlay').classList.add('modal-open'); }
function closeWithdraw(e) {
  if (e && e.target !== document.getElementById('withdrawOverlay')) return;
  document.getElementById('withdrawOverlay').classList.remove('modal-open');
}

function doWithdraw() {
  if (!APP_ID) return;
  const fd = new FormData();
  fd.append('action', 'withdraw');
  fd.append('app_id', APP_ID);
  fetch('apply_handler.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => {
      closeWithdraw();
      if (data.success) {
        showToast('Application withdrawn.', 'success');
        setTimeout(() => window.location.href = 'index.php?page=explore', 1500);
      } else {
        showToast(data.message || 'Failed to withdraw.', 'warn');
      }
    })
    .catch(() => showToast('Something went wrong.', 'warn'));
}

function showToast(msg, type='info') {
  const t = document.getElementById('crudToast');
  const icons = { success:'fa-circle-check', info:'fa-circle-info', warn:'fa-triangle-exclamation' };
  t.className = `crud-toast crud-toast-${type}`;
  t.innerHTML = `<i class="fas ${icons[type]||icons.info}"></i> ${msg}`;
  t.classList.add('crud-toast-show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('crud-toast-show'), 3200);
}
