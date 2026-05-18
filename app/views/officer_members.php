<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/app/controllers/officer_members_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Members</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/UNIFY(db)/public/assets/css/officer_members.css" />
  <link rel="stylesheet" href="/UNIFY(db)/public/assets/css/transitions.css" />
</head>

<body>
  <div class="app">

    <!-- ── SIDEBAR ────────────────────────────────────────────── -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="/UNIFY(db)/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
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
        <a href="index.php?page=officer_members" class="nav-item active">
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
          <span class="topbar-page-title">Members</span>
          <span class="topbar-date" id="topbarDate"></span>
        </div>
        <div class="topbar-center">
          <div class="topbar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="memberSearchInput" placeholder="Search members by name, role, course…"
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
            <div class="topbar-avatar">
              <?php if (!empty($avatar_url)): ?>
                <img src="<?= $avatar_url ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" />
              <?php else: ?>
                <?= htmlspecialchars($userInit) ?>
              <?php endif; ?>
            </div>
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

        <!-- Stats Row -->
        <div class="stats-row">
          <div class="stat-pill sp-green">
            <i class="fas fa-users"></i>
            <span class="sp-num" id="statTotal"><?= $totalMembers ?></span>
            <span class="sp-label">Total Members</span>
          </div>
          <div class="stat-pill sp-blue">
            <i class="fas fa-shield-halved"></i>
            <span class="sp-num"><?= $officerCount ?></span>
            <span class="sp-label">Officers</span>
          </div>
          <div class="stat-pill sp-yellow">
            <i class="fas fa-hourglass-half"></i>
            <span class="sp-num" id="statPending"><?= $pendingCount ?></span>
            <span class="sp-label">Pending</span>
          </div>
          <div class="stat-pill sp-teal">
            <i class="fas fa-calendar-plus"></i>
            <span class="sp-num"><?= $newThisMonth ?></span>
            <span class="sp-label">Joined This Month</span>
          </div>

         
          <?php if (in_array($officerRole, ['president', 'vice president'])): ?>
            <button class="add-member-btn" onclick="openAddMemberModal()">
              <i class="fas fa-user-plus"></i> Add Member
            </button>
          <?php endif; ?>
        </div>

        <!-- Two-column layout -->
        <div class="members-layout">

          <!-- Member List -->
          <div class="members-panel">
            <div class="members-table-header">
              <span>Member</span>
              <span>Role</span>
              <span>Course / Year</span>
              <span>Joined</span>
              <span>Actions</span>
            </div>
            <div class="members-list" id="membersList"></div>
          </div>

          <!-- Right panel: Pending + Officers -->
          <div class="right-panels">

            <!-- Pending Applications -->
            <div class="card pending-card">
              <div class="card-header">
                <div>
                  <h2>Pending Applications</h2>
                  <div class="card-sub">Awaiting officer review</div>
                </div>
                <span class="pending-badge" id="pendingBadge"><?= $pendingCount ?> pending</span>
              </div>
              <div class="applicant-list" id="applicantList"></div>
            </div>

            <!-- Officers Quick View -->
            <div class="card officers-card">
              <div class="card-header">
                <h2>Officer Team</h2>
                <span class="officer-count-badge"><?= $officerCount ?> officers</span>
              </div>
              <div class="officers-list" id="officersList"></div>
            </div>

          </div>
        </div>

      </div><!-- /content -->
    </main>
  </div>

  <!-- ── MODALS ──────────────────────────────────────────────── -->

  <!-- Member Detail Modal -->
  <div class="modal-overlay" id="memberDetailModal" onclick="handleOverlayClick(event,'memberDetailModal')">
    <div class="modal modal-md">
      <div class="modal-header">
        <span class="modal-title">Member Profile</span>
        <button class="modal-close" onclick="closeModal('memberDetailModal')"><i class="fas fa-times"></i></button>
      </div>
      <div class="member-detail-hero">
        <div class="member-detail-avatar" id="mdAvatar">?</div>
        <div class="member-detail-info">
          <div class="member-detail-name" id="mdName">—</div>
          <div class="member-detail-email" id="mdEmail">—</div>
          <div style="margin-top:6px;" id="mdRoleBadgeWrap"></div>
        </div>
      </div>

      <!-- Attendance Score -->
      <div style="padding:10px 0 4px;">
        <div style="font-size:11px;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Attendance</div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="flex:1;height:7px;background:var(--border);border-radius:4px;overflow:hidden;">
            <div id="mdScoreBar" style="height:100%;border-radius:4px;transition:width .4s;width:0%;background:#1D9E75;"></div>
          </div>
          <span id="mdScoreNum" style="font-size:13px;font-weight:700;min-width:36px;text-align:right;color:#0F6E56;">—</span>
        </div>
        <div id="mdScoreBreakdown" style="font-size:11px;color:var(--text-light);margin-top:5px;">—</div>
      </div>

      <div class="detail-info-grid">
        <div class="detail-info-item">
          <div class="detail-info-label">Student ID</div>
          <div class="detail-info-value" id="mdStudentId">—</div>
        </div>
        <div class="detail-info-item">
          <div class="detail-info-label">Course & Year</div>
          <div class="detail-info-value" id="mdCourse">—</div>
        </div>
        <div class="detail-info-item">
          <div class="detail-info-label">Section</div>
          <div class="detail-info-value" id="mdSection">—</div>
        </div>
        <div class="detail-info-item">
          <div class="detail-info-label">Joined</div>
          <div class="detail-info-value" id="mdJoined">—</div>
        </div>
      </div>
      <div id="mdRoleChangeSection" style="display:none;">
        <div class="detail-section-label" style="margin-top:4px;">Change Role</div>
        <div class="role-change-row">
          <select class="form-select" id="mdNewRole">
            <option value="member">Member</option>
            <option value="lead">Lead</option>
            <option value="officer">Officer</option>
            <option value="vice president">Vice President</option>
            <option value="president">President</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <div id="mdRoleSaveWrap" style="display:none;margin-left:auto;">
          <button class="btn-primary" onclick="changeRole()"><i class="fas fa-check"></i> Save</button>
        </div>
        <button class="btn-secondary" onclick="closeModal('memberDetailModal')">Close</button>
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
        <div class="app-info-cell">
          <div class="app-info-cell-label">Student ID</div>
          <div class="app-info-cell-value" id="arStudentId">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Course & Year</div>
          <div class="app-info-cell-value" id="arCourse">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Email</div>
          <div class="app-info-cell-value" id="arEmail">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Contact No.</div>
          <div class="app-info-cell-value" id="arPhone">—</div>
        </div>
        <div class="app-info-cell">
          <div class="app-info-cell-label">Date Applied</div>
          <div class="app-info-cell-value" id="arDate">—</div>
        </div>
      </div>
      <div class="app-section-divider">Why they want to join & Skills</div>
      <div style="padding:0 2px 12px;">
        <div id="arExtras"
          style="font-size:13px;color:var(--text-mid);line-height:1.7;white-space:pre-wrap;background:#f7f7f7;border-radius:8px;padding:10px 12px;min-height:48px;">
          —</div>
      </div>
      <div class="app-action-row">
        <button class="btn-reject-app" onclick="openRejectModal()"><i class="fas fa-times-circle"></i> Decline</button>
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
        <textarea class="form-textarea" id="rejectReason" placeholder="e.g. Slots are full for this semester…"
          style="min-height:80px;"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('rejectReasonModal')">Cancel</button>
        <button class="btn-danger" onclick="rejectApplication()"><i class="fas fa-times-circle"></i> Confirm Decline</button>
      </div>
    </div>
  </div>

  <!-- Add Member Modal -->
  <div class="modal-overlay" id="addMemberModal" onclick="handleOverlayClick(event,'addMemberModal')">
    <div class="modal modal-sm">
      <div class="modal-header">
        <span class="modal-title">Add Member Directly</span>
        <button class="modal-close" onclick="closeModal('addMemberModal')"><i class="fas fa-times"></i></button>
      </div>
      <p style="font-size:12.5px;color:var(--text-light);line-height:1.6;">Search for a registered student by their
        email or student ID to add them directly as a club member.</p>
      <div class="form-group">
        <label class="form-label">Search Student (Email or Student ID)</label>
        <div class="student-search-wrap">
          <input type="text" class="form-input" id="studentSearchInput"
            placeholder="e.g. student@school.edu or 2023-00001" oninput="searchStudent()" />
          <div class="student-search-results" id="studentSearchResults"></div>
        </div>
      </div>
      <div id="selectedStudentCard" style="display:none;" class="selected-student-card">
        <div class="sel-avatar" id="selAvatar">?</div>
        <div class="sel-info">
          <div class="sel-name" id="selName">—</div>
          <div class="sel-email" id="selEmail">—</div>
        </div>
        <button class="sel-clear" onclick="clearSelectedStudent()"><i class="fas fa-times"></i></button>
      </div>
      <div class="form-group">
        <label class="form-label">Assign Role</label>
        <select class="form-select" id="newMemberRole">
          <option value="member">Member</option>
          <option value="lead">Lead</option>
          <option value="officer">Officer</option>
          <option value="vice president">Vice President</option>
        </select>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('addMemberModal')">Cancel</button>
        <button class="btn-primary" onclick="addMemberDirectly()"><i class="fas fa-user-plus"></i> Add Member</button>
      </div>
    </div>
  </div>

  <!-- Confirm Dialog -->
  <div class="modal-overlay" id="confirmModal" onclick="handleOverlayClick(event,'confirmModal')">
    <div class="modal modal-xs">
      <div class="modal-header">
        <span class="modal-title" id="confirmTitle">Confirm</span>
        <button class="modal-close" onclick="closeModal('confirmModal')"><i class="fas fa-times"></i></button>
      </div>
      <p class="confirm-msg" id="confirmMessage"></p>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('confirmModal')">Cancel</button>
        <button class="btn-danger" id="confirmOkBtn">Confirm</button>
      </div>
    </div>
  </div>

  <!-- Notification Panel -->
  <div id="notifPanel" class="notif-panel" style="display:none;">
    <div class="notif-panel-header">
      <span>Notifications</span>
      <button onclick="markAllRead()" class="notif-mark-all">Mark all read</button>
    </div>
    <div id="notifList" class="notif-list">
      <div class="notif-empty">Loading…</div>
    </div>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast"></div>

  <!-- ── DATA INJECTION ──────────────────────────────────────── -->
  <script>
    window.OM = {
      page: 'officer_members',
      userId: <?= (int) $_SESSION['user_id'] ?>,
      clubId: <?= (int) $clubId ?>,
      officerRole: <?= json_encode($officerRole) ?>,
      canManage: <?= json_encode(in_array($officerRole, ['president', 'vice president'])) ?>,

      members: <?= json_encode(array_map(function ($m, $i) use (&$scoreMap) {
        static $colors = ['av-green', 'av-teal', 'av-red', 'av-yellow', 'av-purple'];
        $s = $scoreMap[$m['user_id']] ?? ['score' => 0, 'breakdown' => '—'];
        return [
          'db_id'           => (int) $m['id'],
          'user_id'         => (int) $m['user_id'],
          'initial'         => strtoupper(substr($m['first_name'], 0, 1)),
          'color'           => $colors[$i % 5],
          'name'            => $m['first_name'] . ' ' . $m['last_name'],
          'email'           => $m['email'] ?? '—',
          'role'            => $m['role'],
          'course'          => $m['course'] ?? '',
          'year'            => $m['year'] ?? '',
          'section'         => $m['section'] ?? '',
          'student_id'      => $m['student_id'] ?? '—',
          'club_position'   => $m['club_position'] ?? '',
          'joined'          => $m['joined_at'] ? date('M j, Y', strtotime($m['joined_at'])) : '—',
          'score'           => $s['score'],
          'score_breakdown' => $s['breakdown'],
        ];
      }, $dbMembers, array_keys($dbMembers)), JSON_HEX_TAG | JSON_HEX_APOS) ?>,

      applicants: <?= json_encode(array_map(function ($a, $i) {
        static $colors = ['av-green', 'av-teal', 'av-red', 'av-yellow', 'av-purple'];
        return [
          'db_id'      => (int) $a['id'],
          'initial'    => strtoupper(substr($a['first_name'], 0, 1)),
          'color'      => $colors[$i % 5],
          'name'       => $a['first_name'] . ' ' . $a['last_name'],
          'email'      => $a['email'],
          'course'     => trim(($a['app_course'] ?? '') . ' ' . ($a['year'] ?? '') . ' ' . ($a['section'] ?? '')),
          'student_id' => $a['student_id_no'] ?? '—',
          'phone'      => $a['app_phone'] ?? '—',
          'extras'     => $a['extras'] ?? '',
          'date'       => date('M j, Y', strtotime($a['applied_at'])),
        ];
      }, $dbApplicants, array_keys($dbApplicants)), JSON_HEX_TAG | JSON_HEX_APOS) ?>
    };
  </script>
  <script src="/UNIFY(db)/public/assets/javascripts/officer_members.js?v=2"></script>
</body>

</html>