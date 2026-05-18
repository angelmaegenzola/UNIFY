/* ═══════════════════════════════════════════════════════════
   UNIFY — studenthome.js
   Handles: greeting, date, notification dropdown, toast,
            locked nav toast
════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── TOPBAR DATE ──────────────────────────────────────────── */
  const dateEl = document.getElementById('topbarDate');
  if (dateEl) {
    dateEl.textContent = new Date().toLocaleDateString('en-PH', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
  }

  /* ── WELCOME / EXPLORE GREETING ──────────────────────────── */
  const greetingEl = document.getElementById('wbGreeting');
  if (greetingEl) {
    const hour = new Date().getHours();
    let g = 'Good morning 👋';
    if (hour >= 12 && hour < 17) g = 'Good afternoon 👋';
    else if (hour >= 17)         g = 'Good evening 👋';
    greetingEl.textContent = g;
  }

});

/* ══════════════════════════════════════════════════════════
   NOTIFICATION DROPDOWN
══════════════════════════════════════════════════════════ */
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');

function toggleNotif(e) {
  e.stopPropagation();
  notifBtn.classList.toggle('active');
  notifDropdown.classList.toggle('open');
  if (notifDropdown.classList.contains('open')) loadNotifs();
}

document.addEventListener('click', (e) => {
  if (notifDropdown && !notifDropdown.contains(e.target) && e.target !== notifBtn) {
    notifBtn.classList.remove('active');
    notifDropdown.classList.remove('open');
  }
});

function clearNotifs() {
  fetch('index.php?page=notifications&action=read_all', { method: 'POST' });
  document.querySelectorAll('.notif-item.unread').forEach(n => n.classList.remove('unread'));
  const badge = document.getElementById('notifBadge');
  if (badge) { badge.textContent = '0'; badge.classList.add('hidden'); }
}

const NOTIF_ICONS = {
  app_approved:   'fa-circle-check',
  app_rejected:   'fa-circle-xmark',
  club_position:  'fa-id-badge',
  event_assigned: 'fa-calendar-check',
  collab_request: 'fa-handshake',
  collab_response:'fa-handshake',
  info:           'fa-circle-info',
};

function loadNotifs() {
  fetch('index.php?page=notifications&action=list')
    .then(r => r.json())
    .then(res => {
      if (!res.success) return;
      const list  = document.getElementById('notifList');
      const badge = document.getElementById('notifBadge');
      if (!list) return;

      if (badge) {
        badge.textContent = res.unread || 0;
        badge.classList.toggle('hidden', !res.unread);
      }

      if (!res.notifications || !res.notifications.length) {
        list.innerHTML = `
          <div class="notif-item">
            <div class="notif-content">
              <span class="notif-text">No new notifications.</span>
            </div>
          </div>`;
        return;
      }

      list.innerHTML = res.notifications.map(n => `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}"
             onclick="markNotifRead(${n.id}, '${n.link || ''}', this)">
          <div class="notif-dot"></div>
          <div class="notif-content">
            <span class="notif-text">
              <strong>${n.title}</strong>
            </span>
            <span class="notif-text" style="font-weight:400;margin-top:2px;">${n.message || ''}</span>
            ${n.created_fmt ? `<span class="notif-time" style="margin-top:4px;font-size:10px;color:#7a9a85;">${n.created_fmt}</span>` : ''}
          </div>
        </div>`).join('');
    })
    .catch(() => {});
}

function markNotifRead(id, link, el) {
  fetch('index.php?page=notifications&action=read&id=' + id, { method: 'POST' });
  el.classList.remove('unread');
  if (link) setTimeout(() => window.location = link, 150);
}

// Load on page load
loadNotifs();

/* ══════════════════════════════════════════════════════════
   LOCKED NAV TOAST
══════════════════════════════════════════════════════════ */
function showLockedToast() {
  showToast('Join a club first to unlock this section.', 'info');
}

/* ══════════════════════════════════════════════════════════
   TOAST HELPER
══════════════════════════════════════════════════════════ */
function showToast(msg, type = 'info') {
  const t = document.getElementById('crudToast');
  if (!t) return;
  const icons = {
    success: 'fa-circle-check',
    info:    'fa-circle-info',
    warn:    'fa-triangle-exclamation'
  };
  t.className = `crud-toast crud-toast-${type}`;
  t.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i> ${msg}`;
  t.classList.add('crud-toast-show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('crud-toast-show'), 3200);
}