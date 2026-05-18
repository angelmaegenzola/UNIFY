<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/app/controllers/myclubs_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — My Clubs</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/UNIFY(db)/public/assets/css/myclubs.css" />
</head>
<body>

<div class="app">

  <!-- ═══════════ SIDEBAR ═══════════ -->
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
      <a href="index.php?page=studenthome" class="nav-item ">
        <i class="fas fa-house"></i><span>Home</span>
      </a>
      <a href="index.php?page=myclubs" class="nav-item active">
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

  <!-- ═══════════ MAIN ═══════════ -->
  <main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">My Clubs</span>
        <span class="topbar-date" id="topbarDate"></span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" id="searchInput" placeholder="Search your clubs…" oninput="filterMyClubs()" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" id="notifBtn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="badge red" id="notifBadge">3</span>
        </button>

        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-header">
            <span class="notif-header-title"><i class="fas fa-bell"></i> Notifications</span>
            <button class="notif-mark-btn" id="markAllBtn">Mark all read</button>
          </div>
          <div class="notif-list">
            <div class="notif-item unread">
              <div class="notif-dot"></div>
              <div class="notif-content">
                <span class="notif-text"><strong>ITS</strong> posted a new announcement.</span>
                <span class="notif-time"><i class="fas fa-clock"></i> 30 mins ago</span>
              </div>
            </div>
            <div class="notif-item unread">
              <div class="notif-dot"></div>
              <div class="notif-content">
                <span class="notif-text">Upcoming event: <strong>Tech Talk</strong> on Apr 25.</span>
                <span class="notif-time"><i class="fas fa-clock"></i> 2 hours ago</span>
              </div>
            </div>
            <div class="notif-item unread">
              <div class="notif-dot"></div>
              <div class="notif-content">
                <span class="notif-text"><strong>DPSF</strong> General Assembly this Friday!</span>
                <span class="notif-time"><i class="fas fa-clock"></i> 5 hours ago</span>
              </div>
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
            <span class="tp-role"><?= ucwords($my_role) ?></span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </a>
      </div>
    </header>

    <!-- MEMBERSHIP BANNER -->
    <div class="membership-banner" id="membershipBanner">
      <div class="mb-text">
        <div class="mb-title">You're an active member! 🎉</div>
        <div class="mb-sub">Manage your clubs, track events, and stay connected with your communities below.</div>
      </div>
      <div class="mb-steps">
        <div class="mb-step done"><i class="fas fa-check"></i> Sign Up</div>
        <div class="mb-step-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="mb-step done"><i class="fas fa-check"></i> Explore</div>
        <div class="mb-step-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="mb-step done"><i class="fas fa-check"></i> Apply</div>
        <div class="mb-step-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="mb-step active"><i class="fas fa-door-open"></i> Access</div>
      </div>
    </div>

    <!-- FILTER / ACTION BAR -->
    <div class="filter-bar">
      <div class="filter-label"><i class="fas fa-filter"></i> Filter by:</div>
      <div class="filter-pills" id="filterPills">
        <button class="pill active" onclick="setCat(this,'all')">All</button>
        <button class="pill" onclick="setCat(this,'Tech')"><i class="fas fa-microchip"></i> Tech</button>
        <button class="pill" onclick="setCat(this,'Arts')"><i class="fas fa-palette"></i> Arts</button>
        <button class="pill" onclick="setCat(this,'Sports')"><i class="fas fa-trophy"></i> Sports</button>
        <button class="pill" onclick="setCat(this,'Academic')"><i class="fas fa-graduation-cap"></i> Academic</button>
        <button class="pill" onclick="setCat(this,'Engineering')"><i class="fas fa-gear"></i> Engineering</button>
      </div>
      <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
        <span class="filter-count" id="filterCount">0 clubs</span>
        <a href="index.php?page=explore" class="explore-more-btn">
          <i class="fas fa-compass"></i> Explore More
        </a>
      </div>
    </div>

    <!-- CLUB GRID -->
    <div class="clubs-grid" id="myClubsGrid"></div>

    <!-- EMPTY STATE -->
    <div class="empty-state" id="emptyState" style="display:none;">
      <i class="fas fa-users-slash"></i>
      <p>You haven't joined any clubs yet.</p>
      <span>Explore available clubs and submit an application to get started.</span>
      <a href="index.php?page=explore" class="empty-explore-btn">
        <i class="fas fa-compass"></i> Explore Clubs
      </a>
    </div>

    <!-- NO SEARCH RESULTS -->
    <div class="empty-state" id="noResults" style="display:none;">
      <i class="fas fa-face-meh-blank"></i>
      <p>No clubs match your search.</p>
      <span>Try a different keyword or category.</span>
    </div>

  </main>
</div>


<!-- ═══════════ CLUB DETAIL MODAL ═══════════ -->
<div class="modal-overlay" id="detailOverlay" onclick="closeDetailOverlay(event)">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-logo-wrap">
          <img class="modal-club-logo" id="mdLogo" src="" alt=""
               onerror="this.style.display='none';document.getElementById('mdLogoFallback').style.display='flex'">
          <div class="modal-logo-fallback" id="mdLogoFallback" style="display:none;">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <div>
          <div class="modal-title" id="mdName">Club Name</div>
          <div class="modal-subtitle" id="mdCatLine">Category · Role</div>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('detailOverlay')">
        <i class="fas fa-xmark"></i>
      </button>
    </div>

    <div class="modal-body">
      <div class="modal-stats-strip">
        <div class="modal-stat-box">
          <div class="mstat-val" id="mdMembers">—</div>
          <div class="mstat-label">Members</div>
        </div>
        <div class="modal-stat-box">
          <div class="mstat-val" id="mdEvents">—</div>
          <div class="mstat-label">Events</div>
        </div>
        <div class="modal-stat-box">
          <div class="mstat-val" id="mdFounded">—</div>
          <div class="mstat-label">Founded</div>
        </div>
        <div class="modal-stat-box green-stat">
          <div class="mstat-val">Active</div>
          <div class="mstat-label">Status</div>
        </div>
      </div>

      <div class="modal-section">
        <div class="modal-section-title">About this Club</div>
        <p class="modal-desc" id="mdDesc"></p>
      </div>

      <div class="modal-divider"></div>

      <div class="modal-section">
        <div class="modal-section-title">Club Officers</div>
        <div id="mdOfficers" class="officer-list"></div>
      </div>

      <div class="modal-divider"></div>

      <div class="modal-section">
        <div class="modal-section-title">Your Membership</div>
        <div class="membership-info-row">
          <span><i class="fas fa-id-badge"></i> <strong>Role:</strong> <span id="mdRole">Member</span></span>
          <span><i class="fas fa-calendar-check"></i> <strong>Joined:</strong> <span id="mdJoined">—</span></span>
          <span><i class="fas fa-location-dot"></i> <strong>Room:</strong> <span id="mdRoom">—</span></span>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="modal-btn-danger" onclick="promptLeave()">
        <i class="fas fa-right-from-bracket"></i> Leave Club
      </button>
      <div style="flex:1;"></div>
      <button class="modal-btn-cancel" onclick="closeModal('detailOverlay')">Close</button>
      <button class="modal-btn-primary" onclick="goToEvents()">
        <i class="fas fa-calendar-days"></i> View Events
      </button>
    </div>
  </div>
</div>


<!-- ═══════════ LEAVE CONFIRM MODAL ═══════════ -->
<div class="modal-overlay" id="leaveOverlay" onclick="closeLeaveOverlay(event)">
  <div class="modal-box confirm-box">
    <div class="confirm-icon-wrap">
      <div class="confirm-icon"><i class="fas fa-right-from-bracket"></i></div>
    </div>
    <div class="confirm-title">Leave this club?</div>
    <div class="confirm-msg" id="leaveMsg">You'll lose access to club features and will need to reapply to rejoin.</div>
    <div class="confirm-actions">
      <button class="modal-btn-cancel" onclick="closeModal('leaveOverlay')">Cancel</button>
      <button class="modal-btn-danger full-btn" onclick="confirmLeave()">
        <i class="fas fa-right-from-bracket"></i> Yes, Leave
      </button>
    </div>
  </div>
</div>


<!-- ═══════════ EVENTS MODAL ═══════════ -->
<div class="modal-overlay" id="eventsOverlay" onclick="closeEventsOverlay(event)">
  <div class="modal-box events-box">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-logo-wrap" style="width:36px;height:36px;border-radius:9px;overflow:hidden;">
          <img id="evModalLogo" src="" alt="" style="width:100%;height:100%;object-fit:cover;"
               onerror="this.style.display='none'">
        </div>
        <div>
          <div class="modal-title" id="evModalName">Events</div>
          <div class="modal-subtitle">Upcoming Club Events</div>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('eventsOverlay')">
        <i class="fas fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body" id="evModalBody"></div>
    <div class="modal-footer" style="justify-content:flex-end;">
      <button class="modal-btn-cancel" onclick="closeModal('eventsOverlay')">Close</button>
      <a href="index.php?page=events" class="modal-btn-primary" style="text-decoration:none;">
        <i class="fas fa-calendar-days"></i> All Events
      </a>
    </div>
  </div>
</div>


<!-- Toast -->
<div class="crud-toast" id="crudToast"></div>

<script src="/UNIFY(db)/public/assets/javascripts/myclubs.js"></script>
<?php
  // If redirected from explore with a specific club_id, auto-open that club
  $open_club_id = (int)($_GET['club_id'] ?? 0);
?>
<?php if ($open_club_id > 0): ?>

<?php endif; ?>

<script>
// Wait for myclubs.js to finish loading clubs, then open the target club
  document.addEventListener('DOMContentLoaded', function () {
    const targetId = <?= $open_club_id ?>;
    // myclubs.js fires a custom event or we poll for the club card
    function tryOpenClub(attempts) {
      if (attempts <= 0) return;
      // Try finding the club tab/card rendered by myclubs.js
      const clubCard = document.querySelector(`[data-club-id="${targetId}"]`);
      const clubBtn  = document.querySelector(`[data-id="${targetId}"]`);
      const target   = clubCard || clubBtn;
      if (target) {
        target.click();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        setTimeout(() => tryOpenClub(attempts - 1), 200);
      }
    }
    tryOpenClub(20); // try for up to 4 seconds
  });
</script>
</body>
</html>