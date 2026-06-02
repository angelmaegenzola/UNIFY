/* ============================================================
   UNIFY — officer_messages.js  (REWRITTEN)
   Matches student_messages.js exactly.
   Adds: DM support, profile picture avatars, delete modal.
============================================================ */
'use strict';

const OM = (() => {

  /* ── Config ──────────────────────────────────────────────── */
  const POLL_MS = 3000;
  const CFG     = window.OM_CONFIG || {};
  const BASE    = `index.php?page=officer_messages&club_id=${CFG.clubId}`;

  /* ── State ───────────────────────────────────────────────── */
  let lastId       = 0;
  let pollTimer    = null;
  let isAtBottom   = true;
  let sending      = false;
  let isDM         = CFG.isDM || false;
  let dmUserId     = CFG.dmUserId || 0;
  let dmUserName   = CFG.dmUserName || '';
  let deleteTarget = null;

  /* ── DOM ─────────────────────────────────────────────────── */
  const $id  = id => document.getElementById(id);
  const $msg = () => $id('msgMessages');

  /* ── Init ────────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', () => {
  if (!CFG.isDM) markGroupRead();
    const d = $id('topbarDate');
    if (d) d.textContent = new Date().toLocaleDateString('en-PH', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    const inp = $id('msgInput');
    if (inp) {
      inp.addEventListener('input', onInput);
      inp.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
      });
    }

    const pane = $msg();
    if (pane) {
      pane.addEventListener('scroll', () => {
        isAtBottom = pane.scrollHeight - pane.scrollTop - pane.clientHeight < 60;
      });
    }

    const delBtn = $id('msgDelConfirmBtn');
    if (delBtn) delBtn.addEventListener('click', () => {
      if (deleteTarget) execDelete(deleteTarget.id, deleteTarget.isDm);
      closeDeleteModal();
    });

    const overlay = $id('msgDeleteModal');
    if (overlay) overlay.addEventListener('click', e => {
      if (e.target === overlay) closeDeleteModal();
    });

    loadMessages(true);
    pollDMUnread();
  });

  /* ── Char counter ────────────────────────────────────────── */
  function onInput() {
    const len   = ($id('msgInput')?.value || '').length;
    const count = $id('msgCharCount');
    if (!count) return;
    count.textContent = len > 900 ? `${len}/1000` : '';
    count.style.color = len > 950 ? '#ef4444' : '#94a3b8';
  }

  /* ── Build API URL ───────────────────────────────────────── */
  function apiUrl(action, extra = '') {
    let url = `${BASE}&action=${action}`;
    if (isDM && dmUserId) url += `&dm_user=${dmUserId}`;
    if (extra) url += extra;
    return url;
  }

  /* ── Load messages ───────────────────────────────────────── */
  function loadMessages(initial = false) {
    clearTimeout(pollTimer);
    const url = initial
      ? apiUrl('history')
      : apiUrl('poll', `&since=${lastId}`);

    fetch(url, { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        if (!data.success) { scheduleNext(); return; }
        const msgs = data.messages || [];
        if (initial) {
          renderFull(msgs);
        } else {
          if (!msgs.length) { scheduleNext(); return; }
          appendMessages(msgs);
        }
      })
      .catch(() => {})
      .finally(scheduleNext);
  }

  function scheduleNext() {
    clearTimeout(pollTimer);
    pollTimer = setTimeout(() => loadMessages(false), POLL_MS);
  }

  /* ── Render full history ─────────────────────────────────── */
  function renderFull(msgs) {
    const container = $msg();
    if (!container) return;
    container.innerHTML = '';

    if (!msgs.length) {
      container.innerHTML = `
        <div class="msg-empty">
          <div class="msg-empty-icon"><i class="fas fa-${isDM ? 'user' : 'comments'}"></i></div>
          <div class="msg-empty-title">No messages yet</div>
          <div class="msg-empty-sub">Be the first to say something!</div>
        </div>`;
      scrollToBottom(true);
      return;
    }

    let prevUserId = null;
    msgs.forEach(msg => {
      const showAvatar = msg.sender_id !== prevUserId;
      container.appendChild(buildMsgEl(msg, showAvatar));
      prevUserId = msg.sender_id;
    });

    container.appendChild(buildEndMarker());
    lastId = msgs[msgs.length - 1].id;
    scrollToBottom(true);
  }

  /* ── Append new messages ─────────────────────────────────── */
  function appendMessages(msgs) {
    const container = $msg();
    if (!container) return;

    container.querySelector('.msg-empty')?.remove();
    container.querySelector('.msg-end-marker')?.remove();

    const rows     = container.querySelectorAll('.msg-row');
    const lastRow  = rows[rows.length - 1];
    let prevUserId = lastRow ? parseInt(lastRow.dataset.senderId || 0) : null;

    msgs.forEach(msg => {
      const showAvatar = msg.sender_id !== prevUserId;
      container.appendChild(buildMsgEl(msg, showAvatar));
      prevUserId = msg.sender_id;
    });

    container.appendChild(buildEndMarker());
    lastId = msgs[msgs.length - 1].id;
    scrollToBottom();
  }

  /* ── Build a message element ─────────────────────────────── */
  function buildMsgEl(msg, showAvatar) {
    const isOwn    = (msg.sender_id === CFG.userId || msg.mine);
    const canDel   = isOwn || CFG.isMod;
    const initials = (msg.name || '?').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();

    const avatarColors = ['av-green', 'av-teal', 'av-red', 'av-yellow', 'av-purple'];
    const colorIdx     = Math.abs(hashCode(msg.name || '')) % avatarColors.length;
    const colorClass   = avatarColors[colorIdx];

    let avatarHtml;
    if (msg.avatar) {
      avatarHtml = `<img src="${esc(msg.avatar)}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;" />`;
    } else {
      avatarHtml = esc(initials);
    }

    const roleClass = getRoleClass(msg.role);
    const timeStr   = fmtTime(msg.sent_at);

    const wrap = document.createElement('div');
    wrap.className = `msg-row${isOwn ? ' mine' : ''}${!showAvatar ? ' msg-no-avatar' : ''}`;
    wrap.dataset.id       = msg.id;
    wrap.dataset.senderId = msg.sender_id;

    if (isOwn) {
      wrap.innerHTML = `
        <div class="msg-body">
          <div class="msg-bubble-wrap">
            ${canDel ? `<button class="msg-del-btn" title="Delete" onclick="OM.confirmDelete(${msg.id}, ${isDM})"><i class="fas fa-trash-can"></i></button>` : ''}
            <div class="msg-bubble mine">${esc(msg.message)}</div>
            <div class="msg-avatar ${colorClass}" style="overflow:hidden;">${avatarHtml}</div>
          </div>
          <div class="msg-time">${timeStr}</div>
        </div>
      `;
    } else {
      wrap.innerHTML = `
        <div class="msg-avatar ${colorClass}" style="overflow:hidden;">${showAvatar ? avatarHtml : ''}</div>
        <div class="msg-body">
          ${showAvatar ? `
            <div class="msg-sender-row">
              <span class="msg-sender-name">${esc(msg.name)}</span>
              <span class="msg-role-pill ${roleClass}">${esc(msg.role || 'member')}</span>
              <span class="msg-time">${timeStr}</span>
            </div>` : ''}
          <div class="msg-bubble-wrap">
            <div class="msg-bubble theirs">${esc(msg.message)}</div>
            ${canDel ? `<button class="msg-del-btn" title="Delete" onclick="OM.confirmDelete(${msg.id}, ${isDM})"><i class="fas fa-trash-can"></i></button>` : ''}
          </div>
          ${!showAvatar ? `<div class="msg-time">${timeStr}</div>` : ''}
        </div>
      `;
    }
    return wrap;
  }

  function buildEndMarker() {
    const div = document.createElement('div');
    div.className = 'msg-end-marker';
    div.textContent = '— End of messages —';
    return div;
  }

  /* ── Send message ────────────────────────────────────────── */
  function sendMessage() {
    if (sending) return;
    const inp  = $id('msgInput');
    if (!inp) return;
    const text = inp.value.trim();
    if (!text) return;

    sending      = true;
    inp.disabled = true;

    const payload = { message: text };
    if (isDM && dmUserId) payload.dm_user = dmUserId;

    fetch(`${BASE}&action=send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          inp.value = '';
          $id('msgCharCount') && ($id('msgCharCount').textContent = '');
          clearTimeout(pollTimer);
          loadMessages(false);
        } else {
          showToast(data.error || 'Could not send.', 'error');
        }
      })
      .catch(() => showToast('Network error.', 'error'))
      .finally(() => {
        sending      = false;
        inp.disabled = false;
        inp.focus();
      });
  }

  /* ── Delete ──────────────────────────────────────────────── */
  function confirmDelete(id, isDmMsg) {
    deleteTarget = { id, isDm: !!isDmMsg };
    $id('msgDeleteModal')?.classList.add('open');
  }

  function closeDeleteModal() {
    $id('msgDeleteModal')?.classList.remove('open');
    deleteTarget = null;
  }

  function execDelete(id, isDmMsg) {
    fetch(`${BASE}&action=delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ id, is_dm: isDmMsg }),
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const el = document.querySelector(`.msg-row[data-id="${id}"]`);
          if (el) {
            el.style.transition = 'opacity .2s, transform .2s';
            el.style.opacity    = '0';
            el.style.transform  = 'translateX(16px)';
            setTimeout(() => el.remove(), 220);
          }
          showToast('Message deleted.', 'success');
        } else {
          showToast('Could not delete.', 'error');
        }
      })
      .catch(() => showToast('Network error.', 'error'));
  }

  /* ── Switch to group chat ────────────────────────────────── */
  function switchToGroup() {
    if (!isDM) return;
    isDM       = false;
    dmUserId   = 0;
    dmUserName = '';

    $id('chatTitle') && ($id('chatTitle').textContent = 'Group Chat');
    $id('chatSubtitle') && ($id('chatSubtitle').textContent = `All members · ${CFG.clubName}`);
    $id('msgInput') && ($id('msgInput').placeholder = 'Message the group…');

    const icon = document.querySelector('.msg-hash-icon i');
    if (icon) { icon.className = 'fas fa-hashtag'; }

    document.querySelectorAll('.msg-channel-item').forEach(el => el.classList.add('active'));
    document.querySelectorAll('.msg-member-item').forEach(el => el.classList.remove('dm-active'));

    lastId = 0;
    clearTimeout(pollTimer);
    $msg() && ($msg().innerHTML = '<div class="msg-spinner"><i class="fas fa-spinner fa-spin"></i> Loading…</div>');
    loadMessages(true);
    markGroupRead();
  }

  /* ── Open DM ─────────────────────────────────────────────── */
  function openDM(uid, name) {
    if (isDM && dmUserId === uid) return;
    isDM       = true;
    dmUserId   = uid;
    dmUserName = name;

    $id('chatTitle') && ($id('chatTitle').textContent = name);
    $id('chatSubtitle') && ($id('chatSubtitle').textContent = `Direct message · ${CFG.clubName}`);
    $id('msgInput') && ($id('msgInput').placeholder = `Message ${name.split(' ')[0]}…`);

    const icon = document.querySelector('.msg-hash-icon i');
    if (icon) { icon.className = 'fas fa-user'; }

    document.querySelectorAll('.msg-channel-item').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.msg-member-item').forEach(el => {
      el.classList.toggle('dm-active', parseInt(el.dataset.uid) === uid);
    });

    fetch(`${BASE}&action=dm_mark_read&from=${uid}`, { credentials: 'same-origin' });
    const badge = $id(`dmBadge-${uid}`);
    if (badge) badge.style.display = 'none';

    lastId = 0;
    clearTimeout(pollTimer);
    $msg() && ($msg().innerHTML = '<div class="msg-spinner"><i class="fas fa-spinner fa-spin"></i> Loading…</div>');
    loadMessages(true);
  }

  /* ── Poll DM unread badges ───────────────────────────────── */
  function pollDMUnread() {
    fetch(`${BASE}&action=dm_unread`, { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        if (!data.success) return;
        const map = data.unread || {};
        Object.entries(map).forEach(([uid, cnt]) => {
          const b = $id(`dmBadge-${uid}`);
          if (b) {
            b.textContent   = cnt > 9 ? '9+' : cnt;
            b.style.display = cnt > 0 ? 'inline-flex' : 'none';
          }
        });
      })
      .catch(() => {})
      .finally(() => setTimeout(pollDMUnread, 8000));
  }

  /* ── Club switcher ───────────────────────────────────────── */
  function toggleClubSwitcher(e) {
    e.stopPropagation();
    $id('clubSwitcher')?.classList.toggle('open');
  }
  document.addEventListener('click', e => {
    const sw = $id('clubSwitcher');
    if (sw && !sw.contains(e.target)) sw.classList.remove('open');
  });

  /* ── Scroll ──────────────────────────────────────────────── */
  function scrollToBottom(force = false) {
    const el = $msg();
    if (!el) return;
    if (force || isAtBottom) el.scrollTop = el.scrollHeight;
  }

  /* ── Toast ───────────────────────────────────────────────── */
  function showToast(msg, type = 'info') {
    const t = $id('toast');
    if (!t) return;
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
    t.className = `toast toast-${type} show`;
    t.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i> ${msg}`;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 3200);
  }

  /* ── Helpers ─────────────────────────────────────────────── */
  function fmtTime(dateStr) {
    return new Date(dateStr).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
  }

  function getRoleClass(role) {
    if (!role) return 'member';
    const r = role.toLowerCase().trim();
    if (r === 'vice president') return 'vp';
    return r;
  }

  function hashCode(str) {
    let h = 0;
    for (let i = 0; i < str.length; i++) h = (Math.imul(31, h) + str.charCodeAt(i)) | 0;
    return h;
  }

  function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
  }

  /* ── Public API ──────────────────────────────────────────── */
  return {
    sendMessage,
    confirmDelete,
    closeDeleteModal,
    switchToGroup,
    openDM,
    toggleClubSwitcher,
    showToast,
  };


  /* ── Mark group chat read ──────────────────────────────── */
  function markGroupRead() {
    fetch(`${BASE}&action=mark_group_read`, { method: 'POST', credentials: 'same-origin' });
    const b = document.getElementById('groupChatBadge');
    if (b) b.style.display = 'none';
  }
})();
/* ── Notifications ───────────────────────────────────────── */
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
    const res  = await fetch(`index.php?page=officer_messages&club_id=${CFG.clubId}&action=notif_list`);
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
  await fetch(`index.php?page=officer_messages&club_id=${CFG.clubId}&action=notif_read&id=${id}`);
  if (link) window.location = link;
}

async function markAllRead() {
  await fetch(`index.php?page=officer_messages&club_id=${CFG.clubId}&action=notif_read_all`);
  loadNotifications();
}

document.addEventListener('click', e => {
  const panel = document.getElementById('notifPanel');
  if (notifPanelOpen && panel && !panel.contains(e.target) && !e.target.closest('.icon-btn')) {
    notifPanelOpen = false;
    panel.style.display = 'none';
  }
});

/* ── Mark group chat read ────────────────────────────────── */
function markGroupRead() {
  fetch(`${BASE}&action=mark_group_read`, { method: 'POST', credentials: 'same-origin' });
  const b = document.getElementById('groupChatBadge');
  if (b) b.style.display = 'none';
}
