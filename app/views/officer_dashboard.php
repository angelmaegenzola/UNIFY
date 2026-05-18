<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/officer_dashboard_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Officer Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/officer_dashboard.css" />
  <link rel="stylesheet" href="/assets/css/transitions.css" />
</head>
<body>
<div class="app">

  <!-- ── SIDEBAR ────────────────────────────────────────────── -->
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
        <a href="index.php?page=officer_dashboard" class="nav-item active">
          <i class="fas fa-house"></i><span>Dashboard</span>
        </a>
        <a href="index.php?page=officer_members" class="nav-item ">
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

  <!-- ── MAIN ───────────────────────────────────────────────── -->
  <main class="main">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Officer Dashboard</span>
        <span class="topbar-date" id="topbarDate"></span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" id="dashSearchInput"
                 placeholder="Search announcements…"
                 oninput="handleSearch()" />
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

    <!-- Content -->
    <div class="content">
      <div class="dashboard-grid" id="dashboardGrid">

        <!-- ── LEFT COLUMN ────────────────────────────────── -->
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

          <!-- Stat Cards -->
          <div class="stat-cards-grid">
            <div class="stat-card-new sc-green" onclick="scrollToSection('annSection')">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-users"></i></div>
                <span class="sc-trend">Active</span>
              </div>
              <div class="sc-value"><?= $totalMembers ?></div>
              <div class="sc-label">Club Members</div>
            </div>
            <div class="stat-card-new sc-teal" onclick="openAddEventModal()">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-calendar-check"></i></div>
                <span class="sc-trend">Upcoming</span>
              </div>
              <div class="sc-value"><?= $upcomingEvents ?></div>
              <div class="sc-label">Events Scheduled</div>
            </div>
            <div class="stat-card-new sc-yellow" onclick="openAddAnnouncementModal()">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-bullhorn"></i></div>
                <span class="sc-trend">Posted</span>
              </div>
              <div class="sc-value"><?= $totalAnnouncements ?></div>
              <div class="sc-label">Announcements</div>
            </div>
            <div class="stat-card-new sc-blue" onclick="window.location='index.php?page=officer_messages'">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-clock"></i></div>
                <span class="sc-trend <?= $pendingApps > 0 ? 'urgent' : '' ?>">
                  <?= $pendingApps > 0 ? 'Pending' : 'Clear' ?>
                </span>
              </div>
              <div class="sc-value"><?= $pendingApps ?></div>
              <div class="sc-label">Pending Applications</div>
            </div>
          </div>

          <!-- Announcements -->
          <div id="annSection" class="section-row">
            <h3 class="section-title">Club Announcements</h3>
            <button class="add-item-btn" onclick="openAddAnnouncementModal()">
              <i class="fas fa-plus"></i> Add
            </button>
          </div>

          <div class="card announce-card">
            <div class="table-header-row">
              <span class="th-col">Title</span>
              <span class="th-col">Category</span>
              <span class="th-col">Status</span>
              <span class="th-col th-right">Date</span>
              <span class="th-col th-right">Actions</span>
            </div>
            <div class="table-body" id="announcementsBody"></div>
          </div>

          <!-- Members -->
          <div class="section-row">
            <h3 class="section-title">Club Members</h3>
            <a href="index.php?page=officer_members" class="see-all-link">
              View All <i class="fas fa-chevron-right"></i>
            </a>
          </div>
          <div class="card">
            <div class="member-list" id="memberList"></div>
          </div>

        </div><!-- /left-col -->

        <!-- ── RIGHT COLUMN ───────────────────────────────── -->
        <div class="right-col">

          <!-- Events Timeline -->
          <div class="card events-card">
            <div class="card-header">
              <div>
                <h2>Upcoming Events</h2>
                <div class="calendar-subtitle" id="eventsSubtitle">Loading…</div>
              </div>
              <button class="add-evt-btn" onclick="openAddEventModal()">
                <i class="fas fa-plus"></i> Add
              </button>
            </div>
            <div class="timeline" id="eventsTimeline"></div>
          </div>

          <!-- Pending Applicants -->
          <div class="card applicants-card">
            <div class="card-header">
              <h2>Pending Applicants</h2>
              <span class="pending-badge" id="pendingCount">
                <?= $pendingApps ?> pending
              </span>
            </div>
            <div class="applicant-mini-list" id="applicantList"></div>
          </div>

          <!-- Notification Panel -->
          <div id="notifPanel" class="notif-panel" style="display:none;">
            <div class="notif-panel-header">
              <span>Notifications</span>
              <button onclick="markAllRead()" class="notif-mark-all">Mark all read</button>
            </div>
            <div id="notifList" class="notif-list"><div class="notif-empty">Loading…</div></div>
          </div>

        </div><!-- /right-col -->

      </div><!-- /dashboard-grid -->
    </div><!-- /content -->
  </main>
</div>

<!-- ── MODALS ──────────────────────────────────────────────── -->

<!-- Add / Edit Announcement -->
<div class="modal-overlay" id="addAnnouncementModal"
     onclick="handleOverlayClick(event,'addAnnouncementModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="annModalTitle">Add Announcement</span>
      <button class="modal-close" onclick="closeModal('addAnnouncementModal')">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <input type="hidden" id="editAnnId" value="" />
    <div class="form-group">
      <label class="form-label">Title *</label>
      <input type="text" class="form-input" id="aTitle" placeholder="e.g. Club General Meeting" />
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-select" id="aCategory">
          <option value="General">General</option>
          <option value="Events">Events</option>
          <option value="Finance">Finance</option>
          <option value="Members">Members</option>
          <option value="Achievement">Achievement</option>
          <option value="Admin">Admin</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-select" id="aStatus">
          <option value="info">Info</option>
          <option value="urgent">Urgent</option>
          <option value="approved">Approved</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-textarea" id="aDesc" placeholder="Brief description…"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('addAnnouncementModal')">Cancel</button>
      <button class="btn-primary" onclick="saveAnnouncement()">
        <i class="fas fa-save"></i> <span id="saveAnnBtnText">Save</span>
      </button>
    </div>
  </div>
</div>

<!-- Announcement Detail -->
<div class="modal-overlay" id="viewAnnouncementModal"
     onclick="handleOverlayClick(event,'viewAnnouncementModal')">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Announcement Detail</span>
      <button class="modal-close" onclick="closeModal('viewAnnouncementModal')">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="ann-detail-hero">
      <div class="ann-detail-icon"><i class="fas fa-bullhorn"></i></div>
      <div>
        <div class="ann-detail-title" id="annDetailTitle">Title</div>
        <div class="ann-detail-meta"  id="annDetailMeta">Category · Date</div>
      </div>
    </div>
    <div class="detail-info-grid">
      <div class="detail-info-item">
        <div class="detail-info-label">Status</div>
        <div class="detail-info-value" id="annDetailStatus">—</div>
      </div>
      <div class="detail-info-item">
        <div class="detail-info-label">Date Posted</div>
        <div class="detail-info-value" id="annDetailDate">—</div>
      </div>
    </div>
    <div>
      <div class="detail-section-label">Description</div>
      <p class="ann-detail-desc" id="annDetailDesc">—</p>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('viewAnnouncementModal')">Close</button>
      <button class="btn-danger"    onclick="deleteAnnFromDetail()">
        <i class="fas fa-trash"></i> Delete
      </button>
      <button class="btn-primary"   onclick="editAnnFromDetail()">
        <i class="fas fa-pen"></i> Edit
      </button>
    </div>
  </div>
</div>

<!-- Add Event -->
<div class="modal-overlay" id="addEventModal"
     onclick="handleOverlayClick(event,'addEventModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Event</span>
      <button class="modal-close" onclick="closeModal('addEventModal')">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="form-group">
      <label class="form-label">Event Name *</label>
      <input type="text" class="form-input" id="eName" placeholder="e.g. Club Orientation" />
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Date *</label>
        <input type="date" class="form-input" id="eDate" />
      </div>
      <div class="form-group">
        <label class="form-label">Location</label>
        <input type="text" class="form-input" id="eLocation" placeholder="e.g. Room 301" />
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Start Time</label>
        <input type="time" class="form-input" id="eStart" />
      </div>
      <div class="form-group">
        <label class="form-label">End Time</label>
        <input type="time" class="form-input" id="eEnd" />
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-textarea" id="eDesc" placeholder="What is this event about?"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('addEventModal')">Cancel</button>
      <button class="btn-primary"   onclick="saveEvent()">
        <i class="fas fa-save"></i> Save Event
      </button>
    </div>
  </div>
</div>

<!-- Confirm Dialog -->
<div class="modal-overlay" id="confirmModal"
     onclick="handleOverlayClick(event,'confirmModal')">
  <div class="modal modal-xs">
    <div class="modal-header">
      <span class="modal-title" id="confirmTitle">Confirm</span>
      <button class="modal-close" onclick="closeModal('confirmModal')">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <p class="confirm-msg" id="confirmMessage"></p>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('confirmModal')">Cancel</button>
      <button class="btn-danger"    id="confirmOkBtn">Confirm</button>
    </div>
  </div>
</div>

<!-- Application Review Modal -->
<div class="modal-overlay" id="appReviewModal" onclick="handleOverlayClick(event,'appReviewModal')">
  <div class="modal app-file-modal">
    <div class="modal-header">
      <span class="modal-title">Member Application</span>
      <button class="modal-close" onclick="closeModal('appReviewModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="app-file-header-card">
      <div class="app-file-avatar-lg" id="arAvatar">?</div>
      <div>
        <div class="app-file-name" id="arName">—</div>
        <div class="app-file-role-badge"><i class="fas fa-id-badge"></i> Applicant</div>
        <div style="margin-top:5px;"><span class="app-file-status pending">⏳ Pending Review</span></div>
      </div>
    </div>
    <div class="app-section-divider">Personal Information</div>
    <div class="app-info-grid">
      <div class="app-info-cell"><div class="app-info-cell-label">Student ID</div><div class="app-info-cell-value" id="arStudentId">—</div></div>
      <div class="app-info-cell"><div class="app-info-cell-label">Course &amp; Year</div><div class="app-info-cell-value" id="arCourse">—</div></div>
      <div class="app-info-cell"><div class="app-info-cell-label">Email</div><div class="app-info-cell-value" id="arEmail">—</div></div>
      <div class="app-info-cell"><div class="app-info-cell-label">Contact No.</div><div class="app-info-cell-value" id="arPhone">—</div></div>
      <div class="app-info-cell"><div class="app-info-cell-label">Date Applied</div><div class="app-info-cell-value" id="arDate">—</div></div>
    </div>
    <div class="app-section-divider">Why they want to join &amp; Skills</div>
    <div style="padding:0 2px 12px;">
      <div id="arExtras" style="font-size:13px;color:var(--text-mid);line-height:1.7;white-space:pre-wrap;background:#f7f7f7;border-radius:8px;padding:10px 12px;min-height:48px;">—</div>
    </div>
    <div class="app-action-row">
      <button class="btn-reject-app"  onclick="openRejectModal()"><i class="fas fa-times-circle"></i> Decline</button>
      <button class="btn-approve-app" onclick="approveApplication()"><i class="fas fa-check-circle"></i> Approve</button>
    </div>
  </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal-overlay" id="rejectReasonModal" onclick="handleOverlayClick(event,'rejectReasonModal')">
  <div class="modal modal-xs">
    <div class="modal-header">
      <span class="modal-title">Decline Application</span>
      <button class="modal-close" onclick="closeModal('rejectReasonModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="form-group" style="margin-top:8px;">
      <label class="form-label">Reason (optional)</label>
      <textarea class="form-textarea" id="rejectReason" placeholder="e.g. Slots are full for this semester…" style="min-height:80px;"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('rejectReasonModal')">Cancel</button>
      <button class="btn-danger" onclick="rejectApplication()"><i class="fas fa-times-circle"></i> Confirm Decline</button>
    </div>
  </div>
</div>

<!-- Edit Club Modal -->
<div class="modal-overlay" id="editClubModal" onclick="handleOverlayClick(event,'editClubModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Edit Club Info</span>
      <button class="modal-close" onclick="closeModal('editClubModal')"><i class="fas fa-times"></i></button>
    </div>
    <div style="display:flex;align-items:center;gap:14px;padding:12px;background:#f8faf9;border-radius:12px;">
      <img id="editClubLogoPreview" src="<?= htmlspecialchars($officerClub['logo_path'] ?? '') ?>"
           style="width:54px;height:54px;border-radius:12px;object-fit:cover;background:#e5e7eb;display:<?= $officerClub['logo_path'] ? 'block' : 'none' ?>;">
      <div>
        <div style="font-size:12px;font-weight:700;color:var(--text-mid);margin-bottom:5px;">Club Logo</div>
        <label style="display:inline-flex;align-items:center;gap:6px;background:var(--green-dark);color:#fff;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
          <i class="fas fa-upload"></i> Upload Photo
          <input type="file" id="editClubLogoInput" accept="image/*" style="display:none;" onchange="uploadClubLogo(this)">
        </label>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Club Name *</label>
      <input class="form-input" id="editClubName" type="text" value="<?= htmlspecialchars($clubName) ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-textarea" id="editClubDesc" style="height:90px;"><?= htmlspecialchars($officerClub['club_desc'] ?? '') ?></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Room / Location</label>
        <input class="form-input" id="editClubRoom" type="text" value="<?= htmlspecialchars($officerClub['room'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Founded</label>
        <input class="form-input" id="editClubFounded" type="text" value="<?= htmlspecialchars($officerClub['founded'] ?? '') ?>">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('editClubModal')">Cancel</button>
      <button class="btn-primary" onclick="saveClubInfo()"><i class="fas fa-save"></i> Save Changes</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- ── DATA INJECTION ──────────────────────────────────────── -->
<script>
window.OD = {
  page:   'officer_dashboard',
  userId: <?= (int)$_SESSION['user_id'] ?>,
  announcements: <?= json_encode(array_map(function($a) {
    return [
      'id'       => 'ann_' . $a['id'],
      'db_id'    => (int)$a['id'],
      'title'    => $a['title'],
      'category' => $a['category'] ?? 'General',
      'status'   => $a['status'],
      'date'     => relativeDate($a['posted_at']),
      'dot'      => dotFromStatus($a['status']),
      'desc'     => $a['description'] ?? '',
    ];
  }, $dbAnnouncements), JSON_HEX_TAG | JSON_HEX_APOS) ?>,

  events: <?= json_encode(array_map(function($e) {
    return [
      'id'       => 'evt_' . $e['id'],
      'db_id'    => (int)$e['id'],
      'name'     => $e['name'],
      'date'     => $e['event_date'],
      'time'     => $e['start_time'] ? substr($e['start_time'], 0, 5) : '',
      'endTime'  => $e['end_time']   ? substr($e['end_time'],   0, 5) : '',
      'location' => $e['location']   ?? '',
    ];
  }, $dbEvents), JSON_HEX_TAG | JSON_HEX_APOS) ?>,

  members: <?= json_encode(array_map(function($m, $i) {
    static $colors = ['av-green','av-teal','av-red','av-yellow','av-purple'];
    return [
      'initial' => strtoupper(substr($m['first_name'], 0, 1)),
      'color'   => $colors[$i % 5],
      'name'    => $m['first_name'] . ' ' . $m['last_name'],
      'role'    => $m['role'],
      'course'  => trim(($m['course'] ?? '') . ' ' . ($m['year'] ?? '')),
    ];
  }, $dbMembers, array_keys($dbMembers)), JSON_HEX_TAG | JSON_HEX_APOS) ?>,

  applicants: <?= json_encode(array_map(function($a, $i) {
    static $colors = ['av-green','av-teal','av-red','av-yellow','av-purple'];
    return [
      'db_id'      => (int)$a['id'],
      'initial'    => strtoupper(substr($a['first_name'], 0, 1)),
      'color'      => $colors[$i % 5],
      'name'       => $a['first_name'] . ' ' . $a['last_name'],
      'email'      => $a['email'],
      'course'     => trim(($a['app_course'] ?? '') . ' ' . ($a['year'] ?? '') . ' ' . ($a['section'] ?? '')),
      'student_id' => $a['student_id_no'] ?? '—',
      'phone'      => $a['app_phone']    ?? '—',
      'extras'     => $a['extras']       ?? '',
      'date'       => date('M j, Y', strtotime($a['applied_at'])),
    ];
  }, $dbApplicants, array_keys($dbApplicants)), JSON_HEX_TAG | JSON_HEX_APOS) ?>
};
</script>
<script src="/assets/javascripts/officer_dashboard.js"></script>
</body>
</html>