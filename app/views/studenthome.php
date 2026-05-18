<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/app/controllers/studenthome_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UNIFY — <?= $has_club ? 'Home' : 'Explore' ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/UNIFY(db)/public/assets/css/studenthome.css"/>
  <?php if ($has_club): ?>
  <?php endif; ?>
</head>
<body>
<div class="app">

  <!-- ═══════ SIDEBAR ═══════ -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="/UNIFY(db)/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
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
      <a href="index.php?page=studenthome" class="nav-item active">
        <i class="fas fa-house"></i><span>Home</span>
      </a>
      <a href="index.php?page=myclubs" class="nav-item">
        <i class="fas fa-users"></i><span>My Clubs</span>
      </a>
      <a href="index.php?page=studentevents" class="nav-item">
        <i class="fas fa-calendar-days"></i><span>Events</span>
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

  <!-- ═══════ MAIN ═══════ -->
  <main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title"><?= $has_club ? 'Home' : 'Explore' ?></span>
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
            <span class="tp-role"><?= $has_club ? ucfirst($my_role) : 'Student' ?></span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </a>
      </div>
    </header>

    <?php if (!$has_club): ?>
    <!-- ══════════════════════════════════════════════════════
         STATE A: NO CLUB — EXPLORE-ONLY VIEW
    ══════════════════════════════════════════════════════ -->
    <div class="content">

      <div class="explore-hero">
        <div class="eh-deco">
          <div class="eh-ring r1"></div>
          <div class="eh-ring r2"></div>
          <div class="eh-ring r3"></div>
        </div>
        <div class="eh-body">
          <div class="eh-greeting" id="wbGreeting">Good morning 👋</div>
          <h2 class="eh-name">Welcome, <?= $first_name ?>!</h2>
          <p class="eh-sub">
            You're not part of a club yet. Browse the <?= $club_total ?> active clubs on campus
            and apply to one that matches your interests.
          </p>
          <div class="eh-actions">
            <a href="index.php?page=explore" class="eh-btn-primary">
              <i class="fas fa-compass"></i> Browse Clubs
            </a>
            <a href="index.php?page=studentprofile" class="eh-btn-ghost">
              <i class="fas fa-user"></i> Complete Profile
            </a>
          </div>
        </div>
        <div class="eh-stat-pill">
          <i class="fas fa-users"></i>
          <span><?= $club_total ?> clubs available</span>
        </div>
      </div>

      <div class="steps-card">
        <div class="steps-header"><i class="fas fa-route"></i><span>How to join a club</span></div>
        <div class="steps-list">
          <div class="step-item done">
            <div class="step-dot"><i class="fas fa-check"></i></div>
            <div class="step-body"><div class="step-title">Create your account</div><div class="step-sub">You're signed in — you're all set here.</div></div>
          </div>
          <div class="step-line done"></div>
          <div class="step-item done">
            <div class="step-dot"><i class="fas fa-check"></i></div>
            <div class="step-body"><div class="step-title">Complete your profile</div><div class="step-sub">Add your course, year level, and student ID.</div></div>
          </div>
          <div class="step-line active"></div>
          <div class="step-item active">
            <div class="step-dot"><i class="fas fa-compass"></i></div>
            <div class="step-body"><div class="step-title">Browse &amp; apply to a club</div><div class="step-sub">Explore active clubs and submit your application.</div></div>
          </div>
          <div class="step-line"></div>
          <div class="step-item">
            <div class="step-dot"><i class="fas fa-hourglass-half"></i></div>
            <div class="step-body"><div class="step-title">Wait for approval</div><div class="step-sub">Officers will review and approve your application.</div></div>
          </div>
          <div class="step-line"></div>
          <div class="step-item">
            <div class="step-dot"><i class="fas fa-unlock"></i></div>
            <div class="step-body"><div class="step-title">Get full access</div><div class="step-sub">Once approved, your dashboard and club features unlock.</div></div>
          </div>
        </div>
      </div>

      <div class="campus-grid">
        <div class="home-card">
          <div class="hc-header"><div class="hc-title"><i class="fas fa-calendar-days"></i> Campus Events</div></div>
          <?php if (empty($campus_events)): ?>
          <div class="empty-state"><i class="fas fa-calendar-xmark"></i><span>No upcoming events.</span></div>
          <?php else: ?>
          <div class="events-list">
            <?php foreach ($campus_events as $ev):
              $ev_date  = new DateTime($ev['event_date']);
              $ev_month = strtoupper($ev_date->format('M'));
              $ev_day   = $ev_date->format('j');
              $ev_time  = $ev['start_time'] ? date('g:i A', strtotime($ev['start_time'])) : 'TBA';
            ?>
            <div class="ev-item">
              <div class="ev-date-box"><span class="ev-month"><?= $ev_month ?></span><span class="ev-day"><?= $ev_day ?></span></div>
              <div class="ev-body">
                <div class="ev-title"><?= htmlspecialchars($ev['name']) ?></div>
                <div class="ev-meta"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($ev['location'] ?? 'TBA') ?> &nbsp;·&nbsp; <i class="fas fa-clock"></i> <?= $ev_time ?></div>
                <div class="ev-club-tag"><?= htmlspecialchars($ev['acronym'] ?? $ev['club_name']) ?></div>
              </div>
              <span class="ev-tag open">Open</span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="home-card">
          <div class="hc-header"><div class="hc-title"><i class="fas fa-bullhorn"></i> Campus Announcements</div></div>
          <?php if (empty($campus_ann)): ?>
          <div class="empty-state"><i class="fas fa-bell-slash"></i><span>No announcements yet.</span></div>
          <?php else: ?>
          <div class="announcements-list">
            <?php foreach ($campus_ann as $ann):
              $a_status = $ann['status'] ?? 'info';
              $a_icon   = $ann_icon[$a_status]  ?? 'fa-circle-info';
              $a_color  = $ann_color[$a_status] ?? 'ann-teal';
              $a_ago    = human_time_diff(strtotime($ann['posted_at']));
            ?>
            <div class="ann-item">
              <div class="ann-icon <?= $a_color ?>"><i class="fas <?= $a_icon ?>"></i></div>
              <div class="ann-body">
                <div class="ann-title"><?= htmlspecialchars($ann['title']) ?></div>
                <div class="ann-sub"><?= htmlspecialchars(mb_strimwidth($ann['description'] ?? '', 0, 100, '…')) ?></div>
                <div class="ann-meta">
                  <?php if ($ann['club_name']): ?><span class="ann-tag info"><?= htmlspecialchars($ann['club_name']) ?></span><?php endif; ?>
                  <span class="ann-time"><?= $a_ago ?></span>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /content -->

    <?php else: ?>
    <!-- ══════════════════════════════════════════════════════
         STATE B: APPROVED MEMBER DASHBOARD — CLUB SWITCHER
    ══════════════════════════════════════════════════════ -->
    <div class="content-with-switcher">

      <!-- ── CLUB SWITCHER NAV ── -->
      <nav class="club-switcher" id="clubSwitcher">
        <?php foreach ($my_clubs_all as $i => $mc):
          $cid     = (int)$mc['club_id'];
          $mc_rb   = $role_badge_map[$mc['role']] ?? ['label' => ucfirst($mc['role']), 'class' => 'role-member'];
        ?>
        <button
          class="cs-tab <?= $i === 0 ? 'active' : '' ?>"
          data-club="<?= $cid ?>"
          onclick="switchClub(<?= $cid ?>)"
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

      <!-- ── ONE PANEL PER CLUB ── -->
      <div class="panel-scroll">
      <?php foreach ($my_clubs_all as $i => $mc):
        $cid       = (int)$mc['club_id'];
        $cd        = $all_club_data[$cid];
        $mc_role   = $mc['role'];
        $mc_rb     = $role_badge_map[$mc_role] ?? ['label' => ucfirst($mc_role), 'class' => 'role-member'];
        $club_members = $cd['members'];
        $club_events  = $cd['events'];
        $club_ann     = $cd['ann'];
      ?>
      <div class="club-panel <?= $i === 0 ? 'active' : '' ?>" id="panel-<?= $cid ?>">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
          <div class="wb-deco-rings">
            <div class="wb-ring r1"></div>
            <div class="wb-ring r2"></div>
            <div class="wb-ring r3"></div>
          </div>
          <div class="wb-body">
            <div class="wb-greeting" id="wbGreeting-<?= $cid ?>">Good morning 👋</div>
            <h2 class="wb-name">Welcome back, <?= $first_name ?>!</h2>
            <p class="wb-sub">
              You're an active <strong><?= $mc_rb['label'] ?></strong> of
              <strong><?= htmlspecialchars($mc['club_name']) ?></strong>.
              Here's what's happening today.
            </p>
            <div class="wb-actions">
              <a href="index.php?page=myclubs&club_id=<?= $cid ?>" class="wb-btn-primary">
                <i class="fas fa-users"></i> My Club
              </a>
              <a href="index.php?page=studentevents" class="wb-btn-ghost">
                <i class="fas fa-calendar-days"></i> View Events
              </a>
              <?php if ($is_officer): ?>
              <a href="index.php?page=officer_dashboard" class="wb-btn-ghost" style="border-color:#2a7a48;color:#2a7a48;">
                <i class="fas fa-shield-halved"></i> Officer Dashboard
              </a>
              <?php endif; ?>
            </div>
          </div>
          <div class="wb-status-badge active-badge">
            <div class="wsb-pulse"></div>
            <span>Active <?= $mc_rb['label'] ?></span>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
          <div class="qs-card qs-green">
            <div class="qs-icon"><i class="fas fa-users"></i></div>
            <div class="qs-info">
              <div class="qs-val"><?= (int)$mc['member_count'] ?></div>
              <div class="qs-label">Club Members</div>
            </div>
          </div>
          <div class="qs-card qs-teal">
            <div class="qs-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="qs-info">
              <div class="qs-val"><?= $event_count ?></div>
              <div class="qs-label">Events Attended</div>
            </div>
          </div>
          <div class="qs-card qs-purple">
            <div class="qs-icon"><i class="fas fa-star"></i></div>
            <div class="qs-info">
              <div class="qs-val"><?= $mc_rb['label'] ?></div>
              <div class="qs-label">My Role</div>
            </div>
          </div>
          <div class="qs-card qs-yellow">
            <div class="qs-icon"><i class="fas fa-calendar-days"></i></div>
            <div class="qs-info">
              <div class="qs-val"><?= count($club_events) ?></div>
              <div class="qs-label">Upcoming Events</div>
            </div>
          </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="home-grid">

          <!-- LEFT -->
          <div class="hg-left">

            <!-- Club Info Card -->
            <div class="home-card">
              <div class="hc-header">
                <div class="hc-title"><i class="fas fa-users"></i> <?= htmlspecialchars($mc['club_name']) ?></div>
                <a href="index.php?page=myclubs&club_id=<?= $cid ?>" class="hc-link">Details <i class="fas fa-arrow-right"></i></a>
              </div>
              <div class="club-info-block">
                <?php if ($mc['logo_path']): ?>
                  <img class="club-logo"
                       src="<?= htmlspecialchars($mc['logo_path']) ?>"
                       alt="<?= htmlspecialchars($mc['club_name']) ?>"
                       onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                  <div class="club-logo-fallback" style="display:none"><i class="fas fa-users"></i></div>
                <?php else: ?>
                  <div class="club-logo-fallback" style="display:flex"><i class="fas fa-users"></i></div>
                <?php endif; ?>
                <div class="club-info-text">
                  <div class="club-info-name"><?= htmlspecialchars($mc['club_name']) ?></div>
                  <div class="club-info-meta">
                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($mc['category'] ?? '—') ?></span>
                    <span><i class="fas fa-users"></i> <?= (int)$mc['member_count'] ?> members</span>
                    <span><i class="fas fa-location-dot"></i> <?= htmlspecialchars($mc['room'] ?? 'TBA') ?></span>
                  </div>
                </div>
                <span class="role-badge <?= $mc_rb['class'] ?>">
                  <i class="fas fa-circle-check"></i> <?= $mc_rb['label'] ?>
                </span>
              </div>
              <?php if ($mc['club_desc']): ?>
              <p class="club-desc-text"><?= htmlspecialchars($mc['club_desc']) ?></p>
              <?php endif; ?>
            </div>

            <!-- Members Preview -->
            <div class="home-card">
              <div class="hc-header">
                <div class="hc-title"><i class="fas fa-id-badge"></i> Club Members</div>
                <a href="index.php?page=myclubs&club_id=<?= $cid ?>" class="hc-link">See All <i class="fas fa-arrow-right"></i></a>
              </div>
              <?php if (empty($club_members)): ?>
              <div class="empty-state"><i class="fas fa-users"></i><span>No members found.</span></div>
              <?php else: ?>
              <div class="member-list">
                <?php foreach ($club_members as $mbr):
                  $m_init = strtoupper(substr($mbr['first_name'],0,1) . substr($mbr['last_name'],0,1));
                  $m_name = htmlspecialchars($mbr['first_name'] . ' ' . $mbr['last_name']);
                  $mrb    = $role_badge_map[$mbr['role']] ?? ['label' => ucfirst($mbr['role']), 'class' => 'role-member'];
                ?>
                <div class="member-row">
                  <div class="member-avatar"><?= $m_init ?></div>
                  <div class="member-info">
                    <div class="member-name"><?= $m_name ?></div>
                    <div class="member-role-text"><?= $mrb['label'] ?></div>
                  </div>
                  <span class="role-badge-sm <?= $mrb['class'] ?>"><?= $mrb['label'] ?></span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>

          </div><!-- /hg-left -->

          <!-- RIGHT -->
          <div class="hg-right">

            <!-- Club Events -->
            <div class="home-card">
              <div class="hc-header">
                <div class="hc-title"><i class="fas fa-calendar-days"></i> Club Events</div>
                <a href="index.php?page=studentevents" class="hc-link">See All <i class="fas fa-arrow-right"></i></a>
              </div>
              <?php if (empty($club_events)): ?>
              <div class="empty-state"><i class="fas fa-calendar-xmark"></i><span>No upcoming events.</span></div>
              <?php else: ?>
              <div class="events-list">
                <?php foreach ($club_events as $ev):
                  $ev_date  = new DateTime($ev['event_date']);
                  $ev_month = strtoupper($ev_date->format('M'));
                  $ev_day   = $ev_date->format('j');
                  $ev_time  = $ev['start_time'] ? date('g:i A', strtotime($ev['start_time'])) : 'TBA';
                ?>
                <div class="ev-item">
                  <div class="ev-date-box"><span class="ev-month"><?= $ev_month ?></span><span class="ev-day"><?= $ev_day ?></span></div>
                  <div class="ev-body">
                    <div class="ev-title"><?= htmlspecialchars($ev['name']) ?></div>
                    <div class="ev-meta"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($ev['location'] ?? 'TBA') ?> &nbsp;·&nbsp; <i class="fas fa-clock"></i> <?= $ev_time ?></div>
                  </div>
                  <span class="ev-tag open">Open</span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>

            <!-- Club Announcements -->
            <div class="home-card">
              <div class="hc-header">
                <div class="hc-title"><i class="fas fa-bullhorn"></i> Club Announcements</div>
              </div>
              <?php if (empty($club_ann)): ?>
              <div class="empty-state"><i class="fas fa-bell-slash"></i><span>No announcements from your club yet.</span></div>
              <?php else: ?>
              <div class="announcements-list">
                <?php foreach ($club_ann as $ann):
                  $a_status = $ann['status'] ?? 'info';
                  $a_icon   = $ann_icon[$a_status]  ?? 'fa-circle-info';
                  $a_color  = $ann_color[$a_status] ?? 'ann-teal';
                  $a_ago    = human_time_diff(strtotime($ann['posted_at']));
                ?>
                <div class="ann-item">
                  <div class="ann-icon <?= $a_color ?>"><i class="fas <?= $a_icon ?>"></i></div>
                  <div class="ann-body">
                    <div class="ann-title"><?= htmlspecialchars($ann['title']) ?></div>
                    <div class="ann-sub"><?= htmlspecialchars(mb_strimwidth($ann['description'] ?? '', 0, 100, '…')) ?></div>
                    <div class="ann-meta"><span class="ann-time"><?= $a_ago ?></span></div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>

            <!-- Campus Announcements -->
            <div class="home-card">
              <div class="hc-header">
                <div class="hc-title"><i class="fas fa-globe"></i> Campus Announcements</div>
              </div>
              <?php if (empty($campus_ann)): ?>
              <div class="empty-state"><i class="fas fa-bell-slash"></i><span>No announcements yet.</span></div>
              <?php else: ?>
              <div class="announcements-list">
                <?php foreach ($campus_ann as $ann):
                  $a_status = $ann['status'] ?? 'info';
                  $a_icon   = $ann_icon[$a_status]  ?? 'fa-circle-info';
                  $a_color  = $ann_color[$a_status] ?? 'ann-teal';
                  $a_ago    = human_time_diff(strtotime($ann['posted_at']));
                ?>
                <div class="ann-item">
                  <div class="ann-icon <?= $a_color ?>"><i class="fas <?= $a_icon ?>"></i></div>
                  <div class="ann-body">
                    <div class="ann-title"><?= htmlspecialchars($ann['title']) ?></div>
                    <div class="ann-sub"><?= htmlspecialchars(mb_strimwidth($ann['description'] ?? '', 0, 100, '…')) ?></div>
                    <div class="ann-meta">
                      <?php if ($ann['club_name']): ?><span class="ann-tag info"><?= htmlspecialchars($ann['club_name']) ?></span><?php endif; ?>
                      <span class="ann-time"><?= $a_ago ?></span>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>

          </div><!-- /hg-right -->
        </div><!-- /home-grid -->
      </div><!-- /club-panel -->
      <?php endforeach; ?>
      </div><!-- /panel-scroll -->

    </div><!-- /content-with-switcher -->
    <?php endif; ?>

  </main>
</div><!-- /app -->

<div class="crud-toast" id="crudToast"></div>
<script src="/UNIFY(db)/public/assets/javascripts/studenthome.js"></script>


<script>
// ── Club switcher ──────────────────────────────────────────
function switchClub(clubId) {
  document.querySelectorAll('.cs-tab').forEach(t => {
    t.classList.toggle('active', parseInt(t.dataset.club) === clubId);
  });
  document.querySelectorAll('.club-panel').forEach(p => {
    p.classList.toggle('active', p.id === 'panel-' + clubId);
  });
}

// ── Set greeting per panel based on time ─────────────────
(function() {
  const h = new Date().getHours();
  const g = h < 12 ? 'Good morning 👋' : h < 18 ? 'Good afternoon 👋' : 'Good evening 👋';
  document.querySelectorAll('[id^="wbGreeting-"]').forEach(el => el.textContent = g);
  const wbg = document.getElementById('wbGreeting');
  if (wbg) wbg.textContent = g;
})();

<?php if ($has_club): ?>
// Auto-open club if ?club=ID is in the URL
(function() {
  const params = new URLSearchParams(window.location.search);
  const cid = parseInt(params.get('club'));
  if (cid) switchClub(cid);
})();
<?php endif; ?>
</script>
</body>
</html>