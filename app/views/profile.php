<?php
$user = [
  'id'          => 1,
  'first_name'  => 'Alex',
  'last_name'   => 'Santos',
  'username'    => 'alexsantos',
  'email'       => 'alex.santos@chmsu.edu.ph',
  'phone'       => '+63 912 345 6789',
  'department'  => 'College of Information Technology',
  'course'      => 'BS Computer Science',
  'year_level'  => '3rd Year',
  'student_id'  => '2023-10045',
  'bio'         => 'Club Admin at UNIFY. Passionate about technology, event management, and student leadership.',
  'role'        => 'Club Admin',
  'status'      => 'Active',
  'joined'      => 'August 2023',
  'events_managed' => 14,
  'clubs_joined'   => 3,
  'announcements'  => 22,
];

// Handle form submission
$success_msg = '';
$error_msg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'update_profile') {
    $success_msg = 'Profile updated successfully.';

  } elseif ($action === 'update_password') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
      $error_msg = 'All password fields are required.';
    } elseif ($new !== $confirm) {
      $error_msg = 'New passwords do not match.';
    } elseif (strlen($new) < 8) {
      $error_msg = 'Password must be at least 8 characters.';
    } else {
      $success_msg = 'Password changed successfully.';
    }

  } elseif ($action === 'update_notifications') {
    $success_msg = 'Notification preferences saved.';
  }
}

// Clubs the user belongs to 
$clubs = [
  ['name' => 'CS Society',         'role' => 'Admin',   'icon' => 'fa-laptop-code', 'color' => '#2563eb', 'badge' => 'cbadge-admin'],
  ['name' => 'Business Mgmt Club', 'role' => 'Member',  'icon' => 'fa-briefcase',   'color' => '#d4a017', 'badge' => 'cbadge-member'],
  ['name' => 'Science & Tech Club','role' => 'Officer', 'icon' => 'fa-flask',       'color' => '#0e7c6e', 'badge' => 'cbadge-officer'],
];

// Recent activity log
$activity = [
  ['icon' => 'fa-calendar-plus',    'dot' => 'dot-green',  'title' => 'Created event "Financial Literacy Seminar"',    'meta' => 'Business Management Club', 'time' => 'Today, 9:12 AM'],
  ['icon' => 'fa-circle-check',     'dot' => 'dot-green',  'title' => 'Approved "Campus Cleanup Drive"',               'meta' => 'Environmental Society',    'time' => 'Yesterday, 3:40 PM'],
  ['icon' => 'fa-bullhorn',         'dot' => 'dot-gold',   'title' => 'Posted announcement "Meeting Reminder"',        'meta' => 'All Clubs',                'time' => 'Mar 30, 11:00 AM'],
  ['icon' => 'fa-user-plus',        'dot' => 'dot-blue',   'title' => 'Approved membership for Neeru Abraham',         'meta' => 'CS Society',               'time' => 'Mar 28, 2:15 PM'],
  ['icon' => 'fa-xmark-circle',     'dot' => 'dot-red',    'title' => 'Rejected event "Unauthorized Off-Campus Trip"', 'meta' => 'Student Admin',            'time' => 'Mar 27, 10:05 AM'],
  ['icon' => 'fa-pen-to-square',    'dot' => 'dot-orange', 'title' => 'Updated profile information',                   'meta' => 'Account Settings',         'time' => 'Mar 25, 4:50 PM'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — My Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/profile.css" />
</head>
<body>
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon"><i class="fas fa-layer-group"></i></div>
      <div class="brand-text">
        <div class="brand-name">UNIFY</div>
        <div class="brand-tagline">Club Management System</div>
      </div>
    </div>
<nav class="sidebar-nav">
      <div class="nav-section-label">MAIN MENU</div>
      <a href="index.php?page=dashboard" class="nav-item">
        <i class="fas fa-house"></i><span>Dashboard</span>
      </a>
      <a href="index.php?page=members" class="nav-item">
        <i class="fas fa-users"></i><span>Members</span>
      </a>
      <a href="index.php?page=clubpage" class="nav-item">
        <i class="fas fa-building-columns"></i><span>Clubs</span>
      </a>
      <a href="index.php?page=events" class="nav-item">
        <i class="fas fa-calendar-days"></i><span>Events</span>
      </a>
      <a href="index.php?page=finance" class="nav-item">
        <i class="fas fa-coins"></i><span>Finances</span>
      </a>
      <div class="nav-section-label">REPORTS</div>
      <a href="index.php?page=reports" class="nav-item">
        <i class="fas fa-chart-bar"></i><span>Reports</span>
      </a>
    </nav>


    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <span class="profile-avatar-fallback"><?= strtoupper(substr($user['first_name'], 0, 1)) ?></span>
          <span class="profile-online-dot"></span>
        </div>
        <div class="profile-info">
          <span class="profile-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
          <span class="profile-role"><?= htmlspecialchars($user['role']) ?></span>
        </div>
        <a href="logout.php" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="index.php?page=profile" class="sidebar-settings-btn active" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">My Profile</span>
        <span class="topbar-date"><?= date('l, F j, Y') ?></span>
      </div>
      <div class="topbar-center"></div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="badge red">4</span>
        </button>
        <button class="icon-btn" title="Sync"><i class="fas fa-rotate"></i></button>
        <div class="topbar-profile">
          <div class="topbar-avatar"><?= strtoupper(substr($user['first_name'], 0, 1)) ?></div>
          <div class="topbar-profile-info">
            <span class="tp-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
            <span class="tp-role"><?= htmlspecialchars($user['role']) ?></span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </div>
      </div>
    </header>

    <!-- Content -->
    <div class="content">
      <div class="profile-body">

        <!-- LEFT PANEL -->
        <div class="profile-left">

          <!-- Avatar Card -->
          <div class="avatar-card">
            <div class="avatar-card-banner"></div>
            <div class="avatar-card-body">
              <div class="avatar-wrap">
                <div class="avatar-circle"><?= strtoupper(substr($user['first_name'], 0, 1)) ?></div>
                <button class="avatar-edit-btn" title="Change photo" onclick="showToast('Photo upload coming soon!')">
                  <i class="fas fa-camera"></i>
                </button>
              </div>
              <div class="avatar-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
              <div class="avatar-role"><?= htmlspecialchars($user['department']) ?></div>
              <div class="avatar-badges">
                <span class="role-badge admin"><?= htmlspecialchars($user['role']) ?></span>
                <span class="role-badge active"><?= htmlspecialchars($user['status']) ?></span>
              </div>
              <div class="avatar-stats">
                <div class="avatar-stat">
                  <div class="avatar-stat-num"><?= $user['events_managed'] ?></div>
                  <div class="avatar-stat-label">Events</div>
                </div>
                <div class="avatar-stat">
                  <div class="avatar-stat-num"><?= $user['clubs_joined'] ?></div>
                  <div class="avatar-stat-label">Clubs</div>
                </div>
                <div class="avatar-stat">
                  <div class="avatar-stat-num"><?= $user['announcements'] ?></div>
                  <div class="avatar-stat-label">Posts</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Settings Nav -->
          <div class="settings-nav-card">
            <div class="settings-nav-label">Account Settings</div>
            <button class="settings-nav-item active" onclick="switchTab('personal', this)">
              <i class="fas fa-user"></i> Personal Info
              <i class="fas fa-chevron-right nav-arrow"></i>
            </button>
            <button class="settings-nav-item" onclick="switchTab('password', this)">
              <i class="fas fa-lock"></i> Password & Security
              <i class="fas fa-chevron-right nav-arrow"></i>
            </button>
            <button class="settings-nav-item" onclick="switchTab('notifications', this)">
              <i class="fas fa-bell"></i> Notifications
              <i class="fas fa-chevron-right nav-arrow"></i>
            </button>
            <button class="settings-nav-item" onclick="switchTab('clubs', this)">
              <i class="fas fa-building-columns"></i> My Clubs
              <i class="fas fa-chevron-right nav-arrow"></i>
            </button>
            <button class="settings-nav-item" onclick="switchTab('activity', this)">
              <i class="fas fa-clock-rotate-left"></i> Activity Log
              <i class="fas fa-chevron-right nav-arrow"></i>
            </button>
            <div class="settings-nav-label" style="margin-top:6px;">Danger</div>
            <button class="settings-nav-item danger-item" onclick="switchTab('danger', this)">
              <i class="fas fa-triangle-exclamation"></i> Danger Zone
              <i class="fas fa-chevron-right nav-arrow"></i>
            </button>
          </div>

        </div>

        <!--RIGHT PANEL -->
        <div class="profile-right">

          <?php if ($success_msg): ?>
          <div style="background:#dcfce7;border:1.5px solid #86efac;border-radius:var(--radius-sm);padding:10px 14px;font-size:12.5px;font-weight:600;color:#166534;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
          </div>
          <?php endif; ?>

          <?php if ($error_msg): ?>
          <div style="background:var(--red-bg);border:1.5px solid #fca5a5;border-radius:var(--radius-sm);padding:10px 14px;font-size:12.5px;font-weight:600;color:var(--red-accent);display:flex;align-items:center;gap:8px;">
            <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error_msg) ?>
          </div>
          <?php endif; ?>

          <!-- TAB: Personal Info-->
          <div id="tab-personal" class="tab-panel">

            <form method="POST" action="">
              <input type="hidden" name="action" value="update_profile" />

              <!-- Basic Info -->
              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-user"></i> Basic Information
                    </div>
                    <div class="section-card-subtitle">Your name, bio, and contact details</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <div class="form-grid">
                    <div class="form-group">
                      <label class="form-label">First Name <span class="required">*</span></label>
                      <input type="text" name="first_name" class="form-input"
                             value="<?= htmlspecialchars($user['first_name']) ?>" required />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Last Name <span class="required">*</span></label>
                      <input type="text" name="last_name" class="form-input"
                             value="<?= htmlspecialchars($user['last_name']) ?>" required />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Username</label>
                      <div class="input-with-icon">
                        <i class="fas fa-at input-icon"></i>
                        <input type="text" name="username" class="form-input"
                               value="<?= htmlspecialchars($user['username']) ?>" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Student ID</label>
                      <input type="text" name="student_id" class="form-input readonly" readonly
                             value="<?= htmlspecialchars($user['student_id']) ?>" />
                      <span class="input-hint">Contact admin to change your Student ID.</span>
                    </div>
                    <div class="form-group span-2">
                      <label class="form-label">Bio</label>
                      <textarea name="bio" class="form-textarea" placeholder="Tell us a bit about yourself..."><?= htmlspecialchars($user['bio']) ?></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Contact -->
              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-envelope"></i> Contact Information
                    </div>
                    <div class="section-card-subtitle">Email and phone number</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <div class="form-grid">
                    <div class="form-group">
                      <label class="form-label">Email Address <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-input"
                               value="<?= htmlspecialchars($user['email']) ?>" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Phone Number</label>
                      <div class="input-with-icon">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="phone" class="form-input"
                               value="<?= htmlspecialchars($user['phone']) ?>" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Academic Info -->
              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-graduation-cap"></i> Academic Information
                    </div>
                    <div class="section-card-subtitle">Your department, course, and year level</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <div class="form-grid">
                    <div class="form-group span-2">
                      <label class="form-label">Department</label>
                      <select name="department" class="form-select">
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
                          <option value="<?= htmlspecialchars($d) ?>"
                            <?= $d === $user['department'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Course / Program</label>
                      <input type="text" name="course" class="form-input"
                             value="<?= htmlspecialchars($user['course']) ?>" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Year Level</label>
                      <select name="year_level" class="form-select">
                        <?php foreach (['1st Year','2nd Year','3rd Year','4th Year','5th Year','Graduate'] as $y): ?>
                          <option value="<?= $y ?>" <?= $y === $user['year_level'] ? 'selected' : '' ?>>
                            <?= $y ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="btn-row">
                <button type="button" class="btn-secondary" onclick="resetForm(this.form)">
                  <i class="fas fa-rotate-left"></i> Reset
                </button>
                <button type="submit" class="btn-primary">
                  <i class="fas fa-floppy-disk"></i> Save Changes
                </button>
              </div>

            </form>
          </div>

          <!--TAB: Password & Security -->
          <div id="tab-password" class="tab-panel" style="display:none;">

            <form method="POST" action="">
              <input type="hidden" name="action" value="update_password" />

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-lock"></i> Change Password
                    </div>
                    <div class="section-card-subtitle">Use a strong, unique password</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <div class="form-grid cols-1">
                    <div class="form-group">
                      <label class="form-label">Current Password <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="current_password" id="currentPass"
                               class="form-input" placeholder="Enter your current password" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">New Password <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="new_password" id="newPass"
                               class="form-input" placeholder="At least 8 characters"
                               oninput="checkStrength(this.value)" />
                      </div>
                      <div class="password-strength">
                        <div class="strength-bars">
                          <div class="strength-bar" id="bar1"></div>
                          <div class="strength-bar" id="bar2"></div>
                          <div class="strength-bar" id="bar3"></div>
                          <div class="strength-bar" id="bar4"></div>
                        </div>
                        <span class="strength-label" id="strengthLabel">Enter a password</span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Confirm New Password <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="fas fa-check-double input-icon"></i>
                        <input type="password" name="confirm_password" id="confirmPass"
                               class="form-input" placeholder="Re-enter new password"
                               oninput="checkMatch()" />
                      </div>
                      <span class="input-hint" id="matchHint"></span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Security Info -->
              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-shield-halved"></i> Security Overview
                    </div>
                  </div>
                </div>
                <div class="section-card-body">
                  <div class="form-grid">
                    <div class="form-group">
                      <label class="form-label">Last Password Change</label>
                      <input type="text" class="form-input readonly" readonly value="March 10, 2026" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Last Login</label>
                      <input type="text" class="form-input readonly" readonly value="Today, 8:45 AM" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Login IP</label>
                      <input type="text" class="form-input readonly" readonly value="192.168.1.104" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Account Created</label>
                      <input type="text" class="form-input readonly" readonly
                             value="<?= htmlspecialchars($user['joined']) ?>" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="btn-row">
                <button type="submit" class="btn-primary">
                  <i class="fas fa-lock"></i> Update Password
                </button>
              </div>
            </form>
          </div><!-- /tab-password -->

          <!--TAB: Notifications -->
          <div id="tab-notifications" class="tab-panel" style="display:none;">

            <form method="POST" action="">
              <input type="hidden" name="action" value="update_notifications" />

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-bell"></i> Email Notifications
                    </div>
                    <div class="section-card-subtitle">Choose what updates you receive by email</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <?php
                  $email_notifs = [
                    ['key' => 'notif_event_approved',   'label' => 'Event Approved',          'desc' => 'When your event submission gets approved',       'default' => true],
                    ['key' => 'notif_event_rejected',   'label' => 'Event Rejected',           'desc' => 'When your event submission is rejected',         'default' => true],
                    ['key' => 'notif_new_member',       'label' => 'New Member Application',   'desc' => 'When someone applies to your club',              'default' => true],
                    ['key' => 'notif_announcement',     'label' => 'New Announcements',        'desc' => 'When a new announcement is posted',              'default' => false],
                    ['key' => 'notif_rsvp',             'label' => 'RSVP Updates',             'desc' => 'When attendees confirm or cancel RSVP',          'default' => false],
                    ['key' => 'notif_weekly_digest',    'label' => 'Weekly Summary',           'desc' => 'A weekly digest of club activity',               'default' => true],
                  ];
                  foreach ($email_notifs as $n): ?>
                    <div class="toggle-row">
                      <div class="toggle-row-info">
                        <span class="toggle-label"><?= $n['label'] ?></span>
                        <span class="toggle-desc"><?= $n['desc'] ?></span>
                      </div>
                      <label class="toggle">
                        <input type="checkbox" name="<?= $n['key'] ?>"
                               <?= $n['default'] ? 'checked' : '' ?> />
                        <span class="toggle-slider"></span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-mobile-screen"></i> In-App Notifications
                    </div>
                    <div class="section-card-subtitle">Alerts shown inside UNIFY</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <?php
                  $app_notifs = [
                    ['key' => 'inapp_mentions',   'label' => 'Mentions & Tags',    'desc' => 'When someone tags you in a post or comment', 'default' => true],
                    ['key' => 'inapp_reminders',  'label' => 'Event Reminders',    'desc' => '1 hour before an event you\'re attending',  'default' => true],
                    ['key' => 'inapp_approvals',  'label' => 'Pending Approvals',  'desc' => 'Remind me of unreviewed approval requests',  'default' => true],
                  ];
                  foreach ($app_notifs as $n): ?>
                    <div class="toggle-row">
                      <div class="toggle-row-info">
                        <span class="toggle-label"><?= $n['label'] ?></span>
                        <span class="toggle-desc"><?= $n['desc'] ?></span>
                      </div>
                      <label class="toggle">
                        <input type="checkbox" name="<?= $n['key'] ?>"
                               <?= $n['default'] ? 'checked' : '' ?> />
                        <span class="toggle-slider"></span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="btn-row">
                <button type="submit" class="btn-primary">
                  <i class="fas fa-floppy-disk"></i> Save Preferences
                </button>
              </div>
            </form>
          </div>

          <!-- TAB: My Clubs -->
          <div id="tab-clubs" class="tab-panel" style="display:none;">
            <div class="section-card">
              <div class="section-card-header">
                <div>
                  <div class="section-card-title">
                    <i class="fas fa-building-columns"></i> My Clubs
                  </div>
                  <div class="section-card-subtitle">Clubs you are a member or officer of</div>
                </div>
              </div>
              <div class="section-card-body">
                <div class="clubs-grid">
                  <?php foreach ($clubs as $club): ?>
                  <div class="club-chip">
                    <div class="club-chip-icon" style="background:<?= $club['color'] ?>;">
                      <i class="fas <?= $club['icon'] ?>"></i>
                    </div>
                    <div class="club-chip-info">
                      <div class="club-chip-name"><?= htmlspecialchars($club['name']) ?></div>
                      <div class="club-chip-role"><?= htmlspecialchars($club['role']) ?></div>
                    </div>
                    <span class="club-chip-badge <?= $club['badge'] ?>"><?= htmlspecialchars($club['role']) ?></span>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div><!-- /tab-clubs -->

          <!-- TAB: Activity Log -->
          <div id="tab-activity" class="tab-panel" style="display:none;">
            <div class="section-card">
              <div class="section-card-header">
                <div>
                  <div class="section-card-title">
                    <i class="fas fa-clock-rotate-left"></i> Recent Activity
                  </div>
                  <div class="section-card-subtitle">Your last 6 actions in UNIFY</div>
                </div>
              </div>
              <div class="section-card-body">
                <div class="activity-list">
                  <?php foreach ($activity as $a): ?>
                  <div class="activity-item">
                    <div class="activity-dot-col">
                      <div class="activity-dot <?= $a['dot'] ?>">
                        <i class="fas <?= $a['icon'] ?>"></i>
                      </div>
                    </div>
                    <div class="activity-info">
                      <div class="activity-title"><?= htmlspecialchars($a['title']) ?></div>
                      <div class="activity-meta">
                        <span><i class="fas fa-building-columns" style="font-size:9px;"></i> <?= htmlspecialchars($a['meta']) ?></span>
                      </div>
                    </div>
                    <div class="activity-time"><?= htmlspecialchars($a['time']) ?></div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div><!-- /tab-activity -->

          <!-- TAB: Danger Zone -->
          <div id="tab-danger" class="tab-panel" style="display:none;">
            <div class="danger-zone">
              <div class="danger-zone-header">
                <div class="danger-zone-title">
                  <i class="fas fa-triangle-exclamation"></i> Danger Zone
                </div>
              </div>
              <div class="danger-zone-body">

                <div class="danger-action">
                  <div class="danger-action-info">
                    <div class="danger-action-title">Export My Data</div>
                    <div class="danger-action-desc">Download a copy of all your data in JSON format.</div>
                  </div>
                  <button class="btn-gold" onclick="showToast('Data export started. You will receive an email shortly.')">
                    <i class="fas fa-download"></i> Export
                  </button>
                </div>

                <div class="danger-action">
                  <div class="danger-action-info">
                    <div class="danger-action-title">Deactivate Account</div>
                    <div class="danger-action-desc">Temporarily disable your account. You can reactivate it later.</div>
                  </div>
                  <button class="btn-danger" onclick="confirmDeactivate()">
                    <i class="fas fa-user-slash"></i> Deactivate
                  </button>
                </div>

                <div class="danger-action">
                  <div class="danger-action-info">
                    <div class="danger-action-title">Delete Account</div>
                    <div class="danger-action-desc">Permanently delete your account and all associated data. <strong>This cannot be undone.</strong></div>
                  </div>
                  <button class="btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash-can"></i> Delete
                  </button>
                </div>

              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>

<!-- Toast -->
<div class="toast" id="toast">
  <i class="fas fa-circle-check"></i>
  <span id="toast-msg">Changes saved!</span>
</div>

<script>
/* Tab Switcher */
function switchTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
  document.getElementById('tab-' + name).style.display = 'flex';
  document.getElementById('tab-' + name).style.flexDirection = 'column';
  document.getElementById('tab-' + name).style.gap = '0';

  document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');


  document.querySelector('.profile-right').scrollTop = 0;
}

/* Password Strength */
function checkStrength(val) {
  const bars   = [1,2,3,4].map(i => document.getElementById('bar' + i));
  const label  = document.getElementById('strengthLabel');
  const levels = ['weak','weak','medium','strong'];
  let score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  bars.forEach((b, i) => {
    b.className = 'strength-bar';
    if (i < score) b.classList.add(levels[score - 1]);
  });

  const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
  const cls    = ['', 'weak', 'medium', 'medium', 'strong'];
  label.textContent = val.length ? labels[score] : 'Enter a password';
  label.className   = 'strength-label ' + (val.length ? cls[score] : '');
}

/* Password Match */
function checkMatch() {
  const np = document.getElementById('newPass').value;
  const cp = document.getElementById('confirmPass').value;
  const hint = document.getElementById('matchHint');
  if (!cp) { hint.textContent = ''; return; }
  if (np === cp) {
    hint.textContent = '✓ Passwords match';
    hint.className   = 'input-hint success';
  } else {
    hint.textContent = '✗ Passwords do not match';
    hint.className   = 'input-hint error';
  }
}

/* Reset Form */
function resetForm(form) {
  form.reset();
  showToast('Form reset to original values.');
}

/* Toast */
function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg || 'Changes saved!';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

<?php if ($success_msg): ?>
window.addEventListener('DOMContentLoaded', () => showToast(<?= json_encode($success_msg) ?>));
<?php endif; ?>

/* Danger Zone Confirms */
function confirmDeactivate() {
  if (confirm('Are you sure you want to deactivate your account? You can reactivate it by logging in again.')) {
    showToast('Account deactivation requested. Redirecting...');
    // window.location.href = 'deactivate.php';
  }
}

function confirmDelete() {
  const confirmed = prompt('Type DELETE to permanently remove your account:');
  if (confirmed === 'DELETE') {
    showToast('Account deletion scheduled. You will receive a confirmation email.');
    // window.location.href = 'delete-account.php';
  }
}

document.getElementById('tab-personal').style.display = 'flex';
document.getElementById('tab-personal').style.flexDirection = 'column';
document.getElementById('tab-personal').style.gap = '0';
</script>
</body>
</html>