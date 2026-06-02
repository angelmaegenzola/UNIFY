/* ============================================================
   UNIFY — Events JS  (events.js)
   DB data is injected by events.php as window.DB_EVENTS
============================================================ */

/* ════════════════════════════════════════════════
   State
════════════════════════════════════════════════ */
let events        = (window.DB_EVENTS || []).map(e => ({ ...e }));
let activeFilter  = 'all';
let searchQuery   = '';
let editingId     = null;
let pendingDeleteId = null;

const COLORS = ['green','gold','orange','red','teal','blue'];
const ICONS  = ['fa-calendar-days','fa-users','fa-trophy','fa-star','fa-bolt','fa-music','fa-flask','fa-book','fa-microphone','fa-globe'];

/* ════════════════════════════════════════════════
   Helpers
════════════════════════════════════════════════ */
async function postPage(action, fields) {
  const body = new URLSearchParams({ action, ...fields });
  const res  = await fetch(window.location.href, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  });
  if (!res.ok) throw new Error('Server error ' + res.status);
  const data = await res.json();
  if (!data.success) throw new Error(data.message || 'Operation failed.');
  return data;
}

function fmtDate(d) {
  if (!d) return '—';
  const dt = new Date(d + 'T00:00:00');
  return dt.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
}

function fmtTime(t) {
  if (!t) return '';
  const [h,m] = t.split(':');
  const hr = parseInt(h);
  return `${hr % 12 || 12}:${m} ${hr < 12 ? 'AM' : 'PM'}`;
}

function isToday(dateStr) {
  const today = new Date();
  const d     = new Date(dateStr + 'T00:00:00');
  return d.toDateString() === today.toDateString();
}

function isPast(dateStr) {
  const today = new Date(); today.setHours(0,0,0,0);
  return new Date(dateStr + 'T00:00:00') < today;
}

function statusBadge(e) {
  if (e.status === 'pending_approval') return '<span class="event-status-badge badge-pending">Pending Approval</span>';
  if (e.status === 'rejected') return '<span class="event-status-badge badge-rejected">Rejected</span>';
  if (e.status === 'cancelled') return '<span class="event-status-badge badge-cancelled">Cancelled</span>';
  if (e.status === 'ongoing')   return '<span class="event-status-badge badge-ongoing">Ongoing</span>';
  if (e.status === 'completed') return '<span class="event-status-badge badge-completed">Completed</span>';
  if (isToday(e.event_date))    return '<span class="event-status-badge badge-today">Today</span>';
  return '<span class="event-status-badge badge-upcoming">Upcoming</span>';
}

function escHtml(s) {
  return String(s)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}

/* ════════════════════════════════════════════════
   Filter
════════════════════════════════════════════════ */
function getFiltered() {
  return events.filter(e => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q
      || e.name.toLowerCase().includes(q)
      || e.club_name.toLowerCase().includes(q)
      || (e.location||'').toLowerCase().includes(q);

    let matchFilter = true;
    if (activeFilter === 'today')     matchFilter = isToday(e.event_date);
    if (activeFilter === 'upcoming')  matchFilter = ['upcoming','ongoing','pending_approval'].includes(e.status) && !isPast(e.event_date);
    if (activeFilter === 'completed') matchFilter = e.status === 'completed' || e.status === 'rejected' || (isPast(e.event_date) && e.status !== 'upcoming' && e.status !== 'ongoing');

    return matchSearch && matchFilter;
  });
}

/* ════════════════════════════════════════════════
   Render Events
════════════════════════════════════════════════ */
function groupByDate(arr) {
  const groups = {};
  arr.forEach(e => {
    if (!groups[e.event_date]) groups[e.event_date] = [];
    groups[e.event_date].push(e);
  });
  return groups;
}

function renderEvents() {
  const col      = document.getElementById('eventsMainCol');
  const filtered = getFiltered();

  if (!filtered.length) {
    col.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-calendar-xmark"></i>
        <p>No events found</p>
        <small style="font-size:11px;margin-top:4px;color:var(--text-light);">Try adjusting your search or filter</small>
      </div>`;
    return;
  }

  const groups = groupByDate(filtered);
  let html = '';
  let globalIdx = 0;

  Object.keys(groups).sort().forEach(dateKey => {
    const dayEvents = groups[dateKey];
    const label    = isToday(dateKey) ? `Today's Events` : `Events – ${fmtDate(dateKey)}`;
    const sublabel = isToday(dateKey) ? fmtDate(dateKey) : '';

    html += `
      <div class="section-row">
        <span class="section-title">${label}</span>
        ${sublabel ? `<span class="section-date">${sublabel}</span>` : ''}
      </div>
      <div class="event-cards-grid">`;

    dayEvents.forEach(e => {
      const color   = COLORS[globalIdx % COLORS.length];
      const icon    = ICONS[globalIdx % ICONS.length];
      globalIdx++;

      const timeStr = e.start_time
        ? fmtTime(e.start_time) + (e.end_time ? ' – ' + fmtTime(e.end_time) : '')
        : '—';

      html += `
        <div class="event-card color-${color}" data-id="${e.id}">
          <div class="event-card-top">
            <div class="event-icon-wrap icon-${color}">
              <i class="fas ${icon}"></i>
            </div>
            ${statusBadge(e)}
          </div>
          <div>
            <div class="event-title">${escHtml(e.name)}</div>
            <div class="event-club">${escHtml(e.club_name)}</div>
          </div>
          <div class="event-card-footer">
            <div class="event-meta-item">
              <i class="fas fa-clock"></i> ${timeStr}
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              ${e.location ? `<span class="event-meta-item"><i class="fas fa-location-dot"></i> ${escHtml(e.location)}</span>` : ''}
              <div class="event-card-actions">
                <button class="card-act-btn edit" title="Edit"   onclick="openEdit(${e.id}); event.stopPropagation();"><i class="fas fa-pen"></i></button>
                <button class="card-act-btn del"  title="Delete" onclick="openDeleteModal(${e.id}); event.stopPropagation();"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>`;
    });

    html += `</div>`; // .event-cards-grid
  });

  col.innerHTML = html;
}

/* ════════════════════════════════════════════════
   Mini Calendar
════════════════════════════════════════════════ */
let calDate = new Date();

function renderCalendar() {
  const year  = calDate.getFullYear();
  const month = calDate.getMonth();

  document.getElementById('calMonthLabel').textContent =
    calDate.toLocaleDateString('en-US', { month:'long', year:'numeric' });

  const eventDates    = new Set(events.map(e => e.event_date));
  const today         = new Date(); today.setHours(0,0,0,0);
  const firstDay      = new Date(year, month, 1).getDay();
  const daysInMonth   = new Date(year, month+1, 0).getDate();
  const prevMonthDays = new Date(year, month, 0).getDate();
  const totalCells    = Math.ceil((firstDay + daysInMonth) / 7) * 7;

  let html = '';
  for (let i = 0; i < totalCells; i++) {
    let day, dateStr, cls = 'cal-day';
    if (i < firstDay) {
      day = prevMonthDays - firstDay + i + 1;
      cls += ' other-month';
      dateStr = `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
    } else if (i >= firstDay + daysInMonth) {
      day = i - firstDay - daysInMonth + 1;
      cls += ' other-month';
      dateStr = `${year}-${String(month+2).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
    } else {
      day = i - firstDay + 1;
      dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
      const d = new Date(year, month, day);
      if (d.getTime() === today.getTime()) cls += ' today';
    }
    if (eventDates.has(dateStr)) cls += ' has-event';
    html += `<div class="${cls}">${day}</div>`;
  }
  document.getElementById('calDays').innerHTML = html;
}

/* ════════════════════════════════════════════════
   Pending Approvals
════════════════════════════════════════════════ */
function renderApprovals() {
  const today   = new Date(); today.setHours(0,0,0,0);
  const pending = events.filter(e => {
    const d = new Date(e.event_date + 'T00:00:00');
    return e.status === 'pending_approval';
  }).slice(0, 5);

  document.getElementById('pendingCount').textContent = `${pending.length} pending`;

  const scrollEl = document.getElementById('approvalsScroll');
  if (!pending.length) {
    scrollEl.innerHTML = `<div class="no-approvals"><i class="fas fa-check-circle"></i>No pending approvals</div>`;
    return;
  }

  const urgencies    = ['High','Medium','Low'];
  const urgencyClass = ['urgency-high','urgency-medium','urgency-low'];
  const iconColors   = ['icon-red','icon-gold','icon-teal'];
  const icons        = ['fa-microphone','fa-palette','fa-flask','fa-users','fa-trophy'];

  scrollEl.innerHTML = pending.map((e, i) => `
    <div class="approval-item">
      <div class="approval-item-top">
        <div class="approval-item-icon ${iconColors[i % 3]}">
          <i class="fas ${icons[i % icons.length]}"></i>
        </div>
        <span class="approval-title">${escHtml(e.name)}</span>
        <span class="approval-urgency ${urgencyClass[i % 3]}">${urgencies[i % 3]}</span>
      </div>
      <div class="approval-meta">
        <span><i class="fas fa-building-columns"></i> ${escHtml(e.club_name)}</span>
        <span><i class="fas fa-calendar"></i> ${fmtDate(e.event_date)}</span>
      </div>
      <div class="approval-actions">
        <button class="btn-view-details" onclick="openEdit(${e.id})"><i class="fas fa-eye"></i> Details</button>
        <button class="btn-approve" onclick="markCompleted(${e.id})">Approve</button>
        <button class="btn-reject"  onclick="rejectPendingEvent(${e.id})">Reject</button>
      </div>
    </div>`).join('');
}

/* ════════════════════════════════════════════════
   Modal: Add / Edit
════════════════════════════════════════════════ */
function openAdd() {
  editingId = null;
  document.getElementById('event-modal-title').textContent = 'Add Event';
  document.getElementById('ef-submit').innerHTML = '<i class="fas fa-plus"></i> Save Event';
  document.getElementById('event-form').reset();
  document.getElementById('ef-error').style.display = 'none';
  document.getElementById('event-modal').classList.add('open');
}

function openEdit(id) {
  const e = events.find(x => x.id === parseInt(id));
  if (!e) return;
  editingId = e.id;
  document.getElementById('event-modal-title').textContent = 'Edit Event';
  document.getElementById('ef-submit').innerHTML = '<i class="fas fa-floppy-disk"></i> Save Changes';
  document.getElementById('ef-club').value     = e.club_id;
  document.getElementById('ef-name').value     = e.name;
  document.getElementById('ef-desc').value     = e.description || '';
  document.getElementById('ef-date').value     = e.event_date;
  document.getElementById('ef-start').value    = e.start_time  || '';
  document.getElementById('ef-end').value      = e.end_time    || '';
  document.getElementById('ef-location').value = e.location    || '';
  document.getElementById('ef-status').value   = e.status;
  document.getElementById('ef-error').style.display = 'none';
  document.getElementById('event-modal').classList.add('open');
}

function closeEventModal() {
  document.getElementById('event-modal').classList.remove('open');
  editingId = null;
}

async function saveEvent(evt) {
  evt.preventDefault();
  const club_id     = document.getElementById('ef-club').value;
  const name        = document.getElementById('ef-name').value.trim();
  const description = document.getElementById('ef-desc').value.trim();
  const event_date  = document.getElementById('ef-date').value;
  const start_time  = document.getElementById('ef-start').value;
  const end_time    = document.getElementById('ef-end').value;
  const location    = document.getElementById('ef-location').value.trim();
  const status      = document.getElementById('ef-status').value;
  const errEl       = document.getElementById('ef-error');
  const btn         = document.getElementById('ef-submit');

  if (!club_id || !name || !event_date) {
    errEl.textContent = 'Club, name and date are required.';
    errEl.style.display = 'block';
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

  try {
    const fields = { club_id, name, description, event_date, start_time, end_time, location, status };

    if (editingId) {
      // ── UPDATE ──
      await postPage('event_update', { id: editingId, ...fields });
      const idx      = events.findIndex(x => x.id === parseInt(editingId));
      const clubSel  = document.getElementById('ef-club');
      const clubName = clubSel.options[clubSel.selectedIndex].text;
      if (idx > -1) {
        events[idx] = {
          ...events[idx],
          ...fields,
          club_id:   parseInt(club_id),   // ← ensure number wins over string from fields
          club_name: clubName,
        };
      }
      showToast(`"${name}" updated.`);
    } else {
      // ── CREATE ──
      const res     = await postPage('event_create', fields);
      const clubSel = document.getElementById('ef-club');
      const clubName = clubSel.options[clubSel.selectedIndex].text;
      const acronym  = clubName.split(' ').map(w => w[0]).join('').toUpperCase();
      events.push({
        ...fields,
        id:           parseInt(res.id),   // ← force number AFTER spread so it wins
        club_id:      parseInt(club_id),  // ← force number AFTER spread so it wins
        club_name:    clubName,
        club_acronym: acronym,
      });
      showToast(`"${name}" created.`);
    }
    closeEventModal();
    renderAll();
  } catch (err) {
    errEl.textContent = err.message;
    errEl.style.display = 'block';
  } finally {
    btn.disabled = false;
    btn.innerHTML = editingId
      ? '<i class="fas fa-floppy-disk"></i> Save Changes'
      : '<i class="fas fa-plus"></i> Save Event';
  }
}

/* ════════════════════════════════════════════════
   Modal: Delete
════════════════════════════════════════════════ */
function openDeleteModal(id) {
  const e = events.find(x => x.id === parseInt(id));
  if (!e) return;
  pendingDeleteId = parseInt(id);   // ← store as number for consistent comparison
  document.getElementById('delete-event-name').textContent = e.name;
  document.getElementById('delete-modal').classList.add('open');
}

async function confirmDeleteExec() {
  if (!pendingDeleteId) return;
  const e = events.find(x => x.id === pendingDeleteId);
  try {
    await postPage('event_delete', { id: pendingDeleteId });
    events = events.filter(x => x.id !== pendingDeleteId);
    closeDeleteModal();
    renderAll();
    if (e) showToast(`"${e.name}" deleted.`, true);
  } catch (err) {
    showToast(err.message, true);
    closeDeleteModal();
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

/* ════════════════════════════════════════════════
   Approve pending event → sets status = 'upcoming'
════════════════════════════════════════════════ */
function markCompleted(id) {
  const e = events.find(x => x.id === parseInt(id));
  if (!e) return;
  _pendingApproveId = id;
  document.getElementById('evtApproveNameLabel').textContent = e.name;
  document.getElementById('evtApproveModal').classList.add('open');
}

/* ════════════════════════════════════════════════
   Reject pending event → sets status = 'rejected'
   (previously wrongly wired to openDeleteModal which
   permanently deleted the DB row instead of rejecting it)
════════════════════════════════════════════════ */
/* ════════════════════════════════════════════════
   Pending approve/reject — uses styled modals
════════════════════════════════════════════════ */
let _pendingApproveId = null;
let _pendingRejectId  = null;

function rejectPendingEvent(id) {
  const e = events.find(x => x.id === parseInt(id));
  if (!e) return;
  _pendingRejectId = id;
  document.getElementById('evtRejectNameLabel').textContent = e.name;
  document.getElementById('evtRejectNote').value = '';
  document.getElementById('evtRejectModal').classList.add('open');
}

async function confirmRejectEvent() {
  const e = events.find(x => x.id === parseInt(_pendingRejectId));
  if (!e) return;
  const note = document.getElementById('evtRejectNote')?.value?.trim() || '';
  document.getElementById('evtRejectModal').classList.remove('open');
  try {
    await postPage('event_reject', { id: e.id, note });
    e.status = 'rejected';
    renderAll();
    showToast(`"${e.name}" rejected.`, true);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function confirmApproveEvent() {
  const e = events.find(x => x.id === parseInt(_pendingApproveId));
  if (!e) return;
  document.getElementById('evtApproveModal').classList.remove('open');
  try {
    await postPage('event_approve', { id: e.id });
    e.status = 'upcoming';
    renderAll();
    showToast(`"${e.name}" approved and is now upcoming.`);
  } catch (err) {
    showToast(err.message, true);
  }
}

/* ════════════════════════════════════════════════
   Toast
════════════════════════════════════════════════ */
function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.className = `toast${isError ? ' error' : ''} show`;
  t.innerHTML = `<i class="fas fa-${isError ? 'circle-xmark' : 'circle-check'}"></i> ${msg}`;
  setTimeout(() => t.classList.remove('show'), 3000);
}

/* ════════════════════════════════════════════════
   Render All
════════════════════════════════════════════════ */
function renderAll() {
  renderEvents();
  renderCalendar();
  renderApprovals();
}

/* ════════════════════════════════════════════════
   Init
════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  // Filter tabs
  document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.dataset.filter;
      renderEvents();
    });
  });

  // Search
  document.getElementById('topbarSearch').addEventListener('input', e => {
    searchQuery = e.target.value;
    renderEvents();
  });

  // Add Event button
  document.getElementById('addEventBtn').addEventListener('click', openAdd);

  // Calendar nav
  document.getElementById('calPrev').addEventListener('click', () => {
    calDate = new Date(calDate.getFullYear(), calDate.getMonth() - 1, 1);
    renderCalendar();
  });
  document.getElementById('calNext').addEventListener('click', () => {
    calDate = new Date(calDate.getFullYear(), calDate.getMonth() + 1, 1);
    renderCalendar();
  });

  // Escape key closes modals
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(el => el.classList.remove('open'));
  });

  renderAll();
});