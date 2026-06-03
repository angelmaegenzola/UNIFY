<?php require_once __DIR__ . '/../../app/controllers/events_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>UNIFY — Events</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/events.css" />
  <link rel="stylesheet" href="/assets/css/transitions.css" />
</head>

<body>
  <div class="app">

    <!-- ── Sidebar ── -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    <aside class="sidebar" id="mainSidebar">
      <div class="sidebar-brand">
        <img src="/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
        <div class="brand-text">
          <div class="brand-name">UNIFY</div>
          <div class="brand-tagline">Club Management System</div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">MAIN MENU</div>
        <a href="index.php?page=dashboard" class="nav-item"><i class="fas fa-house"></i><span>Dashboard</span></a>
        <a href="index.php?page=members" class="nav-item"><i class="fas fa-users"></i><span>Members</span></a>
        <a href="index.php?page=clubpage" class="nav-item "><i
            class="fas fa-building-columns"></i><span>Clubs</span></a>
        <a href="index.php?page=events" class="nav-item active"><i
            class="fas fa-calendar-days"></i><span>Events</span></a>
        <div class="nav-section-label">REPORTS</div>
        <a href="index.php?page=reports" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
      </nav>
      <div class="sidebar-bottom">
        <div class="sidebar-profile">
          <div class="profile-avatar-wrap">
            <?php if (!empty($avatar_url)): ?><img src="<?= $avatar_url ?>" alt="Avatar"
                class="profile-avatar-img" /><?php else: ?><span
                class="profile-avatar-fallback"><?= $adminInitial ?></span><?php endif; ?>
            <span class="profile-online-dot"></span>
          </div>
          <a href="index.php?page=adminprofile" class="profile-link">
            <div class="profile-info">
              <span class="profile-name"><?= htmlspecialchars($adminName) ?></span>
              <span class="profile-role">Club Admin</span>
            </div>
          </a>
          <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i
              class="fas fa-arrow-right-from-bracket"></i></a>
          <a href="#" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
        </div>
      </div>
    </aside>

    <!-- ── Main ── -->
    <main class="main">

      <!-- Topbar -->
      <header class="topbar">
      <div class="topbar-left">
        <button class="topbar-hamburger" onclick="toggleSidebar()" title="Menu">
          <img src="/assets/pictures/unifylogo.png" alt="Menu" class="topbar-logo-btn" />
        </button>
        <div class="topbar-title-group">
          <span class="topbar-page-title">Events</span>
          <span class="topbar-date" id="topbarDate"></span>
<script>const _d=new Date();document.getElementById("topbarDate").textContent=_d.toLocaleDateString("en-US",{weekday:"long",year:"numeric",month:"long",day:"numeric"});</script>
        </div>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" id="topbarSearch" placeholder="Search clubs, events, announcements…"/>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Sync" onclick="syncPage()"><i class="fas fa-rotate"></i></button>
        <button class="icon-btn" id="notifBtn" title="Notifications" onclick="toggleNotif(event)">
          <i class="fas fa-bell"></i>
          <span class="badge red hidden" id="notifBadge">0</span>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-header">
            <span class="notif-header-title"><i class="fas fa-bell"></i> Notifications</span>
            <button class="notif-mark-btn" onclick="clearNotifs()">Mark all read</button>
          </div>
          <div class="notif-list" id="notifList">
            <div class="notif-item">
              <div class="notif-content"><span class="notif-text">No new notifications.</span></div>
            </div>
          </div>
          <div class="notif-footer">Only showing recent notifications</div>
        </div>
        <a href="index.php?page=studentprofile" class="topbar-profile" title="View Profile">
          <div class="topbar-avatar">
            <?php if (!empty($avatar_url)): ?>
              <img src="<?= $avatar_url ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" />
            <?php else: ?>
              <?= $avatar ?>
            <?php endif; ?>
          </div>
          <div class="topbar-profile-info">
            <span class="tp-name"><?= htmlspecialchars($adminName) ?></span>
            <span class="tp-role"><?= isset($my_role) ? ucfirst($my_role) : 'Student' ?></span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </a>
      </div>
    </header>

      <div class="content">

        <!-- Stat Cards -->
        <div class="stat-cards-grid">
          <div class="stat-card-new sc-green">
            <div class="sc-top">
              <div class="sc-icon-wrap"><i class="fas fa-calendar-days"></i></div>
            </div>
            <div class="sc-value"><?= $totalEvents ?></div>
            <div class="sc-label">Total Events</div>
          </div>
          <div class="stat-card-new sc-teal">
            <div class="sc-top">
              <div class="sc-icon-wrap"><i class="fas fa-calendar-plus"></i></div>
            </div>
            <div class="sc-value"><?= $upcomingEvents ?></div>
            <div class="sc-label">Upcoming</div>
          </div>
          <div class="stat-card-new sc-yellow">
            <div class="sc-top">
              <div class="sc-icon-wrap"><i class="fas fa-circle-play"></i></div>
            </div>
            <div class="sc-value"><?= $ongoingEvents ?></div>
            <div class="sc-label">Ongoing</div>
          </div>
          <div class="stat-card-new sc-red">
            <div class="sc-top">
              <div class="sc-icon-wrap"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="sc-value"><?= $completedEvents ?></div>
            <div class="sc-label">Completed</div>
          </div>
        </div>

        <!-- Toolbar -->
        <div class="events-toolbar">
          <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All Events</button>
            <button class="filter-tab" data-filter="today">Today</button>
            <button class="filter-tab" data-filter="upcoming">Upcoming</button>
            <button class="filter-tab" data-filter="completed">Past</button>
          </div>
          <div class="toolbar-right">
            <button class="date-range-btn" id="dateRangeBtn" onclick="toggleDatePicker()">
              <i class="fas fa-calendar"></i>
              <span><?= date('M j') ?> – <?= date('M j', strtotime('+14 days')) ?>, <?= date('Y') ?></span>
              <i class="fas fa-chevron-down" style="font-size:9px;"></i>
            </button>
            <button class="add-event-btn" id="addEventBtn"><i class="fas fa-plus"></i> Add Event</button>
          </div>
        </div>

        <!-- Events Body -->
        <div class="events-body">

          <!-- Left: Event Cards (rendered by JS) -->
          <div class="events-main-col" id="eventsMainCol"></div>

          <!-- Right: Calendar + Approvals -->
          <div class="events-side-col">

            <!-- Mini Calendar -->
            <div class="card">
              <div class="card-header">
                <div class="calendar-nav">
  <span class="cal-month-label" id="calMonthLabel"></span>
  <button class="cal-nav-btn" id="calPrev"><i class="fas fa-chevron-left"></i></button>
  <button class="cal-nav-btn" id="calNext"><i class="fas fa-chevron-right"></i></button>
</div>
              </div>
              <div class="mini-calendar">
                <div class="cal-weekdays">
                  <?php foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $d): ?>
                    <div class="cal-wd"><?= $d ?></div>
                  <?php endforeach; ?>
                </div>
                <div class="cal-days" id="calDays"></div>
              </div>
            </div>

            <!-- Pending Approvals -->
            <div class="card approvals-card">
              <div class="card-header">
                <h2>Pending Approvals</h2>
                <span class="approval-badge-count" id="pendingCount">0 pending</span>
              </div>
              <div class="approvals-scroll" id="approvalsScroll">
                <div class="no-approvals"><i class="fas fa-check-circle"></i>No pending approvals</div>
              </div>
            </div>

          </div><!-- .events-side-col -->
        </div><!-- .events-body -->
      </div><!-- .content -->
    </main>
  </div><!-- .app -->

  <!-- ── Add / Edit Event Modal ── -->
  <div id="event-modal" class="modal-overlay" onclick="closeModalOverlay(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3 id="event-modal-title" class="modal-heading">Add Event</h3>
        <button class="modal-close" onclick="closeEventModal()"><i class="fas fa-times"></i></button>
      </div>
      <form id="event-form" onsubmit="saveEvent(event)">
        <div class="modal-body">
          <div class="form-group">
            <label>Club <span class="req">*</span></label>
            <div class="custom-select-wrap" id="efClubWrap" style="position:relative;">
              <button type="button" class="custom-select-btn" id="efClubBtn" onclick="toggleEvtDrop('efClub',event)">Select club</button>
              <input type="hidden" id="ef-club" required />
              <div class="custom-select-list" id="efClubList">
                <div class="custom-select-option" onclick="setEvtDrop('efClub','','Select club')">Select club</div>
                <?php foreach ($clubs as $c): ?>
                  <div class="custom-select-option" onclick="setEvtDrop('efClub','<?= $c['id'] ?>','<?= htmlspecialchars($c['name']) ?>')"><?= htmlspecialchars($c['name']) ?></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Event Name <span class="req">*</span></label>
            <input type="text" id="ef-name" placeholder="e.g. Tech Summit 2026" required />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea id="ef-desc" rows="3" placeholder="Brief description…"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Date <span class="req">*</span></label>
              <div class="custom-select-wrap" id="efDateWrap" style="position:relative;">
              <button type="button" class="custom-select-btn" id="efDateBtn" onclick="toggleEvtDrop('efDate',event)">Select date</button>
              <input type="hidden" id="ef-date" required />
              <div class="custom-select-list" id="efDateList" style="padding:12px;min-width:260px;">
                <div id="efCalendar"></div>
              </div>
            </div>
            </div>
            <div class="form-group">
              <label>Status</label>
              <div class="custom-select-wrap" id="efStatusWrap" style="position:relative;">
                <button type="button" class="custom-select-btn" id="efStatusBtn" onclick="toggleEvtDrop('efStatus',event)">Upcoming</button>
                <input type="hidden" id="ef-status" value="upcoming" />
                <div class="custom-select-list" id="efStatusList">
                  <div class="custom-select-option selected" onclick="setEvtDrop('efStatus','upcoming','Upcoming')">Upcoming</div>
                  <div class="custom-select-option" onclick="setEvtDrop('efStatus','ongoing','Ongoing')">Ongoing</div>
                  <div class="custom-select-option" onclick="setEvtDrop('efStatus','completed','Completed')">Completed</div>
                  <div class="custom-select-option" onclick="setEvtDrop('efStatus','cancelled','Cancelled')">Cancelled</div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Start Time</label>
              <div class="custom-select-wrap" id="efStartWrap" style="position:relative;">
              <button type="button" class="custom-select-btn" id="efStartBtn" onclick="toggleEvtDrop('efStart',event)">Select time</button>
              <input type="hidden" id="ef-start" />
              <div class="custom-select-list" id="efStartList" style="padding:8px;min-width:160px;">
                <div class="time-picker" id="efStartPicker"></div>
              </div>
            </div>
            </div>
            <div class="form-group">
              <label>End Time</label>
              <div class="custom-select-wrap" id="efEndWrap" style="position:relative;">
              <button type="button" class="custom-select-btn" id="efEndBtn" onclick="toggleEvtDrop('efEnd',event)">Select time</button>
              <input type="hidden" id="ef-end" />
              <div class="custom-select-list" id="efEndList" style="padding:8px;min-width:160px;">
                <div class="time-picker" id="efEndPicker"></div>
              </div>
            </div>
            </div>
          </div>
          <div class="form-group">
            <label>Location</label>
            <input type="text" id="ef-location" placeholder="e.g. Main Auditorium" onfocus="document.querySelectorAll('.custom-select-list').forEach(el=>el.classList.remove('open'))" />
          </div>
          <p id="ef-error" class="form-error" style="display:none;"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel-modal" onclick="closeEventModal()">Cancel</button>
          <button type="submit" class="btn-save" id="ef-submit"><i class="fas fa-plus"></i> Save Event</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ── Delete Confirm Modal ── -->
  <div id="delete-modal" class="modal-overlay" onclick="closeModalOverlay(event)">
    <div class="modal-box modal-box-sm" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3 class="modal-heading">Delete Event</h3>
        <button class="modal-close" onclick="closeDeleteModal()"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body" style="padding-top:8px;">
        <div class="delete-icon-wrap"><i class="fas fa-trash-can"></i></div>
        <p class="delete-msg">Delete <strong id="delete-event-name"></strong>?<br>This cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel-modal" onclick="closeDeleteModal()">Cancel</button>
        <button type="button" class="btn-delete-confirm" onclick="confirmDeleteExec()"><i class="fas fa-trash"></i>
          Delete</button>
      </div>
    </div>
  </div>

  <!-- ── Approve Event Modal ── -->
  <div id="evtApproveModal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box modal-box-sm" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3 class="modal-heading">Approve Event</h3>
        <button class="modal-close" onclick="document.getElementById('evtApproveModal').classList.remove('open')"><i
            class="fas fa-times"></i></button>
      </div>
      <div style="text-align:center;padding:10px 0 14px;">
        <div
          style="width:54px;height:54px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:24px;color:#16a34a;margin:0 auto 12px;">
          <i class="fas fa-calendar-check"></i>
        </div>
        <div style="font-size:15px;font-weight:800;color:#0d3320;margin-bottom:6px;" id="evtApproveNameLabel">—</div>
        <p style="font-size:12.5px;color:#7aaa85;line-height:1.6;">Approving this event will set it as
          <strong>Upcoming</strong> and notify club members.</p>
      </div>
      <div class="modal-footer">
        <button class="btn-cancel-modal"
          onclick="document.getElementById('evtApproveModal').classList.remove('open')">Cancel</button>
        <button class="btn-save" onclick="confirmApproveEvent()" style="background:#16a34a;color:#fff;border:none;">
          <i class="fas fa-check-circle"></i> Approve
        </button>
      </div>
    </div>
  </div>

  <!-- ── Reject Event Modal ── -->
  <div id="evtRejectModal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box modal-box-sm" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3 class="modal-heading">Reject Event</h3>
        <button class="modal-close" onclick="document.getElementById('evtRejectModal').classList.remove('open')"><i
            class="fas fa-times"></i></button>
      </div>
      <div class="modal-body" style="padding-top:8px;">
        <div
          style="width:54px;height:54px;border-radius:50%;background:#fdecea;display:flex;align-items:center;justify-content:center;font-size:24px;color:#b91c1c;margin:0 auto 14px;">
          <i class="fas fa-calendar-xmark"></i>
        </div>
        <p class="delete-msg" style="margin-bottom:16px;">
          Reject <strong id="evtRejectNameLabel">—</strong>?<br>
          <span style="font-size:12px;color:#7aaa85;">Officers will be notified.</span>
        </p>
        <div class="form-group">
          <label style="font-size:11.5px;font-weight:700;color:#3a6a45;">
            Reason <span style="color:#7aaa85;font-weight:400;">(optional)</span>
          </label>
          <textarea id="evtRejectNote" rows="3"
            placeholder="e.g. Missing required details, conflict with another event…"
            style="border:1.5px solid #d0e8d8;border-radius:10px;padding:9px 13px;font-family:inherit;font-size:13px;color:#0d3320;outline:none;background:#fff;resize:none;width:100%;margin-top:4px;box-sizing:border-box;"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-cancel-modal"
          onclick="document.getElementById('evtRejectModal').classList.remove('open')">Cancel</button>
        <button class="btn-delete-confirm" onclick="confirmRejectEvent()">
          <i class="fas fa-times-circle"></i> Confirm Rejection
        </button>
      </div>
    </div>
  </div>

  <div id="toast" class="toast"></div>

  <!-- DB data bridge: PHP injects here, events.js reads window.DB_EVENTS -->
  <script>
    window.DB_EVENTS = <?= json_encode(array_map(fn($e) => [
      'id' => (int) $e['id'],
      'club_id' => (int) $e['club_id'],
      'club_name' => $e['club_name'],
      'club_acronym' => $e['club_acronym'],
      'name' => $e['name'],
      'description' => $e['description'] ?? '',
      'event_date' => $e['event_date'],
      'start_time' => $e['start_time'] ?? '',
      'end_time' => $e['end_time'] ?? '',
      'location' => $e['location'] ?? '',
      'status' => $e['status'],
    ], $dbEvents), JSON_HEX_TAG) ?>;
  </script>
  <script src="/assets/javascripts/events.js"></script>

<script>
function preventScroll(e) { e.preventDefault(); }
function toggleSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const isOpen = sidebar.classList.toggle('open');
  overlay.classList.toggle('open', isOpen);
  document.body.classList.toggle('sidebar-open', isOpen);
}
function closeSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  sidebar.classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.body.classList.remove('sidebar-open');
}


</script>

<button class="fab-menu-btn" id="fabMenuBtn" onclick="toggleSidebar()" title="Menu">
  <i class="fas fa-bars"></i>
</button>
<!-- Date Range Picker -->
<div id="datePickerDropdown" style="display:none;position:absolute;z-index:9999;background:var(--card-bg);border:1.5px solid var(--border);border-radius:var(--radius);box-shadow:0 8px 32px rgba(13,43,26,.18);padding:20px;width:288px;">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);">
    <div style="width:32px;height:32px;border-radius:8px;background:var(--green-light);display:flex;align-items:center;justify-content:center;">
      <i class="fas fa-calendar" style="color:var(--green-accent);font-size:13px;"></i>
    </div>
    <div>
      <div style="font-weight:700;font-size:13px;color:var(--text-dark);">Filter by Date Range</div>
      <div style="font-size:11px;color:var(--text-light);">Select start and end dates</div>
    </div>
  </div>
  <div style="display:flex;flex-direction:column;gap:12px;">
    <div style="position:relative;">
      <div style="font-size:10px;font-weight:700;color:var(--green-accent);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">From</div>
      <button type="button" id="dateFromBtn" onclick="toggleDrCal('from',event)" style="width:100%;padding:10px 12px;background:#fff;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:inherit;font-size:13px;font-weight:600;color:var(--text-dark);cursor:pointer;text-align:left;display:flex;align-items:center;justify-content:space-between;">
        <span>Select date</span>
        <i class="fas fa-calendar" style="font-size:12px;opacity:0.5;"></i>
      </button>
      <input type="hidden" id="dateFrom" />
      <div id="dateFromCal" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:12px;min-width:260px;z-index:3000;box-shadow:0 8px 24px rgba(0,0,0,0.12);"></div>
    </div>
    <div style="display:flex;justify-content:center;">
      <i class="fas fa-arrow-down" style="color:var(--text-light);font-size:11px;"></i>
    </div>
    <div style="position:relative;">
      <div style="font-size:10px;font-weight:700;color:var(--green-accent);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">To</div>
      <button type="button" id="dateToBtn" onclick="toggleDrCal('to',event)" style="width:100%;padding:10px 12px;background:#fff;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:inherit;font-size:13px;font-weight:600;color:var(--text-dark);cursor:pointer;text-align:left;display:flex;align-items:center;justify-content:space-between;">
        <span>Select date</span>
        <i class="fas fa-calendar" style="font-size:12px;opacity:0.5;"></i>
      </button>
      <input type="hidden" id="dateTo" />
      <div id="dateToCal" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:12px;min-width:260px;z-index:3000;box-shadow:0 8px 24px rgba(0,0,0,0.12);"></div>
    </div>
    <div style="display:flex;gap:8px;margin-top:4px;">
      <button onclick="applyDateRange()" style="flex:1;padding:10px;background:var(--green-dark);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:700;font-size:12.5px;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px;"><i class="fas fa-check"></i> Apply</button>
      <button onclick="clearDateRange()" style="flex:1;padding:10px;background:transparent;color:var(--text-mid);border:1.5px solid var(--border);border-radius:var(--radius-sm);font-weight:600;font-size:12.5px;cursor:pointer;font-family:inherit;">Clear</button>
    </div>
  </div>
</div>
<script>
function toggleDatePicker() {
  var dd = document.getElementById('datePickerDropdown');
  var btn = document.getElementById('dateRangeBtn');
  if (dd.style.display === 'none') {
    var rect = btn.getBoundingClientRect();
    var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    dd.style.top = (rect.bottom + scrollTop + 8) + 'px';
    dd.style.left = Math.min(rect.left, window.innerWidth - 296) + 'px';
    dd.style.display = 'block';
  } else {
    dd.style.display = 'none';
  }
}
function applyDateRange() {
  var from = document.getElementById('dateFrom').value;
  var to   = document.getElementById('dateTo').value;
  if (!from || !to) { alert('Please select both dates.'); return; }
  window.dateRangeStart = new Date(from + 'T00:00:00');
  window.dateRangeEnd   = new Date(to + 'T23:59:59');
  if (typeof dateRangeStart !== 'undefined') { dateRangeStart = window.dateRangeStart; dateRangeEnd = window.dateRangeEnd; }
  var fmt = function(s) { var d = new Date(s+'T00:00:00'); return d.toLocaleDateString('en-US',{month:'short',day:'numeric'}); };
  document.getElementById('dateRangeBtn').querySelector('span').textContent = fmt(from) + ' – ' + fmt(to) + ', ' + new Date(from).getFullYear();
  document.getElementById('datePickerDropdown').style.display = 'none';
  if (typeof renderEvents === 'function') renderEvents();
}
function clearDateRange() {
  window.dateRangeStart = null; window.dateRangeEnd = null;
  if (typeof dateRangeStart !== 'undefined') { dateRangeStart = null; dateRangeEnd = null; }
  document.getElementById('dateFrom').value = '';
  document.getElementById('dateTo').value = '';
  var now = new Date(); var end = new Date(); end.setDate(end.getDate()+14);
  var fmt = function(d) { return d.toLocaleDateString('en-US',{month:'short',day:'numeric'}); };
  document.getElementById('dateRangeBtn').querySelector('span').textContent = fmt(now) + ' – ' + fmt(end) + ', ' + now.getFullYear();
  document.getElementById('datePickerDropdown').style.display = 'none';
  if (typeof renderEvents === 'function') renderEvents();
}
document.addEventListener('click', function(e) {
  var dd = document.getElementById('datePickerDropdown');
  var btn = document.getElementById('dateRangeBtn');
  if (dd && !dd.contains(e.target) && btn && !btn.contains(e.target)) dd.style.display = 'none';
});
</script>
<script>
function toggleEvtDrop(id, e) {
  e.stopPropagation();
  const list = document.getElementById(id + 'List');
  const btn  = document.getElementById(id + 'Btn');
  const isOpen = list.classList.contains('open');
  document.querySelectorAll('.custom-select-list.open').forEach(el => el.classList.remove('open'));
  document.querySelectorAll('.custom-select-btn.open').forEach(el => el.classList.remove('open'));
  if (!isOpen) { list.classList.add('open'); btn.classList.add('open'); }
}
function setEvtDrop(id, val, label) {
  document.getElementById(id).value = val;
  document.getElementById(id + 'Btn').textContent = label;
  document.getElementById(id + 'List').classList.remove('open');
  document.getElementById(id + 'Btn').classList.remove('open');
  document.querySelectorAll('#' + id + 'List .custom-select-option').forEach(o => {
    o.classList.toggle('selected', o.textContent.trim() === label);
  });
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.custom-select-wrap')) {
    document.querySelectorAll('.custom-select-list.open').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.custom-select-btn.open').forEach(el => el.classList.remove('open'));
  }
});
</script>
<script>
/* ── Custom Calendar ── */
(function(){
  let efCal = { year: new Date().getFullYear(), month: new Date().getMonth() };
  const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const DAYS = ['Su','Mo','Tu','We','Th','Fr','Sa'];

  function renderCal() {
    const { year, month } = efCal;
    const today = new Date();
    const first = new Date(year, month, 1).getDay();
    const days  = new Date(year, month+1, 0).getDate();
    const sel   = document.getElementById('ef-date').value;
    let html = `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
      <button type="button" onclick="efCalPrev()" style="background:none;border:none;cursor:pointer;font-size:16px;color:var(--green-dark);">&#8249;</button>
      <span style="font-size:13px;font-weight:700;color:var(--text-dark);">${MONTHS[month]} ${year}</span>
      <button type="button" onclick="efCalNext()" style="background:none;border:none;cursor:pointer;font-size:16px;color:var(--green-dark);">&#8250;</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;">`;
    DAYS.forEach(d => { html += `<div style="font-size:10px;font-weight:700;color:var(--text-mid);padding:4px 0;">${d}</div>`; });
    for(let i=0;i<first;i++) html += '<div></div>';
    for(let d=1;d<=days;d++){
      const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const isToday = d===today.getDate()&&month===today.getMonth()&&year===today.getFullYear();
      const isSel   = dateStr===sel;
      html += `<div onclick="efCalPick('${dateStr}','${d} ${MONTHS[month]} ${year}')" style="
        cursor:pointer;border-radius:6px;padding:5px 2px;font-size:12px;
        background:${isSel?'var(--green-dark)':isToday?'#e4f0e8':'transparent'};
        color:${isSel?'#fff':isToday?'var(--green-dark)':'var(--text-dark)'};
        font-weight:${isSel||isToday?'700':'400'};
      ">${d}</div>`;
    }
    html += '</div>';
    document.getElementById('efCalendar').innerHTML = html;
  }
  window.efCalPrev = function(){ if(efCal.month===0){efCal.month=11;efCal.year--;}else efCal.month--; renderCal(); }
  window.efCalNext = function(){ if(efCal.month===11){efCal.month=0;efCal.year++;}else efCal.month++; renderCal(); }
  window.efCalPick = function(val, label){
    document.getElementById('ef-date').value = val;
    document.getElementById('efDateBtn').textContent = label;
    document.getElementById('efDateList').classList.remove('open');
    document.getElementById('efDateBtn').classList.remove('open');
    renderCal();
  }
  document.addEventListener('DOMContentLoaded', renderCal);
  window._renderEfCal = renderCal;
})();

/* ── Custom Time Picker ── */
function buildTimePicker(pickerId, hiddenId, btnId, listId) {
  const wrap = document.getElementById(pickerId);
  if (!wrap) return;
  let selH = null, selM = null, selP = 'am';
  function render() {
    const hours   = Array.from({length:12},(_,i)=>String(i+1).padStart(2,'0'));
    const minutes = ['00','05','10','15','20','25','30','35','40','45','50','55'];
    let html = `<div style="display:flex;gap:6px;max-height:180px;">
      <div style="flex:1;overflow-y:auto;scrollbar-width:none;">`;
    hours.forEach(h => {
      const sel = h===selH;
      html += `<div onclick="efTimePick('${pickerId}','${hiddenId}','${btnId}','${listId}','h','${h}')" style="
        padding:6px 10px;border-radius:6px;cursor:pointer;font-size:12px;text-align:center;
        background:${sel?'var(--green-dark)':'transparent'};
        color:${sel?'#fff':'var(--text-dark)'};font-weight:${sel?'700':'400'};
      ">${h}</div>`;
    });
    html += `</div><div style="flex:1;overflow-y:auto;scrollbar-width:none;">`;
    minutes.forEach(m => {
      const sel = m===selM;
      html += `<div onclick="efTimePick('${pickerId}','${hiddenId}','${btnId}','${listId}','m','${m}')" style="
        padding:6px 10px;border-radius:6px;cursor:pointer;font-size:12px;text-align:center;
        background:${sel?'var(--green-dark)':'transparent'};
        color:${sel?'#fff':'var(--text-dark)'};font-weight:${sel?'700':'400'};
      ">${m}</div>`;
    });
    html += `</div><div style="display:flex;flex-direction:column;gap:4px;">`;
    ['am','pm'].forEach(p => {
      const sel = p===selP;
      html += `<div onclick="efTimePick('${pickerId}','${hiddenId}','${btnId}','${listId}','p','${p}')" style="
        padding:6px 10px;border-radius:6px;cursor:pointer;font-size:12px;text-align:center;
        background:${sel?'var(--green-dark)':'transparent'};
        color:${sel?'#fff':'var(--text-dark)'};font-weight:${sel?'700':'400'};
      ">${p}</div>`;
    });
    html += '</div></div>';
    wrap.innerHTML = html;
    wrap.querySelectorAll('div[style*="scrollbar-width"]').forEach(el => { el.style.setProperty('scrollbar-width','none'); });
  }
  wrap._state = { get h(){return selH;}, get m(){return selM;}, get p(){return selP;},
    set h(v){selH=v;}, set m(v){selM=v;}, set p(v){selP=v;}, render };
  render();
}

window.efTimePick = function(pickerId, hiddenId, btnId, listId, type, val) {
  const s = document.getElementById(pickerId)._state;
  s[type] = val;
  s.render();
  if (s.h && s.m) {
    const label = `${s.h}:${s.m} ${s.p}`;
    document.getElementById(hiddenId).value = label;
    document.getElementById(btnId).textContent = label;
  }
}

document.addEventListener('DOMContentLoaded', function(){
  buildTimePicker('efStartPicker','ef-start','efStartBtn','efStartList');
  buildTimePicker('efEndPicker','ef-end','efEndBtn','efEndList');
  // re-render cal when date dropdown opens
  const origToggle = window.toggleEvtDrop;
  window.toggleEvtDrop = function(id, e) {
    origToggle(id, e);
    if (id === 'efDate') window._renderEfCal && window._renderEfCal();
  }
});
</script>

<script>
(function(){
  const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const DAYS = ['Su','Mo','Tu','We','Th','Fr','Sa'];
  let drCal = { from: null, to: null };

  function renderCal(which) {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    const first = new Date(year, month, 1).getDay();
    const days = new Date(year, month+1, 0).getDate();
    const sel = document.getElementById('date' + (which==='from'?'From':'To')).value;
    
    let html = `<div style="margin-bottom:8px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
        <button type="button" onclick="window.drCalPrev('${which}')" style="background:none;border:none;cursor:pointer;font-size:14px;color:var(--green-dark);padding:4px;">‹</button>
        <span style="font-size:12px;font-weight:700;color:var(--text-dark);">${MONTHS[month]} ${year}</span>
        <button type="button" onclick="window.drCalNext('${which}')" style="background:none;border:none;cursor:pointer;font-size:14px;color:var(--green-dark);padding:4px;">›</button>
      </div>
      <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;">`;
    
    DAYS.forEach(d => html += `<div style="font-size:10px;font-weight:700;color:var(--text-mid);padding:4px;">${d}</div>`);
    for(let i=0;i<first;i++) html += '<div></div>';
    for(let d=1;d<=days;d++){
      const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const isSel = dateStr===sel;
      html += `<button type="button" onclick="window.drPickDate('${which}','${dateStr}')" style="
        padding:6px 2px;border:none;border-radius:6px;font-size:11px;cursor:pointer;
        background:${isSel?'var(--green-dark)':'transparent'};
        color:${isSel?'#fff':'var(--text-dark)'};
        font-weight:${isSel?'700':'400'};
      ">${d}</button>`;
    }
    html += '</div></div>';
    document.getElementById('date' + (which==='from'?'From':'To') + 'Cal').innerHTML = html;
  }

  window.toggleDrCal = function(which, e) {
    e.stopPropagation();
    const cal = document.getElementById('date' + (which==='from'?'From':'To') + 'Cal');
    document.getElementById('dateFromCal').style.display = 'none';
    document.getElementById('dateToCal').style.display = 'none';
    cal.style.display = 'block';
    renderCal(which);
  }

  window.drCalPrev = function(which) {
    renderCal(which);
  }

  window.drCalNext = function(which) {
    renderCal(which);
  }

  window.drPickDate = function(which, val) {
    const field = document.getElementById('date' + (which==='from'?'From':'To'));
    const btn = document.getElementById('date' + (which==='from'?'From':'To') + 'Btn');
    field.value = val;
    const d = new Date(val);
    btn.innerHTML = `<span>${d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})}</span><i class="fas fa-calendar" style="font-size:12px;opacity:0.5;"></i>`;
    document.getElementById('date' + (which==='from'?'From':'To') + 'Cal').style.display = 'none';
  }

  document.addEventListener('click', function(e) {
    if (!e.target.closest('[id*="Cal"], [id*="Btn"]')) {
      document.getElementById('dateFromCal').style.display = 'none';
      document.getElementById('dateToCal').style.display = 'none';
    }
  });
})();
</script>
</body>

</html>
<script>
function syncPage() {
  const icon = document.querySelector('.icon-btn .fa-rotate');
  if (icon) {
    icon.style.transition = 'transform 0.5s ease';
    icon.style.transform = 'rotate(360deg)';
    setTimeout(() => { icon.style.transform = ''; }, 500);
  }
  setTimeout(() => { window.location.href = window.location.pathname + '?v=' + Date.now(); }, 500);
}
</script>
