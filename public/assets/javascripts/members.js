/* ============================================================
   UNIFY — Members CRUD
   members.js  — posts to members.php (same page), no api-helper
============================================================ */

/* ── POST helper ─────────────────────────────────────────── */
async function postPage(action, fields) {
  const body = new URLSearchParams({ action, ...fields });
  const res  = await fetch(window.location.href, {
    method:  'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body:    body.toString(),
  });
  if (!res.ok) throw new Error('Server error ' + res.status);
  const data = await res.json();
  if (!data.success) throw new Error(data.message || 'Operation failed.');
  return data;
}

/* ── State ───────────────────────────────────────────────── */
let members = (window.DB_MEMBERS || []).map(m => ({
  id:        m.id,
  firstName: m.name.split(' ')[0],
  lastName:  m.name.split(' ').slice(1).join(' ') || '',
  course:    m.course,
  year:      m.year,
  club:      m.club,
  role:      m.role.toLowerCase(),
  status:    m.status,
  email:     m.email,
}));

let currentFilter = 'all';
let searchQuery   = '';
let clubFilter    = '';
let roleFilter    = '';
let editingId     = null;
let viewingId     = null;
let pendingDeleteId = null;

/* ── Helpers ─────────────────────────────────────────────── */
function initials(first, last) { return (first[0] + (last[0] || '')).toUpperCase(); }
function fullName(m)           { return `${m.firstName} ${m.lastName}`; }

function roleBadgeClass(role) {
  if (role === 'officer')        return 'role-badge officer';
  if (role === 'vice president') return 'role-badge exec';
  if (role === 'president')      return 'role-badge exec';
  return 'role-badge';
}
function roleLabel(role)   { return role.charAt(0).toUpperCase() + role.slice(1); }
function statusBadgeClass(s) { return `status-badge status-${s}`; }

function getFiltered() {
  return members.filter(m => {
    const matchStatus = currentFilter === 'all' || m.status === currentFilter;
    const q = searchQuery.toLowerCase();
    const matchSearch = !q ||
      fullName(m).toLowerCase().includes(q) ||
      m.course.toLowerCase().includes(q)    ||
      m.club.toLowerCase().includes(q)      ||
      m.year.toLowerCase().includes(q);
    const matchClub = !clubFilter || m.club === clubFilter;
    const matchRole = !roleFilter || m.role === roleFilter;
    return matchStatus && matchSearch && matchClub && matchRole;
  });
}

/* ── Render table ────────────────────────────────────────── */
function renderTable() {
  const tbody = document.getElementById('membersTbody');
  if (!tbody) return;

  const filtered = getFiltered();

  // Update counts
  const countEl   = document.getElementById('memberCount');
  const showingEl = document.getElementById('showingText');
  if (countEl)   countEl.textContent   = `${filtered.length} member${filtered.length !== 1 ? 's' : ''}`;
  if (showingEl) showingEl.textContent = `Showing 1–${filtered.length} of ${filtered.length} member${filtered.length !== 1 ? 's' : ''}`;

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr><td colspan="8" style="text-align:center;padding:40px 0;color:var(--text-light);">
        <i class="fas fa-users" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3;"></i>
        No members found.
      </td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map(m => `
    <tr data-id="${m.id}">
      <td><input type="checkbox" /></td>
      <td>
        <div class="name-cell">
          <div class="avatar">${initials(m.firstName, m.lastName)}</div>
          <span class="name-text">${fullName(m)}</span>
        </div>
      </td>
      <td class="td-mid">${m.course}</td>
      <td class="td-mid td-bold">${m.year}</td>
      <td class="td-mid td-ellipsis" title="${m.club}">${m.club}</td>
      <td><span class="${roleBadgeClass(m.role)}">${roleLabel(m.role)}</span></td>
      <td><span class="${statusBadgeClass(m.status)}">${m.status.charAt(0).toUpperCase() + m.status.slice(1)}</span></td>
      <td>
        <div class="actions-cell">
          <button class="act-btn view" title="View"   onclick="openView(${m.id})"><i class="fas fa-eye"></i></button>
          <button class="act-btn edit" title="Edit"   onclick="openEdit(${m.id})"><i class="fas fa-pen"></i></button>
          <button class="act-btn del"  title="Delete" onclick="confirmDelete(${m.id})"><i class="fas fa-trash"></i></button>
        </div>
      </td>
    </tr>
  `).join('');
}

/* ── Modals HTML ─────────────────────────────────────────── */
function injectModals() {
  if (document.getElementById('member-modal')) return;

  // Build club options from DB
  const clubOptions = (window.DB_CLUBS_LIST || [])
    .map(c => `<option value="${c.name}">${c.name}</option>`).join('');

  document.body.insertAdjacentHTML('beforeend', `

  <!-- ADD / EDIT MODAL -->
  <div id="member-modal" class="modal-overlay" onclick="closeModalOverlay(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3 id="modal-title" class="modal-heading">Edit Member</h3>
        <button class="modal-close" onclick="closeMemberModal()"><i class="fas fa-times"></i></button>
      </div>
      <form id="member-form" onsubmit="saveMember(event)">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label>First Name <span class="req">*</span></label>
              <input type="text" id="f-first" placeholder="e.g. Juan" required />
            </div>
            <div class="form-group">
              <label>Last Name <span class="req">*</span></label>
              <input type="text" id="f-last" placeholder="e.g. Dela Cruz" required />
            </div>
          </div>
          <div class="form-group">
            <label>Course <span class="req">*</span></label>
            <input type="text" id="f-course" placeholder="e.g. BS Information Technology" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Year &amp; Section <span class="req">*</span></label>
              <input type="text" id="f-year" placeholder="e.g. 3A" required />
            </div>
            <div class="form-group">
              <label>Role <span class="req">*</span></label>
              <select id="f-role" required>
                <option value="">Select role</option>
                <option value="member">Member</option>
                <option value="officer">Officer</option>
                <option value="vice president">Vice President</option>
                <option value="president">President</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Club</label>
            <input type="text" id="f-club" readonly style="background:#f5f5f5;cursor:not-allowed;" title="Club cannot be changed here" />
          </div>
          <div class="form-group">
            <label>Status <span class="req">*</span></label>
            <select id="f-status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          <p id="form-error" class="form-error" style="display:none;"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel-modal" onclick="closeMemberModal()">Cancel</button>
          <button type="submit" class="btn-save" id="btn-save-label">
            <i class="fas fa-floppy-disk"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- VIEW MODAL -->
  <div id="view-modal" class="modal-overlay" onclick="closeModalOverlay(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3 class="modal-heading">Member Details</h3>
        <button class="modal-close" onclick="closeViewModal()"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <div class="view-avatar-row">
          <div class="view-avatar" id="view-avatar"></div>
          <div>
            <div class="view-name" id="view-name"></div>
            <div id="view-role-badge"></div>
          </div>
        </div>
        <div class="view-grid">
          <div class="view-item"><span class="view-label">Email</span><span id="view-email" class="view-val"></span></div>
          <div class="view-item"><span class="view-label">Course</span><span id="view-course" class="view-val"></span></div>
          <div class="view-item"><span class="view-label">Year &amp; Section</span><span id="view-year" class="view-val"></span></div>
          <div class="view-item"><span class="view-label">Club</span><span id="view-club" class="view-val"></span></div>
          <div class="view-item"><span class="view-label">Status</span><span id="view-status" class="view-val"></span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel-modal" onclick="closeViewModal()">Close</button>
        <button type="button" class="btn-save" onclick="switchToEdit()">
          <i class="fas fa-pen"></i> Edit
        </button>
      </div>
    </div>
  </div>

  <!-- DELETE CONFIRM MODAL -->
  <div id="delete-modal" class="modal-overlay" onclick="closeModalOverlay(event)">
    <div class="modal-box modal-box-sm" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3 class="modal-heading">Remove Member</h3>
        <button class="modal-close" onclick="closeDeleteModal()"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <div class="delete-icon-wrap"><i class="fas fa-trash-can"></i></div>
        <p class="delete-msg">Are you sure you want to remove <strong id="delete-name"></strong>? This cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel-modal" onclick="closeDeleteModal()">Cancel</button>
        <button type="button" class="btn-delete-confirm" onclick="deleteMember()">
          <i class="fas fa-trash"></i> Remove
        </button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div id="toast" class="toast"></div>

  <style>
    .modal-overlay { position:fixed;inset:0;z-index:9999;background:rgba(13,43,26,.45);display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:opacity .22s ease; }
    .modal-overlay.open { opacity:1;pointer-events:all; }
    .modal-box { background:#fff;border-radius:18px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(13,43,26,.22);transform:translateY(16px) scale(.97);transition:transform .22s ease;overflow:hidden; }
    .modal-overlay.open .modal-box { transform:translateY(0) scale(1); }
    .modal-box-sm { max-width:400px; }
    .modal-header { display:flex;align-items:center;justify-content:space-between;padding:18px 22px 14px;border-bottom:1px solid var(--border); }
    .modal-heading { font-size:15px;font-weight:800;color:var(--text-dark); }
    .modal-close { width:30px;height:30px;border-radius:8px;border:none;background:var(--main-bg);color:var(--text-mid);cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;transition:all .15s; }
    .modal-close:hover { background:#fde8e6;color:var(--red); }
    .modal-body { padding:18px 22px; }
    .modal-footer { padding:14px 22px 18px;display:flex;justify-content:flex-end;gap:8px;border-top:1px solid var(--border); }
    .form-row { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .form-group { display:flex;flex-direction:column;gap:5px;margin-bottom:12px; }
    .form-group label { font-size:11.5px;font-weight:700;color:var(--text-mid); }
    .form-group input,.form-group select { border:1.5px solid var(--border);border-radius:10px;padding:9px 12px;font-size:13px;font-family:inherit;color:var(--text-dark);background:var(--main-bg);outline:none;transition:all .18s; }
    .form-group input:focus,.form-group select:focus { border-color:var(--green-accent);background:#fff;box-shadow:0 0 0 3px var(--green-glow); }
    .req { color:var(--red); }
    .form-error { font-size:12px;color:var(--red);background:#fde8e6;border-radius:8px;padding:8px 12px;margin-top:4px; }
    .btn-save { display:flex;align-items:center;gap:6px;background:var(--green-accent);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .18s; }
    .btn-save:hover { background:var(--green-mid); }
    .btn-cancel-modal { display:flex;align-items:center;gap:6px;background:var(--main-bg);color:var(--text-mid);border:1.5px solid var(--border);border-radius:10px;padding:9px 18px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .18s; }
    .btn-cancel-modal:hover { border-color:var(--green-accent);color:var(--green-dark); }
    .btn-delete-confirm { display:flex;align-items:center;gap:6px;background:var(--red);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .18s; }
    .btn-delete-confirm:hover { background:#a02820; }
    .view-avatar-row { display:flex;align-items:center;gap:14px;margin-bottom:18px; }
    .view-avatar { width:54px;height:54px;border-radius:50%;background:var(--green-light);color:var(--green-dark);font-size:18px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid var(--border); }
    .view-name { font-size:17px;font-weight:800;color:var(--text-dark);margin-bottom:5px; }
    .view-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .view-item { display:flex;flex-direction:column;gap:3px; }
    .view-label { font-size:10.5px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.5px; }
    .view-val { font-size:13px;font-weight:600;color:var(--text-dark); }
    .delete-icon-wrap { text-align:center;font-size:2.2rem;color:var(--red);margin-bottom:12px;opacity:.8; }
    .delete-msg { text-align:center;font-size:13.5px;color:var(--text-mid);line-height:1.6; }
    .delete-msg strong { color:var(--text-dark); }
    .toast { position:fixed;bottom:28px;right:28px;z-index:99999;background:var(--green-mid);color:#fff;padding:11px 20px;border-radius:12px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(13,43,26,.25);transform:translateY(20px);opacity:0;transition:all .28s ease;pointer-events:none;display:flex;align-items:center;gap:8px; }
    .toast.show { transform:translateY(0);opacity:1; }
    .toast.error { background:var(--red); }
  </style>
  `);
}

/* ── Toast ───────────────────────────────────────────────── */
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.className = `toast${type === 'error' ? ' error' : ''}`;
  t.innerHTML = `<i class="fas fa-${type === 'error' ? 'circle-xmark' : 'circle-check'}"></i> ${msg}`;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

async function saveMember(e) {
  e.preventDefault();

  const course  = document.getElementById('f-course').value.trim();
  const year    = document.getElementById('f-year').value.trim();
  const role    = document.getElementById('f-role').value;
  const status  = document.getElementById('f-status').value;
  const errEl   = document.getElementById('form-error');

  if (!course || !year || !role || !status) {
    errEl.textContent   = 'Please fill in all required fields.';
    errEl.style.display = 'block';
    return;
  }

  const saveBtn = document.getElementById('btn-save-label');
  saveBtn.disabled  = true;
  saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

  try {
    const firstName = document.getElementById('f-first').value.trim();
    const lastName  = document.getElementById('f-last').value.trim();
    await postPage('member_update', { id: editingId, first_name: firstName, last_name: lastName, course, year, role, status });

    const idx = members.findIndex(m => m.id === editingId);
    if (idx > -1) {
      members[idx] = { ...members[idx], firstName, lastName, course, year, role, status };
    }
    showToast(`${members[idx] ? fullName(members[idx]) : 'Member'} updated successfully!`);
    closeMemberModal();
    renderTable();
  } catch (err) {
    errEl.textContent   = err.message || 'Save failed. Try again.';
    errEl.style.display = 'block';
  } finally {
    saveBtn.disabled  = false;
    saveBtn.innerHTML = '<i class="fas fa-floppy-disk"></i> Save Changes';
  }
}

function closeMemberModal() {
  document.getElementById('member-modal').classList.remove('open');
  editingId = null;
}

function closeViewModal() {
  document.getElementById('view-modal').classList.remove('open');
  viewingId = null;
}

function switchToEdit() { if (viewingId !== null) openEdit(viewingId); }

/* ── Delete ──────────────────────────────────────────────── */
function confirmDelete(id) {
  const m = members.find(x => x.id === id);
  if (!m) return;
  pendingDeleteId = id;
  document.getElementById('delete-name').textContent = fullName(m);
  document.getElementById('delete-modal').classList.add('open');
}

async function deleteMember() {
  if (pendingDeleteId === null) return;
  const m = members.find(x => x.id === pendingDeleteId);

  try {
    await postPage('member_delete', { id: pendingDeleteId });
    members = members.filter(x => x.id !== pendingDeleteId);
    closeDeleteModal();
    renderTable();
    if (m) showToast(`${fullName(m)} has been removed.`, 'error');
  } catch (err) {
    closeDeleteModal();
    showToast(err.message || 'Delete failed.', 'error');
  }
  pendingDeleteId = null;
}

function closeDeleteModal() {
  document.getElementById('delete-modal').classList.remove('open');
}

function closeModalOverlay(e) {
  if (e.target.classList.contains('modal-overlay')) {
    document.querySelectorAll('.modal-overlay').forEach(el => el.classList.remove('open'));
  }
}

/* ── Filters & search ────────────────────────────────────── */
function initFilters() {
  // Status tabs
  document.querySelectorAll('.filter .tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.filter .tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      currentFilter = tab.dataset.status || tab.textContent.trim().toLowerCase();
      renderTable();
    });
  });

  // Club filter
  const clubSel = document.getElementById('clubFilter');
  if (clubSel) clubSel.addEventListener('change', e => { clubFilter = e.target.value; renderTable(); });

  // Role filter
  const roleSel = document.getElementById('roleFilter');
  if (roleSel) roleSel.addEventListener('change', e => { roleFilter = e.target.value.toLowerCase(); renderTable(); });

  // Member search
  const searchEl = document.getElementById('memberSearch');
  if (searchEl) searchEl.addEventListener('input', e => { searchQuery = e.target.value; renderTable(); });

  // Topbar search
  const topSearch = document.querySelector('.topbar-search input');
  if (topSearch) topSearch.addEventListener('input', e => { searchQuery = e.target.value; renderTable(); });
}

/* ── ESC closes modals ───────────────────────────────────── */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(el => el.classList.remove('open'));
});

/* ── Init ────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  injectModals();
  initFilters();
  // renderTable();
});
