/* ============================================================
   UNIFY — officer_explore.js
   Explore Clubs page logic
============================================================ */

(function () {
  'use strict';

  /* ── Refs ── */
  const grid      = document.getElementById('expGrid');
  const emptyEl   = document.getElementById('expEmpty');
  const countEl   = document.getElementById('expCount');
  const statEl    = document.getElementById('expStatClubs');
  const searchEl  = document.getElementById('exploreSearch');

  /* ── State ── */
  let currentCat  = 'all';
  let currentView = 'grid';
  let currentQ    = '';

  /* Category → icon map (mirrors PHP) */
  const CAT_ICONS = {
    Tech:     'fa-microchip',
    Arts:     'fa-palette',
    Sports:   'fa-trophy',
    Academic: 'fa-book-open',
    Science:  'fa-flask',
    Advocacy: 'fa-fist-raised',
    Business: 'fa-briefcase',
  };

  /* ── Topbar date ── */
  (function setDate() {
    const el = document.getElementById('topbarDate');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleDateString('en-US', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
  })();

  /* ═══════════════════════════════════════════════════════
     RENDER
  ═══════════════════════════════════════════════════════ */
  function render() {
    const clubs = filtered();

    /* count label */
    const label = clubs.length === 1 ? '1 club' : `${clubs.length} clubs`;
    if (countEl) countEl.textContent = label;

    if (clubs.length === 0) {
      grid.innerHTML = '';
      emptyEl.style.display = 'block';
      return;
    }
    emptyEl.style.display = 'none';

    grid.innerHTML = clubs.map((c, i) => cardHTML(c, i)).join('');
  }

  function filtered() {
    const q = currentQ.toLowerCase().trim();
    return (window.EX.clubs || []).filter(c => {
      const catOk  = currentCat === 'all' || c.category === currentCat;
      const termOk = !q
        || (c.name      || '').toLowerCase().includes(q)
        || (c.acronym   || '').toLowerCase().includes(q)
        || (c.category  || '').toLowerCase().includes(q);
      return catOk && termOk;
    });
  }

  function cardHTML(c, i) {
    const initial = (c.name || '?').charAt(0).toUpperCase();
    const catKey  = (c.category || '').toLowerCase();
    const icon    = CAT_ICONS[c.category] || 'fa-circle';
    const delay   = Math.min(i * 30, 300);

    const logoHTML = c.logo_path
      ? `<img src="${esc(c.logo_path)}" alt="${esc(c.name)}"
              onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
         <span class="exp-card-logo-fallback" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;">${initial}</span>`
      : initial;

    return `
    <div class="exp-card" onclick="openClub(${c.id})" style="animation-delay:${delay}ms">
      <div class="exp-card-top">
        <div class="exp-card-logo">${logoHTML}</div>
        <div class="exp-card-info">
          <div class="exp-card-name" title="${esc(c.name)}">${esc(c.name)}</div>
          ${c.acronym ? `<div class="exp-card-acronym">${esc(c.acronym)}</div>` : ''}
        </div>
        <span class="exp-cat-badge cat-${catKey}">
          <i class="fas ${icon}"></i> ${esc(c.category || 'General')}
        </span>
      </div>
      ${c.description
        ? `<p class="exp-card-desc">${esc(c.description)}</p>`
        : ''}
      <div class="exp-card-meta">
        <div class="exp-card-members">
          <i class="fas fa-users"></i>
          ${c.member_count ?? 0} member${c.member_count !== 1 ? 's' : ''}
        </div>
        <button class="exp-view-btn-inline" onclick="event.stopPropagation();openClub(${c.id})">
          View <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>`;
  }

  /* ── Helpers ── */
  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  /* ═══════════════════════════════════════════════════════
     PUBLIC API (called from inline HTML)
  ═══════════════════════════════════════════════════════ */

  /* Filter by category pill */
  window.setCat = function (btn, cat) {
    currentCat = cat;
    document.querySelectorAll('.exp-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    render();
  };

  /* Toggle grid/list view */
  window.setView = function (view, btn) {
    currentView = view;
    document.querySelectorAll('.exp-view-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (view === 'list') {
      grid.classList.add('list-view');
    } else {
      grid.classList.remove('list-view');
    }
  };

  /* Search input */
  window.filterClubs = function () {
    currentQ = searchEl ? searchEl.value : '';
    render();
  };

  /* ═══════════════════════════════════════════════════════
     MODAL — Club Detail
  ═══════════════════════════════════════════════════════ */
  let activeClubId = null;

  window.openClub = function (id) {
    activeClubId   = id;
    officersLoaded = false;
    eventsLoaded   = false;
    const c = (window.EX.clubs || []).find(x => x.id === id);
    if (!c) return;

    /* logo */
    const logoImg      = document.getElementById('mdLogo');
    const logoFallback = document.getElementById('mdLogoFallback');
    if (c.logo_path) {
      logoImg.src = c.logo_path;
      logoImg.style.display = 'block';
      logoFallback.style.display = 'none';
    } else {
      logoImg.style.display = 'none';
      logoFallback.style.display = 'flex';
    }

    /* header */
    document.getElementById('mdName').textContent    = c.name    || '—';
    document.getElementById('mdAcronym').textContent = c.acronym || '';

    /* stats */
    document.getElementById('mdMembers').textContent  = c.member_count  ?? '—';
    document.getElementById('mdCategory').textContent = c.category      || '—';
    document.getElementById('mdFounded').textContent  = c.founded       || '—';
    document.getElementById('mdRoom').textContent     = c.room          || '—';

    /* about tab */
    document.getElementById('mdDesc').textContent = c.description || 'No description available.';

    /* collab button */
    const collabBtn = document.getElementById('mdCollabBtn');
    if (collabBtn) {
      const isMyClub = (c.id === window.EX.myClubId);
      collabBtn.style.display = isMyClub ? 'none' : 'flex';
    }

    /* reset tabs */
    resetTabs();

    /* clear officer/event lists so they reload */
    document.getElementById('mdOfficersList').innerHTML =
      `<div class="exp-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>`;
    document.getElementById('mdEventsList').innerHTML   =
      `<div class="exp-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>`;

    openModal('clubDetailModal');
  };

  /* Tab switcher */
  window.switchTab = function (btn, panelId) {
    document.querySelectorAll('.exp-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.exp-tab-panel').forEach(p => p.style.display = 'none');
    btn.classList.add('active');
    document.getElementById(panelId).style.display = 'block';

    /* lazy-load officers / events */
    if (panelId === 'tabOfficers') loadOfficers();
    if (panelId === 'tabEvents')   loadEvents();
  };

  function resetTabs() {
    document.querySelectorAll('.exp-tab').forEach((t, i) => {
      t.classList.toggle('active', i === 0);
    });
    document.querySelectorAll('.exp-tab-panel').forEach((p, i) => {
      p.style.display = i === 0 ? 'block' : 'none';
    });
  }

  /* ── AJAX helpers ── */

  /* Track which tabs have already loaded so we don't re-fetch */
  let officersLoaded = false;
  let eventsLoaded   = false;

  function loadOfficers() {
    if (officersLoaded) return;
    const el = document.getElementById('mdOfficersList');
    fetch(`index.php?page=officer_explore&ajax=club_officers&club_id=${activeClubId}`)
      .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(data => {
        officersLoaded = true;
        if (!Array.isArray(data) || !data.length) {
          el.innerHTML = `<div class="exp-no-data"><i class="fas fa-user-slash"></i>No officers listed.</div>`;
          return;
        }
        el.innerHTML = data.map(o => {
          /* controller returns { name, role } */
          const name  = o.name || 'Unknown';
          const init  = name.charAt(0).toUpperCase();
          const role  = (o.role || '').toLowerCase().replace(/\s+/g, '-');
          const label = ucFirst(o.role || '');
          return `
          <div class="exp-officer-row">
            <div class="exp-officer-avatar">${init}</div>
            <div>
              <div class="exp-officer-name">${esc(name)}</div>
              <div class="exp-officer-role">${label}</div>
            </div>
            <span class="exp-officer-badge ${role}">${label}</span>
          </div>`;
        }).join('');
      })
      .catch(() => {
        el.innerHTML = `<div class="exp-no-data"><i class="fas fa-triangle-exclamation"></i> Could not load officers.</div>`;
      });
  }

  function loadEvents() {
    if (eventsLoaded) return;
    const el = document.getElementById('mdEventsList');
    fetch(`index.php?page=officer_explore&ajax=club_events&club_id=${activeClubId}`)
      .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(data => {
        eventsLoaded = true;
        if (!Array.isArray(data) || !data.length) {
          el.innerHTML = `<div class="exp-no-data"><i class="fas fa-calendar-xmark"></i>No upcoming events.</div>`;
          return;
        }
        el.innerHTML = data.map(ev => {
          /* controller returns { name, event_date, start_time, location } */
          const d   = new Date(ev.event_date);
          const day = isNaN(d) ? '—' : d.getDate();
          const mon = isNaN(d) ? '' : d.toLocaleString('en-US', { month: 'short' }).toUpperCase();
          return `
          <div class="exp-event-row">
            <div class="exp-event-date-block">
              <div class="exp-event-day">${day}</div>
              <div class="exp-event-mon">${mon}</div>
            </div>
            <div class="exp-event-divider"></div>
            <div>
              <div class="exp-event-name">${esc(ev.name)}</div>
              <div class="exp-event-meta">
                ${ev.location   ? `<i class="fas fa-location-dot"></i> ${esc(ev.location)}` : ''}
                ${ev.start_time ? `&nbsp;·&nbsp;<i class="fas fa-clock"></i> ${esc(ev.start_time)}` : ''}
              </div>
            </div>
          </div>`;
        }).join('');
      })
      .catch(() => {
        el.innerHTML = `<div class="exp-no-data"><i class="fas fa-triangle-exclamation"></i> Could not load events.</div>`;
      });
  }

  /* ═══════════════════════════════════════════════════════
     COLLAB MODAL
  ═══════════════════════════════════════════════════════ */
  window.openCollab = function () {
    const c = (window.EX.clubs || []).find(x => x.id === activeClubId);
    if (!c) return;
    const inp = document.getElementById('collabTargetClub');
    if (inp) inp.value = c.name || '';
    document.getElementById('collabEventName').value = '';
    document.getElementById('collabDate').value      = '';
    document.getElementById('collabMessage').value   = '';
    openModal('collabModal');
  };

  window.sendCollab = function () {
    const eventName = document.getElementById('collabEventName').value.trim();
    if (!eventName) {
      showToast('Please enter a proposed event name.', 'error');
      return;
    }

    const payload = {
      target_club_id: activeClubId,
      event_name:     eventName,
      proposed_date:  document.getElementById('collabDate').value,
      message:        document.getElementById('collabMessage').value.trim(),
    };

    fetch('index.php?page=officer_explore&ajax=send_collab', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          closeModal('collabModal');
          showToast('Collaboration proposal sent!', 'success');
        } else {
          showToast(data.message || 'Failed to send proposal.', 'error');
        }
      })
      .catch(() => showToast('Network error. Try again.', 'error'));
  };

  /* ═══════════════════════════════════════════════════════
     SHARED MODAL HELPERS
  ═══════════════════════════════════════════════════════ */
  window.openModal = function (id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'flex'; el.classList.add('active'); }
  };

  window.closeModal = function (id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'none'; el.classList.remove('active'); }
  };

  window.handleOverlayClick = function (e, id) {
    if (e.target === e.currentTarget) closeModal(id);
  };

  /* ═══════════════════════════════════════════════════════
     NOTIFICATIONS (reuse dashboard pattern)
  ═══════════════════════════════════════════════════════ */
  window.toggleNotifPanel = function () {
    let panel = document.querySelector('.notif-panel');
    if (panel) { panel.remove(); return; }

    panel = document.createElement('div');
    panel.className = 'notif-panel';
    panel.innerHTML = `
      <div class="notif-panel-header">
        <span>Notifications</span>
        <button class="notif-mark-all" onclick="markAllRead()">Mark all read</button>
      </div>
      <div class="notif-list" id="notifListInner">
        <div class="notif-empty"><i class="fas fa-bell-slash" style="margin-bottom:6px;display:block;font-size:18px;opacity:.3;"></i>No notifications</div>
      </div>`;

    document.querySelector('.topbar-actions').appendChild(panel);

    fetch('index.php?page=officer_explore&ajax=notifications')
      .then(r => r.json())
      .then(data => {
        if (!data.length) return;
        document.getElementById('notifListInner').innerHTML = data.map(n => `
          <div class="notif-item ${n.is_read ? '' : 'unread'}" onclick="markRead(${n.id})">
            <div class="notif-title">${esc(n.title)}</div>
            <div class="notif-msg">${esc(n.message)}</div>
            <div class="notif-time">${esc(n.created_at)}</div>
          </div>`).join('');
      })
      .catch(() => {});

    /* close on outside click */
    setTimeout(() => {
      document.addEventListener('click', function handler(e) {
        if (!panel.contains(e.target) && !e.target.closest('[onclick="toggleNotifPanel()"]')) {
          panel.remove();
          document.removeEventListener('click', handler);
        }
      });
    }, 0);
  };

  window.markRead = function (id) {
    fetch(`index.php?page=officer_explore&ajax=mark_notif_read&id=${id}`, { method: 'POST' });
  };

  window.markAllRead = function () {
    fetch('index.php?page=officer_explore&ajax=mark_all_notifs_read', { method: 'POST' }).then(() => {
      document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
      const badge = document.querySelector('.icon-btn .badge');
      if (badge) badge.remove();
    });
  };

  /* ═══════════════════════════════════════════════════════
     TOAST
  ═══════════════════════════════════════════════════════ */
  window.showToast = function (msg, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.className = `toast toast-${type} show`;
    const icon = type === 'success' ? 'fa-circle-check'
               : type === 'error'   ? 'fa-circle-xmark'
               : 'fa-circle-info';
    toast.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove('show'), 3200);
  };

  /* ── Utility ── */
  function ucFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  /* ═══════════════════════════════════════════════════════
     INIT
  ═══════════════════════════════════════════════════════ */
  render();

})();
