/* ============================================================
   UNIFY — officer_members.js
============================================================ */
'use strict';

let members    = window.OM?.members    ?? [];
let applicants = window.OM?.applicants ?? [];
const canManage = window.OM?.canManage ?? false;

let filterRole       = 'all';
let searchQuery      = '';
let currentMemberId  = null;
let currentAppId     = null;
let selectedStudent  = null;
let confirmCallback  = null;
let notifPanelOpen   = false;
let searchTimeout    = null;

/* ── API Helper ─────────────────────────────────────────────── */
async function apiPost(action, data) {
  const res = await fetch(`index.php?page=${window.OM.page}&action=${action}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  let json;
  try { json = await res.json(); } catch { throw new Error('Server returned an invalid response.'); }
  if (!res.ok || json.error) throw new Error(json.error || 'Request failed');
  return json;
}

/* ── Utilities ───────────────────────────────────────────────── */
function esc(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function capitalize(str) {
  if (!str) return '';
  return str.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}
function roleClass(role) {
  if (!role) return 'member';
  const r = role.toLowerCase().trim();
  if (r === 'vice president') return 'vice-president';
  return r;
}
const COLORS = ['av-green','av-teal','av-red','av-yellow','av-purple'];

/* ── Filter & Search ─────────────────────────────────────────── */
function setFilter(role, btn) {
  filterRole = role;
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  renderMembers();
}
function handleSearch() {
  searchQuery = document.getElementById('memberSearchInput')?.value || '';
  renderMembers();
}

/* ── Render Members List ─────────────────────────────────────── */
function renderMembers() {
  const list = document.getElementById('membersList');
  if (!list) return;
  const q = searchQuery.toLowerCase().trim();
  let filtered = members;
  if (filterRole !== 'all') filtered = filtered.filter(m => m.role?.toLowerCase() === filterRole);
  if (q) filtered = filtered.filter(m =>
    (m.name||'').toLowerCase().includes(q) ||
    (m.email||'').toLowerCase().includes(q) ||
    (m.role||'').toLowerCase().includes(q) ||
    (m.course||'').toLowerCase().includes(q) ||
    (m.student_id||'').toLowerCase().includes(q)
  );

  document.getElementById('statTotal').textContent = members.length;

  if (!filtered.length) {
    list.innerHTML = `<div class="empty-state"><i class="fas fa-users-slash"></i>No members found.</div>`;
    return;
  }

  list.innerHTML = filtered.map((m, i) => {
    const colorCls  = COLORS[i % 5];
    const courseStr = [m.course, m.year, m.section].filter(Boolean).join(' ');
    const score     = m.score ?? 0;

    return `
      <div class="member-row" onclick="openMemberDetail(${m.db_id})">
        <div class="member-row-identity">
          <div class="mr-avatar ${colorCls}">${esc(m.initial)}</div>
          <div>
            <div class="mr-name">${esc(m.name)}</div>
            <div class="mr-email">${esc(m.email)}</div>
          </div>
        </div>
        <span class="mr-col">
          <span class="role-tag ${roleClass(m.role)}">${capitalize(m.role)}</span>
          ${m.club_position ? `<span class="position-tag" style="margin-left:5px;font-size:10px;background:#e8f5e9;color:#1D9E75;padding:2px 7px;border-radius:20px;">${esc(m.club_position)}</span>` : ''}
        </span>
        <span class="mr-col">${esc(courseStr) || '—'}</span>
        <span class="mr-date">${esc(m.joined)}</span>
        <span class="mr-actions" onclick="event.stopPropagation()">
          <button class="icon-action view" onclick="openMemberDetail(${m.db_id})" title="View"><i class="fas fa-eye"></i></button>
          ${canManage ? `<button class="icon-action danger" onclick="event.stopPropagation();confirmRemoveMemberById(${m.db_id},'${esc(m.name)}')" title="Remove"><i class="fas fa-user-minus"></i></button>` : `<span style="width:28px;"></span>`}
          <span style="width:20px;display:flex;align-items:center;justify-content:center;margin-left:4px;">
            ${score < 40 ? `<span title="Low attendance" style="color:#e53935;font-size:15px;line-height:1;">⚠</span>` : ''}
          </span>
        </span>
      </div>`;
  }).join('');
}

/* ── Member Detail Modal ─────────────────────────────────────── */
function openMemberDetail(dbId) {
  const m = members.find(x => x.db_id === dbId);
  if (!m) return;
  currentMemberId = dbId;
  document.getElementById('mdAvatar').textContent    = m.initial;
  document.getElementById('mdName').textContent      = m.name;
  document.getElementById('mdEmail').textContent     = m.email || '—';
  document.getElementById('mdStudentId').textContent = m.student_id || '—';
  document.getElementById('mdCourse').textContent    = m.course || '—';
  document.getElementById('mdSection').textContent   = m.section || '—';
  document.getElementById('mdJoined').textContent    = m.joined || '—';

  const wrap = document.getElementById('mdRoleBadgeWrap');
  wrap.innerHTML = `<span class="role-tag ${roleClass(m.role)}" style="font-size:11px;">${capitalize(m.role)}</span>`;

  const roleSection = document.getElementById('mdRoleChangeSection');
  const saveWrap    = document.getElementById('mdRoleSaveWrap');
  if (canManage) {
    roleSection.style.display = 'block';
    if (saveWrap) saveWrap.style.display = 'flex';
    document.getElementById('mdNewRole').value = m.role;

    // Inject club position field into the role section
    let posSection = document.getElementById('mdPositionSection');
    if (!posSection) {
      posSection = document.createElement('div');
      posSection.id = 'mdPositionSection';
      posSection.style.marginTop = '12px';
      roleSection.appendChild(posSection);
    }
    posSection.innerHTML = `
      <div class="detail-section-label" style="margin-top:4px;">Club Position</div>
      <div class="role-change-row" style="display:flex;gap:8px;align-items:center;">
        <input type="text" id="mdPositionInput"
          class="form-select" style="flex:1;font-size:13px;"
          placeholder="e.g. Secretary, Treasurer, PRO…"
          value="${esc(m.club_position || '')}" />
        <button class="btn-primary" style="padding:7px 14px;font-size:12px;white-space:nowrap;"
          onclick="setPosition(${m.db_id})">
          <i class="fas fa-tag"></i> Set
        </button>
      </div>`;
  } else {
    roleSection.style.display = 'none';
    if (saveWrap) saveWrap.style.display = 'none';
  }

  const score     = m.score ?? 0;
  const bar       = document.getElementById('mdScoreBar');
  const num       = document.getElementById('mdScoreNum');
  const breakdown = document.getElementById('mdScoreBreakdown');
  if (bar) { bar.style.width = score + '%'; bar.style.background = score >= 70 ? '#1D9E75' : score >= 40 ? '#EF9F27' : '#E24B4A'; }
  if (num) { num.textContent = score + ' / 100'; num.style.color = score >= 70 ? '#0F6E56' : score >= 40 ? '#854F0B' : '#A32D2D'; }
  if (breakdown) breakdown.textContent = m.score_breakdown ?? '—';

  openModal('memberDetailModal');
}

async function changeRole() {
  if (!currentMemberId) return;
  const newRole = document.getElementById('mdNewRole').value;
  try {
    await apiPost('member_role', { member_id: currentMemberId, role: newRole });
    const idx = members.findIndex(m => m.db_id === currentMemberId);
    if (idx >= 0) members[idx].role = newRole;
    renderMembers();
    renderOfficers();
    closeModal('memberDetailModal');
    showToast('Role updated!', 'success');
  } catch(e) { showToast(e.message, 'error'); }
}

/* ── Set Club Position ───────────────────────────────────────── */
async function setPosition(dbId) {
  const inp = document.getElementById('mdPositionInput');
  if (!inp) return;
  const position = inp.value.trim();
  try {
    await apiPost('member_set_position', { member_id: dbId, position });
    const idx = members.findIndex(m => m.db_id === dbId);
    if (idx >= 0) members[idx].club_position = position;
    renderMembers();
    renderOfficers();
    showToast(position ? `Position set: ${position} — member notified!` : 'Position cleared.', 'success');
  } catch(e) { showToast(e.message, 'error'); }
}

function confirmRemoveMember() {
  if (!currentMemberId) return;
  const m = members.find(x => x.db_id === currentMemberId);
  if (!m) return;
  closeModal('memberDetailModal');
  showConfirm('Remove Member', `Remove ${m.name} from the club?`, async () => {
    try {
      await apiPost('member_remove', { member_id: currentMemberId });
      members = members.filter(x => x.db_id !== currentMemberId);
      renderMembers();
      renderOfficers();
      showToast(`${m.name} removed.`, 'info');
    } catch(e) { showToast(e.message, 'error'); }
  });
}

function confirmRemoveMemberById(dbId, name) {
  showConfirm('Remove Member', `Remove ${name} from the club?`, async () => {
    try {
      await apiPost('member_remove', { member_id: dbId });
      members = members.filter(x => x.db_id !== dbId);
      renderMembers();
      renderOfficers();
      showToast(`${name} removed.`, 'info');
    } catch(e) { showToast(e.message, 'error'); }
  });
}

/* ── Officers Sidebar ────────────────────────────────────────── */
function renderOfficers() {
  const list = document.getElementById('officersList');
  if (!list) return;
  const officers = members.filter(m => ['president','vice president','officer','lead'].includes(m.role?.toLowerCase()));
  if (!officers.length) {
    list.innerHTML = `<div class="empty-state" style="padding:16px;">No officers yet.</div>`;
    return;
  }
  list.innerHTML = officers.map((m, i) => `
    <div class="officer-item">
      <div class="of-avatar ${COLORS[i % 5]}">${esc(m.initial)}</div>
      <div class="of-info">
        <div class="of-name">${esc(m.name)}</div>
        ${m.club_position ? `<div class="of-position" style="font-size:10px;color:#1D9E75;">${esc(m.club_position)}</div>` : ''}
      </div>
      <span class="role-tag ${roleClass(m.role)}" style="font-size:9.5px;">${capitalize(m.role)}</span>
    </div>`).join('');
}

/* ── Applicants ──────────────────────────────────────────────── */
function renderApplicants() {
  const list  = document.getElementById('applicantList');
  const badge = document.getElementById('pendingBadge');
  const stat  = document.getElementById('statPending');
  if (badge) badge.textContent = `${applicants.length} pending`;
  if (stat)  stat.textContent  = applicants.length;
  if (!list) return;
  if (!applicants.length) {
    list.innerHTML = `<div class="empty-state" style="padding:20px;"><i class="fas fa-inbox"></i>No pending applications.</div>`;
    return;
  }
  list.innerHTML = applicants.map((a, i) => `
    <div class="ap-item">
      <div class="ap-avatar ${COLORS[i % 5]}">${esc(a.initial)}</div>
      <div class="ap-info">
        <div class="ap-name">${esc(a.name)}</div>
        <div class="ap-meta">${esc(a.course) || 'No course info'}</div>
      </div>
      <button class="ap-review-btn" onclick="openAppReview(${i})" title="Review"><i class="fas fa-paperclip"></i></button>
    </div>`).join('');
}

function openAppReview(idx) {
  const a = applicants[idx]; if (!a) return;
  currentAppId = a.db_id;
  document.getElementById('arAvatar').textContent    = a.initial;
  document.getElementById('arName').textContent      = a.name;
  document.getElementById('arEmail').textContent     = a.email    || '—';
  document.getElementById('arStudentId').textContent = a.student_id || '—';
  document.getElementById('arCourse').textContent    = a.course   || '—';
  document.getElementById('arPhone').textContent     = a.phone    || '—';
  document.getElementById('arDate').textContent      = a.date     || '—';
  document.getElementById('arExtras').textContent    = a.extras   || '—';
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
    const approved = applicants.find(a => a.db_id === currentAppId);
    applicants = applicants.filter(a => a.db_id !== currentAppId);
    if (approved) {
      members.push({
        db_id:           approved.db_id,
        user_id:         approved.user_id,
        initial:         approved.initial,
        color:           COLORS[members.length % 5],
        name:            approved.name,
        email:           approved.email,
        role:            'member',
        club_position:   '',
        course:          approved.course || '',
        year:            '',
        section:         '',
        student_id:      approved.student_id,
        joined:          'Today',
        score:           0,
        score_breakdown: '—',
      });
    }
    renderApplicants();
    renderMembers();
    renderOfficers();
    closeModal('appReviewModal');
    showToast('Application approved! Member added & notified.', 'success');
  } catch(e) { showToast(e.message, 'error'); }
}

async function rejectApplication() {
  if (!currentAppId) return;
  const reason = document.getElementById('rejectReason').value.trim();
  try {
    await apiPost('app_reject', { id: currentAppId, reason });
    applicants = applicants.filter(a => a.db_id !== currentAppId);
    renderApplicants();
    closeModal('rejectReasonModal');
    closeModal('appReviewModal');
    showToast('Application declined.', 'info');
  } catch(e) { showToast(e.message, 'error'); }
}

/* ── Add Member Directly ─────────────────────────────────────── */
function openAddMemberModal() {
  selectedStudent = null;
  document.getElementById('studentSearchInput').value = '';
  document.getElementById('studentSearchResults').classList.remove('open');
  document.getElementById('selectedStudentCard').style.display = 'none';
  document.getElementById('newMemberRole').value = 'member';
  openModal('addMemberModal');
}

function clearSelectedStudent() {
  selectedStudent = null;
  document.getElementById('selectedStudentCard').style.display = 'none';
  document.getElementById('studentSearchInput').value = '';
}

function searchStudent() {
  clearTimeout(searchTimeout);
  const q = document.getElementById('studentSearchInput').value.trim();
  if (q.length < 2) { document.getElementById('studentSearchResults').classList.remove('open'); return; }
  searchTimeout = setTimeout(async () => {
    try {
      const res = await fetch(`index.php?page=${window.OM.page}&action=student_search`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ q }),
      });
      const data = await res.json();
      const results = data.results || [];
      const box = document.getElementById('studentSearchResults');
      if (!results.length) {
        box.innerHTML = `<div style="padding:12px;text-align:center;font-size:12px;color:var(--text-light);">No students found</div>`;
        box.classList.add('open'); return;
      }
      box.innerHTML = results.map(u => `
        <div class="student-result-item" onclick="selectStudent(${u.id},'${esc(u.first_name)} ${esc(u.last_name)}','${esc(u.email)}')">
          <div class="stu-av">${esc((u.first_name||'?')[0].toUpperCase())}</div>
          <div>
            <div class="stu-name">${esc(u.first_name)} ${esc(u.last_name)}</div>
            <div class="stu-meta">${esc(u.email)} · ID: ${esc(u.student_id_no||'—')}</div>
          </div>
        </div>`).join('');
      box.classList.add('open');
    } catch {}
  }, 350);
}

function selectStudent(id, name, email) {
  selectedStudent = { id, name, email };
  document.getElementById('studentSearchResults').classList.remove('open');
  document.getElementById('studentSearchInput').value = '';
  document.getElementById('selAvatar').textContent  = name[0].toUpperCase();
  document.getElementById('selName').textContent    = name;
  document.getElementById('selEmail').textContent   = email;
  document.getElementById('selectedStudentCard').style.display = 'flex';
}

async function addMemberDirectly() {
  if (!selectedStudent) { showToast('Please select a student first.', 'error'); return; }
  const role = document.getElementById('newMemberRole').value;
  try {
    await apiPost('member_add_direct', { user_id: selectedStudent.id, role });
    members.push({
      db_id:           Date.now(),
      initial:         selectedStudent.name[0].toUpperCase(),
      color:           COLORS[members.length % 5],
      name:            selectedStudent.name,
      email:           selectedStudent.email,
      role,
      club_position:   '',
      course:          '',
      year:            '',
      section:         '',
      student_id:      '—',
      joined:          'Today',
      score:           0,
      score_breakdown: '—',
    });
    renderMembers();
    renderOfficers();
    closeModal('addMemberModal');
    showToast(`${selectedStudent.name} added as ${role} & notified!`, 'success');
  } catch(e) { showToast(e.message, 'error'); }
}

/* ── Modals ──────────────────────────────────────────────────── */
function openModal(id)  { const m = document.getElementById(id); if (m) m.classList.add('open'); }
function closeModal(id) { const m = document.getElementById(id); if (m) m.classList.remove('open'); }
function handleOverlayClick(e, id) { if (e.target.classList.contains('modal-overlay')) closeModal(id); }

function showConfirm(title, msg, cb) {
  document.getElementById('confirmTitle').textContent   = title;
  document.getElementById('confirmMessage').textContent = msg;
  confirmCallback = cb;
  openModal('confirmModal');
}

/* ── Notifications ───────────────────────────────────────────── */
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
    const res  = await fetch(`index.php?page=${window.OM.page}&action=notif_list`);
    const data = await res.json();
    if (!data.notifications?.length) {
      list.innerHTML = '<div class="notif-empty">No notifications</div>';
      return;
    }
    const iconMap = {
      app_approved:  'fa-circle-check',
      app_rejected:  'fa-circle-xmark',
      club_position: 'fa-id-badge',
      info:          'fa-circle-info',
    };
    list.innerHTML = data.notifications.map(n => `
      <div class="notif-item ${n.is_read ? '' : 'unread'}" onclick="readNotif(${n.id},'${esc(n.link)}')">
        <div class="notif-title"><i class="fas ${iconMap[n.type] || 'fa-bell'}"></i> ${esc(n.title)}</div>
        <div class="notif-msg">${esc(n.message||'')}</div>
        <div class="notif-time">${esc(n.created_fmt)}</div>
      </div>`).join('');
  } catch {
    list.innerHTML = '<div class="notif-empty">Failed to load</div>';
  }
}

async function readNotif(id, link) {
  await fetch(`index.php?page=${window.OM.page}&action=notif_read&id=${id}`);
  if (link) window.location = link;
}

async function markAllRead() {
  await fetch(`index.php?page=${window.OM.page}&action=notif_read_all`);
  loadNotifications();
}

/* ── Toast ───────────────────────────────────────────────────── */
function showToast(msg, type = 'info') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className   = `toast toast-${type} show`;
  setTimeout(() => t.classList.remove('show'), 3200);
}

/* ── Init ────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const dateEl = document.getElementById('topbarDate');
  if (dateEl) dateEl.textContent = new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  renderMembers();
  renderOfficers();
  renderApplicants();

  const okBtn = document.getElementById('confirmOkBtn');
  if (okBtn) okBtn.addEventListener('click', () => { closeModal('confirmModal'); if (confirmCallback) confirmCallback(); });

  document.addEventListener('click', e => {
    const panel = document.getElementById('notifPanel');
    if (notifPanelOpen && panel && !panel.contains(e.target) && !e.target.closest('.icon-btn')) {
      notifPanelOpen = false;
      panel.style.display = 'none';
    }
    const results = document.getElementById('studentSearchResults');
    if (results && !results.contains(e.target) && e.target.id !== 'studentSearchInput') {
      results.classList.remove('open');
    }
  });
});