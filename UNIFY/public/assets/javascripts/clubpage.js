/* ============================================================
   UNIFY — Club Management CRUD
   clubpage.js
   Posts to clubpage.php (same page) — no api.php needed.
============================================================ */

(function () {
  'use strict';

  /* ══════════════════════════════════════════════════════════
     INTERNAL POST HELPER — replaces apiPost() / api-helper.js
     Sends a POST to the current page (clubpage.php) and returns
     the parsed JSON response. Throws on HTTP error or
     success:false from the server.
  ══════════════════════════════════════════════════════════ */

  async function postToPage(action, fields) {
    const body = new URLSearchParams({ action, ...fields });
    const res  = await fetch(window.location.href, {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    body.toString(),
    });
    if (!res.ok) throw new Error('Server error: ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Operation failed.');
    return data;
  }

  /* ══════════════════════════════════════════════════════════
     HELPERS — render sub-sections of the right panel
  ══════════════════════════════════════════════════════════ */

  

  

  function renderActivity(activities) {
    const el = document.getElementById('activityList');
    if (!activities || !activities.length) {
      el.innerHTML = '<p class="empty-msg">No recent activity.</p>';
      return;
    }
    el.innerHTML = activities.map(a => `
      <div class="activity-item">
        <div class="act-icon ${a.color}"><i class="fas ${a.icon}"></i></div>
        <div class="act-info">
          <span class="act-text">${a.text}</span>
          <span class="act-time">${a.time}</span>
        </div>
      </div>`).join('');
  }

  /* ══════════════════════════════════════════════════════════
     READ — Update right panel from a club-item element
  ══════════════════════════════════════════════════════════ */

  

  /* ══════════════════════════════════════════════════════════
     CLUB LIST helpers
  ══════════════════════════════════════════════════════════ */

  

  function selectItem(item) {
    getAllItems().forEach(i => i.classList.remove('selected'));
    item.classList.add('selected');
    updateRightPanel(item);
    updateTabCounts();
  }

  

  /* ══════════════════════════════════════════════════════════
     TAB COUNTS
  ══════════════════════════════════════════════════════════ */

  function updateTabCounts() {
    const items   = getAllItems();
    const all     = items.length;
    const active  = [...items].filter(i => i.dataset.status === 'active').length;
    const pending = [...items].filter(i => i.dataset.status === 'pending').length;

    document.querySelectorAll('.filter-tab').forEach(tab => {
      const f   = tab.dataset.filter;
      const cnt = tab.querySelector('.tab-count');
      if (!cnt) return;
      if (f === 'all')     cnt.textContent = all;
      if (f === 'active')  cnt.textContent = active;
      if (f === 'pending') cnt.textContent = pending;
    });
  }

  /* ══════════════════════════════════════════════════════════
     TOAST notification
  ══════════════════════════════════════════════════════════ */

  function showToast(msg, type) {
    const old = document.getElementById('crudToast');
    if (old) old.remove();

    const t = document.createElement('div');
    t.id        = 'crudToast';
    t.className = 'crud-toast crud-toast-' + (type || 'success');
    t.innerHTML = `<i class="fas ${type === 'danger' ? 'fa-trash' : type === 'info' ? 'fa-pen' : 'fa-circle-check'}"></i> ${msg}`;
    document.body.appendChild(t);

    void t.offsetWidth;
    t.classList.add('crud-toast-show');
    setTimeout(() => {
      t.classList.remove('crud-toast-show');
      setTimeout(() => t.remove(), 350);
    }, 2800);
  }

  /* ══════════════════════════════════════════════════════════
     CONFIRM DIALOG
  ══════════════════════════════════════════════════════════ */

  function showConfirm(message, onConfirm) {
    const old = document.getElementById('crudConfirmOverlay');
    if (old) old.remove();

    const overlay = document.createElement('div');
    overlay.id        = 'crudConfirmOverlay';
    overlay.className = 'crud-confirm-overlay';
    overlay.innerHTML = `
      <div class="crud-confirm-box">
        <div class="crud-confirm-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <p class="crud-confirm-msg">${message}</p>
        <div class="crud-confirm-btns">
          <button class="crud-confirm-cancel">Cancel</button>
          <button class="crud-confirm-ok">Delete</button>
        </div>
      </div>`;
    document.body.appendChild(overlay);

    void overlay.offsetWidth;
    overlay.classList.add('crud-confirm-visible');

    function close() {
      overlay.classList.remove('crud-confirm-visible');
      setTimeout(() => overlay.remove(), 220);
    }

    overlay.querySelector('.crud-confirm-cancel').addEventListener('click', close);
    overlay.querySelector('.crud-confirm-ok').addEventListener('click', () => {
      close();
      onConfirm();
    });
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
  }

  /* ══════════════════════════════════════════════════════════
     MODAL SHARED HELPERS
  ══════════════════════════════════════════════════════════ */

  const overlay        = document.getElementById('newClubOverlay');
  const logoUploadArea = document.getElementById('logoUploadArea');
  const logoFileInput  = document.getElementById('logoFileInput');
  const logoPreview    = document.getElementById('logoPreview');
  const modalTitle     = document.querySelector('#newClubModal .modal-title');
  const modalSubtitle  = document.querySelector('#newClubModal .modal-subtitle');
  const submitBtn      = document.getElementById('modalSubmit');

  let editingItem = null;

  

  

  function resetModalForm() {
    document.getElementById('newClubName').value     = '';
    document.getElementById('newClubCategory').value = '';
    document.getElementById('newClubStatus').value   = 'pending';
    document.getElementById('newClubFounded').value  = '';
    document.getElementById('newClubRoom').value     = '';
    document.getElementById('newClubDesc').value     = '';
    logoPreview.style.display                                    = 'none';
    logoPreview.src                                              = '';
    logoUploadArea.querySelector('.logo-upload-inner').style.display = 'flex';
    logoFileInput.value = '';
    document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
  }

  /* ══════════════════════════════════════════════════════════
     CREATE — build a new club-item and insert into list
  ══════════════════════════════════════════════════════════ */

  function createClubItem(data) {
    const { name, category, status, founded, room, desc, logoSrc } = data;
    const initial  = name.charAt(0).toUpperCase();
    const dotClass = status === 'active' ? 'active-dot' : 'pending-dot';

    const newItem = document.createElement('div');
    newItem.className = 'club-item';
    Object.assign(newItem.dataset, {
      status, name, logo: logoSrc, category, founded, room, desc,
      members: '0', events: '0', budget: '₱0.00', attendance: '—',
      officers: '[]', upcoming: '[]', activity: '[]'
    });
    newItem.innerHTML = `
      ${logoSrc
        ? `<img class="club-item-logo" src="${logoSrc}" alt="${name} logo">`
        : `<div class="club-item-logo club-item-logo-initial">${initial}</div>`}
      <div class="club-item-info">
        <span class="club-item-name">${name}</span>
        <span class="club-item-meta">0 members · ${category}</span>
      </div>
      <div class="club-item-right"><span class="ci-status-dot ${dotClass}"></span></div>
    `;

    bindItem(newItem);
    document.getElementById('clubList').appendChild(newItem);
    selectItem(newItem);
    return newItem;
  }

  /* ══════════════════════════════════════════════════════════
     UPDATE — patch an existing club-item's data and DOM
  ══════════════════════════════════════════════════════════ */

  function updateClubItem(item, data) {
    const { name, category, status, founded, room, desc, logoSrc } = data;
    const initial  = name.charAt(0).toUpperCase();
    const dotClass = status === 'active' ? 'active-dot' : 'pending-dot';

    Object.assign(item.dataset, { name, category, status, founded, room, desc });
    if (logoSrc) item.dataset.logo = logoSrc;

    const existingLogoSrc = logoSrc || (item.querySelector('img.club-item-logo') ? item.querySelector('img.club-item-logo').src : '');
    item.innerHTML = `
      ${existingLogoSrc
        ? `<img class="club-item-logo" src="${existingLogoSrc}" alt="${name} logo">`
        : `<div class="club-item-logo club-item-logo-initial">${initial}</div>`}
      <div class="club-item-info">
        <span class="club-item-name">${name}</span>
        <span class="club-item-meta">${item.dataset.members} members · ${category}</span>
      </div>
      <div class="club-item-right"><span class="ci-status-dot ${dotClass}"></span></div>
    `;

    bindItem(item);

    if (item.classList.contains('selected')) {
      updateRightPanel(item);
    }
  }

  /* ══════════════════════════════════════════════════════════
     DELETE — POST to clubpage.php, then remove from DOM
  ══════════════════════════════════════════════════════════ */

  async function deleteClubItem(item) {
    const name        = item.dataset.name;
    const dbId        = item.dataset.dbId;
    const wasSelected = item.classList.contains('selected');

    try {
      await postToPage('club_delete', { id: dbId });

      item.classList.add('club-item-deleting');
      setTimeout(() => {
        item.remove();
        updateTabCounts();
        if (wasSelected) {
          const first = document.querySelector('.club-item');
          if (first) selectItem(first);
          else clearRightPanel();
        }
      }, 320);

      showToast(`"${name}" has been deleted.`, 'danger');
    } catch (err) {
      showToast(err.message || 'Delete failed.', 'danger');
    }
  }

  function clearRightPanel() {
    document.getElementById('detailName').textContent     = 'No club selected';
    document.getElementById('detailDesc').textContent     = 'Select a club from the list to view details.';
    document.getElementById('detailCategory').textContent = '—';
    document.getElementById('detailFounded').textContent  = '—';
    document.getElementById('detailRoom').textContent     = '—';
    document.getElementById('statMembers').textContent    = '—';
    document.getElementById('statEvents').textContent     = '—';
    document.getElementById('statBudget').textContent     = '—';
    document.getElementById('statAttendance').textContent = '—';
    renderOfficers([]);
    renderEvents([]);
    renderActivity([]);
  }

  /* ══════════════════════════════════════════════════════════
     FORM SUBMISSION — CREATE and UPDATE via postToPage()
  ══════════════════════════════════════════════════════════ */

  async function handleFormSubmit() {
    const nameEl     = document.getElementById('newClubName');
    const categoryEl = document.getElementById('newClubCategory');
    const descEl     = document.getElementById('newClubDesc');

    const name     = nameEl.value.trim();
    const category = categoryEl.value;
    const status   = document.getElementById('newClubStatus').value;
    const founded  = document.getElementById('newClubFounded').value.trim() || 'N/A';
    const room     = document.getElementById('newClubRoom').value.trim()    || 'TBA';
    const desc     = descEl.value.trim();

    let valid = true;
    [nameEl, categoryEl, descEl].forEach(el => {
      const empty = !el.value.trim();
      el.classList.toggle('field-error', empty);
      if (empty) valid = false;
    });
    if (!valid) return;

    let logoSrc = '';
    if (logoPreview.style.display !== 'none' && logoPreview.src) {
      logoSrc = logoPreview.src;
    }

    const data = { name, category, status, founded, room, desc, logoSrc };

    submitBtn.disabled  = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

    try {
      if (editingItem) {
        const dbId = editingItem.dataset.dbId;
        await postToPage('club_update', { id: dbId, name, category, status, founded, room, desc });
        updateClubItem(editingItem, data);
        showToast(`"${name}" has been updated.`, 'info');
      } else {
        const res     = await postToPage('club_create', { name, category, status, founded, room, desc });
        const newItem = createClubItem(data);
        newItem.dataset.dbId = res.id;
        showToast(`"${name}" has been created successfully.`, 'success');
      }
      closeModal();
      updateTabCounts();
    } catch (err) {
      showToast(err.message || 'Save failed. Try again.', 'danger');
    } finally {
      submitBtn.disabled  = false;
      submitBtn.innerHTML = editingItem
        ? '<i class="fas fa-floppy-disk"></i> Save Changes'
        : '<i class="fas fa-plus"></i> Create Club';
    }
  }

  /* ══════════════════════════════════════════════════════════
     WIRE UP — modal triggers
  ══════════════════════════════════════════════════════════ */

  document.querySelector('.add-club-btn').addEventListener('click', () => openModal('create'));

  document.getElementById('modalClose').addEventListener('click',  closeModal);
  document.getElementById('modalCancel').addEventListener('click', closeModal);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  logoUploadArea.addEventListener('click', () => logoFileInput.click());
  logoFileInput.addEventListener('change', () => {
    const file = logoFileInput.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
      logoPreview.src                                              = ev.target.result;
      logoPreview.style.display                                    = 'block';
      logoUploadArea.querySelector('.logo-upload-inner').style.display = 'none';
    };
    reader.readAsDataURL(file);
  });

  document.getElementById('modalSubmit').addEventListener('click', handleFormSubmit);

  /* ══════════════════════════════════════════════════════════
     WIRE UP — Edit & Delete buttons
  ══════════════════════════════════════════════════════════ */

  document.querySelector('.cdh-btn-secondary').addEventListener('click', () => {
    const rp = document.getElementById('rightPanel');
    if (!rp._activeItem) return;
    openModal('edit', rp._activeItem);
  });

  const deleteBtn = document.createElement('button');
  deleteBtn.className = 'cdh-btn-danger';
  deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
  document.querySelector('.cdh-actions').appendChild(deleteBtn);

  deleteBtn.addEventListener('click', () => {
    const rp = document.getElementById('rightPanel');
    if (!rp._activeItem) return;
    const name = rp._activeItem.dataset.name;
    showConfirm(`Are you sure you want to delete <strong>"${name}"</strong>? This action cannot be undone.`, () => {
      deleteClubItem(rp._activeItem);
    });
  });

  /* ══════════════════════════════════════════════════════════
     WIRE UP — filter tabs & inline search
  ══════════════════════════════════════════════════════════ */

  document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const f = tab.dataset.filter;
      getAllItems().forEach(item => {
        item.style.display = (f === 'all' || item.dataset.status === f) ? 'flex' : 'none';
      });
    });
  });

  document.getElementById('clubFilterInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    getAllItems().forEach(item => {
      const name = item.querySelector('.club-item-name').textContent.toLowerCase();
      item.style.display = name.includes(q) ? 'flex' : 'none';
    });
  });

  /* ══════════════════════════════════════════════════════════
     INIT
  ══════════════════════════════════════════════════════════ */

  getAllItems().forEach(bindItem);

  const initSel = document.querySelector('.club-item.selected');
  if (initSel) {
    updateRightPanel(initSel);
    document.getElementById('rightPanel')._activeItem = initSel;
  }

  updateTabCounts();

})();
