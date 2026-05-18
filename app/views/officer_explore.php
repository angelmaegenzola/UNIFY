<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/officer_explore_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Explore Clubs</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/officer_dashboard.css" />
  <link rel="stylesheet" href="/assets/css/officer_explore.css" />
  <link rel="stylesheet" href="/assets/css/transitions.css" />
</head>
<body>
<div class="app">

  <!-- ═══════ SIDEBAR ═══════ -->
   <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
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
        <a href="index.php?page=officer_explore" class="nav-item active">
          <i class="fas fa-compass"></i><span>Explore Clubs</span>
        </a>
        <a href="index.php?page=officer_home" class="nav-item">
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

  <!-- ═══════ MAIN ═══════ -->
  <main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Explore Clubs</span>
        <span class="topbar-date" id="topbarDate"></span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" id="exploreSearch"
                 placeholder="Search clubs by name, acronym, or category…"
                 oninput="filterClubs()" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications" onclick="toggleNotifPanel()">
          <i class="fas fa-bell"></i>
          <?php if ($unreadNotifs > 0): ?>
            <span class="badge"><?= $unreadNotifs ?></span>
          <?php endif; ?>
        </button>
        <a href="index.php?page=profile" class="topbar-profile" style="text-decoration:none;cursor:pointer;">
          <div class="topbar-avatar"><?php if(!empty($avatar_url)): ?><img src="<?= $avatar_url ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" /><?php else: ?><?= htmlspecialchars($userInit) ?><?php endif; ?></div>
          <div class="topbar-profile-info">
            <span class="tp-name"><?= htmlspecialchars($userName) ?></span>
            <span class="tp-role"><?= ucfirst(htmlspecialchars($officerRole)) ?></span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </a>
      </div>
    </header>

    <!-- CONTENT -->
    <div class="content exp-content">

      <!-- Hero strip -->
      <div class="exp-hero">
        <div class="exp-hero-left">
          <div class="exp-hero-label"><i class="fas fa-compass"></i> Campus Directory</div>
          <h2 class="exp-hero-title">Explore All Clubs</h2>
          <p class="exp-hero-sub">
            Browse the <?= $totalClubs ?> active clubs on campus. As an officer you can view any club's details,
            upcoming events, and officers — great for planning collaborations.
          </p>
        </div>
        <div class="exp-hero-stats">
          <div class="exp-stat-pill">
            <i class="fas fa-users"></i>
            <span id="expStatClubs"><?= $totalClubs ?></span> clubs
          </div>
          <div class="exp-stat-pill">
            <i class="fas fa-layer-group"></i>
            <span><?= count($categories) ?></span> categories
          </div>
        </div>
      </div>

      <!-- Filter bar -->
      <div class="exp-filter-bar">
        <div class="exp-filter-pills" id="filterPills">
          <button class="exp-pill active" onclick="setCat(this,'all')">
            <i class="fas fa-grip"></i> All
          </button>
          <?php foreach ($categories as $cat): ?>
          <button class="exp-pill" onclick="setCat(this,'<?= htmlspecialchars($cat) ?>')">
            <?php
              $icons = [
                'Tech'     => 'fa-microchip',
                'Arts'     => 'fa-palette',
                'Sports'   => 'fa-trophy',
                'Academic' => 'fa-book-open',
                'Science'  => 'fa-flask',
                'Advocacy' => 'fa-fist-raised',
                'Business' => 'fa-briefcase',
              ];
              $icon = $icons[$cat] ?? 'fa-circle';
            ?>
            <i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($cat) ?>
          </button>
          <?php endforeach; ?>
        </div>
        <div class="exp-filter-right">
          <span class="exp-count" id="expCount"><?= $totalClubs ?> clubs</span>
          <div class="exp-view-toggle">
            <button class="exp-view-btn active" id="btnGrid" onclick="setView('grid',this)" title="Grid">
              <i class="fas fa-grip"></i>
            </button>
            <button class="exp-view-btn" id="btnList" onclick="setView('list',this)" title="List">
              <i class="fas fa-list"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Club grid -->
      <div class="exp-grid" id="expGrid">
        <!-- Rendered by JS from window.EX.clubs -->
      </div>

      <!-- Empty state -->
      <div class="exp-empty" id="expEmpty" style="display:none;">
        <i class="fas fa-compass"></i>
        <p>No clubs match your search.</p>
        <span>Try a different keyword or filter.</span>
      </div>

    </div><!-- /content -->
  </main>
</div><!-- /app -->

<!-- ═══════ CLUB DETAIL MODAL ═══════ -->
<div class="modal-overlay" id="clubDetailModal" onclick="handleOverlayClick(event,'clubDetailModal')">
  <div class="modal exp-detail-modal">

    <div class="modal-header">
      <div style="display:flex;align-items:center;gap:12px;">
        <div class="exp-modal-logo-wrap" id="mdLogoWrap">
          <img id="mdLogo" src="" alt="" style="display:none;"
               onerror="this.style.display='none';document.getElementById('mdLogoFallback').style.display='flex'">
          <div id="mdLogoFallback" class="exp-modal-logo-fallback"><i class="fas fa-users"></i></div>
        </div>
        <div>
          <div class="modal-title" id="mdName">—</div>
          <div style="font-size:11px;color:var(--text-light);margin-top:2px;" id="mdAcronym">—</div>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('clubDetailModal')">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Stat strip -->
    <div class="exp-modal-stats">
      <div class="exp-mstat">
        <div class="exp-mstat-val" id="mdMembers">—</div>
        <div class="exp-mstat-label">Members</div>
      </div>
      <div class="exp-mstat">
        <div class="exp-mstat-val" id="mdCategory">—</div>
        <div class="exp-mstat-label">Category</div>
      </div>
      <div class="exp-mstat">
        <div class="exp-mstat-val" id="mdFounded">—</div>
        <div class="exp-mstat-label">Founded</div>
      </div>
      <div class="exp-mstat">
        <div class="exp-mstat-val" id="mdRoom">—</div>
        <div class="exp-mstat-label">Room</div>
      </div>
    </div>

    <!-- Body tabs -->
    <div class="exp-modal-tabs">
      <button class="exp-tab active" onclick="switchTab(this,'tabAbout')">About</button>
      <button class="exp-tab" onclick="switchTab(this,'tabOfficers')">Officers</button>
      <button class="exp-tab" onclick="switchTab(this,'tabEvents')">Upcoming Events</button>
    </div>

    <div class="modal-body exp-modal-body">

      <!-- Tab: About -->
      <div id="tabAbout" class="exp-tab-panel">
        <p class="exp-modal-desc" id="mdDesc">—</p>
      </div>

      <!-- Tab: Officers -->
      <div id="tabOfficers" class="exp-tab-panel" style="display:none;">
        <div id="mdOfficersList" class="exp-officers-list">
          <div class="exp-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
        </div>
      </div>

      <!-- Tab: Upcoming Events -->
      <div id="tabEvents" class="exp-tab-panel" style="display:none;">
        <div id="mdEventsList" class="exp-events-list">
          <div class="exp-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
        </div>
      </div>

    </div>

    <!-- Footer — no Apply button for officers -->
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('clubDetailModal')">Close</button>
      <?php if (in_array($officerRole, ['president','vice president'])): ?>
      <button class="btn-primary" id="mdCollabBtn" onclick="openCollab()" style="display:none;">
        <i class="fas fa-handshake"></i> Propose Collaboration
      </button>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- ═══════ COLLAB MODAL (president/VP only) ═══════ -->
<?php if (in_array($officerRole, ['president','vice president'])): ?>
<div class="modal-overlay" id="collabModal" onclick="handleOverlayClick(event,'collabModal')">
  <div class="modal modal-xs">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-handshake"></i> Propose Collaboration</span>
      <button class="modal-close" onclick="closeModal('collabModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="form-group" style="margin-top:8px;">
      <label class="form-label">Club you're reaching out to</label>
      <input class="form-input" id="collabTargetClub" type="text" readonly>
    </div>
    <div class="form-group">
      <label class="form-label">Proposed event / activity *</label>
      <input class="form-input" id="collabEventName" type="text" placeholder="e.g. Joint Tech Seminar 2026" maxlength="191">
    </div>
    <div class="form-group">
      <label class="form-label">Proposed date</label>
      <input class="form-input" id="collabDate" type="date">
    </div>
    <div class="form-group">
      <label class="form-label">Message</label>
      <textarea class="form-textarea" id="collabMessage"
                placeholder="Brief description of what you have in mind…"
                style="min-height:80px;"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('collabModal')">Cancel</button>
      <button class="btn-primary" onclick="sendCollab()">
        <i class="fas fa-paper-plane"></i> Send Proposal
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- ═══════ DATA INJECTION ═══════ -->
<script>
window.EX = {
  userId   : <?= $userId ?>,
  myClubId : <?= $myClubId ?? 'null' ?>,
  clubName : <?= json_encode($clubName) ?>,
  role     : <?= json_encode($officerRole) ?>,
  clubs    : <?= json_encode($allClubs, JSON_HEX_TAG | JSON_HEX_APOS) ?>,
  categories: <?= json_encode($categories, JSON_HEX_TAG | JSON_HEX_APOS) ?>
};
</script>
<script src="/assets/javascripts/officer_explore.js"></script>
</body>
</html>