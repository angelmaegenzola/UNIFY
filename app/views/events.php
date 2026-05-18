<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/events_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>UNIFY — Events</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/unify/assets/css/events.css" />
  <link rel="stylesheet" href="/unify/assets/css/transitions.css" />
</head>

<body>
  <div class="app">

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="/unify/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
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
          <span class="topbar-page-title">Events</span>
          <span class="topbar-date"><?= date('l, F j, Y') ?></span>
        </div>
        <div class="topbar-center">
          <div class="topbar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="topbarSearch" placeholder="Search events, clubs…" />
          </div>
        </div>
        <div class="topbar-actions">
          <button class="icon-btn"><i class="fas fa-bell"></i></button>
          <div class="topbar-profile">
            <div class="topbar-avatar">
              <?php if (!empty($avatar_url)): ?>
                <img src="<?= $avatar_url ?>" alt="Avatar" class="topbar-avatar-img" />
              <?php else: ?>
                <?= $adminInitial ?>
              <?php endif; ?>
            </div>
            <div class="topbar-profile-info">
              <span class="tp-name"><?= htmlspecialchars($adminName) ?></span>
              <span class="tp-role">Club Admin</span>
            </div>
            <i class="fas fa-chevron-down tp-caret"></i>
          </div>
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
            <button class="date-range-btn">
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
                  <button class="cal-nav-btn" id="calPrev"><i class="fas fa-chevron-left"></i></button>
                  <span class="cal-month-label" id="calMonthLabel"></span>
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
            <select id="ef-club" required>
              <option value="">Select club</option>
              <?php foreach ($clubs as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
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
              <input type="date" id="ef-date" required />
            </div>
            <div class="form-group">
              <label>Status</label>
              <select id="ef-status">
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Start Time</label>
              <input type="time" id="ef-start" />
            </div>
            <div class="form-group">
              <label>End Time</label>
              <input type="time" id="ef-end" />
            </div>
          </div>
          <div class="form-group">
            <label>Location</label>
            <input type="text" id="ef-location" placeholder="e.g. Main Auditorium" />
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
  <script src="/unify/assets/javascripts/events.js"></script>
</body>

</html>