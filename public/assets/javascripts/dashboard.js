/* ============================================================
   UNIFY — dashboard.js
   Handles: Announcements CRUD, Applicant review,
            Events timeline, Renders, Modals, Toast
============================================================ */

'use strict';

/* ── Data (seeded from PHP via window.DB_*) ─────────────────── */

const TODAY_STR = new Date().toISOString().slice(0, 10);

let announcements      = (window.DB_ANNOUNCEMENTS && window.DB_ANNOUNCEMENTS.length) ? window.DB_ANNOUNCEMENTS : [];
let leaderApps         = window.DB_LEADER_APPS   || [];
let clubRequests       = window.DB_CLUB_REQUESTS || [];
const events           = (window.DB_EVENTS && window.DB_EVENTS.length) ? window.DB_EVENTS : [];

let currentViewAnnouncementId = null;
let currentApplicantId        = null;
let currentLeaderAppId        = null;
let currentClubReqId          = null;
let annSearchQuery            = '';

/* ── API helper ─────────────────────────────────────────────── */

async function apiPost(action, data) {
  const res = await fetch('index.php?page=dashboard&action=' + action, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(data),
  });
  const text = await res.text();
  let json;
  try {
    json = JSON.parse(text);
  } catch (e) {
    throw new Error('Server returned invalid response. Check PHP error log.');
  }
  if (!res.ok || json.error) throw new Error(json.error || 'Request failed');
  return json;
}

/* ── Utility ────────────────────────────────────────────────── */

function esc(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function capitalize(str) {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

function formatTime(time) {
  if (!time) return '';
  let [h, m] = time.split(':').map(Number);
  const ampm = h >= 12 ? 'PM' : 'AM';
  h = h % 12 || 12;
  return `${h}:${String(m).padStart(2, '0')} ${ampm}`;
}

/* ── Render: Timeline ───────────────────────────────────────── */

function renderTimeline() {
  const container = document.getElementById('eventsTimeline');
  const subtitle  = document.getElementById('eventsSubtitle');
  if (!container) return;

  const todayEvts    = events.filter(ev => ev.date === TODAY_STR).sort((a, b) => a.time.localeCompare(b.time));
  const upcomingEvts = events.filter(ev => ev.date >  TODAY_STR).sort((a, b) => a.date.localeCompare(b.date) || a.time.localeCompare(b.time));
  const displayEvts  = [...todayEvts, ...upcomingEvts].slice(0, 6);

  if (subtitle) subtitle.textContent = `${todayEvts.length} event${todayEvts.length !== 1 ? 's' : ''} today · ${upcomingEvts.length} upcoming`;

  if (displayEvts.length === 0) {
    container.innerHTML = `<div style="text-align:center;padding:24px;color:var(--text-light);font-size:13px;">No upcoming events.</div>`;
    return;
  }

  container.innerHTML = displayEvts.map((ev, idx) => {
    const isLast    = idx === displayEvts.length - 1;
    const isActive  = idx === 0;
    const dateLabel = ev.date === TODAY_STR ? '' : ` · ${ev.date.slice(5).replace('-', '/')}`;
    return `
      <div class="timeline-row ${isLast ? 'last-row' : ''}">
        <span class="timeline-time">${formatTime(ev.time)}</span>
        <div class="timeline-line-col">
          <div class="${isActive ? 'timeline-dot active' : 'timeline-dot'}"></div>
          ${isLast ? '' : `<div class="${isActive ? 'timeline-vline active-line' : 'timeline-vline'}"></div>`}
        </div>
        <div class="timeline-event-wrap">
          <div class="${isActive ? 'timeline-event active-event' : 'timeline-event inactive-event'}">
            <div class="tl-event-icon"><i class="fas ${ev.icon || 'fa-calendar'}"></i></div>
            <div class="tl-event-info">
              <span class="tl-event-title">${esc(ev.name)}</span>
              <span class="tl-event-meta">
                <i class="fas fa-clock"></i>
                ${formatTime(ev.time)}${ev.endTime ? '–' + formatTime(ev.endTime) : ''}${dateLabel}
              </span>
            </div>
          </div>
        </div>
      </div>`;
  }).join('');
}

/* ── Render: Announcements ──────────────────────────────────── */

function renderAnnouncements() {
  const body = document.getElementById('announcementsBody');
  if (!body) return;

  const q        = annSearchQuery.toLowerCase();
  const filtered = q
    ? announcements.filter(a =>
        a.title.toLowerCase().includes(q) ||
        a.category.toLowerCase().includes(q) ||
        a.status.toLowerCase().includes(q))
    : announcements;

  if (filtered.length === 0) {
    body.innerHTML = `<div style="text-align:center;padding:24px;color:var(--text-light);font-size:13px;">No announcements found.</div>`;
    return;
  }

  body.innerHTML = filtered.map(a => `
    <div class="table-row" onclick="viewAnnouncement('${a.id}')">
      <div class="tr-title-col">
        <div class="tr-dot ${a.dot}-dot"></div>
        <span class="tr-title">${esc(a.title)}</span>
      </div>
      <span class="tr-category">${esc(a.category)}</span>
      <span class="tr-status-badge ${a.status}">${capitalize(a.status)}</span>
      <span class="tr-date">${esc(a.date)}</span>
      <div class="tr-actions" onclick="event.stopPropagation()">
        <button class="tr-btn tr-btn-edit" onclick="openEditAnnouncementModal('${a.id}')"><i class="fas fa-pen"></i></button>
        <button class="tr-btn tr-btn-del"  onclick="confirmDeleteAnnouncement('${a.id}')"><i class="fas fa-trash"></i></button>
      </div>
    </div>`).join('');
}

/* ── Render: Applicants card — shows leader apps on admin dashboard ── */

function renderApplicants() {
  const container = document.getElementById('applicantsList');
  if (!container) return;

  const pending = leaderApps.filter(a => !a._reviewed);

  if (pending.length === 0) {
    container.innerHTML = `<div style="text-align:center;padding:24px;color:var(--text-light);font-size:13px;">No pending leader applications.</div>`;
    return;
  }

  container.innerHTML = pending.slice(0, 5).map(a => `
    <div class="applicant-item" onclick="openLeaderAppDetailFromCard('${a.id}')">
      <div class="applicant-avatar ${a.color}">${esc(a.initial)}</div>
      <div class="applicant-info">
        <span class="applicant-name">${esc(a.name)}</span>
        <span class="applicant-role">${esc(a.club)} · <em style="color:#f59e0b;">Leader</em></span>
      </div>
      <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">
        <span style="font-size:9px;font-weight:700;padding:3px 8px;border-radius:20px;background:#fef3c7;color:#92400e;">Pending</span>
        <button class="file-btn" title="View Application" onclick="event.stopPropagation();openLeaderAppDetailFromCard('${a.id}')">
          <i class="fas fa-paperclip"></i>
        </button>
      </div>
    </div>`).join('');
}

/* ── Announcements CRUD ─────────────────────────────────────── */

function viewAnnouncement(id) {
  const ann = announcements.find(a => a.id === id);
  if (!ann) return;
  currentViewAnnouncementId = id;
  document.getElementById('annDetailTitle').textContent  = ann.title;
  document.getElementById('annDetailMeta').textContent   = `${ann.category} · ${ann.date}`;
  document.getElementById('annDetailStatus').textContent = capitalize(ann.status);
  document.getElementById('annDetailDate').textContent   = ann.date;
  document.getElementById('annDetailDesc').textContent   = ann.desc || '—';
  openModal('viewAnnouncementModal');
}

function openAddAnnouncementModal() {
  document.getElementById('announcementModalTitle').textContent = 'Add Announcement';
  document.getElementById('saveAnnBtnText').textContent         = 'Save';
  document.getElementById('editAnnouncementId').value           = '';
  document.getElementById('aTitle').value                       = '';
  document.getElementById('aCategory').value                    = 'General';
  document.getElementById('aStatus').value                      = 'info';
  document.getElementById('aDesc').value                        = '';
  document.getElementById('aTitle').classList.remove('error');
  openModal('addAnnouncementModal');
}

function openEditAnnouncementModal(id) {
  const ann = announcements.find(a => a.id === id);
  if (!ann) return;
  document.getElementById('announcementModalTitle').textContent = 'Edit Announcement';
  document.getElementById('saveAnnBtnText').textContent         = 'Update';
  document.getElementById('editAnnouncementId').value           = id;
  document.getElementById('aTitle').value                       = ann.title;
  document.getElementById('aCategory').value                    = ann.category;
  document.getElementById('aStatus').value                      = ann.status;
  document.getElementById('aDesc').value                        = ann.desc || '';
  document.getElementById('aTitle').classList.remove('error');
  openModal('addAnnouncementModal');
}

function editAnnouncementFromDetail() {
  if (!currentViewAnnouncementId) return;
  closeModal('viewAnnouncementModal');
  openEditAnnouncementModal(currentViewAnnouncementId);
}

async function saveAnnouncement() {
  const title    = document.getElementById('aTitle').value.trim();
  const category = document.getElementById('aCategory').value;
  const status   = document.getElementById('aStatus').value;
  const desc     = document.getElementById('aDesc').value.trim();
  const editId   = document.getElementById('editAnnouncementId').value;

  if (!title) {
    document.getElementById('aTitle').classList.add('error');
    showToast('Please enter a title.', 'error');
    return;
  }

  const dotMap = { urgent: 'red', approved: 'green', info: 'yellow' };
  const dot    = dotMap[status] || 'blue';

  try {
    if (editId) {
      const ann = announcements.find(a => a.id === editId);
      await apiPost('ann_update', { id: ann.db_id, title, category, status, desc });
      const idx = announcements.findIndex(a => a.id === editId);
      if (idx > -1) announcements[idx] = { ...announcements[idx], title, category, status, desc, dot };
      showToast('Announcement updated!', 'success');
    } else {
      const res = await apiPost('ann_create', { title, category, status, desc });
      announcements.unshift({
        id: 'ann_' + res.id, db_id: res.id,
        title, category, status, date: 'Just now', desc, dot,
      });
      showToast('Announcement added!', 'success');
    }
    closeModal('addAnnouncementModal');
    renderAnnouncements();
  } catch (err) {
    showToast(err.message || 'Save failed.', 'error');
  }
}

function confirmDeleteAnnouncement(id) {
  const ann = announcements.find(a => a.id === id);
  if (!ann) return;
  showConfirm('Delete Announcement', `Delete "${ann.title}"? This cannot be undone.`, async () => {
    try {
      await apiPost('ann_delete', { id: ann.db_id });
      announcements = announcements.filter(a => a.id !== id);
      renderAnnouncements();
      showToast('Announcement deleted.', 'error');
    } catch (err) {
      showToast(err.message || 'Delete failed.', 'error');
    }
  });
}

function deleteAnnouncementFromDetail() {
  if (!currentViewAnnouncementId) return;
  closeModal('viewAnnouncementModal');
  confirmDeleteAnnouncement(currentViewAnnouncementId);
}

/* ── Leader App detail — opened from the applicants card ───── */

function openLeaderAppDetailFromCard(id) {
  const idx = leaderApps.findIndex(a => a.id === id);
  if (idx === -1) return;
  openLeaderAppDetail(idx);
}

/* ── All Applicants Modal — shows leader apps only ──────────── */

function openAllApplicantsModal() {
  const body = document.getElementById('allApplicantsBody');
  if (!body) return;

  if (!leaderApps.length) {
    body.innerHTML = `<div style="text-align:center;padding:24px;color:var(--text-light);font-size:13px;">No pending leader applications.</div>`;
  } else {
    body.innerHTML = leaderApps.map((a, i) => `
      <div class="applicant-item" onclick="closeModal('allApplicantsModal');openLeaderAppDetail(${i})" style="cursor:pointer;">
        <div class="applicant-avatar ${a.color}">${esc(a.initial)}</div>
        <div class="applicant-info">
          <span class="applicant-name">${esc(a.name)}</span>
          <span class="applicant-role">${esc(a.club)} · <em style="color:#f59e0b;">Leader in: ${esc(a.leaderIn)}</em></span>
        </div>
        <span style="font-size:9px;font-weight:700;padding:3px 8px;border-radius:20px;flex-shrink:0;background:#fef3c7;color:#92400e;">
          Pending
        </span>
      </div>`).join('');
  }

  openModal('allApplicantsModal');
}

/* ── Update badge/stat after action ─────────────────────────── */

function updateRequestBadge() {
  const count = leaderApps.filter(a => !a._reviewed).length
              + clubRequests.length;
  const el    = document.getElementById('statRequests');
  const trend = document.getElementById('statReqTrend');
  const badge = document.getElementById('notifBadge');
  if (el)    el.textContent    = count;
  if (trend) trend.textContent = count > 0 ? 'Urgent' : 'Clear';
  if (badge) {
    badge.textContent = count;
    badge.classList.toggle('hidden', count === 0);
  }
}

/* ── Search ─────────────────────────────────────────────────── */

function handleDashSearch() {
  annSearchQuery = document.getElementById('dashSearchInput').value.trim();
  renderAnnouncements();
}

/* ── Sync ───────────────────────────────────────────────────── */

function syncDashboard() {
  renderTimeline();
  renderAnnouncements();
  renderApplicants();
  showToast('Dashboard synced!', 'info');
}

/* ── Modal helpers ──────────────────────────────────────────── */

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function handleOverlayClick(e, id) {
  if (e.target === document.getElementById(id)) closeModal(id);
}

/* ── Toast ──────────────────────────────────────────────────── */

let toastTimer = null;

function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<i class="fas ${icons[type] || 'fa-info-circle'}"></i> ${esc(message)}`;
  toast.classList.remove('show');
  void toast.offsetWidth;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
}

/* ── Confirm dialog ─────────────────────────────────────────── */

function showConfirm(title, message, onConfirm) {
  document.getElementById('confirmTitle').textContent   = title;
  document.getElementById('confirmMessage').textContent = message;
  const btn    = document.getElementById('confirmOkBtn');
  const newBtn = btn.cloneNode(true);
  btn.parentNode.replaceChild(newBtn, btn);
  newBtn.addEventListener('click', () => { closeModal('confirmModal'); onConfirm(); });
  openModal('confirmModal');
}

/* ── Bootstrap ──────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', () => {

  // Greeting
  const hour = new Date().getHours();
  document.getElementById('wbGreeting').textContent =
    hour < 12 ? 'Good morning 👋' : hour < 17 ? 'Good afternoon 👋' : 'Good evening 👋';

  // Topbar date
  const dateEl = document.getElementById('topbarDate');
  if (dateEl) {
    dateEl.textContent = new Date().toLocaleDateString('en-US', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
  }

  // Initial renders
  renderAnnouncements();
  renderTimeline();
  renderApplicants();

  // Hash-based auto-open
  if (window.location.hash === '#clubRequests') openClubRequestsModal();
  if (window.location.hash === '#leaderApps')   openLeaderAppsModal();

  // Stat trend badge
  updateRequestBadge();
});

/* ── Leader Applications ────────────────────────────────────── */

function openLeaderAppsModal() {
  const body = document.getElementById('leaderAppsBody');
  if (!body) return;
  if (!leaderApps.length) {
    body.innerHTML = '<div style="text-align:center;padding:24px;color:var(--text-light);font-size:13px;">No leader applications pending.</div>';
  } else {
    const colors = ['av-green','av-teal','av-red','av-yellow','av-purple'];
    body.innerHTML = leaderApps.map((a, i) => `
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:#fffbeb;border:1.5px solid #f59e0b;border-radius:12px;cursor:pointer;" onclick="openLeaderAppDetail(${i})">
        <div class="app-mini-avatar ${colors[i%5]}">${esc(a.initial)}</div>
        <div style="flex:1;">
          <div style="font-weight:700;font-size:13.5px;">${esc(a.name)}</div>
          <div style="font-size:12px;color:var(--text-light);">Applying to: <strong>${esc(a.club)}</strong> · Leader in: ${esc(a.leaderIn)}</div>
        </div>
        <div style="font-size:11.5px;color:#92400e;">${esc(a.dateApplied)}</div>
      </div>`).join('');
  }
  openModal('leaderAppsModal');
}

function openLeaderAppDetail(idx) {
  const a = leaderApps[idx]; if (!a) return;
  currentLeaderAppId = a.db_id;

  const avatarEl = document.getElementById('appFileAvatar');
  avatarEl.textContent = a.initial;
  const AVATAR_COLORS = {
    'av-green':'#1a4d2e','av-teal':'#0e7c6e','av-red':'#c0392b','av-yellow':'#d4940a','av-purple':'#7c3aed',
  };
  avatarEl.style.background = AVATAR_COLORS[a.color] || '#1a4d2e';

  document.getElementById('appFileName').textContent      = a.name;
  document.getElementById('appFileRoleLabel').textContent = 'Leader Application';
  document.getElementById('appStudentId').textContent     = a.studentId   || '—';
  document.getElementById('appCourse').textContent        = a.course      || '—';
  document.getElementById('appEmail').textContent         = a.email       || '—';
  document.getElementById('appContact').textContent       = a.contact     || '—';
  document.getElementById('appClub').textContent          = a.club        || '—';
  document.getElementById('appDate').textContent          = a.dateApplied || '—';
  document.getElementById('appExtras').textContent        = a.extras      || '—';

  const statusBadge = document.getElementById('appFileStatusBadge');
  if (statusBadge) {
    statusBadge.className   = 'app-file-status pending';
    statusBadge.innerHTML   = `⚠️ Leader in: ${esc(a.leaderIn)}`;
  }

  const row = document.getElementById('appActionRow');
  if (row) {
    row.style.display = 'flex';
    row.innerHTML = `
      <button class="btn-reject-app"  onclick="openLeaderRejectModal()"><i class="fas fa-times-circle"></i> Decline</button>
      <button class="btn-approve-app" onclick="approveLeaderApp()"><i class="fas fa-check-circle"></i> Approve Application</button>`;
  }
  openModal('appFileModal');
}

function openLeaderRejectModal() {
  document.getElementById('leaderRejectReason').value = '';
  openModal('leaderRejectModal');
}

async function approveLeaderApp() {
  if (!currentLeaderAppId) return;
  try {
    await apiPost('app_approve', { id: currentLeaderAppId });
    leaderApps = leaderApps.filter(a => a.db_id !== currentLeaderAppId);
    closeModal('appFileModal');
    closeModal('leaderAppsModal');
    renderApplicants();
    updateRequestBadge();
    showToast('Leader application approved!', 'success');
  } catch(e) { showToast(e.message, 'error'); }
}

async function confirmLeaderReject() {
  if (!currentLeaderAppId) return;
  const reason = document.getElementById('leaderRejectReason').value.trim();
  try {
    await apiPost('app_reject', { id: currentLeaderAppId, reason });
    leaderApps = leaderApps.filter(a => a.db_id !== currentLeaderAppId);
    closeModal('leaderRejectModal');
    closeModal('appFileModal');
    closeModal('leaderAppsModal');
    renderApplicants();
    updateRequestBadge();
    showToast('Application declined.', 'info');
  } catch(e) { showToast(e.message, 'error'); }
}

/* ── Club Requests ──────────────────────────────────────────── */

function openClubRequestsModal() {
  const body = document.getElementById('clubRequestsBody');
  if (!body) return;
  if (!clubRequests.length) {
    body.innerHTML = '<div style="text-align:center;padding:24px;color:var(--text-light);font-size:13px;">No pending club requests.</div>';
  } else {
    body.innerHTML = clubRequests.map((r, i) => `
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;cursor:pointer;" onclick="openClubReqDetail(${i})">
        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#1a5c38,#2d8a57);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:15px;flex-shrink:0;">${esc(r.name.charAt(0))}</div>
        <div style="flex:1;">
          <div style="font-weight:700;font-size:13.5px;">${esc(r.name)} ${r.acronym ? '<span style="font-size:11px;font-weight:600;color:#6b7280;">('+esc(r.acronym)+')</span>' : ''}</div>
          <div style="font-size:12px;color:var(--text-light);">${esc(r.category)} · Submitted by ${esc(r.submittedBy)}</div>
        </div>
        <div style="font-size:11.5px;color:#166534;">${esc(r.date)}</div>
      </div>`).join('');
  }
  openModal('clubRequestsModal');
}

function openClubReqDetail(idx) {
  const r = clubRequests[idx]; if (!r) return;
  currentClubReqId = r.id;
  document.getElementById('crDetailName').textContent     = r.name;
  document.getElementById('crDetailAcronym').textContent  = r.acronym  || '—';
  document.getElementById('crDetailCategory').textContent = r.category || '—';
  document.getElementById('crDetailBy').textContent       = r.submittedBy;
  document.getElementById('crDetailEmail').textContent    = r.email;
  document.getElementById('crDetailRoom').textContent     = r.room     || '—';
  document.getElementById('crDetailFounded').textContent  = r.founded  || '—';
  document.getElementById('crDetailDate').textContent     = r.date;
  document.getElementById('crDetailDesc').textContent     = r.description || '—';
  document.getElementById('crAdminNote').value            = '';
  document.getElementById('crRejectNoteGroup').style.display = 'none';
  document.getElementById('crDetailActions').style.display   = 'flex';
  document.getElementById('crRejectActions').style.display   = 'none';
  openModal('clubReqDetailModal');
}

function showCrRejectNote() {
  document.getElementById('crRejectNoteGroup').style.display = 'flex';
  document.getElementById('crDetailActions').style.display   = 'none';
  document.getElementById('crRejectActions').style.display   = 'flex';
}

function hideCrRejectNote() {
  document.getElementById('crRejectNoteGroup').style.display = 'none';
  document.getElementById('crDetailActions').style.display   = 'flex';
  document.getElementById('crRejectActions').style.display   = 'none';
}

async function approveClubRequest() {
  if (!currentClubReqId) return;
  try {
    await apiPost('club_approve', { req_id: currentClubReqId });
    clubRequests = clubRequests.filter(r => r.id !== currentClubReqId);
    closeModal('clubReqDetailModal');
    closeModal('clubRequestsModal');
    updateRequestBadge();
    showToast('Club approved and created!', 'success');
  } catch(e) { showToast(e.message || 'Failed to approve.', 'error'); }
}

async function rejectClubRequest() {
  if (!currentClubReqId) return;
  const note = document.getElementById('crAdminNote').value.trim();
  try {
    await apiPost('club_reject', { req_id: currentClubReqId, admin_note: note });
    clubRequests = clubRequests.filter(r => r.id !== currentClubReqId);
    closeModal('clubReqDetailModal');
    closeModal('clubRequestsModal');
    updateRequestBadge();
    showToast('Club request rejected.', 'info');
  } catch(e) { showToast(e.message || 'Failed to reject.', 'error'); }
}
