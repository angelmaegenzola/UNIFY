/* ============================================================
   UNIFY — Explore & Apply JS
   explore.js
============================================================ */

let currentCat  = 'all';
let currentCard = null;

/* ── Topbar Date ──────────────────────────────────────────── */
(function () {
  const el = document.getElementById('topbarDate');
  if (!el) return;
  const now = new Date();
  el.textContent = now.toLocaleDateString('en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  });
})();

/* ── Category Filter ──────────────────────────────────────── */
function setCat(btn, cat) {
  currentCat = cat;
  document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  filterClubs();
}

/* ── Combined Filter (search + category) ─────────────────── */
function filterClubs() {
  const query = document.getElementById('searchInput').value.toLowerCase().trim();
  const cards  = document.querySelectorAll('.club-card');
  let visible  = 0;

  cards.forEach(card => {
    const name      = (card.dataset.name || '').toLowerCase();
    const cat       = card.dataset.cat || '';
    const matchCat  = currentCat === 'all' || cat === currentCat;
    const matchText = !query || name.includes(query) || cat.toLowerCase().includes(query);

    if (matchCat && matchText) {
      card.style.display = '';
      visible++;
    } else {
      card.style.display = 'none';
    }
  });

  document.getElementById('filterCount').textContent = `${visible} club${visible !== 1 ? 's' : ''}`;
  document.getElementById('emptyState').style.display = visible === 0 ? 'flex' : 'none';
}

/* ── Open Apply Modal ─────────────────────────────────────── */
function openApply(btn) {
  currentCard = btn.closest('.club-card');
  const d = currentCard.dataset;

  // Populate header / info strip
  document.getElementById('modalClubName').textContent  = d.name  || '';
  document.getElementById('modalLogo').src              = d.logo  || '';
  document.getElementById('modalDesc').textContent      = d.desc  || '';
  document.getElementById('mInfoMembers').textContent   = d.members || '0';
  document.getElementById('mInfoEvents').textContent    = d.events  || '0';
  document.getElementById('mInfoRoom').textContent      = d.room    || '—';
  document.getElementById('mInfoFounded').textContent   = d.founded || '—';

  // CRITICAL: bind the club id so apply_handler.php receives it
  document.getElementById('fClubId').value = d.id || '';

  // Reset editable fields
  ['fReason', 'fSkills'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = '';
    el.classList.remove('field-error');
  });

  document.getElementById('applyOverlay').classList.add('modal-open');
}

function closeApply(e) {
  if (e && e.target !== document.getElementById('applyOverlay')) return;
  document.getElementById('applyOverlay').classList.remove('modal-open');
}

function closeSuccess() {
  document.getElementById('successOverlay').classList.remove('modal-open');
}

/* NOTE:
   submitApplication() lives inline at the bottom of explore.php so it can
   POST to apply_handler.php. Do NOT redefine it here — it would override
   the working one. */

/* ── Update card button states ────────────────────────────── */

/** Called after successful application submit — flips button to Pending */
function updateCardToPending(clubId) {
  const card = document.querySelector(`.club-card[data-id="${clubId}"]`);
  if (!card) return;
  const btn = card.querySelector('.cc-apply-btn');
  if (!btn) return;
  btn.outerHTML = `<button class="cc-apply-btn applied" disabled>
    <i class="fas fa-hourglass-half"></i> Pending
  </button>`;
}

/** Called if admin approval is detected client-side — flips button to Visit Club */
function updateCardToVisit(clubId) {
  const card = document.querySelector(`.club-card[data-id="${clubId}"]`);
  if (!card) return;
  const btn = card.querySelector('.cc-apply-btn');
  if (!btn) return;
  btn.outerHTML = `<a class="cc-apply-btn visit-btn"
    href="index.php?page=myclubs&club_id=${clubId}">
    <i class="fas fa-door-open"></i> Visit Club
  </a>`;
}
function showLockedToast() {
  showToast('Join a club first to access this section.', 'info');
}

/* ── Toast ────────────────────────────────────────────────── */
function showToast(msg, type = 'info') {
  const t     = document.getElementById('crudToast');
  if (!t) return;
  const icons = { success: 'fa-circle-check', info: 'fa-circle-info', warn: 'fa-triangle-exclamation' };
  t.className = `crud-toast crud-toast-${type}`;
  t.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i> ${msg}`;
  t.classList.add('crud-toast-show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('crud-toast-show'), 3200);
}

/* ── Close modals on overlay click ───────────────────────── */
const _applyOv = document.getElementById('applyOverlay');
if (_applyOv) {
  _applyOv.addEventListener('click', function (e) {
    if (e.target === this) closeApply(e);
  });
}
const _successOv = document.getElementById('successOverlay');
if (_successOv) {
  _successOv.addEventListener('click', function (e) {
    if (e.target === this) closeSuccess();
  });
}

/* ── ESC key ──────────────────────────────────────────────── */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeApply();
    closeSuccess();
    if (typeof closeNotifDropdown === 'function') closeNotifDropdown();
  }
});

/* ── Init count ───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const total = document.querySelectorAll('.club-card').length;
  const fc = document.getElementById('filterCount');
  if (fc) fc.textContent = `${total} clubs`;
});

/* ============================================================
   NOTIFICATIONS
============================================================ */
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');
const notifBadge    = document.getElementById('notifBadge');

function openNotifDropdown() {
  if (!notifDropdown || !notifBtn) return;
  notifDropdown.classList.add('open');
  notifBtn.classList.add('active');
}

function closeNotifDropdown() {
  if (!notifDropdown || !notifBtn) return;
  notifDropdown.classList.remove('open');
  notifBtn.classList.remove('active');
}

if (notifBtn) {
  notifBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    notifDropdown.classList.contains('open') ? closeNotifDropdown() : openNotifDropdown();
  });
}

document.addEventListener('click', function (e) {
  if (notifDropdown && !notifDropdown.contains(e.target) && e.target !== notifBtn) {
    closeNotifDropdown();
  }
});

function clearNotifs() {
  fetch('index.php?page=notifications&action=read_all', { method: 'POST' });
  document.querySelectorAll('.notif-item.unread').forEach(item => {
    item.classList.remove('unread');
  });
  if (notifBadge) notifBadge.classList.add('hidden');
  closeNotifDropdown();
  showToast('All notifications marked as read.', 'success');
}

function renderNotifs(notifications, unread) {
  const list = document.getElementById('notifList');
  if (!list) return;
  if (!notifications.length) {
    list.innerHTML = '<div class="notif-item"><div class="notif-content"><span class="notif-text">No new notifications.</span></div></div>';
  } else {
    list.innerHTML = notifications.map(n => `
      <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" onclick="markNotifRead(${n.id}, this, '${n.link}')">
        <div class="notif-content">
          <span class="notif-text"><strong>${n.title}</strong><br>${n.message}</span>
          <span class="notif-time">${n.created_fmt}</span>
        </div>
      </div>
    `).join('');
  }
  if (notifBadge) {
    if (unread > 0) {
      notifBadge.textContent = unread;
      notifBadge.classList.remove('hidden');
    } else {
      notifBadge.classList.add('hidden');
    }
  }
}

function markNotifRead(id, el, link) {
  fetch('index.php?page=notifications&action=read', {
    method: 'POST',
    body: new URLSearchParams({ id })
  });
  el.classList.remove('unread');
  const unreadCount = document.querySelectorAll('.notif-item.unread').length;
  if (notifBadge) {
    if (unreadCount > 0) { notifBadge.textContent = unreadCount; }
    else { notifBadge.classList.add('hidden'); }
  }
  if (link) window.location.href = link;
}

function fetchNotifs() {
  fetch('index.php?page=notifications&action=list')
    .then(r => r.json())
    .then(data => {
      if (data.success) renderNotifs(data.notifications, data.unread);
    })
    .catch(() => {});
}

// Fetch immediately, then every 30 seconds
fetchNotifs();
setInterval(fetchNotifs, 30000);

/* ── explore inline script 1 (moved from inline) ── */
setTimeout(() => { const t = document.getElementById('welcomeToast'); if (t) t.style.transition = 'opacity .5s', t.style.opacity = '0', setTimeout(() => t.remove(), 500); }, 5000);


/* ── explore inline script 2 (moved from inline) ── */
function openAlreadyMember(clubId, clubName) {
      document.getElementById('amClubId').value = clubId;
      document.getElementById('amClubName').textContent = clubName;
      document.getElementById('amFirstName').value = '';
      document.getElementById('amLastName').value = '';
      document.getElementById('amCourse').value = '';
      document.getElementById('amYear').value = '';
      document.getElementById('amRole').value = 'member';
      document.getElementById('alreadyMemberOverlay').classList.add('modal-open');
    }
    function closeAlreadyMember() {
      document.getElementById('alreadyMemberOverlay').classList.remove('modal-open');
    }
    function submitAlreadyMember() {
      const club_id = document.getElementById('amClubId').value;
      const first_name = document.getElementById('amFirstName').value.trim();
      const last_name = document.getElementById('amLastName').value.trim();
      const course = document.getElementById('amCourse').value.trim();
      const year = document.getElementById('amYear').value.trim();
      const role = document.getElementById('amRole').value;

      if (!first_name) { showToast('Please enter your first name.', 'warn'); return; }
      if (!last_name) { showToast('Please enter your last name.', 'warn'); return; }
      if (!course) { showToast('Please enter your course.', 'warn'); return; }
      if (!year) { showToast('Please enter your year level.', 'warn'); return; }

      const btn = document.querySelector('#alreadyMemberOverlay .modal-btn-submit');
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering…'; }

      const fd = new FormData();
      fd.append('club_id', club_id);
      fd.append('first_name', first_name);
      fd.append('last_name', last_name);
      fd.append('course', course);
      fd.append('year', year);
      fd.append('role', role);

      fetch('index.php?page=already_member_handler', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-user-check"></i> Register as Member'; }
          if (data.success) {
            closeAlreadyMember();
            showToast('You have been registered as a member!', 'success');
          } else {
            showToast(data.message || 'Registration failed.', 'warn');
          }
        })
        .catch(() => {
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-user-check"></i> Register as Member'; }
          showToast('Something went wrong. Please try again.', 'warn');
        });
    }

    function submitApplication() {
      const club_id = document.getElementById('fClubId').value;
      const studentId = document.getElementById('fStudentId').value.trim();
      const course = document.getElementById('fCourse').value.trim();
      const year = document.getElementById('fYear').value.trim();
      const section = document.getElementById('fSection').value.trim();
      const phone = document.getElementById('fPhone').value.trim();
      const reason = document.getElementById('fReason').value.trim();
      const skills = document.getElementById('fSkills').value.trim();

      if (!studentId) { showToast('Please enter your Student ID.', 'warn'); return; }
      if (!course) { showToast('Please enter your Course.', 'warn'); return; }
      if (!year) { showToast('Please enter your Year Level.', 'warn'); return; }
      if (!section) { showToast('Please enter your Section.', 'warn'); return; }
      if (!phone) { showToast('Please enter your Contact No.', 'warn'); return; }
      if (!reason) { showToast('Please tell us why you want to join.', 'warn'); return; }

      let extras = reason;
      if (skills) extras += '\n\nSkills/Experience: ' + skills;

      const fd = new FormData();
      fd.append('club_id', club_id);
      fd.append('student_id', studentId);
      fd.append('course', course);
      fd.append('year', year);
      fd.append('section', section);
      fd.append('phone', phone);
      fd.append('extras', extras);

      const btn = document.querySelector('#applyOverlay .modal-btn-submit');
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…'; }

      fetch('index.php?page=apply_handler', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application'; }
          if (data.success) {
            closeApply();
            document.getElementById('successClubName').textContent = document.getElementById('modalClubName').textContent;
            document.getElementById('successOverlay').classList.add('modal-open');
            const card = document.querySelector(`.club-card[data-id="${club_id}"]`);
            if (card) { updateCardToPending(club_id); }
          } else { showToast(data.message || 'Failed to submit application.', 'warn'); }
        })
        .catch(() => {
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application'; }
          showToast('Something went wrong. Please try again.', 'warn');
        });
    }
