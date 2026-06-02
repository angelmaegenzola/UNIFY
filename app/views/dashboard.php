<?php require_once __DIR__ . '/../../app/controllers/dashboard_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Dashboard</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/dashboard.css" />
  <link rel="stylesheet" href="/assets/css/transitions.css" />
</head>

<body>
  <div class="app">

    <!-- SIDEBAR -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
 <aside class="sidebar" id="mainSidebar">
      <div class="sidebar-brand">
        <img src="assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
        <div class="brand-text">
          <div class="brand-name">UNIFY</div>
          <div class="brand-tagline">Club Management System</div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">MAIN MENU</div>
        <a href="index.php?page=dashboard" class="nav-item active"><i
            class="fas fa-house"></i><span>Dashboard</span></a>
        <a href="index.php?page=members" class="nav-item"><i class="fas fa-users"></i><span>Members</span></a>
        <a href="index.php?page=clubpage" class="nav-item"><i class="fas fa-building-columns"></i><span>Clubs</span></a>
        <a href="index.php?page=events" class="nav-item"><i class="fas fa-calendar-days"></i><span>Events</span></a>
        <div class="nav-section-label">REPORTS</div>
        <a href="index.php?page=reports" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
      </nav>
      <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <?php if(!empty($avatar_url)): ?><img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" /><?php else: ?><span class="profile-avatar-fallback"><?= $adminInitial ?></span><?php endif; ?>
          <span class="profile-online-dot"></span>
        </div>
        <a href="index.php?page=adminprofile" class="profile-link">
          <div class="profile-info">
            <span class="profile-name"><?= htmlspecialchars($adminName) ?></span>
            <span class="profile-role">Club Admin</span>
          </div>
        </a>
        <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="#" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
</aside>

    <!-- MAIN -->
    <main class="main">

      <header class="topbar">
        <div class="topbar-left">
          <button class="topbar-hamburger" onclick="toggleSidebar()" title="Menu">
            <img src="/assets/pictures/unifylogo.png" alt="Menu" class="topbar-logo-btn" />
          </button>
          <div class="topbar-title-group">
            <span class="topbar-page-title">Dashboard</span>
            <span class="topbar-date" id="topbarDate"></span>
<script>const _d=new Date();document.getElementById("topbarDate").textContent=_d.toLocaleDateString("en-US",{weekday:"long",year:"numeric",month:"long",day:"numeric"});</script>
          </div>
        </div>
        <div class="topbar-center">
          <div class="topbar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="dashSearchInput" placeholder="Search announcements…" oninput="handleDashSearch()" />
          </div>
        </div>
        <div class="topbar-actions">
          <div class="notif-wrap" id="notifWrap">
            <button class="icon-btn" id="notifBellBtn" title="Notifications" onclick="toggleNotifDropdown(event)">
              <i class="fas fa-bell"></i>
              <?php if ($totalAdminPending > 0): ?>
                <span class="badge red" id="notifBadge"><?= $totalAdminPending ?></span>
              <?php else: ?>
                <span class="badge red hidden" id="notifBadge">0</span>
              <?php endif; ?>
            </button>
            <div class="notif-dropdown" id="notifDropdown">
              <div class="notif-header">
                <span class="notif-header-title"><i class="fas fa-bell"></i> Notifications</span>
                <button class="notif-mark-btn" onclick="markAllAdminNotifsRead()">Mark all read</button>
              </div>
              <div class="notif-list" id="notifList">
                <div class="notif-loading-row"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
              </div>
              <div class="notif-footer">Showing pending reviews &amp; requests</div>
            </div>
          </div>
          <button class="icon-btn" title="Sync" onclick="syncDashboard()">
            <i class="fas fa-rotate"></i>
          </button>
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
        <button class="hamburger-btn" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
      </header>

      <div class="content">
        <div class="dashboard-grid">

          <!-- LEFT COLUMN -->
          <div class="left-col">

            <div class="wb-wrapper">
              <div class="welcome-banner">
                <div class="wb-text">
                  <span class="wb-greeting" id="wbGreeting">Good morning 👋</span>
                  <h2 class="wb-name">Welcome back, <?= htmlspecialchars($adminFirst) ?>!</h2>
                  <p class="wb-sub">Let's get things done today. Be Productive.</p>
                </div>
                <div class="wb-deco">
                  <div class="wb-deco-rings">
                    <div class="wb-deco-ring r1"></div>
                    <div class="wb-deco-ring r2"></div>
                    <div class="wb-deco-ring r3"></div>
                  </div>
                </div>
              </div>
              <img src="/assets/pictures/visuals.png" alt="" class="wb-char-img"
                onerror="this.style.display='none'" />
            </div>

            <div class="stat-cards-grid">
              <div class="stat-card-new sc-green" onclick="window.location='index.php?page=members'">
                <div class="sc-top">
                  <div class="sc-icon-wrap"><i class="fas fa-users"></i></div><span class="sc-trend">↑ 12</span>
                </div>
                <div class="sc-value" id="statMembers"><?= (int) $totalMembers ?></div>
                <div class="sc-label">Total Members</div>
              </div>
              <div class="stat-card-new sc-yellow" onclick="window.location='index.php?page=clubpage'">
                <div class="sc-top">
                  <div class="sc-icon-wrap"><i class="fas fa-building-columns"></i></div><span
                    class="sc-trend">Active</span>
                </div>
                <div class="sc-value" id="statClubs"><?= (int) $activeClubs ?></div>
                <div class="sc-label">Active Clubs</div>
              </div>
              <div class="stat-card-new sc-teal" onclick="window.location='index.php?page=events'">
                <div class="sc-top">
                  <div class="sc-icon-wrap"><i class="fas fa-calendar-check"></i></div><span
                    class="sc-trend">Today</span>
                </div>
                <div class="sc-value" id="statEvents"><?= (int) $upcomingEvents ?></div>
                <div class="sc-label">Upcoming Events</div>
              </div>
              <div class="stat-card-new sc-red" onclick="openPendingEventsModal()" style="cursor:pointer;">
                <div class="sc-top">
                  <div class="sc-icon-wrap"><i class="fas fa-calendar-plus"></i></div>
                  <span
                    class="sc-trend<?= (int) $pendingEvents > 0 ? ' urgent' : '' ?>"><?= (int) $pendingEvents > 0 ? 'Action needed' : 'Clear' ?></span>
                </div>
                <div class="sc-value" id="statPendingEvents"><?= (int) $pendingEvents ?></div>
                <div class="sc-label">Events for Approval</div>
              </div>
            </div>

            <div class="section-row">
              <h3 class="section-title">Announcements</h3>
              <div style="display:flex;gap:8px;align-items:center;">
                <button class="add-item-btn" onclick="openAddAnnouncementModal()"><i class="fas fa-plus"></i>
                  Add</button>
                <a href="#" class="see-all-link">View All <i class="fas fa-chevron-right"></i></a>
              </div>
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

          </div><!-- /left-col -->

          <!-- RIGHT COLUMN -->
          <div class="right-col">

            <div class="card events-card">
              <div class="card-header">
                <div>
                  <h2>Upcoming Events</h2>
                  <div class="calendar-subtitle" id="eventsSubtitle">Loading events…</div>
                </div>
                <button class="today-btn" onclick="window.location='index.php?page=events'">
                  View All <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                </button>
              </div>
              <div class="timeline" id="eventsTimeline"></div>
            </div>

            <div class="card applicants-card">
              <div class="card-header">
                <h2>New Applicants</h2>
                <a href="#" class="see-all-link" onclick="openAllApplicantsModal(); return false;">
                  View All <i class="fas fa-chevron-right"></i>
                </a>
              </div>
              <div class="applicant-list" id="applicantsList"></div>
            </div>

          </div><!-- /right-col -->

        </div>
      </div>
    </main>
  </div>


  <!-- ── MODALS ─────────────────────────────────────────────── -->

  <!-- Add / Edit Announcement -->
  <div class="modal-overlay" id="addAnnouncementModal" onclick="handleOverlayClick(event,'addAnnouncementModal')">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title" id="announcementModalTitle">Add Announcement</span>
        <button class="modal-close" onclick="closeModal('addAnnouncementModal')"><i class="fas fa-times"></i></button>
      </div>
      <input type="hidden" id="editAnnouncementId" value="" />
      <div class="form-group">
        <label class="form-label">Title *</label>
        <input type="text" class="form-input" id="aTitle" placeholder="e.g. Club Officer Meeting" />
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <div class="custom-select-wrap" id="aCategoryWrap">
          <button type="button" class="custom-select-btn" id="aCategoryBtn" onclick="toggleAnnCategoryDrop()">
            <span id="aCategoryLabel">General</span>
            <i class="fas fa-chevron-down" style="font-size:10px;margin-left:auto;"></i>
          </button>
          <div class="custom-select-list" id="aCategoryList">
            <div class="custom-select-option selected" onclick="selectAnnCategory('General')">General</div>
            <div class="custom-select-option" onclick="selectAnnCategory('Events')">Events</div>
            <div class="custom-select-option" onclick="selectAnnCategory('Finance')">Finance</div>
            <div class="custom-select-option" onclick="selectAnnCategory('Members')">Members</div>
            <div class="custom-select-option" onclick="selectAnnCategory('Achievement')">Achievement</div>
            <div class="custom-select-option" onclick="selectAnnCategory('Reminder')">Reminder</div>
            <div class="custom-select-option" onclick="selectAnnCategory('Admin')">Admin</div>
          </div>
        </div>
        <input type="hidden" id="aCategory" value="General" />
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <div class="custom-select-wrap" id="aStatusWrap">
          <button type="button" class="custom-select-btn" id="aStatusBtn" onclick="toggleAnnStatusDrop()">
            <span id="aStatusLabel">Urgent</span>
            <i class="fas fa-chevron-down" style="font-size:10px;margin-left:auto;"></i>
          </button>
          <div class="custom-select-list" id="aStatusList">
            <div class="custom-select-option selected" onclick="selectAnnStatus('urgent','Urgent')">Urgent</div>
            <div class="custom-select-option" onclick="selectAnnStatus('approved','Approved')">Approved</div>
            <div class="custom-select-option" onclick="selectAnnStatus('info','Info')">Info</div>
          </div>
        </div>
        <input type="hidden" id="aStatus" value="urgent" />
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
  <div class="modal-overlay" id="viewAnnouncementModal" onclick="handleOverlayClick(event,'viewAnnouncementModal')">
    <div class="modal" style="max-width:420px;">
      <div class="modal-header">
        <span class="modal-title">Announcement Detail</span>
        <button class="modal-close" onclick="closeModal('viewAnnouncementModal')"><i class="fas fa-times"></i></button>
      </div>
      <div class="ann-detail-hero">
        <div class="ann-detail-icon"><i class="fas fa-bullhorn"></i></div>
        <div>
          <div class="ann-detail-title" id="annDetailTitle">Title</div>
          <div class="ann-detail-meta" id="annDetailMeta">Category · Date</div>
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
        <p style="font-size:12.5px;color:var(--text-mid);line-height:1.7;margin-top:8px;" id="annDetailDesc">—</p>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('viewAnnouncementModal')">Close</button>
        <button class="btn-danger" onclick="deleteAnnouncementFromDetail()"><i class="fas fa-trash"></i> Delete</button>
        <button class="btn-primary" onclick="editAnnouncementFromDetail()"><i class="fas fa-pen"></i> Edit</button>
      </div>
    </div>
  </div>

  <!-- Student Application File -->
  <div class="modal-overlay" id="appFileModal" onclick="handleOverlayClick(event,'appFileModal')">
    <div class="modal app-file-modal">
      <div class="modal-header">
        <span class="modal-title">Student Application</span>
        <button class="modal-close" onclick="closeModal('appFileModal')"><i class="fas fa-times"></i></button>
      </div>
      <div class="app-file-header-card">
        <div class="app-file-avatar-lg" id="appFileAvatar">M</div>
        <div>
          <div class="app-file-name" id="appFileName">Name</div>
          <div class="app-file-role-badge"><i class="fas fa-id-badge"></i> <span id="appFileRoleLabel">Applicant</span>
          </div>
          <div style="margin-top:5px;">
            <span class="app-file-status pending" id="appFileStatusBadge">⏳ Pending Review</span>
          </div>
        </div>
      </div>
      <div class="app-section-divider">Personal Information</div>
      <div class="app-info-grid">
        <div class="app-info-cell">
          <div class="app-info-cell-label">Student ID</div>
          <div class="app-info-cell-value" id="appStudentId">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Course &amp; Section</div>
          <div class="app-info-cell-value" id="appCourse">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Email</div>
          <div class="app-info-cell-value" id="appEmail">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Contact No.</div>
          <div class="app-info-cell-value" id="appContact">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Applied For</div>
          <div class="app-info-cell-value" id="appClub">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Date Applied</div>
          <div class="app-info-cell-value" id="appDate">—</div>
        </div>
      </div>
      <div class="app-section-divider">Application Details</div>
      <div style="padding:0 2px 12px;">
        <div
          style="font-size:11px;font-weight:600;color:var(--text-light);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
          Why they want to join &amp; Skills</div>
        <div id="appExtras"
          style="font-size:13px;color:var(--text-mid);line-height:1.7;white-space:pre-wrap;background:var(--bg-hover,#f7f7f7);border-radius:8px;padding:10px 12px;min-height:48px;">
          —</div>
      </div>
      <div class="app-action-row" id="appActionRow">
        <button class="btn-reject-app" onclick="rejectApplicant()"><i class="fas fa-times-circle"></i> Decline</button>
        <button class="btn-approve-app" onclick="approveApplicant()"><i class="fas fa-check-circle"></i> Approve
          Application</button>
      </div>
    </div>
  </div>

  <!-- All Applicants -->
  <div class="modal-overlay" id="allApplicantsModal" onclick="handleOverlayClick(event,'allApplicantsModal')">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title">All Applicants</span>
        <button class="modal-close" onclick="closeModal('allApplicantsModal')"><i class="fas fa-times"></i></button>
      </div>
      <div id="allApplicantsBody" style="display:flex;flex-direction:column;gap:6px;"></div>
    </div>
  </div>


  <!-- Leader Applications Modal -->
  <div class="modal-overlay" id="leaderAppsModal" onclick="handleOverlayClick(event,'leaderAppsModal')">
    <div class="modal" style="max-width:680px;">
      <div class="modal-header">
        <span class="modal-title"><i class="fas fa-shield-halved" style="color:#f59e0b;margin-right:6px;"></i> Leader
          Applications</span>
        <button class="modal-close" onclick="closeModal('leaderAppsModal')"><i class="fas fa-times"></i></button>
      </div>
      <p style="font-size:12.5px;color:var(--text-light);margin-bottom:12px;">These applicants hold a leadership role in
        another club. Only admin can approve or reject them.</p>
      <div id="leaderAppsBody" style="display:flex;flex-direction:column;gap:8px;max-height:420px;overflow-y:auto;">
      </div>
    </div>
  </div>

  <!-- Club Requests Modal -->
  <div class="modal-overlay" id="clubRequestsModal" onclick="handleOverlayClick(event,'clubRequestsModal')">
    <div class="modal" style="max-width:680px;">
      <div class="modal-header">
        <span class="modal-title"><i class="fas fa-plus-circle" style="color:#22c55e;margin-right:6px;"></i> Pending
          Club Requests</span>
        <button class="modal-close" onclick="closeModal('clubRequestsModal')"><i class="fas fa-times"></i></button>
      </div>
      <div id="clubRequestsBody" style="display:flex;flex-direction:column;gap:10px;max-height:440px;overflow-y:auto;">
      </div>
    </div>
  </div>

  <!-- Club Request Detail Modal -->
  <div class="modal-overlay" id="clubReqDetailModal" onclick="handleOverlayClick(event,'clubReqDetailModal')">
    <div class="modal app-file-modal">
      <div class="modal-header">
        <span class="modal-title">Club Request Detail</span>
        <button class="modal-close" onclick="closeModal('clubReqDetailModal')"><i class="fas fa-times"></i></button>
      </div>
      <div class="app-file-header-card">
        <div class="app-file-avatar-lg" style="background:linear-gradient(135deg,#1a5c38,#2d8a57);font-size:1.4rem;"><i
            class="fas fa-building-columns"></i></div>
        <div>
          <div class="app-file-name" id="crDetailName">—</div>
          <div class="app-file-role-badge"><span id="crDetailAcronym"></span> · <span id="crDetailCategory"></span>
          </div>
        </div>
      </div>
      <div class="app-section-divider">Submitted By</div>
      <div class="app-info-grid">
        <div class="app-info-cell">
          <div class="app-info-cell-label">Student Name</div>
          <div class="app-info-cell-value" id="crDetailBy">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Email</div>
          <div class="app-info-cell-value" id="crDetailEmail">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Proposed Room</div>
          <div class="app-info-cell-value" id="crDetailRoom">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Founded</div>
          <div class="app-info-cell-value" id="crDetailFounded">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Date Submitted</div>
          <div class="app-info-cell-value" id="crDetailDate">—</div>
        </div>
      </div>
      <div class="app-section-divider">Description</div>
      <div id="crDetailDesc"
        style="font-size:13px;color:var(--text-mid);line-height:1.7;white-space:pre-wrap;background:#f7f7f7;border-radius:8px;padding:10px 12px;margin-bottom:12px;min-height:48px;">
        —</div>
      <div class="form-group" id="crRejectNoteGroup" style="display:none;">
        <label class="form-label">Rejection Reason (optional)</label>
        <textarea class="form-textarea" id="crAdminNote" placeholder="Explain why the request was not approved…"
          style="min-height:70px;"></textarea>
      </div>
      <div class="app-action-row" id="crDetailActions">
        <button class="btn-reject-app" onclick="showCrRejectNote()"><i class="fas fa-times-circle"></i> Reject</button>
        <button class="btn-approve-app" onclick="approveClubRequest()"><i class="fas fa-check-circle"></i> Approve
          Club</button>
      </div>
      <div id="crRejectActions" style="display:none;justify-content:flex-end;gap:8px;padding-top:4px;">
        <button class="btn-secondary" onclick="hideCrRejectNote()">Cancel</button>
        <button class="btn-danger" onclick="rejectClubRequest()"><i class="fas fa-times-circle"></i> Confirm
          Rejection</button>
      </div>
    </div>
  </div>

  <!-- Reject Reason for Leader App Modal -->
  <div class="modal-overlay" id="leaderRejectModal" onclick="handleOverlayClick(event,'leaderRejectModal')">
    <div class="modal modal-xs">
      <div class="modal-header">
        <span class="modal-title">Decline Leader Application</span>
        <button class="modal-close" onclick="closeModal('leaderRejectModal')"><i class="fas fa-times"></i></button>
      </div>
      <div class="form-group" style="margin-top:8px;">
        <label class="form-label">Reason (optional)</label>
        <textarea class="form-textarea" id="leaderRejectReason" placeholder="e.g. Already holds a president role…"
          style="min-height:80px;"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('leaderRejectModal')">Cancel</button>
        <button class="btn-danger" onclick="confirmLeaderReject()"><i class="fas fa-times-circle"></i> Confirm
          Decline</button>
      </div>
    </div>
  </div>

  <!-- Confirm Dialog -->
  <div class="modal-overlay" id="confirmModal" onclick="handleOverlayClick(event,'confirmModal')">
    <div class="modal" style="max-width:340px;">
      <div class="modal-header">
        <span class="modal-title" id="confirmTitle">Confirm</span>
        <button class="modal-close" onclick="closeModal('confirmModal')"><i class="fas fa-times"></i></button>
      </div>
      <p id="confirmMessage" style="font-size:13px;color:var(--text-mid);line-height:1.6;"></p>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('confirmModal')">Cancel</button>
        <button class="btn-danger" id="confirmOkBtn">Confirm</button>
      </div>
    </div>
  </div>

  <!-- ── Pending Events Approval Modal ──────────────────────── -->
  <div class="modal-overlay" id="pendingEventsModal" onclick="handleOverlayClick(event,'pendingEventsModal')">
    <div class="modal" style="max-width:680px;">
      <div class="modal-header">
        <span class="modal-title"><i class="fas fa-calendar-plus" style="color:#f97316;margin-right:6px;"></i> Events
          Pending Approval</span>
        <button class="modal-close" onclick="closeModal('pendingEventsModal')"><i class="fas fa-times"></i></button>
      </div>
      <div id="pendingEventsBody" style="display:flex;flex-direction:column;gap:10px;max-height:460px;overflow-y:auto;">
        <?php if (empty($dbPendingEvents)): ?>
          <p style="text-align:center;color:var(--text-light);padding:2.5rem 0;font-size:13px;">
            <i class="fas fa-check-circle" style="font-size:2rem;display:block;margin-bottom:10px;color:#22c55e;"></i>
            No events pending approval.
          </p>
        <?php else:
          foreach ($dbPendingEvents as $pe): ?>
            <div class="club-req-item" id="pev-row-<?= $pe['id'] ?>"
              style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:14px 16px;background:#fafafa;border:1px solid #eee;border-radius:12px;">
              <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:700;color:#1a3a2a;margin-bottom:4px;">
                  <?= htmlspecialchars($pe['name']) ?></div>
                <div style="font-size:12px;color:#666;margin-bottom:2px;">
                  <i class="fas fa-building-columns" style="width:14px;"></i> <?= htmlspecialchars($pe['club_name']) ?>
                  &nbsp;·&nbsp;
                  <i class="fas fa-calendar" style="width:14px;"></i> <?= date('M j, Y', strtotime($pe['event_date'])) ?>
                  <?= $pe['start_time'] ? ' · ' . date('g:i a', strtotime($pe['start_time'])) : '' ?>
                </div>
                <?php if ($pe['location']): ?>
                  <div style="font-size:12px;color:#666;"><i class="fas fa-location-dot" style="width:14px;"></i>
                    <?= htmlspecialchars($pe['location']) ?></div>
                <?php endif; ?>
                <?php if ($pe['description']): ?>
                  <div style="font-size:12px;color:#888;margin-top:5px;line-height:1.5;">
                    <?= htmlspecialchars(mb_strimwidth($pe['description'], 0, 140, '…')) ?></div>
                <?php endif; ?>
              </div>
              <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;">
                <button class="btn-approve-app" style="font-size:12px;padding:6px 14px;"
                  onclick="approveEvent(<?= $pe['id'] ?>)"><i class="fas fa-check-circle"></i> Approve</button>
                <button class="btn-reject-app" style="font-size:12px;padding:6px 14px;"
                  onclick="openEvtRejectModal(<?= $pe['id'] ?>)"><i class="fas fa-times-circle"></i> Reject</button>
              </div>
            </div>
          <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Event Reject Reason Modal -->
  <div class="modal-overlay" id="evtRejectModal" onclick="handleOverlayClick(event,'evtRejectModal')">
    <div class="modal modal-xs">
      <div class="modal-header">
        <span class="modal-title">Reject Event</span>
        <button class="modal-close" onclick="closeModal('evtRejectModal')"><i class="fas fa-times"></i></button>
      </div>
      <p style="font-size:12.5px;color:var(--text-light);margin-bottom:10px;">Officers will be notified. You can include
        a reason below.</p>
      <div class="form-group">
        <label class="form-label">Reason (optional)</label>
        <textarea class="form-textarea" id="evtRejectNote"
          placeholder="e.g. Please reschedule — conflicts with another event." style="min-height:80px;"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('evtRejectModal')">Cancel</button>
        <button class="btn-danger" onclick="confirmRejectEvent()"><i class="fas fa-times-circle"></i> Confirm
          Rejection</button>
      </div>
    </div>
  </div>

  <!-- Event Approve Confirm Modal -->
<div class="modal-overlay" id="evtApproveModal" onclick="handleOverlayClick(event,'evtApproveModal')">
  <div class="modal modal-xs">
    <div class="modal-header">
      <span class="modal-title">Approve Event</span>
      <button class="modal-close" onclick="closeModal('evtApproveModal')"><i class="fas fa-times"></i></button>
    </div>
    <div style="text-align:center;padding:10px 0 14px;">
      <div style="width:54px;height:54px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:24px;color:#16a34a;margin:0 auto 12px;">
        <i class="fas fa-calendar-check"></i>
      </div>
      <div style="font-size:15px;font-weight:800;color:var(--text-dark);margin-bottom:6px;" id="evtApproveNameLabel">—</div>
      <p style="font-size:12.5px;color:var(--text-light);line-height:1.6;">Approving this event will notify all active club members.</p>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('evtApproveModal')">Cancel</button>
      <button class="btn-approve-app" onclick="confirmApproveEvent()" style="flex:unset;padding:9px 20px;">
        <i class="fas fa-check-circle"></i> Approve
      </button>
    </div>
  </div>
</div>

  <div id="toast" class="toast"></div>

  <!-- ── DATA INJECTION ──────────────────────────────────────── -->
  <script>
    window.DB_STATS = {
      members: <?= (int) $totalMembers ?>,
      clubs: <?= (int) $activeClubs ?>,
      events: <?= (int) $upcomingEvents ?>,
      requests: <?= (int) $leaderApps + (int) $pendingClubRequests ?>,
      pendingEvents: <?= (int) $pendingEvents ?>
    };

    window.DB_ANNOUNCEMENTS = <?= json_encode(array_map(function ($a) {
      return [
        'id' => 'ann_' . $a['id'],
        'db_id' => (int) $a['id'],
        'title' => $a['title'],
        'category' => $a['category'] ?? 'General',
        'status' => $a['status'],
        'date' => relativeDate($a['posted_at']),
        'dot' => dotFromStatus($a['status']),
        'desc' => $a['description'] ?? '',
      ];
    }, $dbAnnouncements), JSON_HEX_TAG | JSON_HEX_APOS) ?>;

    window.DB_EVENTS = <?= json_encode(array_map(function ($e) {
      return [
        'id' => 'evt_' . $e['id'],
        'db_id' => (int) $e['id'],
        'name' => $e['name'],
        'club' => $e['club_name'],
        'date' => $e['event_date'],
        'time' => $e['start_time'] ? substr($e['start_time'], 0, 5) : '',
        'endTime' => $e['end_time'] ? substr($e['end_time'], 0, 5) : '',
        'icon' => 'fa-calendar-days',
      ];
    }, $dbEvents), JSON_HEX_TAG | JSON_HEX_APOS) ?>;

    window.DB_LEADER_APPS = <?= json_encode(array_map(function ($a) {
      static $colors = ['av-green', 'av-teal', 'av-red', 'av-yellow', 'av-purple'];
      static $i = 0;
      $course = trim(($a['app_course'] ?? '') . ' ' . ($a['app_year'] ?? '') . ' ' . ($a['app_section'] ?? ''));
      return [
        'id' => 'ldr_' . $a['id'],
        'db_id' => (int) $a['id'],
        'initial' => strtoupper(substr($a['first_name'], 0, 1)),
        'color' => $colors[$i++ % 5],
        'name' => $a['first_name'] . ' ' . $a['last_name'],
        'email' => $a['email'] ?? '—',
        'contact' => $a['app_phone'] ?? '—',
        'studentId' => $a['student_id_no'] ?? '—',
        'course' => $course ?: '—',
        'club' => $a['club_name'] ?? '—',
        'extras' => $a['extras'] ?? '',
        'leaderIn' => $a['leader_in_clubs'] ?? '—',
        'dateApplied' => date('F j, Y', strtotime($a['applied_at'])),
      ];
    }, $dbLeaderApps), JSON_HEX_TAG | JSON_HEX_APOS) ?>;

    window.DB_CLUB_REQUESTS = <?= json_encode(array_map(function ($r) {
      return [
        'id' => (int) $r['id'],
        'name' => $r['name'],
        'acronym' => $r['acronym'] ?? '',
        'category' => $r['category'] ?? '',
        'description' => $r['description'] ?? '',
        'room' => $r['room'] ?? '',
        'founded' => $r['founded'] ?? '',
        'submittedBy' => $r['first_name'] . ' ' . $r['last_name'],
        'email' => $r['email'],
        'date' => date('F j, Y', strtotime($r['created_at'])),
      ];
    }, $dbClubRequests), JSON_HEX_TAG | JSON_HEX_APOS) ?>;

    window.DB_APPLICANTS = <?= json_encode(array_map(function ($a) {
      static $colors = ['av-green', 'av-teal', 'av-red', 'av-yellow', 'av-purple'];
      static $i = 0;
      $course = trim($a['app_course'] ?? '');
      $year = trim($a['app_year'] ?? '');
      $section = trim($a['app_section'] ?? '');
      $courseDisplay = implode(' ', array_filter([$course, $year, $section])) ?: '—';
      return [
        'id' => 'apl_' . $a['id'],
        'db_id' => (int) $a['id'],
        'initial' => strtoupper(substr($a['first_name'], 0, 1)),
        'color' => $colors[$i++ % 5],
        'name' => $a['first_name'] . ' ' . $a['last_name'],
        'appStatus' => $a['status'],
        'studentId' => $a['student_id_no'] ?? '—',
        'course' => $courseDisplay,
        'email' => $a['email'] ?? '—',
        'contact' => $a['app_phone'] ?? '—',
        'club' => $a['club_name'] ?? '—',
        'extras' => $a['extras'] ?? '',
        'dateApplied' => date('F j, Y', strtotime($a['applied_at'])),
      ];
    }, $dbApplicants), JSON_HEX_TAG | JSON_HEX_APOS) ?>;
  </script>

  <script src="/public/assets/javascripts/dashboard.js"></script>

<script>
// ── ADMIN NOTIFICATION DROPDOWN ────────────────────────────
    let _notifLoaded = false;

    function toggleNotifDropdown(e) {
      e.stopPropagation();
      const dd = document.getElementById('notifDropdown');
      const isOpen = dd.classList.contains('open');
      document.querySelectorAll('.notif-dropdown.open').forEach(el => el.classList.remove('open'));
      if (!isOpen) {
        dd.classList.add('open');
        if (!_notifLoaded) renderAdminNotifs();
      }
    }

    document.addEventListener('click', function (e) {
      if (!e.target.closest('#notifWrap')) {
        document.getElementById('notifDropdown')?.classList.remove('open');
      }
    });

    function openNotificationsPanel() {
      const dd = document.getElementById('notifDropdown');
      dd.classList.add('open');
      if (!_notifLoaded) renderAdminNotifs();
    }

    function renderAdminNotifs() {
      _notifLoaded = true;
      const list = document.getElementById('notifList');
      const items = [];

      // 1. Pending event approvals
      <?php foreach ($dbPendingEvents as $pe): ?>
        items.push({
          type: 'event_pending',
          icon: 'fas fa-calendar-plus',
          iconClass: 'type-event_pending',
          title: 'Event for Approval: <?= addslashes(htmlspecialchars($pe['name'])) ?>',
          msg: '<?= addslashes(htmlspecialchars($pe['club_name'])) ?> submitted "<?= addslashes(htmlspecialchars($pe['name'])) ?>" for <?= date('M j, Y', strtotime($pe['event_date'])) ?>.',
          time: '<?= date('F j, Y', strtotime($pe['created_at'])) ?>',
          action: () => openPendingEventsModal(),
          actionLabel: 'Review Event',
          unread: true,
        });
      <?php endforeach; ?>

        // 2. Pending club requests
        (window.DB_CLUB_REQUESTS || []).forEach(r => {
          items.push({
            type: 'club_request',
            icon: 'fas fa-plus-circle',
            iconClass: 'type-club_request',
            title: 'New Club Request: ' + r.name,
            msg: r.submittedBy + ' wants to start "' + r.name + '".',
            time: r.date,
            action: () => openClubReqFromNotif(r.id),
            actionLabel: 'Review Request',
            unread: true,
          });
        });

      // 3. Leader applications
      (window.DB_LEADER_APPS || []).forEach(a => {
        items.push({
          type: 'app',
          icon: 'fas fa-shield-halved',
          iconClass: 'type-app',
          title: 'Leader Application: ' + a.name,
          msg: a.name + ' applied to join ' + a.club + ' (currently a leader elsewhere).',
          time: a.dateApplied,
          action: null,
          unread: true,
        });
      });

      if (items.length === 0) {
        list.innerHTML = '<div class="notif-empty-row"><i class="fas fa-bell-slash"></i><p>No notifications yet.</p></div>';
        return;
      }

      list.innerHTML = items.map((n, i) => `
        <div class="notif-item unread" id="nadmin-${i}" onclick="${n.action ? '_notifAction(' + i + ')' : ''}">
          <div class="notif-icon ${n.iconClass}"><i class="${n.icon}"></i></div>
          <div class="notif-content">
            <div class="notif-title">${n.title}</div>
            <div class="notif-msg">${n.msg}</div>
            <div class="notif-time">${n.time}</div>
            ${n.action ? '<button class="notif-review-btn" onclick="event.stopPropagation();_notifAction(' + i + ')"><i class="fas fa-eye"></i> ' + n.actionLabel + '</button>' : ''}
          </div>
        </div>
      `).join('');

      window._adminNotifActions = items.map(n => n.action);
    }

    function _notifAction(i) {
      const fn = window._adminNotifActions?.[i];
      if (typeof fn === 'function') fn();
    }

    function openClubReqFromNotif(reqId) {
      document.getElementById('notifDropdown').classList.remove('open');
      const req = (window.DB_CLUB_REQUESTS || []).find(r => r.id === reqId);
      if (!req) { showToast('Request not found.', 'error'); return; }
      if (typeof openClubReqDetail === 'function') {
        const idx = (window.DB_CLUB_REQUESTS || []).findIndex(r => r.id === reqId);
        openClubReqDetail(idx);
      } else {
        document.getElementById('crDetailName').textContent = req.name;
        document.getElementById('crDetailAcronym').textContent = req.acronym || '—';
        document.getElementById('crDetailCategory').textContent = req.category || '—';
        document.getElementById('crDetailBy').textContent = req.submittedBy;
        document.getElementById('crDetailEmail').textContent = req.email;
        document.getElementById('crDetailRoom').textContent = req.room || '—';
        document.getElementById('crDetailFounded').textContent = req.founded || '—';
        document.getElementById('crDetailDate').textContent = req.date;
        document.getElementById('crDetailDesc').textContent = req.description || '—';
        document.getElementById('crRejectNoteGroup').style.display = 'none';
        document.getElementById('crDetailActions').style.display = 'flex';
        document.getElementById('crRejectActions').style.display = 'none';
        window._currentClubReqId = req.id;
        document.getElementById('clubReqDetailModal').classList.add('open');
      }
    }

    function markAllAdminNotifsRead() {
      document.querySelectorAll('#notifList .notif-item.unread').forEach(el => el.classList.remove('unread'));
      document.getElementById('notifBadge').classList.add('hidden');
    }

    // ── Pending Events Approval ────────────────────────────────
    function openPendingEventsModal() {
      document.getElementById('notifDropdown').classList.remove('open');
      openModal('pendingEventsModal');
    }

    let _rejectingEventId = null;

    let _approvingEventId = null;

function approveEvent(id) {
  _approvingEventId = id;
  const row = document.getElementById('pev-row-' + id);
  const name = row ? row.querySelector('div[style*="font-weight:700"]')?.textContent?.trim() : 'this event';
  document.getElementById('evtApproveNameLabel').textContent = name || 'this event';
  openModal('evtApproveModal');
}

function confirmApproveEvent() {
  const id = _approvingEventId;
  if (!id) return;
  closeModal('evtApproveModal');
  fetch('index.php?page=dashboard&action=evt_approve', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  })
    .then(r => r.json()).then(d => {
      if (d.success) {
        document.getElementById('pev-row-' + id)?.remove();
        const el = document.getElementById('statPendingEvents');
        if (el) el.textContent = Math.max(0, (parseInt(el.textContent) || 1) - 1);
        showToast('Event approved! Members have been notified.');
      } else {
        showToast(d.message || 'Something went wrong.', 'error');
      }
    }).catch(() => showToast('Network error.', 'error'));
  _approvingEventId = null;
}

    function openEvtRejectModal(id) {
      _rejectingEventId = id;
      document.getElementById('evtRejectNote').value = '';
      openModal('evtRejectModal');
    }

    function confirmRejectEvent() {
      if (!_rejectingEventId) return;
      const note = document.getElementById('evtRejectNote').value.trim();
      fetch('index.php?page=dashboard&action=evt_reject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: _rejectingEventId, admin_note: note })
      })
        .then(r => r.json()).then(d => {
          if (d.success) {
            document.getElementById('pev-row-' + _rejectingEventId)?.remove();
            const el = document.getElementById('statPendingEvents');
            if (el) el.textContent = Math.max(0, (parseInt(el.textContent) || 1) - 1);
            closeModal('evtRejectModal');
            showToast('Event rejected. Officers have been notified.');
          } else {
            showToast(d.message || 'Something went wrong.', 'error');
          }
        }).catch(() => showToast('Network error.', 'error'));
    }
</script>
<script>
function preventScroll(e) { e.preventDefault(); }
function toggleSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  const open = sidebar.classList.toggle('open');
  sidebar.style.setProperty('left', open ? '0px' : '-280px', 'important');
  document.getElementById('sidebarOverlay').classList.toggle('open');
  document.body.classList.toggle('sidebar-open', open);
  const fab = document.getElementById('fabMenuBtn');
  if (fab) {
    fab.style.opacity = open ? '0' : '1';
    fab.style.pointerEvents = open ? 'none' : '';
  }
  const mainEl = document.querySelector('.main');
  if (open) {
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
    document.body.style.top = '-' + window.scrollY + 'px';
    document.body.dataset.scrollY = window.scrollY;
    if (mainEl) mainEl.style.overflow = 'hidden';
    if (mainEl) mainEl.style.pointerEvents = 'none';
  } else {
    const scrollY = document.body.dataset.scrollY || 0;
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
    document.body.style.top = '';
    window.scrollTo(0, parseInt(scrollY));
    if (mainEl) mainEl.style.overflow = '';
    if (mainEl) mainEl.style.pointerEvents = '';
  }
}
function closeSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  sidebar.classList.remove('open');
  sidebar.style.setProperty('left', '-280px', 'important');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.body.classList.remove('sidebar-open');
  const scrollY = document.body.dataset.scrollY || 0;
  document.body.style.overflow = '';
  document.body.style.position = '';
  document.body.style.width = '';
  document.body.style.top = '';
  window.scrollTo(0, parseInt(scrollY));
  const mainEl = document.querySelector('.main');
  if (mainEl) { mainEl.style.overflow = ''; mainEl.style.pointerEvents = ''; }
  const fab = document.getElementById('fabMenuBtn');
  if (fab) { fab.style.opacity = '1'; fab.style.pointerEvents = ''; }
}
var _tsx = 0, _tsy = 0;
document.addEventListener('touchstart', function(e) {
  _tsx = e.touches[0].clientX;
  _tsy = e.touches[0].clientY;
}, {passive:true});
document.addEventListener('touchend', function(e) {
  var dx = e.changedTouches[0].clientX - _tsx;
  var dy = e.changedTouches[0].clientY - _tsy;
  if (Math.abs(dy) > Math.abs(dx)) return; // ignore vertical swipes
  if (dx > 40 && _tsx < 80) toggleSidebar();  // swipe right from left 80px
  if (dx < -40) closeSidebar();               // swipe left anywhere
}, {passive:true});
</script>
<button class="fab-menu-btn" id="fabMenuBtn" onclick="toggleSidebar()" title="Menu">
  <i class="fas fa-bars"></i>
</button>
<script>
function toggleAnnStatusDrop() {
  const list = document.getElementById('aStatusList');
  const btn = document.getElementById('aStatusBtn');
  // Close category first
  document.getElementById('aCategoryList').classList.remove('open');
  document.getElementById('aCategoryBtn').classList.remove('open');
  const isOpen = list.classList.toggle('open');
  btn.classList.toggle('open', isOpen);
}
function selectAnnStatus(val, label) {
  document.getElementById('aStatus').value = val;
  document.getElementById('aStatusLabel').textContent = label;
  document.querySelectorAll('#aStatusList .custom-select-option').forEach(o => {
    o.classList.toggle('selected', o.textContent === label);
  });
  document.getElementById('aStatusList').classList.remove('open');
  document.getElementById('aStatusBtn').classList.remove('open');
}
function toggleAnnCategoryDrop() {
  const list = document.getElementById('aCategoryList');
  const btn = document.getElementById('aCategoryBtn');
  // Close status first
  document.getElementById('aStatusList').classList.remove('open');
  document.getElementById('aStatusBtn').classList.remove('open');
  const isOpen = list.classList.toggle('open');
  btn.classList.toggle('open', isOpen);
}
function selectAnnCategory(val) {
  document.getElementById('aCategory').value = val;
  document.getElementById('aCategoryLabel').textContent = val;
  document.querySelectorAll('#aCategoryList .custom-select-option').forEach(o => {
    o.classList.toggle('selected', o.textContent === val);
  });
  document.getElementById('aCategoryList').classList.remove('open');
  document.getElementById('aCategoryBtn').classList.remove('open');
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('#aCategoryWrap')) {
    document.getElementById('aCategoryList')?.classList.remove('open');
    document.getElementById('aCategoryBtn')?.classList.remove('open');
  }
});
</script>
</body>

</html>