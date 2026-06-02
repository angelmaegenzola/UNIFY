<?php
// ============================================================
//  UNIFY — officer_home.php  (VIEW)
//  app/views/officer_home.php
//  Loaded via index.php?page=officer_home
//  Requires the controller to have run first.
// ============================================================
require_once __DIR__ . '/../controllers/officer_home_controller.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Home · <?= htmlspecialchars($clubName) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/public/assets/css/officer_dashboard.css" />
  <link rel="stylesheet" href="/public/assets/css/officer_home.css" />
  <link rel="stylesheet" href="/public/assets/css/transitions.css" />
</head>

<body>
  <div class="app">

    <!-- ── SIDEBAR ────────────────────────────────────────────── -->
     <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
  <aside class="sidebar" id="mainSidebar">
      <div class="sidebar-brand">
        <img src="/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
        <div class="brand-text">
          <div class="brand-name">UNIFY</div>
          <div class="brand-tagline">Club Management System</div>
        </div>
      </div>

      <div class="sidebar-club-badge">
        <div class="club-badge-icon">
          <?php if (!empty($officerClub['logo_path'])): ?>
            <img src="<?= htmlspecialchars($officerClub['logo_path']) ?>" alt="Club Logo"
              style="width:34px;height:34px;border-radius:9px;object-fit:cover;display:block;" />
          <?php else: ?>
            <?= htmlspecialchars($clubInitial) ?>
          <?php endif; ?>
        </div>
        <div class="club-badge-info">
          <div class="club-badge-name"><?= htmlspecialchars($clubName) ?></div>
          <div class="club-badge-role"><?= ucfirst(htmlspecialchars($officerRole)) ?></div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-label">MY CLUB</div>
        <a href="index.php?page=officer_dashboard" class="nav-item">
          <i class="fas fa-house"></i><span>Dashboard</span>
        </a>
        <a href="index.php?page=officer_members" class="nav-item">
          <i class="fas fa-users"></i><span>Members</span>
        </a>
        <a href="index.php?page=officer_events" class="nav-item">
          <i class="fas fa-calendar-days"></i><span>Events</span>
        </a>
        <a href="index.php?page=officer_messages" class="nav-item">
          <i class="fas fa-comments"></i><span>Club Chat</span>
          <?php if (!empty($unreadChat) && $unreadChat > 0): ?>
            <span class="nav-chat-badge"><?= $unreadChat ?></span>
          <?php endif; ?>
        </a>
        <div class="nav-section-label">GENERAL</div>
        <a href="index.php?page=officer_explore" class="nav-item">
          <i class="fas fa-compass"></i><span>Explore Clubs</span>
        </a>
        <a href="index.php?page=officer_home" class="nav-item active">
          <i class="fas fa-house-chimney"></i><span>Student Home</span>
        </a>
      </nav>

      <div class="sidebar-bottom">
        <div class="sidebar-profile">
          <div class="profile-avatar-wrap">
            <?php if ($avatar_url): ?>
              <img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" id="of-sidebar-avatar" />
            <?php else: ?>
              <span class="profile-avatar-fallback" id="of-sidebar-avatar"><?= htmlspecialchars($userInit) ?></span>
            <?php endif; ?>
            <span class="profile-online-dot"></span>
          </div>
          <a href="index.php?page=profile" class="profile-link">
            <div class="profile-info">
              <span class="profile-name"><?= htmlspecialchars($userName) ?></span>
              <span class="profile-role"><?= ucfirst(htmlspecialchars($officerRole)) ?></span>
            </div>
          </a>
          <a href="index.php?page=logout" class="sidebar-logout" title="Logout">
            <i class="fas fa-arrow-right-from-bracket"></i>
          </a>
        </div>
      </div>
    </aside>


    <!-- ═══════════════════════════════ MAIN ═══════════════════════════════ -->
    <main class="main">

      <!-- TOPBAR -->
      <header class="topbar">
        <button class="hamburger-btn" onclick="event.stopPropagation();toggleSidebar();" aria-label="Menu">
        <i class="fas fa-bars"></i>
      </button>
      <div class="topbar-left">
          <span class="topbar-page-title">Home</span>
          <span class="topbar-date" id="topbarDate"></span>
        </div>
        <div class="topbar-center">
          <div class="topbar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Search events, members, announcements…" id="homeSearch"
              oninput="homeSearch(this.value)" />
          </div>
        </div>
        <div class="topbar-actions">
          <button class="icon-btn" id="notifBtn" title="Notifications" onclick="toggleNotif(event)">
            <i class="fas fa-bell"></i>
            <span class="badge <?= $unreadNotifs > 0 ? '' : 'hidden' ?>" id="notifBadge"><?= $unreadNotifs ?></span>
          </button>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
              <span class="notif-header-title"><i class="fas fa-bell"></i> Notifications</span>
              <button class="notif-mark-btn" onclick="markAllNotifs()">Mark all read</button>
            </div>
            <div class="notif-list" id="notifList">
              <div class="notif-item">
                <div class="notif-content"><span class="notif-text">Loading…</span></div>
              </div>
            </div>
            <div class="notif-footer">Only showing recent notifications</div>
          </div>
          <a href="index.php?page=profile" class="topbar-profile" title="View Profile"
            style="text-decoration:none;cursor:pointer;">
            <div class="topbar-avatar">
              <?php if (!empty($avatar_url)): ?>
                <img src="<?= $avatar_url ?>" alt="Avatar"
                  style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" />
              <?php else: ?>
                <?= $userInit ?>
              <?php endif; ?>
            </div>
            <div class="topbar-profile-info">
              <span class="tp-name"><?= $userName ?></span>
              <span class="tp-role"><?= ucfirst($officerRole) ?></span>
            </div>
            <i class="fas fa-chevron-down tp-caret"></i>
          </a>
        </div>
      </header>

      <!-- CONTENT -->
      <div class="content">
        <div class="dashboard-grid">

          <!-- ═══ LEFT COLUMN ═══ -->
                   <div class="left-col">
 
          <!-- Welcome Banner -->
          <div class="welcome-banner">
            <div class="wb-text">
              <span class="wb-greeting" id="wbGreeting">Good morning 👋</span>
              <h2 class="wb-name">Welcome, <?= htmlspecialchars($userFirst) ?>!</h2>
              <div class="wb-bottom-row">
                <div class="wb-club-pill">
                  <i class="fas fa-shield-halved"></i>
                  <?= htmlspecialchars($clubName) ?> · <?= ucfirst(htmlspecialchars($officerRole)) ?>
                </div>
                <?php if (in_array($officerRole, ['president','vice president'])): ?>
                <button class="wb-edit-btn" onclick="openModal('editClubModal')">
                  <i class="fas fa-pen"></i> Edit Club
                </button>
                <?php endif; ?>
              </div>
            </div>
            <div class="wb-right">
              <?php if (!empty($officerClub['logo_path'])): ?>
              <div class="wb-club-logo-wrap">
                <img src="<?= htmlspecialchars($officerClub['logo_path']) ?>"
                     alt="<?= htmlspecialchars($clubName) ?>"
                     class="wb-club-logo" />
              </div>
              <?php else: ?>
              <div class="wb-club-logo-wrap wb-logo-fallback">
                <?= htmlspecialchars($clubInitial) ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

            <!-- STAT CARDS -->
            <div class="stat-cards-grid">
              <div class="stat-card-new sc-green" onclick="window.location='index.php?page=officer_members'">
                <div class="sc-top">
                  <div class="sc-icon-wrap"><i class="fas fa-users"></i></div>
                  <span class="sc-trend">Active</span>
                </div>
                <div class="sc-value"><?= $stats['total_members'] ?></div>
                <div class="sc-label">Total Members</div>
              </div>
              <div class="stat-card-new sc-teal" onclick="window.location='index.php?page=officer_events'">
                <div class="sc-top">
                  <div class="sc-icon-wrap"><i class="fas fa-calendar-check"></i></div>
                  <span class="sc-trend">Upcoming</span>
                </div>
                <div class="sc-value"><?= $stats['upcoming_events'] ?></div>
                <div class="sc-label">Events Ahead</div>
              </div>
              <div class="stat-card-new <?= $stats['pending_apps'] > 0 ? 'sc-yellow' : 'sc-blue' ?>"
                onclick="window.location='index.php?page=officer_members'">
                <div class="sc-top">
                  <div class="sc-icon-wrap"><i class="fas fa-hourglass-half"></i></div>
                  <span class="sc-trend <?= $stats['pending_apps'] > 0 ? 'urgent' : '' ?>">
                    <?= $stats['pending_apps'] > 0 ? 'Needs review' : 'All clear' ?>
                  </span>
                </div>
                <div class="sc-value"><?= $stats['pending_apps'] ?></div>
                <div class="sc-label">Pending Applications</div>
              </div>
            </div><!-- /stat-cards-grid -->

            <!-- UPCOMING EVENTS -->
            <div class="card events-card">
              <div class="card-header">
                <div>
                  <h2><i class="fas fa-calendar-days"></i> Upcoming Events</h2>
                  <div class="calendar-subtitle">
                    <?= $stats['upcoming_events'] ?> event<?= $stats['upcoming_events'] !== 1 ? 's' : '' ?> scheduled
                  </div>
                </div>
                <a href="index.php?page=officer_events" class="see-all-link" style="font-size:11px;">
                  All <i class="fas fa-arrow-right"></i>
                </a>
              </div>

              <?php if (empty($events)): ?>
                <div class="oh-empty"><i class="fas fa-calendar-xmark"></i><span>No upcoming events.</span></div>
                <button class="add-evt-btn" style="align-self:center;margin-top:8px;" onclick="openQuickEvt()">
                  <i class="fas fa-plus"></i> Create Event
                </button>
              <?php else: ?>
                <div class="timeline">
                  <?php foreach ($events as $i => $ev):
                    $ev_dt = new DateTime($ev['event_date']);
                    $ev_month = strtoupper($ev_dt->format('M'));
                    $ev_day = $ev_dt->format('j');
                    $ev_time = $ev['start_time'] ? date('g:i A', strtotime($ev['start_time'])) : 'TBA';
                    $is_first = $i === 0;
                    $is_last = $i === count($events) - 1;
                    ?>
                    <div class="timeline-row <?= $is_last ? 'last-row' : '' ?>">
                      <div class="timeline-time"><?= $ev_month ?><br><?= $ev_day ?></div>
                      <div class="timeline-line-col">
                        <div class="timeline-dot <?= $is_first ? 'active' : '' ?>"></div>
                        <?php if (!$is_last): ?>
                          <div class="timeline-vline <?= $is_first ? 'active-line' : '' ?>"></div>
                        <?php endif; ?>
                      </div>
                      <div class="timeline-event-wrap">
                        <div class="timeline-event <?= $is_first ? 'active-event' : 'inactive-event' ?>">
                          <div class="tl-event-icon">
                            <i class="fas fa-calendar-days"></i>
                          </div>
                          <div style="flex:1;min-width:0;">
                            <div class="te-name"><?= htmlspecialchars($ev['name']) ?></div>
                            <div class="te-meta">
                              <i class="fas fa-clock"></i> <?= $ev_time ?>
                              <?php if ($ev['location']): ?>
                                &nbsp;·&nbsp;<i class="fas fa-location-dot"></i> <?= htmlspecialchars($ev['location']) ?>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div><!-- /events-card -->

            <!-- ANNOUNCEMENTS -->
            <div>
              <div class="section-row" style="margin-bottom:10px;">
                <span class="section-title"><i class="fas fa-bullhorn" style="color:var(--green-accent);"></i> Club
                  Announcements</span>
                <div style="display:flex;gap:8px;align-items:center;">
                  <button class="add-item-btn" onclick="openQuickAnn()">
                    <i class="fas fa-plus"></i> New
                  </button>
                  <a href="index.php?page=officer_dashboard" class="see-all-link">See All <i
                      class="fas fa-arrow-right"></i></a>
                </div>
              </div>
              <div class="card">
                <?php if (empty($anns)): ?>
                  <div class="oh-empty"><i class="fas fa-bell-slash"></i><span>No announcements yet.</span></div>
                <?php else: ?>
                  <div class="table-header-row" style="grid-template-columns:1fr 90px 80px 55px;">
                    <div class="th-col">Title</div>
                    <div class="th-col">Category</div>
                    <div class="th-col">Status</div>
                    <div class="th-col th-right">When</div>
                  </div>
                  <div class="table-body">
                    <?php foreach ($anns as $ann):
                      $ai = $ann_icons[$ann['status']] ?? $ann_icons['info'];
                      $dot = $ai['dot'];
                      ?>
                      <div class="table-row" style="grid-template-columns:1fr 90px 80px 55px;">
                        <div class="td-col">
                          <span class="dot <?= $dot ?>"></span>
                          <span class="td-title"><?= htmlspecialchars($ann['title']) ?></span>
                        </div>
                        <div class="td-col">
                          <span class="cat-badge"><?= htmlspecialchars($ann['category'] ?? 'General') ?></span>
                        </div>
                        <div class="td-col">
                          <span class="status-badge s-<?= htmlspecialchars($ann['status']) ?>">
                            <?= ucfirst($ann['status']) ?>
                          </span>
                        </div>
                        <div class="td-col td-right td-date"><?= ohRelTime($ann['posted_at']) ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div><!-- /announcements -->

            <!-- COLLABORATION REQUESTS (only if any) -->
            <?php if (!empty($collabRequests)): ?>
              <div class="card collab-requests-card" id="collabRequestsCard" style="margin-bottom:16px;">
                <div class="card-header">
                  <h2><i class="fas fa-handshake"></i> Collaboration Proposals
                    <span class="collab-badge"><?= $pendingCollabCount ?></span>
                  </h2>
                </div>
                <div class="collab-requests-list">
                  <?php foreach ($collabRequests as $cr): ?>
                  <div class="collab-request-item" id="collabReq_<?= $cr['id'] ?>">
                    <div class="cr-logo-wrap">
                      <?php if (!empty($cr['from_club_logo'])): ?>
                        <img src="<?= htmlspecialchars($cr['from_club_logo']) ?>" class="cr-club-logo" alt=""
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <span class="cr-logo-fallback" style="display:none"><?= htmlspecialchars(substr($cr['from_club_acronym']??'',0,2)) ?></span>
                      <?php else: ?>
                        <span class="cr-logo-fallback"><?= htmlspecialchars(substr($cr['from_club_acronym']??'',0,2)) ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="cr-body">
                      <div class="cr-from"><strong><?= htmlspecialchars($cr['from_club_name']) ?></strong>
                        <span class="cr-by"> via <?= htmlspecialchars($cr['proposed_by_name']) ?></span>
                      </div>
                      <div class="cr-event-name"><?= htmlspecialchars($cr['event_name']) ?></div>
                      <?php if ($cr['proposed_date']): ?>
                        <div class="cr-meta"><i class="fas fa-calendar"></i> <?= date('F j, Y', strtotime($cr['proposed_date'])) ?></div>
                      <?php endif; ?>
                      <?php if (!empty($cr['message'])): ?>
                        <div class="cr-message"><?= htmlspecialchars($cr['message']) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="cr-actions">
                      <button class="cr-btn accept" onclick="respondCollab(<?= $cr['id'] ?>,'accepted',this)">
                        <i class="fas fa-check"></i> Accept
                      </button>
                      <button class="cr-btn decline" onclick="respondCollab(<?= $cr['id'] ?>,'declined',this)">
                        <i class="fas fa-times"></i> Decline
                      </button>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- PENDING APPLICATIONS (only if any) -->
            <?php if (!empty($pendingApps)): ?>
              <div>
                <div class="section-row" style="margin-bottom:10px;">
                  <span class="section-title">
                    <i class="fas fa-hourglass-half" style="color:var(--yellow);"></i> Pending Applications
                  </span>
                  <a href="index.php?page=officer_members" class="see-all-link">Review All <i
                      class="fas fa-arrow-right"></i></a>
                </div>
                <div class="applicant-list" id="applicantList">
                  <?php foreach ($pendingApps as $i => $ap):
                    $a_init = strtoupper(substr($ap['first_name'], 0, 1) . substr($ap['last_name'], 0, 1));
                    $a_name = htmlspecialchars($ap['first_name'] . ' ' . $ap['last_name']);
                    $a_date = ohRelTime($ap['applied_at']);
                    $a_course = htmlspecialchars($ap['app_course'] ?? '—');
                    ?>
                    <div class="applicant-card" id="apCard<?= $ap['id'] ?>">
                      <div class="apcard-left">
                        <div class="member-avatar <?= $av_colors[$i % count($av_colors)] ?>"><?= $a_init ?></div>
                        <div class="apcard-info">
                          <div class="apcard-name"><?= $a_name ?></div>
                          <div class="apcard-meta">
                            <span><?= $a_course ?></span>
                            <?php if ($ap['app_phone']): ?>
                              &nbsp;·&nbsp;<span><?= htmlspecialchars($ap['app_phone']) ?></span>
                            <?php endif; ?>
                          </div>
                          <?php if ($ap['extras']): ?>
                            <div class="apcard-reason"><?= htmlspecialchars(mb_strimwidth($ap['extras'], 0, 90, '…')) ?></div>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="apcard-right">
                        <span class="apcard-date"><?= $a_date ?></span>
                        <div class="apcard-btns">
                          <button class="apcard-btn approve" onclick="approveApp(<?= $ap['id'] ?>)">
                            <i class="fas fa-check"></i> Approve
                          </button>
                          <button class="apcard-btn reject" onclick="rejectApp(<?= $ap['id'] ?>)">
                            <i class="fas fa-xmark"></i> Reject
                          </button>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

          </div><!-- /left-col -->


          <!-- ═══ RIGHT COLUMN ═══ -->
          <div class="right-col" style="overflow-y:auto;padding-right:2px;">

            <!-- QUICK ACTIONS -->
            <div class="card quick-actions-card">
              <div class="card-header">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
              </div>
              <div class="qa-grid">
                <button class="qa-btn" onclick="openQuickAnn()">
                  <i class="fas fa-bullhorn"></i>
                  <span>Post Announcement</span>
                </button>
                <button class="qa-btn" onclick="openQuickEvt()">
                  <i class="fas fa-calendar-plus"></i>
                  <span>Add Event</span>
                </button>
                <a href="index.php?page=officer_members" class="qa-btn">
                  <i class="fas fa-user-plus"></i>
                  <span>Manage Members</span>
                </a>
                <a href="index.php?page=officer_messages" class="qa-btn">
                  <i class="fas fa-comments"></i>
                  <span>Club Chat</span>
                </a>
              </div>
            </div>

            <!-- MEMBERS PREVIEW -->
            <div>
              <div class="section-row" style="margin-bottom:10px;">
                <span class="section-title">
                  <i class="fas fa-id-badge" style="color:var(--teal);"></i> Members
                </span>
                <a href="index.php?page=officer_members" class="see-all-link">Manage All <i
                    class="fas fa-arrow-right"></i></a>
              </div>
              <div class="card">
                <?php if (empty($members)): ?>
                  <div class="oh-empty"><i class="fas fa-users"></i><span>No members found.</span></div>
                <?php else: ?>
                  <div class="member-list">
                    <?php foreach ($members as $i => $m):
                      $m_init = strtoupper(substr($m['first_name'], 0, 1) . substr($m['last_name'], 0, 1));
                      $m_name = htmlspecialchars($m['first_name'] . ' ' . $m['last_name']);
                      $m_meta = implode(' · ', array_filter([
                        $m['course'] ?? null,
                        $m['year'] ?? null,
                      ]));
                      $rb = $role_labels[$m['role']] ?? ['label' => ucfirst($m['role']), 'class' => 'member'];
                      ?>
                      <div class="member-item">
                        <div class="member-avatar <?= $av_colors[$i % count($av_colors)] ?>"><?= $m_init ?></div>
                        <div class="member-info">
                          <div class="member-name"><?= $m_name ?></div>
                          <div class="member-meta"><?= htmlspecialchars($m_meta ?: $m['email']) ?></div>
                        </div>
                        <span class="role-badge <?= $rb['class'] ?>"><?= $rb['label'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- CLUB INFO CARD -->
            <div class="card">
              <div class="card-header">
                <h2><i class="fas fa-circle-info"></i> Club Info</h2>
                <a href="index.php?page=officer_dashboard" class="see-all-link" style="font-size:11px;">
                  Edit <i class="fas fa-pen"></i>
                </a>
              </div>
              <div class="oh-info-grid">
                <div class="oh-info-item">
                  <span class="oh-info-label">Category</span>
                  <span class="oh-info-val"><?= htmlspecialchars($officerClub['category'] ?? '—') ?></span>
                </div>
                <div class="oh-info-item">
                  <span class="oh-info-label">Room</span>
                  <span class="oh-info-val"><?= htmlspecialchars($officerClub['room'] ?? '—') ?></span>
                </div>
                <div class="oh-info-item">
                  <span class="oh-info-label">Founded</span>
                  <span class="oh-info-val"><?= htmlspecialchars($officerClub['founded'] ?? '—') ?></span>
                </div>
              </div>
              <?php if ($officerClub['club_desc']): ?>
                <p class="oh-club-desc"><?= htmlspecialchars($officerClub['club_desc']) ?></p>
              <?php endif; ?>
            </div>

            <!-- RECENT ACTIVITY -->
            <div class="card">
              <div class="card-header">
                <h2><i class="fas fa-clock-rotate-left"></i> Recent Activity</h2>
              </div>
              <?php if (empty($activity)): ?>
                <div class="oh-empty"><i class="fas fa-inbox"></i><span>No recent activity.</span></div>
              <?php else: ?>
                <div class="activity-list">
                  <?php foreach ($activity as $act):
                    $act_icon = $act['type'] === 'event' ? 'fa-calendar-plus' : 'fa-bullhorn';
                    $act_color = $act['type'] === 'event' ? 'act-teal' : 'act-green';
                    $act_time = ohRelTime($act['activity_at']);
                    ?>
                    <div class="activity-item">
                      <div class="activity-icon <?= $act_color ?>">
                        <i class="fas <?= $act_icon ?>"></i>
                      </div>
                      <div class="activity-body">
                        <div class="activity-label"><?= htmlspecialchars($act['label']) ?></div>
                        <div class="activity-type"><?= ucfirst($act['type']) ?> &nbsp;·&nbsp; <?= $act_time ?></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

          </div><!-- /right-col -->
        </div><!-- /dashboard-grid -->
      </div><!-- /content -->

    </main>
  </div><!-- /app -->


  <!-- ══════════════════ QUICK ANNOUNCE MODAL ══════════════════ -->
  <div class="modal-overlay" id="annModal" onclick="closeModal('annModal',event)">
    <div class="modal-box-sm">
      <div class="modal-hd">
        <span><i class="fas fa-bullhorn"></i> Post Announcement</span>
        <button class="modal-x" onclick="closeModal('annModal')"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body-form">
        <div class="mf-group">
          <label>Title <span class="req">*</span></label>
          <input type="text" id="annTitle" placeholder="Announcement title…" />
        </div>
        <div class="mf-group">
          <label>Description</label>
          <textarea id="annDesc" rows="3" placeholder="Details…"></textarea>
        </div>
        <div class="mf-row2">
          <div class="mf-group">
            <label>Category</label>
            <div class="custom-select-wrap" id="annCategoryWrap" style="position:relative;">
              <button type="button" class="custom-select-btn" id="annCategoryBtn" onclick="toggleOfficerDrop('annCategory',event)">General</button>
              <input type="hidden" id="annCategory" value="General" />
              <div class="custom-select-list" id="annCategoryList">
                <div class="custom-select-option selected" onclick="setOfficerDrop('annCategory','General','General')">General</div>
                <div class="custom-select-option" onclick="setOfficerDrop('annCategory','Event','Event')">Event</div>
                <div class="custom-select-option" onclick="setOfficerDrop('annCategory','Reminder','Reminder')">Reminder</div>
                <div class="custom-select-option" onclick="setOfficerDrop('annCategory','Achievement','Achievement')">Achievement</div>
              </div>
            </div>
          </div>
          <div class="mf-group">
            <label>Status</label>
            <div class="custom-select-wrap" id="annStatusWrap" style="position:relative;">
              <button type="button" class="custom-select-btn" id="annStatusBtn" onclick="toggleOfficerDrop('annStatus',event)">Info</button>
              <input type="hidden" id="annStatus" value="info" />
              <div class="custom-select-list" id="annStatusList">
                <div class="custom-select-option selected" onclick="setOfficerDrop('annStatus','info','Info')">Info</div>
                <div class="custom-select-option" onclick="setOfficerDrop('annStatus','approved','Approved')">Approved</div>
                <div class="custom-select-option" onclick="setOfficerDrop('annStatus','urgent','Urgent')">Urgent</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-ft">
        <button class="modal-cancel" onclick="closeModal('annModal')">Cancel</button>
        <button class="modal-submit" onclick="submitAnn()"><i class="fas fa-paper-plane"></i> Post</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════ QUICK EVENT MODAL ══════════════════ -->
  <div class="modal-overlay" id="evtModal" onclick="closeModal('evtModal',event)">
    <div class="modal-box-sm">
      <div class="modal-hd">
        <span><i class="fas fa-calendar-plus"></i> Create Event</span>
        <button class="modal-x" onclick="closeModal('evtModal')"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body-form">
        <div class="mf-group">
          <label>Event Name <span class="req">*</span></label>
          <input type="text" id="evtName" placeholder="e.g. General Assembly" />
        </div>
        <div class="mf-row2">
          <div class="mf-group">
            <label>Date <span class="req">*</span></label>
            <input type="date" id="evtDate" />
          </div>
          <div class="mf-group">
            <label>Location</label>
            <input type="text" id="evtLocation" placeholder="Room / venue" />
          </div>
        </div>
        <div class="mf-row2">
          <div class="mf-group">
            <label>Start Time</label>
            <input type="time" id="evtStart" />
          </div>
          <div class="mf-group">
            <label>End Time</label>
            <input type="time" id="evtEnd" />
          </div>
        </div>
        <div class="mf-group">
          <label>Description</label>
          <textarea id="evtDesc" rows="2" placeholder="What's this event about?"></textarea>
        </div>
      </div>
      <div class="modal-ft">
        <button class="modal-cancel" onclick="closeModal('evtModal')">Cancel</button>
        <button class="modal-submit" onclick="submitEvt()"><i class="fas fa-plus"></i> Create</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════ REJECT REASON MODAL ══════════════════ -->
  <div class="modal-overlay" id="rejectModal" onclick="closeModal('rejectModal',event)">
    <div class="modal-box-sm" style="max-width:380px;">
      <div class="modal-hd">
        <span><i class="fas fa-circle-xmark" style="color:#fca5a5;"></i> Reject Application</span>
        <button class="modal-x" onclick="closeModal('rejectModal')"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body-form">
        <input type="hidden" id="rejectAppId" />
        <div class="mf-group">
          <label>Reason <span style="color:var(--text-light);font-weight:400;">(optional)</span></label>
          <textarea id="rejectReason" rows="3" placeholder="Let the applicant know why…"></textarea>
        </div>
      </div>
      <div class="modal-ft">
        <button class="modal-cancel" onclick="closeModal('rejectModal')">Cancel</button>
        <button class="modal-submit danger" onclick="confirmReject()">
          <i class="fas fa-xmark"></i> Confirm Reject
        </button>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="crud-toast" id="crudToast"></div>

  <!-- Pass data to JS -->
  <script>
    window.OHData = {
      clubId: <?= $clubId ?>,
      userId: <?= $userId ?>,
      role: <?= json_encode($officerRole) ?>,
      clubName: <?= json_encode($clubName, JSON_HEX_TAG) ?>,
      unread: <?= $unreadNotifs ?>
    };
  </script>
  <script src="/public/assets/javascripts/officer_home.js"></script>
<script>
// ── Respond to Collaboration Proposal ─────────────────────
function respondCollab(requestId, response, btn) {
  const item = document.getElementById('collabReq_' + requestId);
  if (item) { item.style.opacity = '0.5'; item.style.pointerEvents = 'none'; }

  const fd = new FormData();
  fd.append('request_id', requestId);
  fd.append('response',   response);

  fetch('index.php?page=officer_explore&ajax=collab_respond', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        // Use existing toast if available
        if (typeof showToast === 'function') showToast(res.message, 'success');
        else alert(res.message);
        if (item) {
          item.style.transition = 'all 0.3s ease';
          item.style.maxHeight  = '0';
          item.style.overflow   = 'hidden';
          item.style.opacity    = '0';
          setTimeout(() => {
            item.remove();
            const list = document.querySelector('.collab-requests-list');
            if (list && !list.querySelector('.collab-request-item')) {
              const card = document.getElementById('collabRequestsCard');
              if (card) { card.style.transition='opacity .3s'; card.style.opacity='0'; setTimeout(()=>card.remove(),300); }
            }
          }, 350);
        }
      } else {
        if (typeof showToast === 'function') showToast(res.message || 'Error.', 'error');
        else alert(res.message || 'Error.');
        if (item) { item.style.opacity = '1'; item.style.pointerEvents = ''; }
      }
    })
    .catch(() => {
      if (typeof showToast === 'function') showToast('Network error.', 'error');
      if (item) { item.style.opacity = '1'; item.style.pointerEvents = ''; }
    });
}
</script>

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
/* swipe disabled */
</script>
<script>
function toggleOfficerDrop(id, e) {
  e.stopPropagation();
  const list = document.getElementById(id + 'List');
  const btn  = document.getElementById(id + 'Btn');
  const isOpen = list.classList.contains('open');
  document.querySelectorAll('.custom-select-list.open').forEach(el => el.classList.remove('open'));
  document.querySelectorAll('.custom-select-btn.open').forEach(el => el.classList.remove('open'));
  if (!isOpen) { list.classList.add('open'); btn.classList.add('open'); }
}
function setOfficerDrop(id, val, label) {
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
</body>

</html>