// ============================================================
//  UNIFY — officer_home.js
//  assets/javascripts/officer_home.js
// ============================================================

/* ── Page init ───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  setGreeting();
  setDate();
  loadNotifs();
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#notifBtn') && !e.target.closest('#notifDropdown')) {
      document.getElementById('notifDropdown').classList.remove('open');
    }
  });
});

function setGreeting() {
  const h = new Date().getHours();
  const g = h < 12 ? 'Good morning ☀️' : h < 17 ? 'Good afternoon 🌤️' : 'Good evening 🌙';
  const el = document.getElementById('wbGreeting');
  if (el) el.textContent = g;
}

function setDate() {
  const el = document.getElementById('topbarDate');
  if (el) el.textContent = new Date().toLocaleDateString('en-PH', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  });
}

/* ── Toast ───────────────────────────────────────────────── */
function showToast(msg, type = 'info') {
  const t  = document.getElementById('crudToast');
  const ic = { success: 'fa-circle-check', info: 'fa-circle-info', warn: 'fa-triangle-exclamation' };
  t.className = `crud-toast crud-toast-${type} show`;
  t.innerHTML = `<i class="fas ${ic[type] || ic.info}"></i> ${msg}`;
  clearTimeout(t._t);
  t._t = setTimeout(() => t.classList.remove('show'), 3200);
}

/* ── Modal helpers ───────────────────────────────────────── */
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id, e) {
  if (e && e.target !== document.getElementById(id)) return;
  document.getElementById(id).classList.remove('open');
}
function openQuickAnn() {
  document.getElementById('annTitle').value    = '';
  document.getElementById('annDesc').value     = '';
  document.getElementById('annCategory').value = 'General';
  document.getElementById('annStatus').value   = 'info';
  openModal('annModal');
}
function openQuickEvt() {
  document.getElementById('evtName').value     = '';
  document.getElementById('evtDate').value     = '';
  document.getElementById('evtLocation').value = '';
  document.getElementById('evtStart').value    = '';
  document.getElementById('evtEnd').value      = '';
  document.getElementById('evtDesc').value     = '';
  openModal('evtModal');
}

/* ── POST helper ─────────────────────────────────────────── */
function postAction(action, body) {
  return fetch(`index.php?page=officer_home&action=${action}`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(body),
  }).then(r => r.json());
}

/* ── Submit announcement ─────────────────────────────────── */
function submitAnn() {
  const title = document.getElementById('annTitle').value.trim();
  if (!title) { showToast('Title is required.', 'warn'); return; }
  const btn = document.querySelector('#annModal .modal-submit');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting…';

  postAction('ann_quick_create', {
    title,
    desc:     document.getElementById('annDesc').value.trim(),
    category: document.getElementById('annCategory').value,
    status:   document.getElementById('annStatus').value,
  }).then(data => {
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post';
    if (data.success) {
      closeModal('annModal');
      showToast('Announcement posted!', 'success');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast(data.error || 'Failed to post announcement.', 'warn');
    }
  }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post'; showToast('Network error.', 'warn'); });
}

/* ── Submit event ────────────────────────────────────────── */
function submitEvt() {
  const name = document.getElementById('evtName').value.trim();
  const date = document.getElementById('evtDate').value;
  if (!name || !date) { showToast('Name and date are required.', 'warn'); return; }
  const btn = document.querySelector('#evtModal .modal-submit');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating…';

  postAction('evt_quick_create', {
    name,
    date,
    location: document.getElementById('evtLocation').value.trim(),
    start:    document.getElementById('evtStart').value || null,
    end:      document.getElementById('evtEnd').value   || null,
    desc:     document.getElementById('evtDesc').value.trim(),
  }).then(data => {
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus"></i> Create';
    if (data.success) {
      closeModal('evtModal');
      showToast('Event created!', 'success');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast(data.error || 'Failed to create event.', 'warn');
    }
  }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus"></i> Create'; showToast('Network error.', 'warn'); });
}

/* ── Approve application ─────────────────────────────────── */
function approveApp(appId) {
  const card = document.getElementById('apCard' + appId);
  if (!card) return;
  const btn = card.querySelector('.apcard-btn.approve');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

  postAction('app_approve', { id: appId })
    .then(data => {
      if (data.success) {
        card.style.transition = 'all 0.3s';
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        setTimeout(() => {
          card.remove();
          showToast('Application approved. Member added!', 'success');
          // If no more cards, remove the section heading
          const list = document.getElementById('applicantList');
          if (list && !list.children.length) {
            list.closest('div').remove();
          }
        }, 300);
      } else {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Approve';
        showToast(data.error || 'Could not approve.', 'warn');
      }
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Approve'; showToast('Network error.', 'warn'); });
}

/* ── Reject application ──────────────────────────────────── */
function rejectApp(appId) {
  document.getElementById('rejectAppId').value = appId;
  document.getElementById('rejectReason').value = '';
  openModal('rejectModal');
}

function confirmReject() {
  const appId  = parseInt(document.getElementById('rejectAppId').value);
  const reason = document.getElementById('rejectReason').value.trim();
  const btn    = document.querySelector('#rejectModal .modal-submit');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rejecting…';

  postAction('app_reject', { id: appId, reason })
    .then(data => {
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-xmark"></i> Confirm Reject';
      if (data.success) {
        closeModal('rejectModal');
        const card = document.getElementById('apCard' + appId);
        if (card) {
          card.style.transition = 'all 0.3s';
          card.style.opacity = '0';
          setTimeout(() => {
            card.remove();
            showToast('Application rejected.', 'info');
            const list = document.getElementById('applicantList');
            if (list && !list.children.length) list.closest('div').remove();
          }, 300);
        }
      } else {
        showToast(data.error || 'Could not reject.', 'warn');
      }
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-xmark"></i> Confirm Reject'; showToast('Network error.', 'warn'); });
}

/* ── Notifications ───────────────────────────────────────── */
function toggleNotif(e) {
  e.stopPropagation();
  document.getElementById('notifDropdown').classList.toggle('open');
}

const NOTIF_ICONS = {
  app_approved:        'fa-circle-check',
  app_rejected:        'fa-circle-xmark',
  club_position:       'fa-id-badge',
  event_assigned:      'fa-calendar-check',
  assignment_accepted: 'fa-user-check',
  assignment_declined: 'fa-user-xmark',
  collab_request:      'fa-handshake',
  collab_accepted:     'fa-handshake',
  collab_declined:     'fa-handshake-slash',
  event_new:           'fa-calendar-plus',
  event_updated:       'fa-calendar-pen',
  event_cancelled:     'fa-calendar-xmark',
  info:                'fa-circle-info',
};

function loadNotifs() {
  fetch('index.php?page=officer_home&action=notif_list')
    .then(r => r.json())
    .then(data => {
      const list   = document.getElementById('notifList');
      const badge  = document.getElementById('notifBadge');
      const notifs = data.notifications || [];
      const unread = data.unread        || 0;

      badge.textContent = unread;
      badge.classList.toggle('hidden', unread === 0);

      if (!notifs.length) {
        list.innerHTML = '<div class="notif-item"><div class="notif-content"><span class="notif-text">No notifications yet.</span></div></div>';
        return;
      }
      list.innerHTML = notifs.map(n => {
        const hasLink = n.link && n.link.trim() !== '';
        const icon    = NOTIF_ICONS[n.type] || 'fa-bell';
        return `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}"
             style="cursor:${hasLink ? 'pointer' : 'default'}"
             onclick="readNotif(${n.id}, ${JSON.stringify(n.link || '')})">
          ${n.is_read == 0 ? '<div class="notif-dot"></div>' : '<div style="width:7px;"></div>'}
          <div class="notif-content">
            <span class="notif-text">
              <i class="fas ${icon}" style="margin-right:4px;opacity:.75;"></i>
              <strong>${escHtml(n.title)}</strong>
            </span>
            <span class="notif-text" style="font-weight:400;margin-top:2px;">${escHtml(n.message || '')}</span>
            <span class="notif-time"><i class="fas fa-clock"></i> ${n.created_fmt}</span>
          </div>
          ${hasLink ? '<div class="notif-arrow" style="margin-left:auto;color:#aaa;font-size:11px;"><i class="fas fa-chevron-right"></i></div>' : ''}
        </div>
      `}).join('');
    })
    .catch(() => {});
}

function readNotif(id, link) {
  fetch(`index.php?page=officer_home&action=notif_read&id=${id}`)
    .then(() => {
      if (link && link.trim() !== '') {
        window.location.href = link;
      } else {
        loadNotifs();
      }
    });
}

function markAllNotifs() {
  fetch('index.php?page=officer_home&action=notif_read_all')
    .then(() => loadNotifs());
}

/* ── Home search (filter visible text) ───────────────────── */
function homeSearch(q) {
  const lq = q.toLowerCase();
  document.querySelectorAll('.member-item, .applicant-card, .table-row, .timeline-row').forEach(el => {
    el.style.display = !lq || el.textContent.toLowerCase().includes(lq) ? '' : 'none';
  });
}

/* ── Escape html ─────────────────────────────────────────── */
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}