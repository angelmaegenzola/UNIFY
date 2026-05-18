<?php
// ============================================================
//  UNIFY — Student Profile Page
//  app/views/studentprofile.php
// ============================================================

if (session_status() === PHP_SESSION_NONE)
  session_start();

if (empty($_SESSION['user_id'])) {
  header('Location: index.php?page=login');
  exit;
}

$conn = new mysqli('127.0.0.1', 'root', '', 'unify_db');
if ($conn->connect_error)
  die('Database connection failed.');
$conn->set_charset('utf8mb4');

$user_id = (int) $_SESSION['user_id'];

// ── Fetch user ───────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, email, username, role, created_at,
           two_fa_enabled, profile_picture
    FROM users WHERE id = ?
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
  session_destroy();
  header('Location: index.php?page=login');
  exit;
}

// ── Fetch student_profiles ───────────────────────────────────
$sp_stmt = $conn->prepare("
    SELECT phone, date_of_birth, gender, nationality, address,
           course, year_level, section, academic_year, department, campus, student_id
    FROM student_profiles WHERE user_id = ?
");
$sp_stmt->bind_param('i', $user_id);
$sp_stmt->execute();
$profile = $sp_stmt->get_result()->fetch_assoc();
$sp_stmt->close();

// ── Stats ────────────────────────────────────────────────────
$stats_stmt = $conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM applications    WHERE user_id = ?)                       AS app_count,
        (SELECT COUNT(*) FROM members         WHERE user_id = ? AND status = 'active') AS club_count,
        (SELECT COUNT(*) FROM event_attendees WHERE user_id = ?)                       AS event_count
");
$stats_stmt->bind_param('iii', $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

// ── Nav checks ───────────────────────────────────────────────
$mem = $conn->prepare("SELECT id FROM members WHERE user_id = ? AND status = 'active' LIMIT 1");
$mem->bind_param('i', $user_id);
$mem->execute();
$mem->store_result();
$has_club = $mem->num_rows > 0;
$mem->close();

$pend = $conn->prepare("SELECT id FROM applications WHERE user_id = ? AND status = 'pending' LIMIT 1");
$pend->bind_param('i', $user_id);
$pend->execute();
$pend->store_result();
$has_pending = $pend->num_rows > 0;
$pend->close();

// ── Recent activity ──────────────────────────────────────────
$act_stmt = $conn->prepare("
    SELECT a.status, a.applied_at, c.name AS club_name
    FROM applications a
    JOIN clubs c ON c.id = a.club_id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$act_stmt->bind_param('i', $user_id);
$act_stmt->execute();
$activities = $act_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$act_stmt->close();

$conn->close();

// ── Display helpers ──────────────────────────────────────────
$first_name = htmlspecialchars($user['first_name'] ?? '');
$last_name = htmlspecialchars($user['last_name'] ?? '');
$full_name = trim($first_name . ' ' . $last_name);
$initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
$email = htmlspecialchars($user['email'] ?? '');
$username = htmlspecialchars($user['username'] ?? '');
$created_at = $user['created_at'] ? date('F j, Y', strtotime($user['created_at'])) : '—';
$twoFaEnabled = !empty($user['two_fa_enabled']);
$profile_picture = $user['profile_picture'] ?? '';
$avatar_url = '';
if ($profile_picture) {
    $filename  = basename($profile_picture);
    $disk_path = $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/public/assets/pictures/profile_pictures/' . $filename;
    if ($filename && file_exists($disk_path)) {
        $avatar_url = '/UNIFY(db)/public/assets/pictures/profile_pictures/' . htmlspecialchars($filename);
    }
}

$phone = htmlspecialchars($profile['phone'] ?? '—');
$dob = !empty($profile['date_of_birth']) ? date('F j, Y', strtotime($profile['date_of_birth'])) : '—';
$gender = htmlspecialchars($profile['gender'] ?? '—');
$nationality = htmlspecialchars($profile['nationality'] ?? '—');
$address = htmlspecialchars($profile['address'] ?? '—');
$course = htmlspecialchars($profile['course'] ?? '—');
$year_level = htmlspecialchars($profile['year_level'] ?? '—');
$section = htmlspecialchars($profile['section'] ?? '—');
$acad_year = htmlspecialchars($profile['academic_year'] ?? '—');
$department = htmlspecialchars($profile['department'] ?? '—');
$campus = htmlspecialchars($profile['campus'] ?? '—');
$student_id = htmlspecialchars($profile['student_id'] ?? '—');

$app_count = (int) ($stats['app_count'] ?? 0);
$club_count = (int) ($stats['club_count'] ?? 0);
$event_count = (int) ($stats['event_count'] ?? 0);

if ($club_count > 0) {
  $acct_status = '<span class="status-pill active"><i class="fas fa-circle-check"></i> Active Member</span>';
  $access_level = 'Full Member';
} elseif ($app_count > 0) {
  $acct_status = '<span class="status-pill pending"><i class="fas fa-hourglass-half"></i> Pending</span>';
  $access_level = 'Basic (Pre-member)';
} else {
  $acct_status = '<span class="status-pill inactive"><i class="fas fa-circle-xmark"></i> No Application</span>';
  $access_level = 'Basic (Pre-member)';
}

// ── Raw values for modal pre-fill ────────────────────────────
$raw_dob = $profile['date_of_birth'] ?? '';
$raw_gender = $profile['gender'] ?? '';
$raw_nationality = $profile['nationality'] ?? 'Filipino';
$raw_phone = $profile['phone'] ?? '';
$raw_address = $profile['address'] ?? '';
$raw_student_id = $profile['student_id'] ?? '';
$qr_url = $raw_student_id
  ? 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($raw_student_id) . '&bgcolor=ffffff&color=0d2b1a&qzone=2'
  : '';
$raw_course = $profile['course'] ?? '';
$raw_year = $profile['year_level'] ?? '';
$raw_section = $profile['section'] ?? '';
$raw_dept = $profile['department'] ?? '';
$raw_acad_year = $profile['academic_year'] ?? '';
$raw_campus = $profile['campus'] ?? '';

// Sidebar context (reuse officer vars if set, else basic fallback)
$sidebarClubName = $clubName ?? 'Student';
$sidebarClubInitial = $clubInitial ?? 'S';
$sidebarRole = $officerRole ?? ($user['role'] ?? 'member');
$sidebarOfficerClub = $officerClub ?? [];
$sidebarUserInit = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));
$sidebarUserName = $full_name;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — My Profile</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/UNIFY(db)/public/assets/css/studentprofile.css" />
</head>

<body>
  <div class="app">

    <!-- ═══════════════════════════════════════════════════════
       SIDEBAR
  ═══════════════════════════════════════════════════════════ -->
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
        <a href="index.php?page=explore" class="nav-item "><i class="fas fa-compass"></i><span>Explore
            Clubs</span></a>
        <div class="nav-section-label">MY SPACE</div>
        <?php if ($has_club): ?>
          <a href="index.php?page=studenthome" class="nav-item"><i class="fas fa-house"></i><span>Home</span></a>
          <a href="index.php?page=myclubs" class="nav-item"><i class="fas fa-users"></i><span>My Clubs</span></a>
          <a href="index.php?page=studentevents" class="nav-item"><i
              class="fas fa-calendar-days"></i><span>Events</span></a>
          <a href="index.php?page=student_messages" class="nav-item"><i class="fas fa-comments"></i><span>Club
              Chat</span></a>
        <?php else: ?>
          <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-house"></i><span>Home</span><i
              class="fas fa-lock nav-lock-icon"></i></a>
          <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-users"></i><span>My Clubs</span><i
              class="fas fa-lock nav-lock-icon"></i></a>
          <a href="#" class="nav-item locked" onclick="return false;"><i
              class="fas fa-calendar-days"></i><span>Events</span><i class="fas fa-lock nav-lock-icon"></i></a>
          <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-comments"></i><span>Club
              Chat</span><i class="fas fa-lock nav-lock-icon"></i></a>
        <?php endif; ?>
      </nav>
      <div class="sidebar-bottom">
        <div class="sidebar-profile">
          <div class="profile-avatar-wrap">
  <?php if (!empty($avatar_url)): ?>
    <img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" />
  <?php else: ?>
    <span class="profile-avatar-fallback"><?= $initials ?></span>
  <?php endif; ?>
  <span class="profile-online-dot"></span>
</div>
          <a href="index.php?page=studentprofile" class="profile-link">
            <div class="profile-info"><span class="profile-name"><?= $full_name ?></span><span
                class="profile-role">Student</span></div>
          </a>
          <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i
              class="fas fa-arrow-right-from-bracket"></i></a>
        </div>
      </div>
    </aside>

    <!-- ═══════════════════════════════════════════════════════
       MAIN
  ═══════════════════════════════════════════════════════════ -->
    <main class="main">



      <!-- Content -->
      <div class="profile-content">

        <!-- ── LEFT PANEL ── -->
        <div class="profile-left">

          <!-- Avatar Card -->
          <div class="avatar-card">
           <div class="avatar-ring">
  <div class="avatar-circle" id="sp-avatar-circle">
    <?php if ($avatar_url): ?>
      <img src="<?= $avatar_url ?>" alt="Profile" class="avatar-photo" id="sp-avatar-img"
        style="width:100%;height:100%;border-radius:50%;object-fit:cover;object-position:center;display:block;position:absolute;top:0;left:0;" />
    <?php else: ?>
      <?= $initials ?>
    <?php endif; ?>
  </div>
  <div class="avatar-online"></div>
  <input type="file" id="sp-avatar-input" accept="image/jpeg,image/png,image/gif,image/webp"
    style="display:none;" onchange="StudentProfile.uploadAvatar(this)" />
  <button class="sp-avatar-edit-btn" title="Change photo"
    onclick="document.getElementById('sp-avatar-input').click()">
    <i class="fas fa-camera"></i>
  </button>
</div>
            <div class="avatar-name"><?= $full_name ?></div>
            <div class="avatar-role-badge">
              <i class="fas fa-user-graduate"></i>
              <?= htmlspecialchars(ucfirst($user['role'] ?? 'Student')) ?>
            </div>
            <div class="avatar-id"><?= $student_id !== '—' ? 'ID: ' . $student_id : 'No student ID set' ?></div>
            <div class="avatar-stats">
              <div class="astat">
                <div class="astat-val"><?= $app_count ?></div>
                <div class="astat-label">Applied</div>
              </div>
              <div class="astat-divider"></div>
              <div class="astat">
                <div class="astat-val"><?= $club_count ?></div>
                <div class="astat-label">Clubs</div>
              </div>
              <div class="astat-divider"></div>
              <div class="astat">
                <div class="astat-val"><?= $event_count ?></div>
                <div class="astat-label">Events</div>
              </div>
            </div>
          </div>

          <!-- Account Status Card -->
          <div class="info-card">
            <div class="ic-header"><i class="fas fa-shield-halved"></i> Account Status</div>
            <div class="ic-rows">
              <div class="ic-row">
                <span class="ic-label">Status</span>
                <?= $acct_status ?>
              </div>
              <div class="ic-row">
                <span class="ic-label">Access</span>
                <span class="ic-value"><?= htmlspecialchars($access_level) ?></span>
              </div>
              <div class="ic-row">
                <span class="ic-label">Member Since</span>
                <span class="ic-value"><?= $created_at ?></span>
              </div>
              <div class="ic-row">
                <span class="ic-label">2FA</span>
                <?php if ($twoFaEnabled): ?>
                  <span class="status-pill active"><i class="fas fa-lock"></i> Enabled</span>
                <?php else: ?>
                  <span class="status-pill inactive"><i class="fas fa-lock-open"></i> Disabled</span>
                <?php endif; ?>
              </div>
            </div>
            <!-- Quick actions -->
            <button class="change-pw-btn" onclick="StudentProfile.openModal('password')">
              <i class="fas fa-key"></i> Change Password
            </button>
            <button class="change-pw-btn" onclick="StudentProfile.openModal('twofa')" style="margin-top:6px;">
              <i class="fas fa-shield-halved"></i> <?= $twoFaEnabled ? 'Manage 2FA' : 'Enable 2FA' ?>
            </button>
            <button class="change-pw-btn sp-qr-trigger-btn" onclick="StudentProfile.openModal('attendanceqr')"
              style="margin-top:6px;">
              <i class="fas fa-qrcode"></i> My Attendance QR
            </button>
          </div>

        </div><!-- /profile-left -->

        <!-- ── RIGHT PANEL ── -->
        <div class="profile-right">

          <!-- Personal Info Card -->
          <div class="detail-card">
            <div class="dc-header">
              <div class="dc-title"><i class="fas fa-user"></i> Personal Information</div>
              <button class="dc-edit-btn" onclick="StudentProfile.openModal('personal')">
                <i class="fas fa-pen"></i> Edit
              </button>
            </div>
            <div class="dc-fields">
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Full Name</div>
                  <div class="dcf-value"><?= $full_name ?></div>
                </div>
                <div class="dcf-item">
                  <div class="dcf-label">Username</div>
                  <div class="dcf-value"><?= $username ?: '—' ?></div>
                </div>
              </div>
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Email</div>
                  <div class="dcf-value"><?= $email ?></div>
                </div>
                <div class="dcf-item">
                  <div class="dcf-label">Phone</div>
                  <div class="dcf-value"><?= $phone ?></div>
                </div>
              </div>
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Date of Birth</div>
                  <div class="dcf-value"><?= $dob ?></div>
                </div>
                <div class="dcf-item">
                  <div class="dcf-label">Gender</div>
                  <div class="dcf-value"><?= $gender ?></div>
                </div>
              </div>
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Nationality</div>
                  <div class="dcf-value"><?= $nationality ?></div>
                </div>
                <div class="dcf-item dcf-item-full" style="grid-column:span 1;">
                  <div class="dcf-label">Address</div>
                  <div class="dcf-value"><?= $address ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Academic Info Card -->
          <div class="detail-card">
            <div class="dc-header">
              <div class="dc-title"><i class="fas fa-graduation-cap"></i> Academic Information</div>
              <button class="dc-edit-btn" onclick="StudentProfile.openModal('academic')">
                <i class="fas fa-pen"></i> Edit
              </button>
            </div>
            <div class="dc-fields">
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Student ID</div>
                  <div class="dcf-value"><?= $student_id ?></div>
                </div>
                <div class="dcf-item">
                  <div class="dcf-label">Department</div>
                  <div class="dcf-value"><?= $department ?></div>
                </div>
              </div>
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Course / Program</div>
                  <div class="dcf-value"><?= $course ?></div>
                </div>
                <div class="dcf-item">
                  <div class="dcf-label">Year Level</div>
                  <div class="dcf-value"><?= $year_level ?></div>
                </div>
              </div>
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Section</div>
                  <div class="dcf-value"><?= $section ?></div>
                </div>
                <div class="dcf-item">
                  <div class="dcf-label">Academic Year</div>
                  <div class="dcf-value"><?= $acad_year ?></div>
                </div>
              </div>
              <div class="dcf-row">
                <div class="dcf-item">
                  <div class="dcf-label">Campus</div>
                  <div class="dcf-value"><?= $campus ?></div>
                </div>
                <div class="dcf-item"></div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <?php if (!empty($activities)): ?>
            <div class="detail-card">
              <div class="dc-header">
                <div class="dc-title"><i class="fas fa-clock-rotate-left"></i> Recent Activity</div>
              </div>
              <div class="activity-list">
                <?php foreach ($activities as $act):
                  $icon_cls = match ($act['status']) {
                    'approved' => 'done',
                    'rejected' => 'rejected',
                    default => 'pending',
                  };
                  $icon = match ($act['status']) {
                    'approved' => 'fa-circle-check',
                    'rejected' => 'fa-circle-xmark',
                    default => 'fa-hourglass-half',
                  };
                  $label = match ($act['status']) {
                    'approved' => 'Application Approved',
                    'rejected' => 'Application Rejected',
                    default => 'Application Pending',
                  };
                  ?>
                  <div class="activity-item">
                    <div class="act-icon <?= $icon_cls ?>">
                      <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="act-body">
                      <div class="act-title"><?= $label ?> — <strong><?= htmlspecialchars($act['club_name']) ?></strong>
                      </div>
                      <div class="act-time"><?= date('F j, Y · g:i A', strtotime($act['applied_at'])) ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

        </div><!-- /profile-right -->
      </div><!-- /profile-content -->
    </main>
  </div><!-- /app -->


  <!-- ═══════════════════════════════════════════════════════════
     MODAL: Edit Personal Info
═══════════════════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="modal-personal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-title-icon"><i class="fas fa-user"></i></div>
          <div>
            <div class="modal-title">Personal Information</div>
            <div class="modal-subtitle">Update your name, contact, and personal details</div>
          </div>
        </div>
        <button class="modal-close" onclick="StudentProfile.closeModal('personal')"><i
            class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="modal-section-label">Basic Info</div>
        <div class="modal-fields">
          <div class="field-group">
            <label class="field-label">First Name <span style="color:#c0392b">*</span></label>
            <input type="text" id="pi_first_name" class="field-input"
              value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Last Name <span style="color:#c0392b">*</span></label>
            <input type="text" id="pi_last_name" class="field-input"
              value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Username</label>
            <input type="text" id="pi_username" class="field-input"
              value="<?= htmlspecialchars($user['username'] ?? '') ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Email <span style="color:#c0392b">*</span></label>
            <input type="email" id="pi_email" class="field-input"
              value="<?= htmlspecialchars($user['email'] ?? '') ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Phone</label>
            <input type="tel" id="pi_phone" class="field-input" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>"
              placeholder="+63 9XX XXX XXXX" />
          </div>
          <div class="field-group">
            <label class="field-label">Date of Birth</label>
            <input type="date" id="pi_dob" class="field-input"
              value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Gender</label>
            <select id="pi_gender" class="field-input">
              <option value="">— Select —</option>
              <?php foreach (['Male', 'Female', 'Non-binary', 'Prefer not to say'] as $g): ?>
                <option value="<?= $g ?>" <?= ($raw_gender === $g) ? 'selected' : '' ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Nationality</label>
            <input type="text" id="pi_nationality" class="field-input"
              value="<?= htmlspecialchars($profile['nationality'] ?? 'Filipino') ?>" />
          </div>
          <div class="field-group field-full">
            <label class="field-label">Address</label>
            <input type="text" id="pi_address" class="field-input"
              value="<?= htmlspecialchars($profile['address'] ?? '') ?>" placeholder="Street, City, Province" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="modal-btn-cancel" onclick="StudentProfile.closeModal('personal')">Cancel</button>
        <button class="modal-btn-save" onclick="StudentProfile.savePersonal()">
          <i class="fas fa-floppy-disk"></i> Save Changes
        </button>
      </div>
    </div>
  </div>


  <!-- ═══════════════════════════════════════════════════════════
     MODAL: Edit Academic Info
═══════════════════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="modal-academic">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-title-icon"><i class="fas fa-graduation-cap"></i></div>
          <div>
            <div class="modal-title">Academic Information</div>
            <div class="modal-subtitle">Update your course, department, and academic details</div>
          </div>
        </div>
        <button class="modal-close" onclick="StudentProfile.closeModal('academic')"><i
            class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="modal-fields">
          <div class="field-group">
            <label class="field-label">Student ID</label>
            <input type="text" id="ac_student_id" class="field-input"
              value="<?= htmlspecialchars($profile['student_id'] ?? '') ?>" placeholder="e.g. 2021-00001" />
          </div>
          <div class="field-group">
            <label class="field-label">Department</label>
            <select id="ac_department" class="field-input">
              <option value="">— Select —</option>
              <?php
              $depts = [
                'College of Information Technology',
                'College of Business Administration',
                'College of Engineering',
                'College of Education',
                'College of Arts and Sciences',
                'College of Nursing',
              ];
              foreach ($depts as $d): ?>
                <option value="<?= $d ?>" <?= ($raw_dept === $d) ? 'selected' : '' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Course / Program</label>
            <input type="text" id="ac_course" class="field-input"
              value="<?= htmlspecialchars($profile['course'] ?? '') ?>" placeholder="e.g. BSIT, BSCS" />
          </div>
          <div class="field-group">
            <label class="field-label">Year Level</label>
            <select id="ac_year_level" class="field-input">
              <option value="">— Select —</option>
              <?php foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Graduate'] as $y): ?>
                <option value="<?= $y ?>" <?= ($raw_year === $y) ? 'selected' : '' ?>><?= $y ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-group">
            <label class="field-label">Section</label>
            <input type="text" id="ac_section" class="field-input"
              value="<?= htmlspecialchars($profile['section'] ?? '') ?>" placeholder="e.g. IT3A" />
          </div>
          <div class="field-group">
            <label class="field-label">Academic Year</label>
            <input type="text" id="ac_academic_year" class="field-input"
              value="<?= htmlspecialchars($profile['academic_year'] ?? '') ?>" placeholder="e.g. 2024–2025" />
          </div>
          <div class="field-group field-full">
            <label class="field-label">Campus</label>
            <input type="text" id="ac_campus" class="field-input"
              value="<?= htmlspecialchars($profile['campus'] ?? '') ?>" placeholder="e.g. Main Campus" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="modal-btn-cancel" onclick="StudentProfile.closeModal('academic')">Cancel</button>
        <button class="modal-btn-save" onclick="StudentProfile.saveAcademic()">
          <i class="fas fa-floppy-disk"></i> Save Changes
        </button>
      </div>
    </div>
  </div>


  <!-- ═══════════════════════════════════════════════════════════
     MODAL: Change Password
═══════════════════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="modal-password">
    <div class="modal-box modal-box-sm">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-title-icon"><i class="fas fa-lock"></i></div>
          <div>
            <div class="modal-title">Change Password</div>
            <div class="modal-subtitle">Use a strong, unique password</div>
          </div>
        </div>
        <button class="modal-close" onclick="StudentProfile.closeModal('password')"><i
            class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="pw-rules"><i class="fas fa-circle-info"></i> At least 8 characters, with uppercase, number, and
          symbol.</div>
        <div class="modal-fields modal-fields-1col">
          <div class="field-group">
            <label class="field-label">Current Password</label>
            <div class="pw-input-wrap">
              <input type="password" id="pw_current" class="field-input" placeholder="Enter current password" />
              <button type="button" class="pw-toggle" onclick="StudentProfile.togglePw('pw_current', this)"><i
                  class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">New Password</label>
            <div class="pw-input-wrap">
              <input type="password" id="pw_new" class="field-input" placeholder="At least 8 characters"
                oninput="StudentProfile.checkStrength(this.value)" />
              <button type="button" class="pw-toggle" onclick="StudentProfile.togglePw('pw_new', this)"><i
                  class="fas fa-eye"></i></button>
            </div>
            <div class="pw-strength-wrap">
              <div class="pw-strength-bar">
                <div class="pw-strength-fill" id="pw-strength-fill"></div>
              </div>
              <span class="pw-strength-label" id="pw-strength-label" style="color:#aaa;">—</span>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Confirm New Password</label>
            <div class="pw-input-wrap">
              <input type="password" id="pw_confirm" class="field-input" placeholder="Re-enter new password"
                oninput="StudentProfile.checkMatch()" />
              <button type="button" class="pw-toggle" onclick="StudentProfile.togglePw('pw_confirm', this)"><i
                  class="fas fa-eye"></i></button>
            </div>
            <span class="pw-match-msg" id="pw-match-msg"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="modal-btn-cancel" onclick="StudentProfile.closeModal('password')">Cancel</button>
        <button class="modal-btn-save" onclick="StudentProfile.savePassword()">
          <i class="fas fa-lock"></i> Update Password
        </button>
      </div>
    </div>
  </div>


  <!-- ═══════════════════════════════════════════════════════════
     MODAL: Two-Factor Authentication
═══════════════════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="modal-twofa">
    <div class="modal-box modal-box-sm">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-title-icon"><i class="fas fa-shield-halved"></i></div>
          <div>
            <div class="modal-title">Two-Factor Authentication</div>
            <div class="modal-subtitle">Extra security for your account</div>
          </div>
        </div>
        <button class="modal-close" onclick="StudentProfile.closeModal('twofa')"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body" id="twofa-modal-body">

        <?php if (!$twoFaEnabled): ?>
          <!-- ── 2FA OFF ── -->
          <div id="twofa-off-state">
            <p style="font-size:.84rem;color:#555;line-height:1.7;margin-bottom:14px;">
              Protect your account with an extra verification step. After enabling, you'll need
              your authenticator app every time you log in — even if someone has your password.
            </p>
            <button class="modal-btn-save" style="width:100%;justify-content:center;" id="btn-twofa-generate"
              onclick="StudentProfile.twofa.generate()">
              <i class="fas fa-shield-halved"></i> Enable Two-Factor Authentication
            </button>
          </div>

          <!-- QR setup step (hidden until generate clicked) -->
          <div id="twofa-qr-step" style="display:none;">
            <div class="twofa-steps-list">
              <div class="twofa-step"><span class="twofa-step-num">1</span><span>Download <strong>Google
                    Authenticator</strong> or <strong>Authy</strong> on your phone.</span></div>
              <div class="twofa-step"><span class="twofa-step-num">2</span><span>Tap <strong>+</strong> → <em>Scan a QR
                    code</em>.</span></div>
              <div class="twofa-step"><span class="twofa-step-num">3</span><span>Scan the code below, then enter the
                  6-digit code.</span></div>
            </div>
            <div class="twofa-qr-wrap">
              <img id="twofa-qr-img" src="" alt="QR Code" />
            </div>
            <details class="twofa-manual-wrap">
              <summary><i class="fas fa-keyboard"></i> Can't scan? Enter key manually</summary>
              <code id="twofa-manual-secret"
                style="word-break:break-all;font-size:13px;display:block;margin-top:8px;padding:10px;background:#f5f5f5;border-radius:8px;"></code>
            </details>
            <div class="field-group" style="margin-top:14px;">
              <label class="field-label">Enter 6-digit code from your app:</label>
              <input type="text" id="twofa-otp-input" class="field-input" maxlength="6" inputmode="numeric"
                pattern="[0-9]*" placeholder="000000" autocomplete="off"
                style="letter-spacing:6px;font-size:20px;text-align:center;font-weight:800;" />
              <span class="pw-match-msg" id="twofa-otp-err" style="color:#c0392b;"></span>
            </div>
            <button class="modal-btn-save" style="width:100%;justify-content:center;margin-top:10px;"
              id="btn-twofa-enable" onclick="StudentProfile.twofa.enable()">
              <i class="fas fa-check"></i> Confirm &amp; Enable
            </button>
          </div>

        <?php else: ?>
          <!-- ── 2FA ON ── -->
          <div id="twofa-on-state">
            <div class="twofa-enabled-banner">
              <i class="fas fa-circle-check"></i>
              <div>
                <strong>2FA is active on your account.</strong>
                <span>You'll need your authenticator app to log in.</span>
              </div>
            </div>
            <button class="twofa-btn-disable" id="btn-twofa-disable" onclick="StudentProfile.twofa.disable()">
              <i class="fas fa-shield-xmark"></i> Disable Two-Factor Authentication
            </button>
          </div>
        <?php endif; ?>

      </div>
      <div class="modal-footer" id="twofa-modal-footer">
        <button class="modal-btn-cancel" onclick="StudentProfile.closeModal('twofa')">Close</button>
      </div>
    </div>
  </div>


  <!-- Toast -->
  <div class="crud-toast" id="sp-toast">
    <i class="fas fa-circle-check" id="sp-toast-icon"></i>
    <span id="sp-toast-msg">Saved!</span>
  </div>

  <!-- Inject PHP data for JS -->
  <script>
    window.SP_DATA = {
      twoFaEnabled: <?= $twoFaEnabled ? 'true' : 'false' ?>,
      twoFaEndpoint: "<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')) ?>?page=setup_2fa",
      saveEndpoint: "index.php?page=studentprofile_save",
      userId: <?= $user_id ?>
    };
  </script>
  <script src="/UNIFY(db)/public/assets/javascripts/studentprofile.js"></script>

  <!-- ── Attendance QR Modal ──────────────────────────────────── -->
  <div class="modal-overlay" id="modal-attendanceqr"
    onclick="if(event.target===this)StudentProfile.closeModal('attendanceqr')">
    <div class="modal-box sp-qr-modal-box">
      <div class="modal-header">
        <div class="modal-title"><i class="fas fa-qrcode"
            style="margin-right:8px;color:var(--green-accent,#2a7a48);"></i>My Attendance QR Code</div>
        <button class="modal-close" onclick="StudentProfile.closeModal('attendanceqr')"><i
            class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body sp-qr-modal-body">
        <?php if ($qr_url): ?>
          <p class="sp-qr-modal-hint">Present this QR code to your officer during club events to mark your attendance.</p>
          <div class="sp-qr-modal-img-wrap">
            <img src="<?= htmlspecialchars($qr_url) ?>" alt="Attendance QR Code" class="sp-qr-modal-img" />
          </div>
          <div class="sp-qr-modal-lrn"><?= htmlspecialchars($raw_student_id) ?></div>
          <div class="sp-qr-modal-name"><?= htmlspecialchars($full_name) ?></div>
          <a href="<?= htmlspecialchars($qr_url) ?>" download="attendance-qr-<?= htmlspecialchars($raw_student_id) ?>.png"
            class="sp-qr-modal-download">
            <i class="fas fa-download"></i> Download QR Code
          </a>
        <?php else: ?>
          <div class="sp-qr-modal-missing">
            <i class="fas fa-circle-exclamation"
              style="font-size:32px;color:#d4a017;margin-bottom:12px;display:block;"></i>
            <strong>No Student ID on record</strong>
            <p>You need to set your Student ID (LRN) before a QR code can be generated. Go to the <strong>Academic
                Information</strong> section and fill in your Student ID.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</body>

</html>