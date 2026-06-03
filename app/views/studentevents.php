<?php require_once __DIR__ . '/../../app/controllers/studentevents_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Events</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/public/assets/css/studentevents.css" />
</head>
<body>

<div class="app">

  <!-- ═══════════ SIDEBAR ═══════════ -->
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
      <div class="nav-section-label">STUDENT MENU</div>
      <a href="index.php?page=explore" class="nav-item <?= !$has_club ? 'active' : '' ?>">
        <i class="fas fa-compass"></i><span>Explore Clubs</span>
      </a>

      <?php if ($has_club): ?>
      <div class="nav-section-label">MY SPACE</div>
      <a href="index.php?page=studenthome" class="nav-item">
        <i class="fas fa-house"></i><span>Home</span>
      </a>
      <a href="index.php?page=myclubs" class="nav-item">
        <i class="fas fa-users"></i><span>My Clubs</span>
      </a>
      <a href="index.php?page=studentevents" class="nav-item active">
        <i class="fas fa-calendar-days"></i><span>Events</span>
        <?php if (!empty($pending_assignments_count) && $pending_assignments_count > 0): ?>
          <span class="nav-badge"><?= $pending_assignments_count ?></span>
        <?php endif; ?>
      </a>
      <a href="index.php?page=student_messages" class="nav-item">
        <i class="fas fa-comments"></i><span>Club Chat</span>
      </a>
      <?php else: ?>
      <div class="nav-section-label">MY SPACE</div>
      <a href="#" class="nav-item locked" onclick="showLockedToast(); return false;">
        <i class="fas fa-house"></i><span>Home</span><i class="fas fa-lock nav-lock-icon"></i>
      </a>
      <a href="#" class="nav-item locked" onclick="showLockedToast(); return false;">
        <i class="fas fa-users"></i><span>My Club</span><i class="fas fa-lock nav-lock-icon"></i>
      </a>
      <a href="#" class="nav-item locked" onclick="showLockedToast(); return false;">
        <i class="fas fa-calendar-days"></i><span>Events</span><i class="fas fa-lock nav-lock-icon"></i>
      </a>
      <a href="#" class="nav-item locked" onclick="showLockedToast(); return false;">
        <i class="fas fa-comments"></i><span>Club Chat</span><i class="fas fa-lock nav-lock-icon"></i>
      </a>
      <?php endif; ?>

      <?php if ($is_officer): ?>
      <div class="nav-section-label">MANAGEMENT</div>
      <a href="index.php?page=officer_dashboard" class="nav-item officer-link">
        <i class="fas fa-shield-halved"></i><span>Officer Dashboard</span>
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <?php if(!empty($avatar_url)): ?>
            <img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" />
          <?php else: ?>
            <span class="profile-avatar-fallback"><?= $avatar ?></span>
          <?php endif; ?>
          <span class="profile-online-dot"></span>
        </div>
        <a href="index.php?page=studentprofile" class="profile-link">
          <div class="profile-info">
            <span class="profile-name"><?= $full_name ?></span>
            <span class="profile-role"><?= $has_club ? ucfirst($my_role) : 'Student' ?></span>
          </div>
        </a>
        <a href="index.php?page=logout" class="sidebar-logout" title="Logout">
          <i class="fas fa-arrow-right-from-bracket"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- ═══════════ MAIN ═══════════ -->
  <main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
      <button class="hamburger-btn" onclick="event.stopPropagation();toggleSidebar();" aria-label="Menu">
        <i class="fas fa-bars"></i>
      </button>
      <div class="topbar-left">
        <span class="topbar-page-title">Club Events</span>
        <span class="topbar-date" id="topbarDate"></span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search clubs, events, announcements…"/>
        </div>
      </div>
      <div class="topbar-actions">
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
            <span class="tp-name"><?= $full_name ?></span>
            <span class="tp-role"><?= isset($my_role) ? ucfirst($my_role) : 'Student' ?></span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </a>
      </div>
    </header>

    <?php if (count($my_clubs_all) > 1): ?>
    <!-- ── CLUB SWITCHER NAV (multi-club) ── -->
    <nav class="club-switcher" id="clubSwitcher">
      <?php foreach ($my_clubs_all as $i => $mc):
        $cid = (int)$mc['club_id'];
      ?>
      <button
        class="cs-tab <?= $i === 0 ? 'active' : '' ?>"
        data-club="<?= $cid ?>"
        onclick="switchEventsClub(<?= $cid ?>)"
      >
        <?php if ($mc['logo_path']): ?>
          <img class="cs-logo" src="<?= htmlspecialchars($mc['logo_path']) ?>"
               alt="<?= htmlspecialchars($mc['acronym']) ?>"
               onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <span class="cs-logo-fallback" style="display:none"><?= htmlspecialchars(substr($mc['acronym'],0,2)) ?></span>
        <?php else: ?>
          <span class="cs-logo-fallback"><?= htmlspecialchars(substr($mc['acronym'],0,2)) ?></span>
        <?php endif; ?>
        <?= htmlspecialchars($mc['acronym'] ?: $mc['club_name']) ?>
        <span class="cs-role-dot"></span>
      </button>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php foreach ($my_clubs_all as $i => $mc):
      $cid          = (int)$mc['club_id'];
      $cd           = $all_events_data[$cid];
      $p_events     = $cd['events'];
      $p_rsvped     = $cd['rsvped_ids'];
      $p_org_name   = $cd['organizer_name'];
      $p_org_role   = $cd['organizer_role'];
      $p_members    = $cd['total_members'];
      $p_next       = $cd['next_event'];
      $p_stat_tot   = $cd['stat_total'];
      $p_stat_up    = $cd['stat_upcoming'];
      $p_stat_rsv   = $cd['stat_rsvped'];
      $p_stat_wk    = $cd['stat_thisweek'];
      $p_status_set = $cd['status_set'];
    ?>
    <div class="club-events-panel <?= $i === 0 ? 'active' : '' ?>" id="evpanel-<?= $cid ?>">

      <!-- CLUB CONTEXT BANNER -->
      <div class="club-context-banner">
        <div class="ccb-logo">
          <?php if (!empty($mc['logo_path'])): ?>
            <img src="<?= htmlspecialchars($mc['logo_path']) ?>" alt="<?= htmlspecialchars($mc['club_name']) ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="ccb-logo-fallback" style="display:none"><i class="fas fa-users"></i></div>
          <?php else: ?>
            <div class="ccb-logo-fallback"><i class="fas fa-users"></i></div>
          <?php endif; ?>
        </div>
        <div class="ccb-info">
          <div class="ccb-title">Showing events for <?= htmlspecialchars($mc['club_name']) ?></div>
          <div class="ccb-sub">
            <?= htmlspecialchars($mc['acronym'] ?? '') ?>
            <?php if (!empty($mc['category'])): ?> · <?= htmlspecialchars($mc['category']) ?><?php endif; ?>
            <?php if (!empty($mc['room'])): ?> · <i class="fas fa-location-dot"></i> <?= htmlspecialchars($mc['room']) ?><?php endif; ?>
            · <i class="fas fa-user-group"></i> <?= $p_members ?> active members
          </div>
        </div>
      </div>

      <!-- NEXT-UP HERO STRIP -->
<?php if ($p_next): ?>
<div style="
  background: linear-gradient(135deg, var(--green-mid), var(--green-dark));
  color: #fff;
  border-radius: 16px;
padding: 28px 32px;
min-height: 100px;
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
  box-shadow: 0 4px 20px rgba(13,43,26,0.12);
  position: relative;
  overflow: hidden;
  width: 100%;
  box-sizing: border-box;
">
  <div style="position:relative;z-index:1;background:rgba(255,255,255,0.18);padding:5px 11px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;display:inline-flex;align-items:center;gap:6px;flex-shrink:0;">
    <i class="fas fa-bolt"></i> Next up
  </div>
  <div style="position:relative;z-index:1;background:rgba(255,255,255,0.15);border-radius:12px;padding:8px 14px;text-align:center;min-width:64px;flex-shrink:0;">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;opacity:.9;"><?= htmlspecialchars($p_next['month']) ?></div>
    <div style="font-size:22px;font-weight:800;line-height:1;"><?= htmlspecialchars($p_next['day_num']) ?></div>
  </div>
  <div style="position:relative;z-index:1;flex:1;min-width:0;overflow:hidden;width:0;">
    <div style="font-weight:700;font-size:16px;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:100%;">
      <?= htmlspecialchars($p_next['name']) ?>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:6px 12px;font-size:12px;opacity:.92;">
      <span><i class="fas fa-clock"></i> <?= htmlspecialchars($p_next['time_range']) ?></span>
      <?php if (!empty($p_next['location'])): ?>
        <span><i class="fas fa-location-dot"></i> <?= htmlspecialchars($p_next['location']) ?></span>
      <?php endif; ?>
      <span><i class="fas fa-user-group"></i> <?= $p_next['going_count'] ?> going</span>
      <span style="background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:999px;font-weight:600;"><?= htmlspecialchars($p_next['when_label']) ?></span>
    </div>
  </div>
  <button onclick="openEvent(<?= (int)$p_next['id'] ?>)" style="position:relative;z-index:1;background:#fff;color:var(--green-dark);font-weight:700;padding:10px 18px;border-radius:10px;font-size:13px;display:inline-flex;align-items:center;gap:6px;border:none;cursor:pointer;flex-shrink:0;white-space:nowrap;">
    View details <i class="fas fa-arrow-right"></i>
  </button>
</div>
<?php endif; ?>

      <!-- STATS BANNER -->
      <div class="stats-banner">
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
          <div class="stat-info">
            <div class="stat-val" id="statUpcoming_<?= $cid ?>"><?= $p_stat_up ?></div>
            <div class="stat-label">Upcoming Events</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon teal"><i class="fas fa-circle-check"></i></div>
          <div class="stat-info">
            <div class="stat-val" id="statRsvped_<?= $cid ?>"><?= $p_stat_rsv ?></div>
            <div class="stat-label">RSVP'd Events</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-list-check"></i></div>
          <div class="stat-info">
            <div class="stat-val" id="statTotal_<?= $cid ?>"><?= $p_stat_tot ?></div>
            <div class="stat-label">Total Club Events</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-hourglass-half"></i></div>
          <div class="stat-info">
            <div class="stat-val" id="statThisWeek_<?= $cid ?>"><?= $p_stat_wk ?></div>
            <div class="stat-label">This Week</div>
          </div>
        </div>
      </div>

      <!-- FILTER BAR -->
      <div class="filter-bar">
        <div class="filter-label"><i class="fas fa-filter"></i> Filter by:</div>
        <div class="filter-pills" id="filterPills_<?= $cid ?>">
          <button class="pill active" onclick="setCat(this,'all')">All</button>
          <button class="pill" onclick="setCat(this,'rsvped')">
            <i class="fas fa-circle-check"></i> RSVP'd
          </button>
          <?php
            $status_meta = [
              'upcoming'  => ['icon' => 'fa-clock',        'label' => 'Upcoming'],
              'ongoing'   => ['icon' => 'fa-circle-play',  'label' => 'Ongoing'],
              'completed' => ['icon' => 'fa-check-double', 'label' => 'Completed'],
              'cancelled' => ['icon' => 'fa-ban',          'label' => 'Cancelled'],
            ];
            foreach ($status_meta as $key => $meta):
              if (empty($p_status_set[$key])) continue;
          ?>
            <button class="pill" onclick="setCat(this,'<?= $key ?>')">
              <i class="fas <?= $meta['icon'] ?>"></i> <?= $meta['label'] ?>
            </button>
          <?php endforeach; ?>
        </div>
        <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
          <span class="filter-count" id="filterCount_<?= $cid ?>"><?= $p_stat_tot ?> events</span>
          <div class="view-toggle">
            <button class="view-btn active" id="btnGrid_<?= $cid ?>" onclick="setView('grid',this)" title="Grid View">
              <i class="fas fa-grip"></i>
            </button>
            <button class="view-btn" id="btnList_<?= $cid ?>" onclick="setView('list',this)" title="List View">
              <i class="fas fa-list"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- EVENTS GRID -->
      <div class="events-grid" id="eventsGrid_<?= $cid ?>"></div>

      <!-- EMPTY STATE -->
      <div class="empty-state" id="emptyState_<?= $cid ?>" style="<?= empty($p_events) ? '' : 'display:none;' ?>">
        <i class="fas fa-calendar-xmark"></i>
        <p>No events found.</p>
        <span><?= empty($p_events)
                ? 'Your club has no events yet. Check back soon.'
                : 'Try a different filter or clear the search.' ?></span>
      </div>

    </div><!-- /club-events-panel -->
    <?php endforeach; ?>

    <!-- ═══════════ MY ASSIGNMENTS PANEL ═══════════ -->
    <div class="assignments-panel" id="assignmentsPanel" style="display:none;">
      <div class="assignments-header">
        <h2 class="assignments-title"><i class="fas fa-clipboard-list"></i> My Event Assignments</h2>
        <p class="assignments-sub">Events your club officers have assigned you to. Please accept or decline each one.</p>
      </div>

      <?php if (empty($my_assignments)): ?>
        <div class="empty-state" style="margin-top:48px;">
          <i class="fas fa-clipboard-check"></i>
          <p>No assignments yet.</p>
          <span>When officers assign you to events, they'll appear here.</span>
        </div>
      <?php else: ?>
        <div class="assignments-grid" id="assignmentsGrid">
          <?php foreach ($my_assignments as $asgn):
            $aStatus = $asgn['assignment_status'];
            $eStatus = $asgn['event_status'];
            $ts = strtotime($asgn['event_date']);
          ?>
          <div class="asgn-card <?= $aStatus ?>" id="asgnCard_<?= $asgn['event_id'] ?>">
            <div class="asgn-card-left">
              <div class="asgn-date-badge">
                <div class="adb-month"><?= date('M', $ts) ?></div>
                <div class="adb-day"><?= date('j', $ts) ?></div>
              </div>
            </div>
            <div class="asgn-card-body">
              <div class="asgn-club-row">
                <?php if (!empty($asgn['club_logo'])): ?>
                  <img src="<?= htmlspecialchars($asgn['club_logo']) ?>" class="asgn-club-logo" alt=""
                       onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                  <span class="asgn-club-logo-fallback" style="display:none"><?= htmlspecialchars(substr($asgn['acronym'],0,2)) ?></span>
                <?php else: ?>
                  <span class="asgn-club-logo-fallback"><?= htmlspecialchars(substr($asgn['acronym']??'',0,2)) ?></span>
                <?php endif; ?>
                <span class="asgn-club-name"><?= htmlspecialchars($asgn['club_name']) ?></span>
                <span class="asgn-evt-status-chip <?= $eStatus ?>"><?= ucfirst($eStatus) ?></span>
              </div>
              <div class="asgn-event-name"><?= htmlspecialchars($asgn['event_name']) ?></div>
              <div class="asgn-meta-row">
                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($asgn['time_range']) ?></span>
                <?php if (!empty($asgn['location'])): ?>
                  <span><i class="fas fa-location-dot"></i> <?= htmlspecialchars($asgn['location']) ?></span>
                <?php endif; ?>
              </div>
              <?php if (!empty($asgn['role_label'])): ?>
                <div class="asgn-role-chip"><i class="fas fa-user-tag"></i> <?= htmlspecialchars($asgn['role_label']) ?></div>
              <?php endif; ?>
            </div>
            <div class="asgn-card-actions">
              <?php if ($aStatus === 'pending'): ?>
                <button class="asgn-btn accept" onclick="respondAssignment(<?= $asgn['event_id'] ?>, 'accepted')">
                  <i class="fas fa-check"></i> Accept
                </button>
                <button class="asgn-btn decline" onclick="respondAssignment(<?= $asgn['event_id'] ?>, 'declined')">
                  <i class="fas fa-times"></i> Decline
                </button>
              <?php elseif ($aStatus === 'accepted'): ?>
                <div class="asgn-response-badge accepted"><i class="fas fa-circle-check"></i> Accepted</div>
                <button class="asgn-btn decline sm" onclick="respondAssignment(<?= $asgn['event_id'] ?>, 'declined')">
                  <i class="fas fa-times"></i> Withdraw
                </button>
              <?php else: ?>
                <div class="asgn-response-badge declined"><i class="fas fa-circle-xmark"></i> Declined</div>
                <button class="asgn-btn accept sm" onclick="respondAssignment(<?= $asgn['event_id'] ?>, 'accepted')">
                  <i class="fas fa-check"></i> Accept
                </button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div><!-- /assignments-panel -->

  </main>
</div>

<!-- ═══════════ EVENT DETAIL MODAL ═══════════ -->
<div class="modal-overlay" id="detailOverlay" onclick="closeOverlay(event,'detailOverlay')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-date-chip">
          <div class="mdc-month" id="mdMonth">—</div>
          <div class="mdc-day"   id="mdDay">—</div>
        </div>
        <div>
          <div class="modal-title"    id="mdTitle">Event Title</div>
          <div class="modal-subtitle" id="mdClubName">Club Name</div>
          <div class="modal-when"     id="mdWhen">—</div>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('detailOverlay')">
        <i class="fas fa-xmark"></i>
      </button>
    </div>

    <div class="modal-body">
      <div class="modal-stats-strip">
        <div class="modal-stat-box">
          <div class="mstat-val" id="mdTime">—</div>
          <div class="mstat-label">Time</div>
        </div>
        <div class="modal-stat-box">
          <div class="mstat-val" id="mdDuration">—</div>
          <div class="mstat-label">Duration</div>
        </div>
        <div class="modal-stat-box">
          <div class="mstat-val" id="mdGoing">—</div>
          <div class="mstat-label">Going</div>
        </div>
        <div class="modal-stat-box green-stat">
          <div class="mstat-val" id="mdStatus">—</div>
          <div class="mstat-label">Status</div>
        </div>
      </div>

      <div class="modal-section">
        <div class="modal-section-title"><i class="fas fa-location-dot"></i> Location</div>
        <div class="modal-location-box" id="mdLocation">—</div>
      </div>
      <div class="modal-divider"></div>
      <div class="modal-section">
        <div class="modal-section-title"><i class="fas fa-align-left"></i> About this Event</div>
        <p class="modal-desc" id="mdDesc"></p>
      </div>
      <div class="modal-divider"></div>
      <div class="modal-section">
        <div class="modal-section-title"><i class="fas fa-user-group"></i> Who's going</div>
        <div class="modal-attendees" id="mdAttendees">
          <span class="modal-attendees-empty">No confirmed attendees yet.</span>
        </div>
      </div>
      <div class="modal-divider"></div>
      <div class="modal-section">
        <div class="modal-section-title"><i class="fas fa-users"></i> Hosted by</div>
        <div class="modal-club-row">
          <div class="mcr-logo-wrap">
            <img id="mdClubLogo" src="" alt=""
                 onerror="this.style.display='none';document.getElementById('mdClubLogoFallback').style.display='flex'">
            <div id="mdClubLogoFallback" class="mcr-logo-fallback" style="display:none;">
              <i class="fas fa-users"></i>
            </div>
          </div>
          <div>
            <div class="mcr-name" id="mdClubNameRow">—</div>
            <div class="mcr-cat"  id="mdClubCat">—</div>
            <?php if ($organizer_name): ?>
              <div class="mcr-organizer">
                <i class="fas fa-user-tie"></i>
                Organizer: <strong><?= htmlspecialchars($organizer_name) ?></strong>
                <span class="mcr-organizer-role"><?= htmlspecialchars($organizer_role) ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="modal-btn-cancel" onclick="closeModal('detailOverlay')">Close</button>
      <button class="modal-btn-remind" id="mdRemindBtn" onclick="modalToggleReminder()" style="display:none;">
        <i class="fas fa-bell"></i> Remind Me
      </button>
      <button class="modal-btn-feedback" id="mdFeedbackBtn" onclick="modalOpenFeedback()" style="display:none;">
        <i class="fas fa-star"></i> Rate Event
      </button>
      <button class="modal-btn-rsvp" id="mdRsvpBtn" onclick="modalToggleRSVP()">
        <i class="fas fa-calendar-check"></i> RSVP
      </button>
    </div>
  </div>
</div>

<!-- ═══════════ FEEDBACK MODAL ═══════════ -->
<div class="modal-overlay" id="feedbackOverlay" onclick="closeOverlay(event,'feedbackOverlay')">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-date-chip" style="background:linear-gradient(135deg,#d4940a,#a87208);">
          <div class="mdc-month" style="font-size:16px;">⭐</div>
          <div class="mdc-day" style="font-size:13px;">Rate</div>
        </div>
        <div>
          <div class="modal-title" id="fbEventTitle">Rate this Event</div>
          <div class="modal-subtitle" id="fbEventSub">Share your experience</div>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('feedbackOverlay')">
        <i class="fas fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body">
      <!-- Community avg -->
      <div class="fb-avg-row" id="fbAvgRow">
        <div class="fb-avg-score" id="fbAvgScore">—</div>
        <div class="fb-avg-stars" id="fbAvgStars"></div>
        <div class="fb-avg-label" id="fbAvgLabel">No ratings yet</div>
      </div>
      <div class="modal-divider"></div>
      <div class="modal-section-title" style="margin-bottom:10px;"><i class="fas fa-star" style="color:var(--yellow);"></i> Your Rating</div>
      <div class="fb-star-row" id="fbStarRow">
        <?php for($s=1;$s<=5;$s++): ?>
          <button class="fb-star" data-val="<?=$s?>" onclick="fbSetStar(<?=$s?>)" title="<?=$s?> star<?=$s>1?'s':''?>">
            <i class="fas fa-star"></i>
          </button>
        <?php endfor; ?>
      </div>
      <p class="fb-star-hint" id="fbStarHint">Click a star to rate</p>
      <div class="modal-section-title" style="margin:16px 0 8px;"><i class="fas fa-comment" style="color:var(--green-accent);"></i> Review <span style="font-weight:400;color:var(--text-light)">(optional)</span></div>
      <textarea id="fbReview" class="fb-textarea" placeholder="What did you think? Your feedback helps officers improve future events." maxlength="500"></textarea>
      <div class="fb-char-count"><span id="fbCharCount">0</span>/500</div>
    </div>
    <div class="modal-footer">
      <button class="modal-btn-cancel" onclick="closeModal('feedbackOverlay')">Cancel</button>
      <button class="modal-btn-rsvp" id="fbSubmitBtn" onclick="submitFeedback()">
        <i class="fas fa-paper-plane"></i> Submit Feedback
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="crud-toast" id="crudToast"></div>

<!-- Assignment Confirm Toast (inline fallback) -->
<script>
if (typeof showCrudToast !== 'function') {
  window.showCrudToast = function(msg, type) {
    const t = document.getElementById('crudToast');
    if (!t) return;
    t.textContent = msg;
    t.className = 'crud-toast show' + (type === 'error' ? ' error' : ' success');
    setTimeout(() => t.classList.remove('show'), 3200);
  };
}
</script>

<script>
// ── Notification helpers (same as studenthome) ─────────────
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');

function toggleNotif(e) {
  e.stopPropagation();
  notifBtn.classList.toggle('active');
  notifDropdown.classList.toggle('open');
  if (notifDropdown.classList.contains('open')) loadNotifs();
}

document.addEventListener('click', (e) => {
  if (notifDropdown && !notifDropdown.contains(e.target) && e.target !== notifBtn) {
    notifBtn.classList.remove('active');
    notifDropdown.classList.remove('open');
  }
});

function clearNotifs() {
  fetch('index.php?page=notifications&action=read_all', { method: 'POST' });
  document.querySelectorAll('#notifList .notif-item.unread').forEach(n => n.classList.remove('unread'));
  const badge = document.getElementById('notifBadge');
  if (badge) { badge.textContent = '0'; badge.classList.add('hidden'); }
}

const NOTIF_ICONS = {
  app_approved:        'fa-circle-check',
  app_rejected:        'fa-circle-xmark',
  club_position:       'fa-id-badge',
  event_assigned:      'fa-calendar-check',
  assignment_accepted: 'fa-user-check',
  assignment_declined: 'fa-user-xmark',
  collab_request:      'fa-handshake',
  collab_accepted:     'fa-handshake',
  collab_declined:     'fa-handshake-slash',
  event_new:           'fa-calendar-plus',
  event_updated:       'fa-calendar-pen',
  event_cancelled:     'fa-calendar-xmark',
  info:                'fa-circle-info',
};

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
        list.innerHTML = `<div class="notif-item"><div class="notif-content"><span class="notif-text">No new notifications.</span></div></div>`;
        return;
      }
      list.innerHTML = res.notifications.map(n => `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}"
             onclick="markNotifRead(${n.id}, '${n.link || ''}', this)">
          <div class="notif-dot"></div>
          <div class="notif-content">
            <span class="notif-text">
              <strong>${n.title}</strong>
            </span>
            <span class="notif-text" style="font-weight:400;margin-top:2px;">${n.message || ''}</span>
            <span class="notif-time"><i class="fas fa-clock"></i> ${n.created_fmt || ''}</span>
          </div>
        </div>`).join('');
    }).catch(() => {});
}

function markNotifRead(id, link, el) {
  fetch('index.php?page=notifications&action=read&id=' + id, { method: 'POST' });
  el.classList.remove('unread');
  if (link) setTimeout(() => window.location = link, 150);
}

// Load on page load
loadNotifs();

// ── Club data ──────────────────────────────────────────────
window.UNIFY_CLUBS = {
  <?php foreach ($my_clubs_all as $i => $mc):
    $cid = (int)$mc['club_id'];
    $cd  = $all_events_data[$cid];
  ?>
  <?= $cid ?>: {
    userId       : <?= $user_id ?>,
    clubId       : <?= $cid ?>,
    clubName     : <?= json_encode($mc['club_name']) ?>,
    clubAcro     : <?= json_encode($mc['acronym']) ?>,
    clubCat      : <?= json_encode($mc['category']) ?>,
    clubLogo     : <?= json_encode($mc['logo_path']) ?>,
    clubRoom     : <?= json_encode($mc['room']) ?>,
    totalMembers : <?= $cd['total_members'] ?>,
    organizer    : <?= json_encode($cd['organizer_name'] ? ['name'=>$cd['organizer_name'],'role'=>$cd['organizer_role']] : null) ?>,
    rsvped       : <?= json_encode($cd['rsvped_ids']) ?>,
    reminded     : <?= json_encode($cd['reminded_ids']) ?>,
    myFeedback   : <?= json_encode($cd['my_feedback'], JSON_HEX_TAG | JSON_HEX_APOS) ?>,
    events       : <?= json_encode($cd['events'], JSON_HEX_TAG | JSON_HEX_APOS) ?>,
    stats        : {
      total     : <?= $cd['stat_total'] ?>,
      upcoming  : <?= $cd['stat_upcoming'] ?>,
      rsvped    : <?= $cd['stat_rsvped'] ?>,
      thisWeek  : <?= $cd['stat_thisweek'] ?>,
      completed : <?= $cd['stat_completed'] ?>
    }
  }<?= $i < count($my_clubs_all) - 1 ? ',' : '' ?>

  <?php endforeach; ?>
};

window.UNIFY = window.UNIFY_CLUBS[<?= $first_cid ?>];

// ── My Assignments data ────────────────────────────────
window.MY_ASSIGNMENTS = <?= json_encode($my_assignments, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
window.PENDING_ASGN_COUNT = <?= (int)$pending_assignments_count ?>;

// ── Assignment panel toggle ────────────────────────────
let assignmentsPanelOpen = false;
function toggleAssignmentsPanel() {
  const panel  = document.getElementById('assignmentsPanel');
  const panels = document.querySelectorAll('.club-events-panel');
  const btn    = document.getElementById('assignmentsToggleBtn');

  assignmentsPanelOpen = !assignmentsPanelOpen;
  if (assignmentsPanelOpen) {
    panels.forEach(p => p.style.display = 'none');
    const switcher = document.getElementById('clubSwitcher');
    if (switcher) switcher.style.display = 'none';
    panel.style.display = 'block';
    if (btn) btn.classList.add('active');
  } else {
    panel.style.display = 'none';
    panels.forEach(p => {
      p.style.display = p.classList.contains('active') ? '' : 'none';
    });
    const switcher = document.getElementById('clubSwitcher');
    if (switcher) switcher.style.display = '';
    if (btn) btn.classList.remove('active');
  }
}

// Also restore panels if tab is in URL
(function() {
  const params = new URLSearchParams(window.location.search);
  if (params.get('tab') === 'assignments') {
    setTimeout(() => { toggleAssignmentsPanel(); }, 50);
  }
})();

// ── Respond to assignment ──────────────────────────────
function respondAssignment(eventId, response) {
  const card = document.getElementById('asgnCard_' + eventId);
  if (card) {
    card.style.opacity = '0.6';
    card.style.pointerEvents = 'none';
  }

  const fd = new FormData();
  fd.append('action',   'respond_assignment');
  fd.append('event_id', eventId);
  fd.append('response', response);

  fetch('index.php?page=studentevents_ajax', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showCrudToast(res.message, 'success');
        // Update card UI without full reload
        if (card) {
          card.classList.remove('pending','accepted','declined');
          card.classList.add(response);
          const actionsDiv = card.querySelector('.asgn-card-actions');
          if (actionsDiv) {
            if (response === 'accepted') {
              actionsDiv.innerHTML = `
                <div class="asgn-response-badge accepted"><i class="fas fa-circle-check"></i> Accepted</div>
                <button class="asgn-btn decline sm" onclick="respondAssignment(${eventId},'declined')">
                  <i class="fas fa-times"></i> Withdraw
                </button>`;
            } else {
              actionsDiv.innerHTML = `
                <div class="asgn-response-badge declined"><i class="fas fa-circle-xmark"></i> Declined</div>
                <button class="asgn-btn accept sm" onclick="respondAssignment(${eventId},'accepted')">
                  <i class="fas fa-check"></i> Accept
                </button>`;
            }
          }
          card.style.opacity = '1';
          card.style.pointerEvents = '';
        }
        // Update badge count
        updateAssignmentBadge();
      } else {
        showCrudToast(res.message || 'Error. Please try again.', 'error');
        if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
      }
    })
    .catch(() => {
      showCrudToast('Network error. Please try again.', 'error');
      if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
    });
}

function updateAssignmentBadge() {
  // Count remaining pending cards
  const pending = document.querySelectorAll('.asgn-card.pending').length;
  const badge   = document.getElementById('assignmentsBadge');
  const navBadge = document.querySelector('.nav-badge');
  if (badge) {
    badge.textContent = pending;
    badge.classList.toggle('hidden', pending === 0);
  }
  if (navBadge) {
    navBadge.textContent = pending;
    navBadge.style.display = pending > 0 ? '' : 'none';
  }
}

function switchEventsClub(clubId) {
  document.querySelectorAll('.cs-tab').forEach(t => {
    t.classList.toggle('active', parseInt(t.dataset.club) === clubId);
  });
  document.querySelectorAll('.club-events-panel').forEach(p => {
    p.classList.toggle('active', p.id === 'evpanel-' + clubId);
  });
  if (window.UNIFY_CLUBS[clubId]) window.UNIFY = window.UNIFY_CLUBS[clubId];
}
</script>
<script src="/public/assets/javascripts/studentevents.js"></script>

<script>
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
</body>
</html>