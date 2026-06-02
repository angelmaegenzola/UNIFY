/* ============================================================
   UNIFY — My Clubs JS  (DB-backed, no separate API)
   assets/javascripts/myclubs.js

   All POST requests go back to the same page:
     index.php?page=myclubs
   with a FormData `action` field (load / leave / rsvp).
============================================================ */

const PAGE_URL = window.location.href.split('?')[0] + '?page=myclubs';

/* ── State ─────────────────────────────────────────────── */
let allClubs         = [];
let currentCat       = 'all';
let currentClubIndex = null;

/* ══════════════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
    setTopbarDate();
    loadClubs();
    initNotifications();
    initEscKey();
});

function setTopbarDate() {
    const el = document.getElementById('topbarDate');
    if (!el) return;
    el.textContent = new Date().toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
}

/* ══════════════════════════════════════════════════════════
   HELPER — post to the same page
══════════════════════════════════════════════════════════ */
async function postAction(fields) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(fields)) fd.append(k, v);
    const res = await fetch(PAGE_URL, { method: 'POST', body: fd });
    return res.json();
}

/* ══════════════════════════════════════════════════════════
   LOAD CLUBS
══════════════════════════════════════════════════════════ */
async function loadClubs() {
    try {
        const data = await postAction({ action: 'load' });
        if (!data.success) {
            showToast(data.message || 'Could not load clubs.', 'warn');
            renderClubs([]);
            return;
        }
        allClubs = data.clubs || [];
        renderClubs(allClubs);
    } catch (err) {
        console.error('loadClubs error:', err);
        showToast('Network error — could not load clubs.', 'warn');
        renderClubs([]);
    }
}

/* ══════════════════════════════════════════════════════════
   RENDER CLUBS
══════════════════════════════════════════════════════════ */
function renderClubs(clubs) {
    const grid        = document.getElementById('myClubsGrid');
    const emptyEl     = document.getElementById('emptyState');
    const noResultsEl = document.getElementById('noResults');
    const countEl     = document.getElementById('filterCount');

    grid.innerHTML = '';
    emptyEl.style.display     = 'none';
    noResultsEl.style.display = 'none';

    if (!clubs || clubs.length === 0) {
        emptyEl.style.display = 'flex';
        countEl.textContent   = '0 clubs';
        return;
    }

    const searchEl = document.getElementById('searchInput');
    const query = (searchEl ? searchEl.value : '').toLowerCase().trim();
    const visible = clubs.filter(club => {
        const matchCat  = currentCat === 'all' || club.cat === currentCat;
        const matchText = !query ||
            club.name.toLowerCase().includes(query) ||
            club.cat.toLowerCase().includes(query) ||
            (club.desc || '').toLowerCase().includes(query);
        return matchCat && matchText;
    });

    countEl.textContent = `${visible.length} club${visible.length !== 1 ? 's' : ''}`;

    if (visible.length === 0) {
        noResultsEl.style.display = 'flex';
        return;
    }

    visible.forEach(club => {
        const index = allClubs.indexOf(club);
        const card  = document.createElement('div');
        card.className    = 'club-card';
        card.dataset.cat  = club.cat;
        card.dataset.name = club.name;

        const isOfficer = ['Officer','Vice President','President'].includes(club.role);

        card.innerHTML = `
          <div class="cc-header">
            <img class="cc-logo" src="${club.logo || ''}" alt="${club.name}"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="cc-logo-fallback" style="display:none">
              <i class="fas ${club.catIcon || 'fa-users'}"></i>
            </div>
            <div class="cc-badges">
              <span class="cc-category ${club.catClass}">
                <i class="fas ${club.catIcon}"></i> ${club.cat}
              </span>
              <span class="cc-role-badge ${isOfficer ? 'role-officer' : 'role-member'}">
                ${club.role}
              </span>
            </div>
          </div>

          <div class="cc-body">
            <div class="cc-name">${club.name}</div>
            <div class="cc-desc">${club.desc || ''}</div>
            <div class="cc-meta-row">
              <span class="cc-meta"><i class="fas fa-users"></i> ${club.members} members</span>
              <span class="cc-meta"><i class="fas fa-calendar-days"></i> ${club.events} events</span>
              <span class="cc-meta"><i class="fas fa-seedling"></i> ${club.founded}</span>
            </div>
          </div>

          <div class="cc-footer">
            <span class="cc-joined"><i class="fas fa-check-circle"></i> Joined ${club.joined}</span>
            <div class="cc-actions">
              <button class="cc-btn cc-btn-events" onclick="openEventsModal(${index})" title="View Events">
                <i class="fas fa-calendar-days"></i>
              </button>
              <button class="cc-btn cc-btn-view" onclick="openDetailModal(${index})">
                View <i class="fas fa-arrow-right" style="font-size:10px;"></i>
              </button>
              <button class="cc-btn cc-btn-leave" onclick="promptLeave(${index})" title="Leave Club">
                <i class="fas fa-right-from-bracket"></i>
              </button>
            </div>
          </div>`;

        grid.appendChild(card);
    });
}

/* ══════════════════════════════════════════════════════════
   FILTER
══════════════════════════════════════════════════════════ */
function setCat(btn, cat) {
    currentCat = cat;
    document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    renderClubs(allClubs);
}

function filterMyClubs() {
    renderClubs(allClubs);
}

/* ══════════════════════════════════════════════════════════
   DETAIL MODAL
══════════════════════════════════════════════════════════ */
function openDetailModal(index) {
    const club = allClubs[index];
    if (!club) return;
    currentClubIndex = index;

    const logoEl     = document.getElementById('mdLogo');
    const logoFallEl = document.getElementById('mdLogoFallback');
    logoEl.src               = club.logo || '';
    logoEl.style.display     = '';
    logoFallEl.style.display = 'none';
    logoEl.onerror = () => {
        logoEl.style.display     = 'none';
        logoFallEl.style.display = 'flex';
    };

    document.getElementById('mdName').textContent    = club.name;
    document.getElementById('mdCatLine').textContent = `${club.cat} · ${club.role}`;
    document.getElementById('mdMembers').textContent = club.members;
    document.getElementById('mdEvents').textContent  = club.events;
    document.getElementById('mdFounded').textContent = club.founded;
    document.getElementById('mdDesc').textContent    = club.desc || '';
    document.getElementById('mdRole').textContent    = club.role;
    document.getElementById('mdJoined').textContent  = club.joined;
    document.getElementById('mdRoom').textContent    = club.room;

    const officerList = document.getElementById('mdOfficers');
    officerList.innerHTML = '';
    (club.officers || []).forEach(off => {
        officerList.innerHTML += `
          <div class="officer-row">
            <div class="officer-avatar" style="background:${off.color}">${off.initials}</div>
            <div class="officer-name">${off.name}</div>
            <span class="officer-role-pill">${off.role}</span>
          </div>`;
    });

    openModal('detailOverlay');
}

function closeDetailOverlay(e) {
    if (e.target === document.getElementById('detailOverlay')) closeModal('detailOverlay');
}

/* ══════════════════════════════════════════════════════════
   EVENTS MODAL
══════════════════════════════════════════════════════════ */
function openEventsModal(index) {
    const club = allClubs[index];
    if (!club) return;
    currentClubIndex = index;

    document.getElementById('evModalName').textContent = club.name;
    const evLogo = document.getElementById('evModalLogo');
    evLogo.src     = club.logo || '';
    evLogo.onerror = () => { evLogo.style.display = 'none'; };

    const body = document.getElementById('evModalBody');
    const evs  = club.clubEvents || [];

    if (evs.length === 0) {
        body.innerHTML = `
          <div style="text-align:center;padding:32px;color:var(--text-light);">
            <i class="fas fa-calendar-xmark" style="font-size:32px;opacity:.35;margin-bottom:10px;"></i>
            <p style="font-size:13px;font-weight:700;color:var(--text-mid);">No upcoming events.</p>
          </div>`;
    } else {
        body.innerHTML = evs.map((ev, i) => `
          <div class="event-item">
            <div class="event-date-box">
              <div class="ev-month">${ev.month}</div>
              <div class="ev-day">${ev.day}</div>
            </div>
            <div class="event-info">
              <div class="event-title">${ev.title}</div>
              <div class="event-meta-row">
                <span><i class="fas fa-clock"></i> ${ev.time}</span>
                <span><i class="fas fa-location-dot"></i> ${ev.location}</span>
                <span><i class="fas fa-users"></i> ${ev.going} going</span>
              </div>
              <button class="rsvp-btn ${ev.rsvped ? 'going' : ''}"
                      data-event-id="${ev.id}"
                      onclick="toggleRSVP(${index}, ${i}, this)">
                ${ev.rsvped
                    ? '<i class="fas fa-check" style="font-size:10px;"></i> Going'
                    : 'RSVP'}
              </button>
            </div>
          </div>`).join('');
    }

    openModal('eventsOverlay');
}

function closeEventsOverlay(e) {
    if (e.target === document.getElementById('eventsOverlay')) closeModal('eventsOverlay');
}

function goToEvents() {
    closeModal('detailOverlay');
    if (currentClubIndex !== null) openEventsModal(currentClubIndex);
}

/* ── RSVP toggle ───────────────────────────────────────── */
async function toggleRSVP(clubIndex, eventIndex, btn) {
    const ev = allClubs[clubIndex]?.clubEvents?.[eventIndex];
    if (!ev) return;

    btn.disabled = true;
    try {
        const data = await postAction({ action: 'rsvp', event_id: ev.id });

        if (!data.success) {
            showToast(data.message || 'RSVP failed.', 'warn');
            return;
        }

        ev.rsvped = data.rsvped;
        ev.going  = data.going;

        if (ev.rsvped) {
            btn.classList.add('going');
            btn.innerHTML = '<i class="fas fa-check" style="font-size:10px;"></i> Going';
            showToast('RSVP confirmed! See you there 🎉', 'success');
        } else {
            btn.classList.remove('going');
            btn.innerHTML = 'RSVP';
            showToast('RSVP cancelled.', 'warn');
        }

        const goingEl = btn.closest('.event-info')
                           ?.querySelector('.event-meta-row span:last-child');
        if (goingEl) goingEl.innerHTML = `<i class="fas fa-users"></i> ${ev.going} going`;

    } catch (err) {
        console.error('toggleRSVP error:', err);
        showToast('Network error.', 'warn');
    } finally {
        btn.disabled = false;
    }
}

/* ══════════════════════════════════════════════════════════
   LEAVE CLUB FLOW
══════════════════════════════════════════════════════════ */
function promptLeave(index) {
    currentClubIndex = (index !== undefined) ? index : currentClubIndex;
    const name = allClubs[currentClubIndex]?.name || 'this club';
    document.getElementById('leaveMsg').textContent =
        `Are you sure you want to leave "${name}"? You'll lose access to club features and will need to reapply to rejoin.`;
    closeModal('detailOverlay');
    setTimeout(() => openModal('leaveOverlay'), 180);
}

async function confirmLeave() {
    if (currentClubIndex === null) return;

    const club    = allClubs[currentClubIndex];
    const club_id = club?.club_id;
    const name    = club?.name || 'the club';

    if (!club_id) { showToast('Invalid club.', 'warn'); return; }

    try {
        const data = await postAction({ action: 'leave', club_id });

        if (!data.success) {
            showToast(data.message || 'Could not leave club.', 'warn');
            return;
        }

        allClubs.splice(currentClubIndex, 1);
        currentClubIndex = null;
        closeModal('leaveOverlay');
        renderClubs(allClubs);
        showToast(`You have left ${name}.`, 'warn');

    } catch (err) {
        console.error('confirmLeave error:', err);
        showToast('Network error.', 'warn');
    }
}

function closeLeaveOverlay(e) {
    if (e.target === document.getElementById('leaveOverlay')) closeModal('leaveOverlay');
}

/* ══════════════════════════════════════════════════════════
   MODAL HELPERS
══════════════════════════════════════════════════════════ */
function openModal(id) {
    document.getElementById(id).classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('modal-open');
    document.body.style.overflow = '';
}

/* ══════════════════════════════════════════════════════════
   NOTIFICATIONS
══════════════════════════════════════════════════════════ */
function initNotifications() {
    const btn      = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');
    const badge    = document.getElementById('notifBadge');
    const markAll  = document.getElementById('markAllBtn');

    if (!btn || !dropdown) return;

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('open'); if (dropdown.classList.contains('open')) loadNotifs();
        btn.classList.toggle('active');
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== btn) {
            dropdown.classList.remove('open');
            btn.classList.remove('active');
        }
    });

    if (markAll) {
        markAll.addEventListener('click', function () {
            document.querySelectorAll('.notif-item.unread').forEach(n => n.classList.remove('unread'));
            if (badge) badge.classList.add('hidden');
            dropdown.classList.remove('open');
            btn.classList.remove('active');
            showToast('All notifications marked as read.', 'success');
        });
    }
}

function initEscKey() {
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        ['detailOverlay', 'leaveOverlay', 'eventsOverlay'].forEach(closeModal);
        const dropdown = document.getElementById('notifDropdown');
        if (dropdown) {
            dropdown.classList.remove('open');
            document.getElementById('notifBtn')?.classList.remove('active');
        }
    });
}

/* ══════════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════════ */
function showToast(msg, type = 'info') {
    const t = document.getElementById('crudToast');
    if (!t) return;
    const icons = { success: 'fa-circle-check', info: 'fa-circle-info', warn: 'fa-triangle-exclamation' };
    t.className = `crud-toast crud-toast-${type}`;
    t.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i> ${msg}`;
    t.classList.add('crud-toast-show');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('crud-toast-show'), 3400);
}

/* ══════════════════════════════════════════════════════════

/* ══ NOTIF: load & mark read ══ */
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
            <span class="notif-text"><strong>${n.title}</strong></span>
            <span class="notif-text" style="font-weight:400;margin-top:2px;">${n.message || ''}</span>
            ${n.created_fmt ? `<span class="notif-time">${n.created_fmt}</span>` : ''}
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
