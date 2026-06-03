/* ============================================================
   UNIFY — Officer Events JS  |  officer_events.js
   Full: tabs, QR scanner, realtime attendance, modals, search
============================================================ */

const OE = (() => {
  const pageReload = () => window.location.reload();
  const DATA         = window.OE_DATA || { club_id:0, events:[], total_members:0, members:[] };
  let events         = DATA.events.slice();
  let members        = DATA.members || [];
  let activeEventId  = null;
  let activeEventName= '';
  let scanStream     = null;
  let scanStreamFs   = null;
  let scanLoop       = null;
  let scanLoopFs     = null;
  let pollTimer      = null;
  let ctxEventId     = null;
  let attData        = { present:[], absent:[] };
  let attTab         = 'all';

  // ── Assignee state ───────────────────────────────────────
  let assigneeList   = [];   // [{ user_id, first_name, last_name, profile_picture, role_label }]

  // ── Collaboration state
  let otherClubs           = DATA.other_clubs || [];
  let incomingCollabs      = DATA.incoming_collabs || [];
  let collabList           = [];
  let pendingCollabClubId  = null;
  let pendingCollabEventId = null;
  let activeCollabId       = null;
  let collabMemberList     = [];



  const PRESET_ROLES = [
    { icon: '🔧', label: 'Setup / Teardown' },
    { icon: '📋', label: 'Registration / Check-in' },
    { icon: '🚚', label: 'Logistics' },
    { icon: '🎙️', label: 'Emcee / Host' },
    { icon: '📸', label: 'Documentation / Photos' },
    { icon: '📝', label: 'Secretariat' },
    { icon: '🔒', label: 'Security / Ushering' },
    { icon: '🎨', label: 'Decorations' },
    { icon: '💡', label: 'Audio / Visual / Tech' },
    { icon: '🍽️', label: 'Food & Refreshments' },
  ];

  // ── Topbar date ──────────────────────────────────────────
  const dateEl = document.getElementById('topbarDate');
  if (dateEl) {
    const d = new Date();
    dateEl.textContent = d.toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
  }

  // ── Helpers ───────────────────────────────────────────────
  function esc(s){ return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

  function toast(msg, type='') {
    const el = document.getElementById('toast');
    if (!el) return;
    el.className = 'toast' + (type ? ' '+type : '');
    el.innerHTML = (type==='error'?'<i class="fas fa-circle-xmark"></i> ':type==='warn'?'<i class="fas fa-triangle-exclamation"></i> ':'<i class="fas fa-circle-check"></i> ') + esc(msg);
    el.classList.add('show');
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.remove('show'), 3200);
  }

  function ajax(action, data={}) {
    const body = new URLSearchParams({ action, ...data });
    return fetch('index.php?page=officer_events', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: body.toString()
    }).then(r => r.json());
  }

  // ── Tab switching ─────────────────────────────────────────
  function switchTab(name) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab===name));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.toggle('active', p.id==='tab-'+name));
  }

  // ── Event search / filter ─────────────────────────────────
  function filterEvents(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.event-card-row').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
  }

  // ── Add modal ─────────────────────────────────────────────
 function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Event';
    document.getElementById('modalEventId').value     = '';
    document.getElementById('fName').value            = '';
    document.getElementById('fDate').value            = '';
    document.getElementById('fLocation').value        = '';
    document.getElementById('fStart').value           = '';
    document.getElementById('fEnd').value             = '';
    document.getElementById('fDesc').value            = '';
    document.getElementById('fStatus').value          = 'pending_approval';
    if (document.getElementById('fMandatory')) document.getElementById('fMandatory').checked = false;
    if (document.getElementById('addOnlyHideRow')) document.getElementById('addOnlyHideRow').style.display = '';
    assigneeList = [];
    renderAssigneeChips();
    document.getElementById('assigneeSearch').value   = '';
    document.getElementById('assigneeDropdown').classList.remove('open');
    collabList = [];
    renderCollabChips();
    initCollabClubDropdown(null);
    openModal('eventModal');
  }

function openEditModal(dbId) {
    const e = events.find(x => x.db_id === dbId);
    if (!e) return;
    document.getElementById('modalTitle').textContent  = 'Edit Event';
    document.getElementById('modalEventId').value      = e.db_id;
    document.getElementById('fName').value             = e.title || '';
    document.getElementById('fDate').value             = e.event_date || '';
    document.getElementById('fLocation').value         = e.venue || '';
    document.getElementById('fStart').value            = e.start_time || '';
    document.getElementById('fEnd').value              = e.end_time || '';
    document.getElementById('fDesc').value             = e.description || '';
    document.getElementById('fStatus').value           = e.status || 'upcoming';
    if (document.getElementById('fStatus')) document.getElementById('fStatus').disabled = false;
    if (document.getElementById('fMandatory')) document.getElementById('fMandatory').checked = !!e.is_mandatory;
    // Load assignees from event data
    assigneeList = (e.assignees || []).map(a => ({
      user_id:         a.user_id,
      first_name:      a.first_name,
      last_name:       a.last_name,
      profile_picture: a.profile_picture || null,
      role_label:      a.role_label || '',
    }));
    renderAssigneeChips();
    document.getElementById('assigneeSearch').value   = '';
    document.getElementById('assigneeDropdown').classList.remove('open');
    // Show status + mandatory for editing
    if (document.getElementById('addOnlyHideRow')) document.getElementById('addOnlyHideRow').style.display = '';
    // Load existing collabs for this event
    selectedCollabClubId = 0;
    selectedCollabClubName = '';
    collabList = [];
    renderCollabChips();
    initCollabClubDropdown(e.db_id);
    openModal('eventModal');
    loadEventCollabs(e.db_id);
}

  function saveEvent() {
    const id       = document.getElementById('modalEventId').value;
    const name     = document.getElementById('fName').value.trim();
    const date     = document.getElementById('fDate').value;
    const location = document.getElementById('fLocation').value.trim();
    const start    = document.getElementById('fStart').value;
    const end      = document.getElementById('fEnd').value;
    const desc     = document.getElementById('fDesc').value.trim();
    const status   = document.getElementById('fStatus').value;
    const mandatory= document.getElementById('fMandatory').checked ? 1 : 0;

    if (!name || !date) { toast('Event name and date are required.','error'); return; }

    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    document.getElementById('btnSaveText').textContent = 'Saving…';

    ajax(id ? 'evt_update' : 'evt_create', {
      ...(id ? {event_id:id} : {}),
      name, event_date:date, location, start_time:start, end_time:end,
      description:desc, status, is_mandatory:mandatory
    }).then(res => {
      if (!res.success) {
        btn.disabled = false;
        document.getElementById('btnSaveText').textContent = 'Save Event';
        toast(res.message || 'Error saving event.','error');
        return;
      }
      const eventId = id || res.event_id;

      // Save assignees
      const assigneeSave = eventId && assigneeList.length > 0
        ? ajax('assignees_save', {
            event_id:  eventId,
            assignees: JSON.stringify(assigneeList.map(a => ({ user_id: a.user_id, role_label: a.role_label })))
          })
        : eventId
          ? ajax('assignees_save', { event_id: eventId, assignees: '[]' })
          : Promise.resolve();

      // Send collab request if a club was selected but not yet sent
      const collabSave = (eventId && selectedCollabClubId)
        ? ajax('collab_request', {
            event_id:    eventId,
            target_club: selectedCollabClubId,
            message:     document.getElementById('collabInlineMsg')?.value.trim() || '',
          })
        : Promise.resolve();

      Promise.all([assigneeSave, collabSave]).finally(() => {
        btn.disabled = false;
        document.getElementById('btnSaveText').textContent = 'Save Event';
        toast(id ? 'Event updated!' : 'Event created!');
        closeModal('eventModal');
        setTimeout(() => pageReload(), 700);
      });
    }).catch(() => { btn.disabled=false; toast('Network error.','error'); });
  }

  // ── Modal helpers ─────────────────────────────────────────
  function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
  function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

  // Close on overlay click
  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
  });

  // ── Context menu ──────────────────────────────────────────
  function openCtx(ev, dbId) {
    ev.stopPropagation();
    ctxEventId = dbId;
    const menu     = document.getElementById('ctxMenu');
    const backdrop = document.getElementById('ctxBackdrop');
    menu.classList.add('open');
    backdrop.classList.add('open');
    const x = Math.min(ev.clientX, window.innerWidth  - 200);
    const y = Math.min(ev.clientY + 4, window.innerHeight - 200);
    menu.style.top  = y + 'px';
    menu.style.left = x + 'px';
  }
  function closeCtx() {
    document.getElementById('ctxMenu')?.classList.remove('open');
    document.getElementById('ctxBackdrop')?.classList.remove('open');
    ctxEventId = null;
  }
  function ctxEdit()            { const id=ctxEventId; closeCtx(); if(id) openEditModal(id); }
  function ctxScanAtt()         { const id=ctxEventId; closeCtx(); const e=events.find(x=>x.db_id===id); if(e) openScanner(id,e.title); }
  function ctxToggleMandatory() {
    const id=ctxEventId; closeCtx();
    if(!id) return;
    ajax('evt_toggle_mandatory',{event_id:id}).then(res => {
      if(res.success){ toast('Mandatory flag toggled.'); setTimeout(()=>pageReload(),600); }
      else toast(res.message||'Error.','error');
    });
  }
  function ctxDelete() {
    const id = ctxEventId; closeCtx();
    if (!id) return;
    const ev = events.find(x => x.db_id === id);
    const name = ev ? ev.title : 'this event';
    document.getElementById('delEventName').textContent = '"' + name + '"';
    document.getElementById('delConfirmBtn').onclick = () => {
      closeDeleteModal();
      ajax('evt_delete', { event_id: id }).then(res => {
        if (res.success) { toast('Event deleted.'); setTimeout(() => pageReload(), 600); }
        else toast(res.message || 'Error.', 'error');
      });
    };
    document.getElementById('deleteConfirmModal').classList.add('open');
  }

  function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.remove('open');
  }

  

  // ── Attendance view (completed events) ────────────────────
  function openAttView(dbId, name) {
    document.getElementById('attViewTitle').textContent = 'Attendance — ' + name;
    openModal('attViewModal');
    document.getElementById('attViewBody').innerHTML = '<div class="slc-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';

    ajax('att_list',{event_id:dbId}).then(res => {
      if(!res.success){
        document.getElementById('attViewBody').innerHTML='<div class="slc-loading">Failed to load.</div>';
        return;
      }
      const all = [
        ...(res.present||[]).map(m=>({...m,status:'present'})),
        ...(res.absent||[]).map(m=>({...m,status:'absent'}))
      ].sort((a,b)=>a.last_name.localeCompare(b.last_name));

      const presentCount = res.present?.length || 0;
      const absentCount  = res.absent?.length  || 0;

      const rows = all.map(m => {
        const init = (m.first_name.charAt(0)+m.last_name.charAt(0)).toUpperCase();
        const icon = m.status === 'present'
          ? '<i class="fas fa-circle-check" style="color:var(--green-accent);font-size:14px;"></i>'
          : '<i class="fas fa-circle-xmark" style="color:var(--red-accent);font-size:14px;"></i>';
        return `<div class="att-row">
          <div class="att-avatar">${esc(init)}</div>
          <div class="att-info">
            <div class="att-name">${esc(m.first_name+' '+m.last_name)}</div>
            <div class="att-lrn">${esc(m.student_id||'—')}</div>
          </div>
          <div class="att-status ${m.status}" style="display:flex;align-items:center;gap:5px;">${icon} ${m.status}</div>
        </div>`;
      }).join('');

      document.getElementById('attViewBody').innerHTML =
        `<div style="display:flex;gap:12px;padding:14px 18px 0;margin-bottom:8px;">
           <div style="flex:1;text-align:center;background:#f0f9f2;border-radius:10px;padding:10px;">
             <div style="font-size:22px;font-weight:800;color:var(--green-accent);">${presentCount}</div>
             <div style="font-size:11px;color:var(--text-light);">Present</div>
           </div>
           <div style="flex:1;text-align:center;background:var(--red-bg);border-radius:10px;padding:10px;">
             <div style="font-size:22px;font-weight:800;color:var(--red-accent);">${absentCount}</div>
             <div style="font-size:11px;color:var(--text-light);">Absent</div>
           </div>
           <div style="flex:1;text-align:center;background:#f5f9f6;border-radius:10px;padding:10px;">
             <div style="font-size:22px;font-weight:800;color:var(--text-dark);">${all.length}</div>
             <div style="font-size:11px;color:var(--text-light);">Total</div>
           </div>
         </div>
         <div style="max-height:50vh;overflow-y:auto;">${rows||'<div class="slc-loading">No members.</div>'}</div>`;
    });
  }

  // ── Open attendance scanner ───────────────────────────────
  function openScanner(dbId, name) {
    activeEventId   = dbId;
    activeEventName = name;

    const sec   = document.getElementById('scannerSection');
    const label = document.getElementById('scannerLabel');
    label.textContent = 'ATTENDANCE SCANNER — ' + name.toUpperCase();
    sec.style.display = 'block';
    document.getElementById('scrollArrow').style.display = 'flex';

    // Reset stats & list
    document.getElementById('statPresent').textContent = '0';
    document.getElementById('statAbsent').textContent  = '0';
    document.getElementById('statTotal').textContent   = '0';
    document.getElementById('attListBody').innerHTML   = '<div class="slc-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';
    document.getElementById('fsTitle').textContent     = name;

    refreshAttList(dbId);
    clearInterval(pollTimer);
    pollTimer = setInterval(() => refreshAttList(dbId), 6000);

    setTimeout(() => scrollToScanner(), 250);
  }

  function closeScanner() {
    stopCamera();
    stopCameraFs();
    clearInterval(pollTimer);
    document.getElementById('scannerSection').style.display = 'none';
    document.getElementById('scrollArrow').style.display    = 'none';
    activeEventId = null;
  }

  function scrollToScanner() {
    document.getElementById('scannerSection')?.scrollIntoView({ behavior:'smooth', block:'start' });
  }

  // ── Camera ────────────────────────────────────────────────
  function toggleCamera() {
    if (scanStream) { stopCamera(); return; }
    navigator.mediaDevices.getUserMedia({ video:{ facingMode:'environment' } })
      .then(stream => {
        scanStream = stream;
        const video = document.getElementById('scanVideo');
        video.srcObject = stream;
        video.style.display = 'block';
        document.getElementById('sqcPlaceholder').style.display = 'none';
        video.play();
        const btn = document.getElementById('btnCamera');
        btn.innerHTML = '<i class="fas fa-camera-slash"></i> Close camera';
        btn.classList.add('live');
        startScanLoop();
      })
      .catch(err => {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          toast('Camera not available. Make sure the site is running on HTTPS.', 'error');
        } else {
          toast('Camera error: ' + err.name + ' — ' + err.message, 'error');
        }
      });
  }

  function stopCamera() {
    if (!scanStream) return;
    scanStream.getTracks().forEach(t => t.stop());
    scanStream = null;
    clearInterval(scanLoop);
    const video = document.getElementById('scanVideo');
    if (video) video.style.display = 'none';
    const placeholder = document.getElementById('sqcPlaceholder');
    if (placeholder) placeholder.style.display = 'flex';
    const btn = document.getElementById('btnCamera');
    if (btn) { btn.innerHTML = '<i class="fas fa-camera"></i> Open camera'; btn.classList.remove('live'); }
  }

  function startScanLoop() {
    const video  = document.getElementById('scanVideo');
    const canvas = document.getElementById('scanCanvas');
    const ctx    = canvas.getContext('2d');
    clearInterval(scanLoop);
    scanLoop = setInterval(() => {
      if (!video.videoWidth) return;
      canvas.width  = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0);
      const img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const code = typeof jsQR !== 'undefined' ? jsQR(img.data, img.width, img.height) : null;
      if (code?.data) handleScan(code.data.trim());
    }, 400);
  }

  // ── Fullscreen scanner ────────────────────────────────────
  function openFullscreen() {
    const overlay = document.getElementById('fsOverlay');
    overlay.classList.add('open');
    // Sync stats
    syncFsStats();

    navigator.mediaDevices.getUserMedia({ video:{ facingMode:'environment' } })
      .then(stream => {
        scanStreamFs = stream;
        const video = document.getElementById('scanVideoFs');
        video.srcObject = stream;
        const ph = document.getElementById('fsPlaceholder');
        if (ph) ph.style.display = 'none';
        video.play();
        startScanLoopFs();
      }).catch(() => {});
  }

  function closeFullscreen() {
    stopCameraFs();
    document.getElementById('fsOverlay').classList.remove('open');
  }

  function stopCameraFs() {
    if (!scanStreamFs) return;
    scanStreamFs.getTracks().forEach(t => t.stop());
    scanStreamFs = null;
    clearInterval(scanLoopFs);
  }

  function startScanLoopFs() {
    const video  = document.getElementById('scanVideoFs');
    const canvas = document.createElement('canvas');
    const ctx    = canvas.getContext('2d');
    clearInterval(scanLoopFs);
    scanLoopFs = setInterval(() => {
      if (!video.videoWidth) return;
      canvas.width  = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0);
      const img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const code = typeof jsQR !== 'undefined' ? jsQR(img.data, img.width, img.height) : null;
      if (code?.data) handleScan(code.data.trim());
    }, 400);
  }

  // ── Scan logic ────────────────────────────────────────────
  let lastScanned = ''; let lastScannedAt = 0;

  function handleScan(lrn) {
    const now = Date.now();
    if (lrn === lastScanned && now - lastScannedAt < 3500) return;
    lastScanned   = lrn;
    lastScannedAt = now;
    if (!activeEventId) { toast('No active event selected.','warn'); return; }

    ajax('att_scan', { event_id:activeEventId, lrn }).then(res => {
      if (res.success) {
        toast('✓ ' + (res.name||lrn) + ' marked present');
        refreshAttList(activeEventId);
      } else {
        toast(res.message||'Scan failed','warn');
      }
    });
  }

  function manualScan() {
    const inp = document.getElementById('manualLrn');
    const lrn = inp.value.trim();
    if (!lrn) return;
    inp.value = '';
    handleScan(lrn);
  }

  // ── Refresh attendance list ───────────────────────────────
  function refreshAttList(dbId) {
    ajax('att_list',{event_id:dbId}).then(res => {
      if (!res.success) return;
      attData = { present: res.present||[], absent: res.absent||[] };
      renderAttList();
      syncFsStats();
    });
  }

  function setAttTab(tab, btn) {
    attTab = tab;
    document.querySelectorAll('.slc-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderAttList();
  }

  function renderAttList() {
    const { present, absent } = attData;
    const total = present.length + absent.length;

    document.getElementById('statPresent').textContent = present.length;
    document.getElementById('statAbsent').textContent  = absent.length;
    document.getElementById('statTotal').textContent   = total;

    let source;
    if (attTab === 'present')     source = present.map(m=>({...m,status:'present'}));
    else if (attTab === 'absent') source = absent.map(m=>({...m,status:'absent'}));
    else source = [
      ...present.map(m=>({...m,status:'present'})),
      ...absent.map(m=>({...m,status:'absent'}))
    ].sort((a,b)=>a.last_name.localeCompare(b.last_name));

    if (!source.length) {
      document.getElementById('attListBody').innerHTML =
        `<div class="slc-loading">${
          attTab==='present'?'No members present yet.':
          attTab==='absent'?'Everyone is present!':'No members found.'
        }</div>`;
      return;
    }

    document.getElementById('attListBody').innerHTML = source.map(m => {
      const init = (m.first_name.charAt(0) + m.last_name.charAt(0)).toUpperCase();
      const icon = m.status==='present'
        ? '<i class="fas fa-circle-check" style="color:var(--green-accent);font-size:13px;"></i>'
        : '<i class="fas fa-circle-xmark" style="color:var(--red-accent);font-size:13px;"></i>';
      return `<div class="att-row">
        <div class="att-avatar">${esc(init)}</div>
        <div class="att-info">
          <div class="att-name">${esc(m.first_name+' '+m.last_name)}</div>
          <div class="att-lrn">${esc(m.student_id||'—')}</div>
        </div>
        <div class="att-status ${m.status}" style="display:flex;align-items:center;gap:5px;">${icon} ${m.status}</div>
      </div>`;
    }).join('');
  }

  function syncFsStats() {
    const p = document.getElementById('statPresent')?.textContent || '0';
    const a = document.getElementById('statAbsent')?.textContent  || '0';
    const t = document.getElementById('statTotal')?.textContent   || '0';
    const fp = document.getElementById('fsPresent');
    const fa = document.getElementById('fsAbsent');
    const ft = document.getElementById('fsTotal');
    if (fp) fp.textContent = p;
    if (fa) fa.textContent = a;
    if (ft) ft.textContent = t;
  }

  // ── Member search (Members tab) ───────────────────────────
  function filterMembers(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.mem-row').forEach(row => {
      row.style.display = (!q || row.dataset.name.includes(q)) ? '' : 'none';
    });
  }

  // ── Notifications ─────────────────────────────────────────
  function toggleNotif() {
    const panel = document.getElementById('notifPanel');
    const show  = panel.style.display === 'none';
    panel.style.display = show ? 'block' : 'none';
    if (show) loadNotifs();
  }

  function loadNotifs() {
    fetch('index.php?page=officer_events&action=notif_list')
      .then(r => r.json()).then(res => {
        const list = document.getElementById('notifList');
        if (!res.success || !res.data?.length) {
          list.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash" style="opacity:.4;margin-right:6px;"></i>No notifications</div>';
          return;
        }
        list.innerHTML = res.data.map(n => {
          const hasLink = n.link && n.link.trim() !== '';
          const unread  = n.is_read == 0;
          return `
          <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:12.5px;cursor:${hasLink?'pointer':'default'};background:${unread?'var(--surface-2,#f8f9ff)':'inherit'}"
               onclick="markNotifRead(${n.id}, ${JSON.stringify(n.link||'')})">
            <div style="font-weight:700;color:var(--text-dark);">${unread?'<span style=\"display:inline-block;width:7px;height:7px;background:var(--primary,#4f6ef7);border-radius:50%;margin-right:5px;\"></span>':''}${esc(n.title)}</div>
            <div style="color:var(--text-light);margin-top:2px;">${esc(n.message)}</div>
            
          </div>`}).join('');
        const badge = document.getElementById('notifBadge');
        if (badge) { badge.style.display = 'none'; }
      }).catch(() => {
        document.getElementById('notifList').innerHTML = '<div class="notif-empty">Could not load notifications.</div>';
      });
  }

  function markNotifRead(id, link) {
    fetch(`index.php?page=officer_events&action=notif_read&id=${id}`, { method: 'POST' })
      .then(() => {
        if (link && link.trim() !== '') {
          window.location.href = link;
        } else {
          loadNotifs();
        }
      });
  }

  function markAllRead() {
    fetch('index.php?page=officer_events&action=notif_read_all',{method:'POST'})
      .then(() => {
        const badge = document.getElementById('notifBadge');
        if (badge) badge.style.display = 'none';
        loadNotifs();
      });
  }

  // ── Keyboard shortcuts ────────────────────────────────────
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeModal('eventModal');
      closeModal('attViewModal');
      closeCtx();
      closeFullscreen();
      const np = document.getElementById('notifPanel');
      if (np) np.style.display = 'none';
    }
    // 'n' shortcut to open add modal
    if (e.key === 'n' && !e.ctrlKey && !e.metaKey && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
      openAddModal();
    }
  });

  // Close notif panel on outside click
  document.addEventListener('click', e => {
    const panel = document.getElementById('notifPanel');
    const btn   = document.getElementById('notifBtn');
    if (panel && !panel.contains(e.target) && btn && !btn.contains(e.target)) {
      panel.style.display = 'none';
    }
  });

  // Auto-refresh every 30s, but only if no modal/scanner is open
  setInterval(() => {
    const scannerOpen = document.getElementById('scannerSection')?.style.display !== 'none';
    const modalOpen   = document.querySelector('.modal-overlay.open');
    const fsOpen      = document.getElementById('fsOverlay')?.classList.contains('open');
    if (!scannerOpen && !modalOpen && !fsOpen) {
      location.reload();
    }
  }, 30000);


  // ── Assignee Functions ────────────────────────────────────

  function assigneeSearch(q) {
    const drop = document.getElementById('assigneeDropdown');
    q = q.trim().toLowerCase();
    if (!q) { drop.classList.remove('open'); drop.innerHTML = ''; return; }

    const alreadyIds = new Set(assigneeList.map(a => a.user_id));
    const filtered = members.filter(m => {
      if (alreadyIds.has(m.user_id)) return false;
      const full = (m.first_name + ' ' + m.last_name).toLowerCase();
      return full.includes(q);
    }).slice(0, 6);

    if (!filtered.length) {
      drop.innerHTML = `<div class="asn-drop-empty"><i class="fas fa-search"></i> No members found</div>`;
      drop.classList.add('open');
      return;
    }

    drop.innerHTML = filtered.map(m => {
      const initials = (m.first_name[0] + m.last_name[0]).toUpperCase();
      const avatarHtml = m.profile_picture
        ? `<img src="/assets/pictures/profile_pictures/${esc(m.profile_picture)}" class="asn-drop-avatar" />`
        : `<span class="asn-drop-initials">${initials}</span>`;
      return `<div class="asn-drop-item" onmousedown="event.preventDefault(); OE.assigneeAdd(${m.user_id})">
        <div class="asn-drop-avatar-wrap">${avatarHtml}</div>
        <div class="asn-drop-info">
          <span class="asn-drop-name">${esc(m.first_name)} ${esc(m.last_name)}</span>
          <span class="asn-drop-role">${esc(m.role)}</span>
        </div>
      </div>`;
    }).join('');
    drop.classList.add('open');
  }

  function assigneeAdd(userId) {
    const m = members.find(x => x.user_id === userId);
    if (!m) return;
    if (assigneeList.find(a => a.user_id === userId)) return;
    assigneeList.push({ ...m, role_label: '' });
    renderAssigneeChips();
    document.getElementById('assigneeSearch').value = '';
    document.getElementById('assigneeDropdown').classList.remove('open');
    document.getElementById('assigneeDropdown').innerHTML = '';
    // Open role picker for this new assignee
    setTimeout(() => openRolePicker(userId), 50);
  }

  function assigneeRemove(userId) {
    assigneeList = assigneeList.filter(a => a.user_id !== userId);
    renderAssigneeChips();
  }

  function renderAssigneeChips() {
    const container = document.getElementById('assigneeChips');
    const empty     = document.getElementById('assigneeEmpty');
    if (!assigneeList.length) {
      container.innerHTML = '';
      if (empty) { empty.style.display = ''; container.appendChild(empty); }
      return;
    }
    if (empty) empty.style.display = 'none';
    container.innerHTML = assigneeList.map(a => {
      const initials = ((a.first_name||'')[0] + (a.last_name||'')[0]).toUpperCase();
      const avatarHtml = a.profile_picture
        ? `<img src="/assets/pictures/profile_pictures/${esc(a.profile_picture)}" class="asn-chip-avatar" />`
        : `<span class="asn-chip-initials">${initials}</span>`;
      const roleDisplay = a.role_label || '<em style="opacity:.6">Set role…</em>';
      // Status badge for assignment response
      const statusMap = { accepted:'✅', declined:'❌', pending:'⏳' };
      const statusBadge = a.status ? `<span class="asn-status-badge asn-status-${a.status}" title="${a.status}">${statusMap[a.status]||''}</span>` : '';
      return `<div class="asn-chip asn-chip-${a.status||'pending'}" id="asn-chip-${a.user_id}">
        <div class="asn-chip-avatar-wrap">${avatarHtml}</div>
        <div class="asn-chip-body">
          <span class="asn-chip-name">${esc(a.first_name)} ${esc(a.last_name)} ${statusBadge}</span>
          <button class="asn-chip-role-btn" onclick="OE.openRolePicker(${a.user_id})" title="Set role">
            <i class="fas fa-tag"></i> ${roleDisplay}
          </button>
        </div>
        <button class="asn-chip-remove" onclick="OE.assigneeRemove(${a.user_id})" title="Remove">
          <i class="fas fa-xmark"></i>
        </button>
      </div>`;
    }).join('');
  }

  // ── Role picker popup ─────────────────────────────────────
  let rolePickerTargetId = null;

  function openRolePicker(userId) {
    rolePickerTargetId = userId;
    const a = assigneeList.find(x => x.user_id === userId);
    if (!a) return;

    // Remove any existing picker
    document.getElementById('rolePickerPopup')?.remove();

    const popup = document.createElement('div');
    popup.id = 'rolePickerPopup';
    popup.className = 'role-picker-popup';
    popup.innerHTML = `
      <div class="rpp-header">
        <span><i class="fas fa-tag"></i> Set Role for <strong>${esc(a.first_name)}</strong></span>
        <button onclick="OE.closeRolePicker()"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="rpp-presets">
        ${PRESET_ROLES.map(r =>
          `<button class="rpp-preset ${a.role_label === r.label ? 'active' : ''}" onclick="OE.applyRole('${r.label.replace(/'/g,"\\'")}')">
            ${r.icon} ${r.label}
          </button>`
        ).join('')}
      </div>
      <div class="rpp-custom">
        <input type="text" class="form-input" id="rppCustomInput"
               placeholder="Or type a custom role…"
               value="${esc(a.role_label)}"
               onkeydown="if(event.key==='Enter'){OE.applyRole(this.value);}" />
        <button class="btn-primary rpp-apply-btn" onclick="OE.applyRole(document.getElementById('rppCustomInput').value)">
          <i class="fas fa-check"></i> Apply
        </button>
      </div>`;

    // Position near the chip
    const chip = document.getElementById(`asn-chip-${userId}`);
    const modal = document.querySelector('#eventModal .modal-box');
    if (chip && modal) {
      modal.appendChild(popup);
      const chipRect  = chip.getBoundingClientRect();
      const modalRect = modal.getBoundingClientRect();
      popup.style.top  = (chipRect.bottom - modalRect.top + 6) + 'px';
      popup.style.left = (chipRect.left   - modalRect.left)    + 'px';
    } else {
      document.body.appendChild(popup);
    }

    popup.classList.add('open');
    document.getElementById('rppCustomInput')?.focus();
  }

  function applyRole(label) {
    label = label.trim();
    const a = assigneeList.find(x => x.user_id === rolePickerTargetId);
    if (a) a.role_label = label;
    renderAssigneeChips();
    closeRolePicker();
  }

  function closeRolePicker() {
    document.getElementById('rolePickerPopup')?.remove();
    rolePickerTargetId = null;
  }

  // Close assignee dropdown when clicking outside
  document.addEventListener('click', e => {
    const wrap = document.getElementById('assigneeSearch')?.closest('.assignee-search-wrap');
    if (wrap && !wrap.contains(e.target)) {
      document.getElementById('assigneeDropdown')?.classList.remove('open');
    }
    if (!e.target.closest('#rolePickerPopup') && !e.target.closest('.asn-chip-role-btn')) {
      closeRolePicker();
    }
  });


  // ══════════════════════════════════════════════════════════
  // COLLABORATION FUNCTIONS
  // ══════════════════════════════════════════════════════════

  let selectedCollabClubId   = 0;
  let selectedCollabClubName = '';
  let collabPickerOpen       = false;

  function initCollabClubDropdown() {
    selectedCollabClubId = 0; selectedCollabClubName = '';
    collabPickerOpen = false;
    const box = document.getElementById('collabSelectedClub');
    if (box) box.innerHTML = '';
    const inp = document.getElementById('collabSearchInput');
    if (inp) inp.value = '';
    const drop = document.getElementById('collabPickerDropdown');
    if (drop) drop.classList.remove('open');
    renderCollabClubList('');
  }

  function renderCollabClubList(q) {
    const list = document.getElementById('collabPickerList');
    if (!list) return;
    const clubs = (DATA.other_clubs || []).filter(c =>
      !q || c.name.toLowerCase().includes(q.toLowerCase())
    );
    if (!clubs.length) {
      list.innerHTML = `<div class="asn-drop-empty"><i class="fas fa-search"></i> ${q ? 'No clubs found' : 'No other clubs available'}</div>`;
      return;
    }
    list.innerHTML = clubs.map(c => {
      const safeName = esc(c.name);
      const item = document.createElement('div');
      item.className = 'asn-drop-item';
      item.innerHTML = `<span class="asn-drop-initials">${esc(c.name[0])}</span>
        <div class="asn-drop-info">
          <span class="asn-drop-name">${safeName}</span>
          <span class="asn-drop-role">Club</span>
        </div>`;
      item.addEventListener('mousedown', e => {
        e.preventDefault();
        selectCollabClub(c.id, c.name);
      });
      return item.outerHTML;
    }).join('');

    // Attach listeners after setting innerHTML
    list.querySelectorAll('.asn-drop-item').forEach((el, i) => {
      const c = clubs[i];
      el.addEventListener('mousedown', e => {
        e.preventDefault();
        selectCollabClub(c.id, c.name);
      });
    });
  }

  function collabSearch(q) {
    q = (q || '').trim();
    const drop = document.getElementById('collabPickerDropdown');
    if (!drop) return;
    drop.classList.add('open');
    collabPickerOpen = true;
    renderCollabClubList(q);
  }
  function toggleCollabPicker() {
    const drop  = document.getElementById('collabPickerDropdown');
    const arrow = document.getElementById('collabPickerArrow');
    if (!drop) return;
    collabPickerOpen = !collabPickerOpen;
    drop.classList.toggle('open', collabPickerOpen);
    if (arrow) arrow.style.transform = collabPickerOpen ? 'rotate(180deg)' : '';
  }

  function selectCollabClub(id, name) {
    selectedCollabClubId   = id;
    selectedCollabClubName = name;
    const drop  = document.getElementById('collabPickerDropdown');
    const arrow = document.getElementById('collabPickerArrow');
    const trigger = document.getElementById('collabPickerTrigger');
    if (drop)    drop.classList.remove('open');
    if (arrow)   arrow.style.transform = '';
    if (trigger) trigger.classList.remove('active');
    collabPickerOpen = false;
    // Clear search
    const inp = document.getElementById('collabSearchInput');
    if (inp) inp.value = '';
    // Render selected club chip with message box
    renderCollabSelectedClub();
  }

  function renderCollabSelectedClub() {
    const box = document.getElementById('collabSelectedClub');
    if (!box) return;
    if (!selectedCollabClubId) { box.innerHTML = ''; return; }
    box.innerHTML = `
      <div class="asn-chip" style="flex-direction:column;align-items:flex-start;gap:8px;padding:10px 12px;">
        <div style="display:flex;align-items:center;gap:10px;width:100%;">
          <span class="asn-chip-initials">${esc(selectedCollabClubName[0])}</span>
          <div class="asn-chip-body">
            <span class="asn-chip-name">${esc(selectedCollabClubName)}</span>
            <span style="font-size:11px;color:var(--text-light);">Club collaboration</span>
          </div>
          <button class="asn-chip-remove" onclick="OE.clearCollabClub()" title="Remove">
            <i class="fas fa-xmark"></i>
          </button>
        </div>
        <textarea id="collabInlineMsg" class="form-textarea"
          style="width:100%;min-height:60px;font-size:12px;margin-top:2px;resize:none;"
          placeholder="Message to this club — what do you need from them?"></textarea>
        <button class="btn-primary" style="align-self:flex-end;padding:6px 14px;font-size:12px;" onclick="OE.openCollabRequest()">
          <i class="fas fa-paper-plane"></i> Send Request
        </button>
      </div>`;
  }

  function clearCollabClub() {
    selectedCollabClubId   = 0;
    selectedCollabClubName = '';
    const box = document.getElementById('collabSelectedClub');
    if (box) box.innerHTML = '';
    const inp = document.getElementById('collabSearchInput');
    if (inp) inp.value = '';
  }

  // Close picker on outside click
  document.addEventListener('click', e => {
    const picker = document.getElementById('collabCustomPicker');
    const drop = document.getElementById('collabPickerDropdown');
    if (picker && !picker.contains(e.target) && drop) {
      drop.classList.remove('open');
      collabPickerOpen = false;
    }
  });

  function loadEventCollabs(eventId) {
    if (!eventId) return;
    ajax('collab_get', { event_id: eventId }).then(r => {
      if (!r.success) return;
      collabList = r.collabs || [];
      renderCollabChips();
    });
  }

  function renderCollabChips() {
    const container = document.getElementById('collabChips');
    const empty     = document.getElementById('collabChipsEmpty');
    if (!container) return;
    if (!collabList.length) {
      container.innerHTML = '';
      if (empty) { empty.style.display = ''; container.appendChild(empty); }
      return;
    }
    if (empty) empty.style.display = 'none';
    container.innerHTML = collabList.map(c => {
      const statusClass = c.status === 'accepted' ? 'collab-chip-accepted'
                        : c.status === 'rejected'  ? 'collab-chip-rejected'
                        : 'collab-chip-pending';
      const statusLabel = c.status === 'accepted' ? '✅ Accepted'
                        : c.status === 'rejected'  ? '❌ Declined'
                        : '⏳ Pending';
      let membersHtml = '';
      if (c.status === 'accepted' && c.members && c.members.length) {
        membersHtml = `<div class="collab-chip-members">${c.members.map(m =>
          `<span class="collab-member-pill">${esc(m.first_name)} ${esc(m.last_name)}${m.role_label ? ' <em>(' + esc(m.role_label) + ')</em>' : ''}</span>`
        ).join('')}</div>`;
      }
      let responseHtml = '';
      if (c.response_message) responseHtml = `<div class="collab-chip-response-msg">"${esc(c.response_message)}"</div>`;
      return `<div class="collab-chip ${statusClass}">
        <div class="collab-chip-top">
          <span class="collab-chip-club">${esc(c.target_club_name)}</span>
          <span class="collab-chip-status">${statusLabel}</span>
        </div>
        ${c.message ? `<div class="collab-chip-msg">${esc(c.message)}</div>` : ''}
        ${membersHtml}
        ${responseHtml}
      </div>`;
    }).join('');
  }

  function openCollabRequest() {
    if (!selectedCollabClubId) { toast('Please select a club first', 'error'); return; }
    pendingCollabClubId  = selectedCollabClubId;
    pendingCollabEventId = parseInt(document.getElementById('modalEventId')?.value || 0);
    if (!pendingCollabEventId) {
      toast('Please save the event first, then reopen it to send a collaboration request.', 'warn');
      return;
    }
    const message = document.getElementById('collabInlineMsg')?.value.trim() || '';
    ajax('collab_request', {
      event_id: pendingCollabEventId, target_club: pendingCollabClubId, message
    }).then(r => {
      if (r.success) {
        toast('Collaboration request sent! The other club has been notified.');
        const sentEventId = pendingCollabEventId;
        clearCollabClub();
        loadEventCollabs(sentEventId);
      } else {
        toast(r.message || 'Failed to send', 'error');
      }
    });
  }

  function sendCollabRequest() {
    const message = document.getElementById('collabReqMessage').value.trim();
    if (!pendingCollabClubId || !pendingCollabEventId) return;
    ajax('collab_request', {
      event_id:    pendingCollabEventId,
      target_club: pendingCollabClubId,
      message:     message,
    }).then(r => {
      if (r.success) {
        toast('Collaboration request sent!', 'success');
        closeModal('collabRequestModal');
        loadEventCollabs(pendingCollabEventId);
      } else {
        toast(r.message || 'Failed to send', 'error');
      }
    });
  }

  // ── Context menu: overview ────────────────────────────────
  function ctxOverview() {
    closeCtx();
    openEventOverview(ctxEventId);
  }

  function openEventOverview(eventId) {
    document.getElementById('overviewTitle').textContent = 'Event Overview';
    document.getElementById('overviewBody').innerHTML = '<div class="slc-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';
    openModal('eventOverviewModal');
    ajax('event_overview', { event_id: eventId }).then(r => {
      if (!r.success) { document.getElementById('overviewBody').innerHTML = '<p style="color:#e53e3e;padding:16px;">Failed to load.</p>'; return; }
      renderEventOverview(r.event);
    });
  }

  function renderEventOverview(ev) {
    const fmt = v => v ? v : '—';
    const fmtDate = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'}) : '—';
    const fmtTime = t => {
      if (!t) return '';
      const [h, m] = t.split(':');
      const hr = parseInt(h); const ampm = hr >= 12 ? 'PM' : 'AM';
      return ((hr % 12) || 12) + ':' + m + ' ' + ampm;
    };

    // Status badge
    const statusColors = { upcoming:'#3b82f6', ongoing:'#10b981', completed:'#6b7280', cancelled:'#ef4444', pending_approval:'#f59e0b' };
    const statusColor = statusColors[ev.status] || '#6b7280';

    // Assignees section
    let assigneesHtml = '<em style="color:var(--text-light);">No assignees.</em>';
    if (ev.assignees && ev.assignees.length) {
      assigneesHtml = ev.assignees.map(a => {
        const initials = (a.first_name[0] + a.last_name[0]).toUpperCase();
        const statusDot = a.status === 'accepted' ? '🟢' : a.status === 'declined' ? '🔴' : '🟡';
        return `<div class="ov-person-row">
          <span class="ov-avatar">${initials}</span>
          <div class="ov-person-info">
            <span class="ov-person-name">${esc(a.first_name)} ${esc(a.last_name)}</span>
            <span class="ov-person-role">${a.role_label ? esc(a.role_label) : (a.club_role ? esc(a.club_role) : 'Member')}</span>
          </div>
          <span class="ov-status-dot">${statusDot}</span>
        </div>`;
      }).join('');
    }

    // Collaborations section
    let collabsHtml = '<em style="color:var(--text-light);">No club collaborations.</em>';
    if (ev.collaborations && ev.collaborations.length) {
      collabsHtml = ev.collaborations.map(col => {
        const stLabel = col.status === 'accepted' ? '✅ Accepted' : col.status === 'rejected' ? '❌ Declined' : '⏳ Pending';
        const stColor = col.status === 'accepted' ? '#10b981' : col.status === 'rejected' ? '#ef4444' : '#f59e0b';
        let membersHtml = '';
        if (col.members && col.members.length) {
          membersHtml = `<div class="ov-collab-members">
            <div style="font-size:12px;font-weight:600;color:var(--text-light);margin-bottom:6px;">Assigned Members</div>
            ${col.members.map(m => `<div class="ov-person-row" style="margin-bottom:4px;">
              <span class="ov-avatar" style="width:28px;height:28px;font-size:10px;">${(m.first_name[0]+m.last_name[0]).toUpperCase()}</span>
              <div class="ov-person-info">
                <span class="ov-person-name" style="font-size:13px;">${esc(m.first_name)} ${esc(m.last_name)}</span>
                ${m.role_label ? `<span class="ov-person-role">${esc(m.role_label)}</span>` : ''}
              </div>
            </div>`).join('')}
          </div>`;
        }
        return `<div class="ov-collab-card">
          <div class="ov-collab-header">
            <span class="ov-collab-name">${esc(col.club_name)}</span>
            <span class="ov-collab-status" style="color:${stColor};">${stLabel}</span>
          </div>
          ${col.message ? `<div class="ov-collab-msg">"${esc(col.message)}"</div>` : ''}
          ${col.response_message ? `<div class="ov-collab-resp-msg">Their response: "${esc(col.response_message)}"</div>` : ''}
          ${membersHtml}
        </div>`;
      }).join('');
    }

    document.getElementById('overviewTitle').textContent = esc(ev.name);
    document.getElementById('overviewBody').innerHTML = `
      <div class="ov-section ov-event-meta">
        <div class="ov-meta-row">
          <span class="ov-meta-label"><i class="fas fa-calendar"></i> Date</span>
          <span class="ov-meta-val">${fmtDate(ev.event_date)}</span>
        </div>
        <div class="ov-meta-row">
          <span class="ov-meta-label"><i class="fas fa-clock"></i> Time</span>
          <span class="ov-meta-val">${fmtTime(ev.start_time)}${ev.end_time ? ' – ' + fmtTime(ev.end_time) : ''}</span>
        </div>
        <div class="ov-meta-row">
          <span class="ov-meta-label"><i class="fas fa-map-marker-alt"></i> Venue</span>
          <span class="ov-meta-val">${fmt(ev.location)}</span>
        </div>
        <div class="ov-meta-row">
          <span class="ov-meta-label"><i class="fas fa-flag"></i> Status</span>
          <span class="ov-meta-val"><span style="background:${statusColor};color:#fff;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600;">${esc(ev.status.replace('_',' '))}</span></span>
        </div>
        <div class="ov-meta-row">
          <span class="ov-meta-label"><i class="fas fa-users"></i> Organizer</span>
          <span class="ov-meta-val">${esc(ev.club_name)}</span>
        </div>
        ${ev.is_mandatory ? '<div class="ov-meta-row"><span class="ov-meta-label"><i class="fas fa-star"></i></span><span class="ov-meta-val"><strong>Mandatory Event</strong></span></div>' : ''}
        ${ev.description ? `<div class="ov-desc">${esc(ev.description)}</div>` : ''}
      </div>

      <div class="ov-section">
        <div class="ov-section-title"><i class="fas fa-user-check"></i> Assignees (${ev.assignees ? ev.assignees.length : 0})</div>
        <div class="ov-persons-list">${assigneesHtml}</div>
      </div>

      <div class="ov-section">
        <div class="ov-section-title"><i class="fas fa-handshake"></i> Club Collaborations</div>
        <div class="ov-collabs-list">${collabsHtml}</div>
      </div>

      ${ev.attendance_count > 0 ? `<div class="ov-section">
        <div class="ov-section-title"><i class="fas fa-clipboard-check"></i> Attendance</div>
        <div style="font-size:24px;font-weight:800;color:var(--green-accent);padding:8px 0;">${ev.attendance_count} <span style="font-size:14px;font-weight:400;color:var(--text-light);">checked in</span></div>
      </div>` : ''}
    `;
  }

  // ── Incoming collabs tab ──────────────────────────────────
  function renderIncomingCollabs() {
    const container = document.getElementById('collabIncomingList');
    if (!container) return;
    if (!incomingCollabs.length) {
      container.innerHTML = '<div class="collab-empty-state"><i class="fas fa-handshake"></i><p>No partnership requests yet.</p></div>';
      return;
    }
    container.innerHTML = incomingCollabs.map(req => {
      const fmtDate = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric',year:'numeric'}) : '—';
      const fmtTime = t => {
        if (!t) return '';
        const [h, m] = t.split(':');
        const hr = parseInt(h); const ap = hr >= 12 ? 'PM' : 'AM';
        return ((hr % 12) || 12) + ':' + m + ' ' + ap;
      };
      const statusClass = req.status === 'accepted' ? 'collab-req-accepted'
                        : req.status === 'rejected'  ? 'collab-req-rejected'
                        : 'collab-req-pending';
      const statusBadge = req.status === 'accepted'
        ? '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;background:#dcfce7;color:#16a34a;border:1px solid #86efac;"><span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span>Accepted</span>'
        : req.status === 'rejected'
        ? '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"><span style="width:6px;height:6px;border-radius:50%;background:#dc2626;display:inline-block;"></span>Declined</span>'
        : '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;background:#fef9c3;color:#ca8a04;border:1px solid #fde047;"><span style="width:6px;height:6px;border-radius:50%;background:#ca8a04;display:inline-block;"></span>Awaiting Response</span>';

      const timeStr = req.start_time ? fmtTime(req.start_time) + (req.end_time ? ' – ' + fmtTime(req.end_time) : '') : '';

      // Members assigned (for accepted)
      let membersHtml = '';
      if (req.status === 'accepted' && req.chosen_members && req.chosen_members.length) {
        membersHtml = `<div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
          <div style="font-size:11px;font-weight:700;color:var(--text-light);letter-spacing:.8px;margin-bottom:8px;">YOUR ASSIGNED MEMBERS</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;">
            ${req.chosen_members.map(m => `
              <div style="display:flex;align-items:center;gap:6px;background:var(--green-glow);border:1px solid rgba(42,138,74,.2);border-radius:20px;padding:4px 10px 4px 4px;">
                <span style="width:24px;height:24px;border-radius:50%;background:var(--green-accent);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${esc(m.first_name[0]+m.last_name[0]).toUpperCase()}</span>
                <span style="font-size:12px;font-weight:600;color:var(--green-dark);">${esc(m.first_name)} ${esc(m.last_name)}${m.role_label ? ' <em style="font-weight:400;opacity:.75">· ' + esc(m.role_label) + '</em>' : ''}</span>
              </div>`).join('')}
          </div>
        </div>`;
      }

      const actionBtns = req.status === 'pending' ? `
        <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;">
          <button class="btn-evt btn-outline" onclick="OE.openCollabRespond(${req.id})" style="display:flex;align-items:center;gap:6px;">
            <i class="fas fa-eye"></i> View Details &amp; Respond
          </button>
          <button class="btn-primary" style="padding:7px 16px;font-size:12px;border-radius:8px;display:flex;align-items:center;gap:6px;" onclick="OE.openCollabRespond(${req.id})">
            <i class="fas fa-check"></i> Accept
          </button>
          <button style="padding:7px 16px;font-size:12px;border-radius:8px;border:1.5px solid #ef4444;background:transparent;color:#ef4444;font-family:inherit;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .14s;" 
            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'"
            onclick="OE.quickDecline(${req.id})">
            <i class="fas fa-times"></i> Decline
          </button>
        </div>` : req.status === 'accepted' ? `
        <div style="margin-top:10px;">
          <button class="btn-evt btn-outline" onclick="OE.openCollabRespond(${req.id})" style="font-size:12px;">
            <i class="fas fa-users"></i> Edit Member Assignments
          </button>
        </div>` : '';

      return `<div class="collab-req-card ${statusClass}" style="margin-bottom:14px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
              <span style="font-size:16px;font-weight:800;color:var(--green-accent);">${esc(req.requesting_club_name)}</span>
              <span style="color:var(--text-light);font-size:14px;">is asking for your help</span>
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--text-dark);margin-bottom:8px;">"${esc(req.event_name)}"</div>
            <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:var(--text-light);margin-bottom:${req.message ? '12px' : '0'};">
              <span><i class="fas fa-calendar" style="margin-right:4px;"></i>${fmtDate(req.event_date)}</span>
              ${timeStr ? `<span><i class="fas fa-clock" style="margin-right:4px;"></i>${timeStr}</span>` : ''}
              ${req.location ? `<span><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>${esc(req.location)}</span>` : ''}
            </div>
            ${req.message ? `<div style="background:var(--main-bg);border-left:3px solid var(--green-accent);padding:10px 14px;border-radius:0 8px 8px 0;font-size:13px;font-style:italic;color:var(--text-dark);">
              <span style="font-size:10px;font-weight:700;color:var(--text-light);display:block;margin-bottom:3px;letter-spacing:.6px;">THEIR MESSAGE</span>
              "${esc(req.message)}"
            </div>` : ''}
          </div>
          <div style="flex-shrink:0;">${statusBadge}</div>
        </div>
        ${membersHtml}
        ${actionBtns}
      </div>`;
    }).join('');
  }

  function quickDecline(collabId) {
    const req = incomingCollabs.find(r => r.id === collabId);
    if (!req) return;
    if (!confirm('Decline the partnership request from ' + req.requesting_club_name + '?')) return;
    ajax('collab_respond', {
      collab_id: collabId,
      response: 'rejected',
      response_message: '',
      chosen_members: '[]',
    }).then(r => {
      if (r.success) {
        toast('Partnership request declined.');
        setTimeout(() => location.reload(), 700);
      } else {
        toast(r.message || 'Error', 'error');
      }
    });
  }

  function renderPartneredEvents() {
    const container = document.getElementById('partneredEventsContainer');
    if (!container) return;
    const partnered = DATA.partnered_events || [];
    if (!partnered.length) {
      container.innerHTML = `<div class="empty-state">
        <i class="fas fa-handshake"></i>
        <span>No partnered events yet. Once a collaboration is accepted (either direction), it appears here.</span>
      </div>`;
      return;
    }
    const fmtDate = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—';
    const fmtTime = t => {
      if (!t) return '';
      const [h, m] = t.split(':');
      const hr = parseInt(h); const ap = hr >= 12 ? 'PM' : 'AM';
      return ((hr % 12) || 12) + ':' + m + ' ' + ap;
    };

    container.innerHTML = partnered.map(pe => {
      const st = pe.start_time ? fmtTime(pe.start_time) : '';
      const et = pe.end_time ? fmtTime(pe.end_time) : '';
      const statusStr = pe.event_status || 'upcoming';
      const isOutgoing = pe.direction === 'outgoing';
      const collabStatus = pe.collab_status || 'pending';

      // Color-coded collab status badge
      const collabColor = collabStatus === 'accepted'
        ? { bg:'rgba(42,138,74,.1)', color:'var(--green-accent)', border:'rgba(42,138,74,.2)', icon:'fa-handshake' }
        : collabStatus === 'rejected'
        ? { bg:'rgba(192,41,27,.1)', color:'var(--red-accent)', border:'rgba(192,41,27,.2)', icon:'fa-times-circle' }
        : { bg:'rgba(245,158,11,.1)', color:'#b45309', border:'rgba(245,158,11,.3)', icon:'fa-clock' };

      const collabLabel = collabStatus === 'accepted'
        ? (isOutgoing ? `with ${esc(pe.partner_club_name)}` : `helping ${esc(pe.partner_club_name)}`)
        : collabStatus === 'rejected'
        ? `declined by ${esc(pe.partner_club_name)}`
        : `⏳ pending — ${esc(pe.partner_club_name)}`;

      const dirBadge = `<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:10.5px;font-weight:700;background:${collabColor.bg};color:${collabColor.color};border:1px solid ${collabColor.border};">
          <i class="fas ${collabColor.icon}" style="font-size:9px;"></i> ${collabLabel}
        </span>`;

      const membersHtml = pe.our_members && pe.our_members.length
        ? `<div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;">
            ${pe.our_members.map(m =>
              `<span style="display:inline-flex;align-items:center;gap:4px;background:var(--green-glow);border:1px solid rgba(42,138,74,.2);border-radius:20px;padding:2px 10px 2px 4px;font-size:11px;font-weight:600;color:var(--green-dark);">
                <span style="width:18px;height:18px;border-radius:50%;background:var(--green-accent);color:#fff;font-size:9px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;">${esc((m.first_name[0]+m.last_name[0]).toUpperCase())}</span>
                ${esc(m.first_name)} ${esc(m.last_name)}${m.role_label ? ' · <em>' + esc(m.role_label) + '</em>' : ''}
              </span>`).join('')}
          </div>` : '';

      return `<div class="event-card-row">
        <span class="evt-dot dot-${esc(statusStr)}"></span>
        <div class="evt-info">
          <div class="evt-title" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            ${esc(pe.event_name)}
            ${dirBadge}
          </div>
          <div class="evt-meta">
            ${fmtDate(pe.event_date)}${st ? ' · ' + st : ''}${st && et ? ' – ' + et : ''}${pe.venue ? ' · ' + esc(pe.venue) : ''}
          </div>
          ${membersHtml}
        </div>
        <div class="evt-actions">
          <span class="status-chip chip-${esc(statusStr)}">${esc(statusStr.replace('_',' '))}</span>
        </div>
      </div>`;
    }).join('');
  }

  function openCollabRespond(collabId) {
    const req = incomingCollabs.find(r => r.id === collabId);
    if (!req) return;
    activeCollabId   = collabId;
    collabMemberList = [];
    const fmtDate = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric',year:'numeric'}) : '—';
    document.getElementById('collabRespondTitle').textContent = 'Respond — ' + req.event_name;
    document.getElementById('collabRespondEventInfo').innerHTML =
      '<div style="font-weight:700;font-size:15px;color:var(--text-dark);margin-bottom:6px;">' + esc(req.event_name) + '</div>' +
      '<div style="font-size:13px;color:var(--text-light);display:flex;flex-wrap:wrap;gap:12px;">' +
        '<span><i class="fas fa-calendar"></i> ' + fmtDate(req.event_date) + '</span>' +
        (req.location ? '<span><i class="fas fa-map-marker-alt"></i> ' + esc(req.location) + '</span>' : '') +
        '<span><i class="fas fa-building"></i> by ' + esc(req.requesting_club_name) + '</span>' +
      '</div>';
    document.getElementById('collabRespondRequestMsg').innerHTML = req.message
      ? '<div style="background:var(--card-bg);border-left:3px solid var(--green-accent);padding:10px 14px;border-radius:6px;font-size:13px;font-style:italic;color:var(--text-dark);">"' + esc(req.message) + '"</div>'
      : '';
    document.getElementById('collabRespondMsg').value = '';
    document.getElementById('collabMemberSearch').value = '';
    renderCollabMemberChips();
    openModal('collabRespondModal');
  }

  function collabMemberSearch(q) {
    const drop = document.getElementById('collabMemberDropdown');
    q = q.trim().toLowerCase();
    if (!q) { drop.classList.remove('open'); drop.innerHTML = ''; return; }
    const alreadyIds = new Set(collabMemberList.map(m => m.user_id));
    const filtered = members.filter(m => {
      if (alreadyIds.has(m.user_id)) return false;
      return (m.first_name + ' ' + m.last_name).toLowerCase().includes(q);
    }).slice(0, 8);
    if (!filtered.length) {
      drop.innerHTML = '<div class="asn-drop-empty">No members found</div>';
      drop.classList.add('open'); return;
    }
    drop.innerHTML = filtered.map(m => {
      const initials = (m.first_name[0] + m.last_name[0]).toUpperCase();
      return `<div class="asn-drop-item" onclick="OE.collabMemberAdd(${m.user_id})">
        <div class="asn-drop-avatar-wrap">
          ${m.profile_picture
            ? `<img src="/assets/pictures/profile_pictures/${esc(m.profile_picture)}" class="asn-drop-avatar" />`
            : `<span class="asn-drop-initials">${initials}</span>`}
        </div>
        <div class="asn-drop-info">
          <span class="asn-drop-name">${esc(m.first_name)} ${esc(m.last_name)}</span>
          <span class="asn-drop-role">${esc(m.role || '')}</span>
        </div>
        <span class="asn-drop-add"><i class="fas fa-plus-circle"></i></span>
      </div>`;
    }).join('');
    drop.classList.add('open');
  }

  function collabMemberAdd(userId) {
    const m = members.find(x => x.user_id === userId);
    if (!m || collabMemberList.find(x => x.user_id === userId)) return;
    collabMemberList.push({ ...m, role_label: '' });
    document.getElementById('collabMemberSearch').value = '';
    document.getElementById('collabMemberDropdown').classList.remove('open');
    document.getElementById('collabMemberDropdown').innerHTML = '';
    renderCollabMemberChips();
  }

  function collabMemberRemove(userId) {
    collabMemberList = collabMemberList.filter(m => m.user_id !== userId);
    renderCollabMemberChips();
  }

  function renderCollabMemberChips() {
    const container = document.getElementById('collabMemberChips');
    const empty     = document.getElementById('collabMemberEmpty');
    if (!container) return;
    if (!collabMemberList.length) {
      container.innerHTML = '';
      if (empty) { empty.style.display = ''; container.appendChild(empty); }
      return;
    }
    if (empty) empty.style.display = 'none';
    container.innerHTML = collabMemberList.map(m => {
      const initials = (m.first_name[0] + m.last_name[0]).toUpperCase();
      return `<div class="asn-chip" id="cm-chip-${m.user_id}">
        <div class="asn-chip-avatar-wrap">
          ${m.profile_picture
            ? `<img src="/assets/pictures/profile_pictures/${esc(m.profile_picture)}" class="asn-chip-avatar" />`
            : `<span class="asn-chip-initials">${initials}</span>`}
        </div>
        <div class="asn-chip-body">
          <span class="asn-chip-name">${esc(m.first_name)} ${esc(m.last_name)}</span>
          <button class="asn-chip-role-btn" onclick="OE.openCollabRolePicker(${m.user_id})">${m.role_label || '+ Add role'}</button>
        </div>
        <button class="asn-chip-remove" onclick="OE.collabMemberRemove(${m.user_id})"><i class="fas fa-times"></i></button>
      </div>`;
    }).join('');
  }

  function openCollabRolePicker(userId) {
    const m = collabMemberList.find(x => x.user_id === userId);
    if (!m) return;
    const existing = document.getElementById('collabRolePickerPopup');
    if (existing) existing.remove();
    const popup = document.createElement('div');
    popup.id = 'collabRolePickerPopup';
    popup.className = 'role-picker-popup';
    popup.innerHTML = `
      <div class="rpp-header">Set role for ${esc(m.first_name)}</div>
      <div class="rpp-presets">${PRESET_ROLES.map(r =>
        `<button class="rpp-preset" onclick="OE.applyCollabRole('${r.label.replace(/'/g,"\\'")}')">
          <span class="rpp-icon">${r.icon}</span>${r.label}</button>`
      ).join('')}</div>
      <div class="rpp-custom-row">
        <input type="text" id="collabRppCustomInput" class="rpp-custom-input" placeholder="Or type a custom role…"
               value="${esc(m.role_label)}"
               onkeydown="if(event.key==='Enter'){OE.applyCollabRole(this.value);}" />
        <button class="rpp-apply-btn" onclick="OE.applyCollabRole(document.getElementById('collabRppCustomInput').value)">
          <i class="fas fa-check"></i></button>
      </div>`;
    const chip = document.getElementById(`cm-chip-${userId}`);
    const modal = document.querySelector('#collabRespondModal .modal-box');
    if (chip && modal) {
      modal.appendChild(popup);
      const cr = chip.getBoundingClientRect();
      const mr = modal.getBoundingClientRect();
      popup.style.top  = (cr.bottom - mr.top + 6) + 'px';
      popup.style.left = (cr.left   - mr.left)    + 'px';
    } else {
      document.body.appendChild(popup);
    }
    popup.classList.add('open');
    document.getElementById('collabRppCustomInput')?.focus();
    // store target
    popup.dataset.targetId = userId;
  }

  function applyCollabRole(label) {
    label = label.trim();
    const popup = document.getElementById('collabRolePickerPopup');
    const uid = popup ? parseInt(popup.dataset.targetId) : 0;
    const m = collabMemberList.find(x => x.user_id === uid);
    if (m) m.role_label = label;
    renderCollabMemberChips();
    popup?.remove();
  }

  function submitCollabResponse(response) {
    if (!activeCollabId) return;
    const msg = document.getElementById('collabRespondMsg')?.value.trim() || '';
    ajax('collab_respond', {
      collab_id:       activeCollabId,
      response:        response,
      response_message: msg,
      chosen_members:  JSON.stringify(collabMemberList.map(m => ({ user_id: m.user_id, role_label: m.role_label }))),
    }).then(r => {
      if (r.success) {
        toast(response === 'accepted' ? 'Collaboration accepted!' : 'Collaboration declined.', response === 'accepted' ? 'success' : '');
        closeModal('collabRespondModal');
        // Refresh incoming list
        location.reload();
      } else {
        toast(r.message || 'Error', 'error');
      }
    });
  }

  // ── Approve/Reject pending events ────────────────────────
  function approveEvent(eventId, decision) {
    ajax('evt_approve', { event_id: eventId, decision }).then(r => {
      if (r.success) {
        toast(decision === 'upcoming' ? '✅ Event approved!' : '❌ Event rejected.');
        setTimeout(() => pageReload(), 600);
      } else {
        toast(r.message || 'Error', 'error');
      }
    });
  }

  // ── Init ──────────────────────────────────────────────────
  function initCollab() {
    // Show badge if incoming pending
    const pending = DATA.incoming_pending || 0;
    const badge   = document.getElementById('collabTabBadge');
    if (badge && pending > 0) { badge.textContent = pending; badge.style.display = 'inline-flex'; }

    // Show incoming section if there are any incoming requests
    const incomingSection = document.getElementById('incomingRequestsSection');
    if (incomingSection && incomingCollabs.length > 0) {
      incomingSection.style.display = 'block';
    }

    renderIncomingCollabs();
    renderPartneredEvents();
  }
  initCollab();

  // Patch saveEvent to store event_id on the save button for collab use
  const _origSaveBtn = document.getElementById('btnSave');
  if (_origSaveBtn) {
    const _origOnclick = _origSaveBtn.getAttribute('onclick');
  }

  // ── Public API ────────────────────────────────────────────
  return {
    switchTab, filterEvents, filterMembers,
    openAddModal, openEditModal, saveEvent,
    openModal, closeModal,
    openCtx, closeCtx, ctxEdit, ctxScanAtt, ctxToggleMandatory, ctxDelete,
    closeDeleteModal,
    openAttView,
    openScanner, closeScanner, scrollToScanner,
    toggleCamera, openFullscreen, closeFullscreen,
    manualScan, setAttTab,
    toggleNotif, markAllRead,
    // Assignees
    assigneeSearch, assigneeAdd, assigneeRemove,
    openRolePicker, applyRole, closeRolePicker,
    // Collaboration
    openCollabRequest, sendCollabRequest,
    toggleCollabPicker, selectCollabClub, clearCollabClub, collabSearch,
    openCollabRequest, sendCollabRequest,
    collabMemberSearch, collabMemberAdd, collabMemberRemove,
    openCollabRolePicker, applyCollabRole,
    openCollabRespond, submitCollabResponse,
    ctxOverview, openEventOverview,
    approveEvent,
    quickDecline, renderPartneredEvents,
  };
  
})();