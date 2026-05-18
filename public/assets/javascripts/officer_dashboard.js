/* ============================================================
   UNIFY — officer_dashboard.js  (chat removed — now a separate page)
============================================================ */

'use strict';

const TODAY_STR = new Date().toISOString().slice(0, 10);

let announcements = (window.OD?.announcements) ?? [];
let eventsData    = (window.OD?.events)        ?? [];
const members     = (window.OD?.members)        ?? [];
let applicants    = (window.OD?.applicants)     ?? [];

let annSearchQuery   = '';
let currentViewAnnId = null;
let currentAppId     = null;

/* ── API Helper ─────────────────────────────────────────────── */
async function apiPost(action, data) {
  const res = await fetch(`index.php?page=${window.OD.page}&action=${action}`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(data),
  });
  let json;
  try   { json = await res.json(); }
  catch { throw new Error('Server returned an invalid response.'); }
  if (!res.ok || json.error) throw new Error(json.error || 'Request failed');
  return json;
}

/* ── Utility ────────────────────────────────────────────────── */
function esc(str) {
  return String(str ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function capitalize(str) {
  if (!str) return '';
  return str.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}
function formatTime(time) {
  if (!time) return '';
  const [h, m] = time.split(':').map(Number);
  const ampm = h >= 12 ? 'PM' : 'AM';
  return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${ampm}`;
}
function roleClass(role) {
  if (!role) return 'member';
  const r = role.toLowerCase().trim();
  if (r === 'vice president') return 'vice-president';
  return r;
}
function scrollToSection(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
}

/* ── Timeline ───────────────────────────────────────────────── */
function renderTimeline() {
  const container = document.getElementById('eventsTimeline');
  const subtitle  = document.getElementById('eventsSubtitle');
  if (!container) return;
  const todayEvts  = eventsData.filter(e => e.date === TODAY_STR).sort((a,b)=>(a.time||'').localeCompare(b.time||''));
  const futureEvts = eventsData.filter(e => e.date > TODAY_STR).sort((a,b)=>a.date.localeCompare(b.date)||(a.time||'').localeCompare(b.time||''));
  const display    = [...todayEvts, ...futureEvts].slice(0, 6);
  if (subtitle) subtitle.textContent = `${todayEvts.length} event${todayEvts.length!==1?'s':''} today · ${futureEvts.length} upcoming`;
  if (!display.length) {
    container.innerHTML = `<div class="empty-state"><i class="fas fa-calendar-xmark"></i> No upcoming events. Add one!</div>`;
    return;
  }
  container.innerHTML = display.map((ev, idx) => {
    const isActive = idx === 0;
    const dateLbl  = ev.date !== TODAY_STR ? ` · ${ev.date.slice(5).replace('-','/')}` : '';
    return `
      <div class="timeline-row">
        <span class="timeline-time">${formatTime(ev.time)}</span>
        <div class="timeline-line-col">
          <div class="${isActive?'timeline-dot active':'timeline-dot'}"></div>
          ${idx < display.length - 1 ? `<div class="${isActive?'timeline-vline active-line':'timeline-vline'}"></div>` : ''}
        </div>
        <div class="timeline-event-wrap">
          <div class="${isActive?'timeline-event active-event':'timeline-event inactive-event'}">
            <div class="tl-event-icon"><i class="fas fa-calendar-days"></i></div>
            <div class="tl-event-info">
              <span class="tl-event-title">${esc(ev.name)}</span>
              <span class="tl-event-meta"><i class="fas fa-clock"></i> ${formatTime(ev.time)}${ev.endTime?'–'+formatTime(ev.endTime):''}${dateLbl}</span>
            </div>
            <button class="tl-del-btn" onclick="confirmDeleteEvent('${esc(ev.id)}')" title="Delete"><i class="fas fa-trash"></i></button>
          </div>
        </div>
      </div>`;
  }).join('');
}

/* ── Announcements ──────────────────────────────────────────── */
function renderAnnouncements() {
  const body = document.getElementById('announcementsBody');
  if (!body) return;
  const q = annSearchQuery.toLowerCase().trim();
  const list = q ? announcements.filter(a =>
    (a.title||'').toLowerCase().includes(q) || (a.category||'').toLowerCase().includes(q) ||
    (a.status||'').toLowerCase().includes(q) || (a.desc||'').toLowerCase().includes(q)) : announcements;
  if (!list.length) {
    body.innerHTML = `<div class="empty-state"><i class="fas fa-bullhorn"></i> No announcements yet.</div>`;
    return;
  }
  body.innerHTML = list.map(a => `
    <div class="table-row" onclick="viewAnnouncement('${esc(a.id)}')">
      <span class="td-col td-title"><span class="dot dot-${esc(a.dot)}"></span>${esc(a.title)}</span>
      <span class="td-col"><span class="cat-badge">${esc(a.category)}</span></span>
      <span class="td-col"><span class="status-badge s-${esc(a.status)}">${capitalize(a.status)}</span></span>
      <span class="td-col td-right td-date">${esc(a.date)}</span>
      <span class="td-col td-right td-actions">
        <button class="icon-action" onclick="event.stopPropagation();editAnnouncement('${esc(a.id)}')"><i class="fas fa-pen"></i></button>
        <button class="icon-action danger" onclick="event.stopPropagation();confirmDeleteAnn('${esc(a.id)}')"><i class="fas fa-trash"></i></button>
      </span>
    </div>`).join('');
}

function handleSearch() { annSearchQuery = document.getElementById('dashSearchInput')?.value || ''; renderAnnouncements(); }

function viewAnnouncement(id) {
  const a = announcements.find(x => x.id === id); if (!a) return;
  currentViewAnnId = id;
  document.getElementById('annDetailTitle').textContent  = a.title;
  document.getElementById('annDetailMeta').textContent   = `${a.category} · ${a.date}`;
  document.getElementById('annDetailStatus').textContent = capitalize(a.status);
  document.getElementById('annDetailDate').textContent   = a.date;
  document.getElementById('annDetailDesc').textContent   = a.desc || '—';
  openModal('viewAnnouncementModal');
}
function editAnnFromDetail()   { closeModal('viewAnnouncementModal'); editAnnouncement(currentViewAnnId); }
function deleteAnnFromDetail() { closeModal('viewAnnouncementModal'); confirmDeleteAnn(currentViewAnnId); }

function openAddAnnouncementModal() {
  document.getElementById('annModalTitle').textContent = 'Add Announcement';
  document.getElementById('editAnnId').value = '';
  document.getElementById('aTitle').value    = '';
  document.getElementById('aCategory').value = 'General';
  document.getElementById('aStatus').value   = 'info';
  document.getElementById('aDesc').value     = '';
  openModal('addAnnouncementModal');
}
function editAnnouncement(id) {
  const a = announcements.find(x => x.id === id); if (!a) return;
  document.getElementById('annModalTitle').textContent = 'Edit Announcement';
  document.getElementById('editAnnId').value = a.db_id;
  document.getElementById('aTitle').value    = a.title;
  document.getElementById('aCategory').value = a.category;
  document.getElementById('aStatus').value   = a.status;
  document.getElementById('aDesc').value     = a.desc;
  openModal('addAnnouncementModal');
}
async function saveAnnouncement() {
  const title    = document.getElementById('aTitle').value.trim();
  const category = document.getElementById('aCategory').value;
  const status   = document.getElementById('aStatus').value;
  const desc     = document.getElementById('aDesc').value.trim();
  const editId   = document.getElementById('editAnnId').value;
  if (!title) { showToast('Title is required','error'); return; }
  try {
    if (editId) {
      await apiPost('ann_update', { id: parseInt(editId), title, category, status, desc });
      const idx = announcements.findIndex(a => a.db_id === parseInt(editId));
      if (idx >= 0) announcements[idx] = { ...announcements[idx], title, category, status, dot: statusToDot(status), desc };
      showToast('Announcement updated', 'success');
    } else {
      const res = await apiPost('ann_create', { title, category, status, desc });
      announcements.unshift({ id: 'ann_' + res.id, db_id: res.id, title, category, status, date: 'Today', dot: statusToDot(status), desc });
      updateStatCard('.sc-yellow .sc-value', +1);
      showToast('Announcement posted', 'success');
    }
    closeModal('addAnnouncementModal');
    renderAnnouncements();
  } catch(e) { showToast(e.message, 'error'); }
}
function confirmDeleteAnn(id) {
  const a = announcements.find(x => x.id === id); if (!a) return;
  showConfirm('Delete Announcement', `Delete "${a.title}"?`, async () => {
    try {
      await apiPost('ann_delete', { id: a.db_id });
      announcements = announcements.filter(x => x.id !== id);
      updateStatCard('.sc-yellow .sc-value', -1);
      renderAnnouncements();
      showToast('Deleted', 'success');
    } catch(e) { showToast(e.message, 'error'); }
  });
}
function statusToDot(s) { return {urgent:'red',approved:'green',info:'yellow'}[s]||'blue'; }
function updateStatCard(selector, delta) {
  const el = document.querySelector(selector);
  if (el) el.textContent = Math.max(0, parseInt(el.textContent || '0') + delta);
}

/* ── Members ────────────────────────────────────────────────── */
function renderMembers() {
  const list = document.getElementById('memberList');
  if (!list) return;
  if (!members.length) { list.innerHTML = `<div class="empty-state">No members yet.</div>`; return; }
  list.innerHTML = members.map(m => `
    <div class="member-item">
      <div class="member-avatar ${esc(m.color)}">${esc(m.initial)}</div>
      <div class="member-info">
        <div class="member-name">${esc(m.name)}</div>
        <div class="member-meta">${esc(m.course)}</div>
      </div>
      <span class="role-tag ${roleClass(m.role)}">${capitalize(m.role)}</span>
    </div>`).join('');
}

/* ── Applicants ─────────────────────────────────────────────── */
function renderApplicants() {
  const list  = document.getElementById('applicantList');
  const badge = document.getElementById('pendingCount');
  if (!list) return;
  if (badge) badge.textContent = `${applicants.length} pending`;
  if (!applicants.length) {
    list.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i> No pending applications.</div>`;
    return;
  }
  list.innerHTML = applicants.map((a, i) => {
    const colors = ['av-green','av-teal','av-red','av-yellow','av-purple'];
    return `
      <div class="applicant-mini-item">
        <div class="ap-avatar ${colors[i%5]}">${esc(a.initial)}</div>
        <div class="ap-info">
          <div class="ap-name">${esc(a.name)}</div>
          <div class="ap-meta">${esc(a.course)}</div>
        </div>
        <span class="ap-pending-badge">Pending</span>
        <button class="ap-review-btn" onclick="openAppReview(${i})" title="Review Application">
          <i class="fas fa-paperclip"></i>
        </button>
      </div>`;
  }).join('');
}

/* ── Application Review ─────────────────────────────────────── */
function openAppReview(idx) {
  const a = applicants[idx]; if (!a) return;
  currentAppId = a.db_id;
  document.getElementById('arAvatar').textContent    = a.initial;
  document.getElementById('arName').textContent      = a.name;
  document.getElementById('arEmail').textContent     = a.email   || '—';
  document.getElementById('arStudentId').textContent = a.student_id || '—';
  document.getElementById('arCourse').textContent    = a.course  || '—';
  document.getElementById('arPhone').textContent     = a.phone   || '—';
  document.getElementById('arDate').textContent      = a.date    || '—';
  document.getElementById('arExtras').textContent    = a.extras  || '—';
  openModal('appReviewModal');
}
function openRejectModal() {
  document.getElementById('rejectReason').value = '';
  openModal('rejectReasonModal');
}
async function approveApplication() {
  if (!currentAppId) return;
  try {
    await apiPost('app_approve', { id: currentAppId });
    applicants = applicants.filter(a => a.db_id !== currentAppId);
    updateStatCard('.sc-green .sc-value', +1);
    updateStatCard('.sc-blue .sc-value', -1);
    renderApplicants();
    closeModal('appReviewModal');
    showToast('Application approved! Member added.', 'success');
  } catch(e) { showToast(e.message, 'error'); }
}
async function rejectApplication() {
  if (!currentAppId) return;
  const reason = document.getElementById('rejectReason').value.trim();
  try {
    await apiPost('app_reject', { id: currentAppId, reason });
    applicants = applicants.filter(a => a.db_id !== currentAppId);
    updateStatCard('.sc-blue .sc-value', -1);
    renderApplicants();
    closeModal('rejectReasonModal');
    closeModal('appReviewModal');
    showToast('Application declined.', 'info');
  } catch(e) { showToast(e.message, 'error'); }
}

/* ── Events ─────────────────────────────────────────────────── */
function openAddEventModal() {
  document.getElementById('eName').value     = '';
  document.getElementById('eDate').value     = '';
  document.getElementById('eLocation').value = '';
  document.getElementById('eStart').value    = '';
  document.getElementById('eEnd').value      = '';
  document.getElementById('eDesc').value     = '';
  openModal('addEventModal');
}
async function saveEvent() {
  const name     = document.getElementById('eName').value.trim();
  const date     = document.getElementById('eDate').value;
  const location = document.getElementById('eLocation').value.trim();
  const start    = document.getElementById('eStart').value;
  const end      = document.getElementById('eEnd').value;
  const desc     = document.getElementById('eDesc').value.trim();
  if (!name || !date) { showToast('Event name and date are required', 'error'); return; }
  try {
    const res = await apiPost('evt_create', { name, date, location, start, end, desc });
    eventsData.push({ id: 'evt_' + res.id, db_id: res.id, name, date, time: start, endTime: end, location });
    updateStatCard('.sc-teal .sc-value', +1);
    renderTimeline();
    closeModal('addEventModal');
    showToast('Event added!', 'success');
  } catch(e) { showToast(e.message, 'error'); }
}
function confirmDeleteEvent(id) {
  const ev = eventsData.find(e => e.id === id); if (!ev) return;
  showConfirm('Delete Event', `Delete "${ev.name}"?`, async () => {
    try {
      await apiPost('evt_delete', { id: ev.db_id });
      eventsData = eventsData.filter(e => e.id !== id);
      updateStatCard('.sc-teal .sc-value', -1);
      renderTimeline();
      showToast('Event deleted', 'success');
    } catch(e) { showToast(e.message, 'error'); }
  });
}

/* ── Notifications ──────────────────────────────────────────── */
let notifPanelOpen = false;
function toggleNotifPanel() {
  const panel = document.getElementById('notifPanel');
  if (!panel) return;
  notifPanelOpen = !notifPanelOpen;
  panel.style.display = notifPanelOpen ? 'block' : 'none';
  if (notifPanelOpen) loadNotifications();
}
async function loadNotifications() {
  const list = document.getElementById('notifList');
  if (!list) return;
  try {
    const res  = await fetch('index.php?page=notifications&action=list');
    const data = await res.json();
    if (!data.notifications || !data.notifications.length) {
      list.innerHTML = '<div class="notif-empty">No notifications</div>'; return;
    }
    list.innerHTML = data.notifications.map(n => `
      <div class="notif-item ${n.is_read ? '' : 'unread'}" onclick="readNotif(${n.id}, '${esc(n.link)}')">
        <div class="notif-title">${esc(n.title)}</div>
        <div class="notif-msg">${esc(n.message||'')}</div>
        <div class="notif-time">${esc(n.created_fmt)}</div>
      </div>`).join('');
    const bell = document.querySelector('.icon-btn .badge');
    if (bell) { bell.textContent = data.unread; bell.style.display = data.unread > 0 ? '' : 'none'; }
  } catch { list.innerHTML = '<div class="notif-empty">Failed to load</div>'; }
}
async function readNotif(id, link) {
  await fetch('index.php?page=notifications&action=read', { method:'POST', body: new URLSearchParams({action:'read', id}) });
  if (link) window.location = link;
}
async function markAllRead() {
  await fetch('index.php?page=notifications&action=read_all', { method:'POST', body: new URLSearchParams({action:'read_all'}) });
  loadNotifications();
}

/* ── Club Edit ──────────────────────────────────────────────── */
async function saveClubInfo() {
  const name    = document.getElementById('editClubName').value.trim();
  const desc    = document.getElementById('editClubDesc').value.trim();
  const room    = document.getElementById('editClubRoom').value.trim();
  const founded = document.getElementById('editClubFounded').value.trim();
  if (!name) { showToast('Club name is required.', 'error'); return; }
  try {
    const res = await apiPost('club_update', { name, description: desc, room, founded });
    if (res.success) {
      showToast('Club info updated!', 'success');
      closeModal('editClubModal');
      document.querySelectorAll('.club-badge-name').forEach(el => el.textContent = name);
    } else { showToast(res.error || 'Failed to save.', 'error'); }
  } catch { showToast('Something went wrong.', 'error'); }
}
async function uploadClubLogo(input) {
  if (!input.files[0]) return;
  const fd = new FormData();
  fd.append('logo',   input.files[0]);
  fd.append('action', 'club_logo');
  try {
    const res  = await fetch('index.php?page=officer_dashboard&action=club_logo', { method:'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      document.getElementById('editClubLogoPreview').src           = data.logo;
      document.getElementById('editClubLogoPreview').style.display = 'block';
      const badgeImg   = document.querySelector('.club-badge-icon img');
      if (badgeImg)    badgeImg.src = data.logo;
      const bannerLogo = document.querySelector('.wb-club-logo');
      if (bannerLogo)  bannerLogo.src = data.logo;
      showToast('Logo updated!', 'success');
    } else { showToast(data.error || 'Upload failed.', 'error'); }
  } catch { showToast('Upload failed.', 'error'); }
}

/* ── Modals ─────────────────────────────────────────────────── */
function openModal(id)  { const m = document.getElementById(id); if (m) m.classList.add('open'); }
function closeModal(id) { const m = document.getElementById(id); if (m) m.classList.remove('open'); }
function handleOverlayClick(e, id) { if (e.target.classList.contains('modal-overlay')) closeModal(id); }

let confirmCallback = null;
function showConfirm(title, msg, cb) {
  document.getElementById('confirmTitle').textContent   = title;
  document.getElementById('confirmMessage').textContent = msg;
  confirmCallback = cb;
  openModal('confirmModal');
}
/* confirmOkBtn wired in init below */

/* ── Toast ──────────────────────────────────────────────────── */
function showToast(msg, type = 'info') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className   = `toast toast-${type} show`;
  setTimeout(() => t.classList.remove('show'), 3200);
}

/* ── Greeting & Date ────────────────────────────────────────── */
function setGreeting() {
  const h = new Date().getHours();
  const g = h < 12 ? 'Good morning 👋' : h < 17 ? 'Good afternoon 👋' : 'Good evening 👋';
  const el = document.getElementById('wbGreeting'); if (el) el.textContent = g;
}
function setDate() {
  const el = document.getElementById('topbarDate');
  if (el) el.textContent = new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}

/* ── Init ───────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // confirmOkBtn setup
  const confirmBtn = document.getElementById('confirmOkBtn');
  if (confirmBtn) confirmBtn.addEventListener('click', () => { closeModal('confirmModal'); if (confirmCallback) confirmCallback(); });
  setGreeting();
  setDate();
  renderAnnouncements();
  renderTimeline();
  renderMembers();
  renderApplicants();

  document.addEventListener('click', e => {
    const panel = document.getElementById('notifPanel');
    if (notifPanelOpen && panel && !panel.contains(e.target) && !e.target.closest('.icon-btn')) {
      notifPanelOpen = false;
      panel.style.display = 'none';
    }
  });
});