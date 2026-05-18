<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/profile_controller.php'; ?>
<?php
// ── Fallback defaults ──────────────────────────────────────────────────────
$user = $user ?? [];
$clubName = $clubName ?? 'No Club';
$clubInitial = $clubInitial ?? strtoupper(substr($clubName, 0, 1));
$officerRole = $officerRole ?? ($user['role'] ?? 'member');
$officerClub = $officerClub ?? [];
$userName = $userName ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$userInit = $userInit ?? strtoupper(substr($user['first_name'] ?? 'U', 0, 1));
$events_count = $events_count ?? 0;
$clubs_count = $clubs_count ?? 0;
$announcements_count = $announcements_count ?? 0;
$clubs = $clubs ?? [];
$success_msg = $success_msg ?? '';
$error_msg = $error_msg ?? '';

if (!function_exists('clubColor')) {
  function clubColor(string $category = ''): string
  {
    $map = [
      'academic' => '#2a8a4a',
      'arts' => '#9333ea',
      'sports' => '#d4620a',
      'technology' => '#2563eb',
      'culture' => '#d4a017',
      'community' => '#0891b2',
    ];
    return $map[strtolower($category)] ?? '#3a6a45';
  }
}

if (!function_exists('clubIcon')) {
  function clubIcon(string $category = ''): string
  {
    $map = [
      'academic' => 'fa-book',
      'arts' => 'fa-palette',
      'sports' => 'fa-trophy',
      'technology' => 'fa-microchip',
      'culture' => 'fa-globe',
      'community' => 'fa-hands-holding-circle',
    ];
    return $map[strtolower($category)] ?? 'fa-building-columns';
  }
}

if (!function_exists('roleBadge')) {
  function roleBadge(string $role = ''): string
  {
    return match (strtolower($role)) {
      'admin', 'president' => 'cbadge-admin',
      'officer' => 'cbadge-officer',
      default => 'cbadge-member',
    };
  }
}
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
  <link rel="stylesheet" href="/unify/assets/css/profile.css" />
  <link rel="stylesheet" href="/unify/assets/css/setup_2fa.css" />
</head>

<body>
  <div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="/unify/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
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
          </button>
          <a href="index.php?page=profile" class="topbar-profile" style="text-decoration:none;cursor:pointer;">
            <div class="topbar-avatar" id="of-topbar-avatar">
              <?php if ($avatar_url): ?>
                <img src="<?= $avatar_url ?>" alt="Avatar"
                  style="width:100%;height:100%;border-radius:50%;object-fit:cover;object-position:center;display:block;" />
              <?php else: ?>
                <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
              <?php endif; ?>
            </div>
            <div class="topbar-profile-info">
              <span class="tp-name">
                <?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>
              </span>
              <span class="tp-role"><?= htmlspecialchars(ucfirst($user['role'] ?? 'User')) ?></span>
            </div>
            <i class="fas fa-chevron-down tp-caret"></i>
          </a>
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
                  <div class="avatar-circle" id="of-avatar-circle">
                    <?php if ($avatar_url): ?>
                      <img src="<?= $avatar_url ?>" alt="Profile" class="avatar-photo" id="of-avatar-img" />
                    <?php else: ?>
                      <span id="of-avatar-initials">
                        <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <!-- Hidden file input -->
                  <input type="file" id="of-avatar-input" accept="image/jpeg,image/png,image/gif,image/webp"
                    style="display:none;" onchange="uploadAvatar(this)" />
                  <button class="avatar-edit-btn" title="Change photo"
                    onclick="document.getElementById('of-avatar-input').click()">
                    <i class="fas fa-camera"></i>
                  </button>
                </div>
                <div class="avatar-name">
                  <?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>
                </div>
                <div class="avatar-role">
                  <?= htmlspecialchars($user['department'] ?? 'No department set') ?>
                </div>
                <div class="avatar-badges">
                  <span class="role-badge admin">
                    <?= htmlspecialchars(ucfirst($user['role'] ?? 'User')) ?>
                  </span>
                  <span class="role-badge active">Active</span>
                </div>
                <div class="avatar-stats">
                  <div class="avatar-stat">
                    <div class="avatar-stat-num"><?= (int) $events_count ?></div>
                    <div class="avatar-stat-label">Events</div>
                  </div>
                  <div class="avatar-stat">
                    <div class="avatar-stat-num"><?= (int) $clubs_count ?></div>
                    <div class="avatar-stat-label">Clubs</div>
                  </div>
                  <div class="avatar-stat">
                    <div class="avatar-stat-num"><?= (int) $announcements_count ?></div>
                    <div class="avatar-stat-label">Posts</div>
                  </div>
                </div>

                <!-- QR Button inside avatar card -->
                <button onclick="OfficerProfile.openQrModal()" style="margin-top:14px;width:100%;display:flex;align-items:center;
                               justify-content:center;gap:8px;padding:10px 16px;
                               border-radius:11px;border:none;
                               background:linear-gradient(135deg,#0d2b1a 0%,#1a4d2e 100%);
                               color:#fff;font-family:inherit;font-size:12.5px;font-weight:700;
                               cursor:pointer;transition:all 0.18s;
                               box-shadow:0 3px 12px rgba(13,43,26,0.25);"
                  onmouseover="this.style.background='linear-gradient(135deg,#1a4d2e 0%,#2a7a48 100%)'"
                  onmouseout="this.style.background='linear-gradient(135deg,#0d2b1a 0%,#1a4d2e 100%)'">
                  <i class="fas fa-qrcode" style="color:#a8e6c0;"></i> My Attendance QR
                </button>

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
                <i class="fas fa-lock"></i> Password &amp; Security
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
              <div class="settings-nav-label" style="margin-top:6px;">Danger</div>
              <button class="settings-nav-item danger-item" onclick="switchTab('danger', this)">
                <i class="fas fa-triangle-exclamation"></i> Danger Zone
                <i class="fas fa-chevron-right nav-arrow"></i>
              </button>
            </div>

          </div><!-- /profile-left -->

          <!-- RIGHT PANEL -->
          <div class="profile-right">

            <?php if (!empty($success_msg)): ?>
              <div class="alert-success">
                <i class="fas fa-circle-check"></i>
                <?= htmlspecialchars($success_msg) ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
              <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i>
                <?= htmlspecialchars($error_msg) ?>
              </div>
            <?php endif; ?>

            <!-- ── TAB: Personal Info ─────────────────────────────────────── -->
            <div id="tab-personal" class="tab-panel">

              <form method="POST" action="index.php?page=profile">
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
                          value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required />
                      </div>
                      <div class="form-group">
                        <label class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" class="form-input"
                          value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required />
                      </div>
                      <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-with-icon">
                          <i class="fas fa-at input-icon"></i>
                          <input type="text" name="username" class="form-input"
                            value="<?= htmlspecialchars($user['username'] ?? '') ?>" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="form-label">Student ID</label>
                        <input type="text" name="student_id" class="form-input readonly" readonly
                          value="<?= htmlspecialchars($user['student_id'] ?? '') ?>" />
                        <span class="input-hint">Contact admin to change your Student ID.</span>
                      </div>
                      <div class="form-group span-2">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-textarea"
                          placeholder="Tell us a bit about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
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
                            value="<?= htmlspecialchars($user['email'] ?? '') ?>" required />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <div class="input-with-icon">
                          <i class="fas fa-phone input-icon"></i>
                          <input type="tel" name="phone" class="form-input"
                            value="<?= htmlspecialchars($user['phone'] ?? '') ?>" />
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
                            <option value="<?= htmlspecialchars($d) ?>" <?= ($user['department'] ?? '') === $d ? 'selected' : '' ?>>
                              <?= htmlspecialchars($d) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label class="form-label">Course / Program</label>
                        <input type="text" name="course" class="form-input"
                          value="<?= htmlspecialchars($user['course'] ?? '') ?>" />
                      </div>
                      <div class="form-group">
                        <label class="form-label">Year Level</label>
                        <select name="year_level" class="form-select">
                          <?php foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Graduate'] as $y): ?>
                            <option value="<?= $y ?>" <?= ($user['year_level'] ?? '') === $y ? 'selected' : '' ?>>
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
            </div><!-- /tab-personal -->

            <!-- ── TAB: Password & Security ──────────────────────────────── -->
            <div id="tab-password" class="tab-panel" style="display:none;">

              <form method="POST" action="index.php?page=profile">
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
                          <input type="password" name="current_password" id="currentPass" class="form-input"
                            placeholder="Enter your current password" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="form-label">New Password <span class="required">*</span></label>
                        <div class="input-with-icon">
                          <i class="fas fa-key input-icon"></i>
                          <input type="password" name="new_password" id="newPass" class="form-input"
                            placeholder="At least 8 characters" oninput="checkStrength(this.value)" />
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
                          <input type="password" name="confirm_password" id="confirmPass" class="form-input"
                            placeholder="Re-enter new password" oninput="checkMatch()" />
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
                        <label class="form-label">Account Created</label>
                        <input type="text" class="form-input readonly" readonly
                          value="<?= !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A' ?>" />
                      </div>
                      <div class="form-group">
                        <label class="form-label">Login IP</label>
                        <input type="text" class="form-input readonly" readonly
                          value="<?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A') ?>" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="btn-row" style="margin-bottom:14px;">
                  <button type="submit" class="btn-primary">
                    <i class="fas fa-lock"></i> Update Password
                  </button>
                </div>
              </form>

              <!-- ── Two-Factor Authentication Card ────────────── -->
              <div class="section-card twofa-card" id="twoFaCard" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div style="display:flex;align-items:center;gap:14px;flex:1;">
                    <div class="twofa-shield-icon">
                      <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                      <div class="section-card-title">
                        <i class="fas fa-mobile-screen-button"></i> Two-Factor Authentication
                      </div>
                      <div class="section-card-subtitle">Require a phone code on every login</div>
                    </div>
                  </div>
                  <span class="twofa-badge <?= $twoFaEnabled ? 'twofa-badge--on' : 'twofa-badge--off' ?>"
                    id="twofaBadge">
                    <?= $twoFaEnabled ? '<i class="fas fa-check-circle"></i> Enabled' : '<i class="fas fa-circle-xmark"></i> Disabled' ?>
                  </span>
                </div>

                <div class="section-card-body">

                  <?php if (!$twoFaEnabled): ?>
                    <!-- ── Not enabled ── -->
                    <div id="twofaOffState">
                      <p style="font-size:.82rem;color:#666;margin-bottom:16px;line-height:1.6;">
                        Protect your account with an extra verification step. After enabling,
                        you'll need your phone to log in — even if someone has your password.
                      </p>
                      <button class="btn-primary twofa-btn-enable" id="btnGenerate" onclick="twofa.generate()">
                        <i class="fas fa-shield-halved"></i> Enable 2FA
                      </button>
                    </div>

                    <!-- QR setup flow (hidden until Generate clicked) -->
                    <div id="qrStep" style="display:none;margin-top:20px;padding-top:18px;border-top:1px solid #f0f0f0;">
                      <ol class="twofa-steps">
                        <li>Download <strong>Google Authenticator</strong> or <strong>Authy</strong> on your phone.</li>
                        <li>Tap <strong>+</strong> → <em>Scan a QR code</em>.</li>
                        <li>Scan the code below, then enter the 6-digit code to confirm.</li>
                      </ol>

                      <div class="twofa-qr-wrap">
                        <img id="qrImage" src="" alt="QR Code" />
                      </div>

                      <details class="twofa-manual">
                        <summary><i class="fas fa-keyboard"></i> Can't scan? Enter key manually</summary>
                        <code id="manualSecret"></code>
                      </details>

                      <div class="twofa-confirm-row">
                        <label class="form-label">Enter the 6-digit code from your app to confirm:</label>
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                          <input type="text" id="confirmOtp" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                            placeholder="000000" autocomplete="off" class="form-input twofa-otp-input" />
                          <button class="btn-primary" onclick="twofa.enable()">
                            <i class="fas fa-check"></i> Confirm &amp; Enable
                          </button>
                        </div>
                        <p class="twofa-otp-err" id="otpErr" style="display:none;"></p>
                      </div>
                    </div>

                  <?php else: ?>
                    <!-- ── Already enabled ── -->
                    <div id="twofaOnState">
                      <div class="twofa-enabled-banner">
                        <i class="fas fa-circle-check"></i>
                        <div>
                          <strong>2FA is active on your account.</strong>
                          <span>You'll be asked for a code from your authenticator app each time you log in.</span>
                        </div>
                      </div>
                      <button class="twofa-btn-disable" id="btnDisable" onclick="twofa.disable()">
                        <i class="fas fa-shield-xmark"></i> Disable 2FA
                      </button>
                    </div>
                  <?php endif; ?>

                </div><!-- /section-card-body -->
              </div><!-- /twofa-card -->

            </div><!-- /tab-password -->

            <!-- ── TAB: Notifications ────────────────────────────────────── -->
            <div id="tab-notifications" class="tab-panel" style="display:none;">

              <form method="POST" action="index.php?page=profile">
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
                      ['key' => 'notif_event_approved', 'label' => 'Event Approved', 'desc' => 'When your event submission gets approved', 'default' => true],
                      ['key' => 'notif_event_rejected', 'label' => 'Event Rejected', 'desc' => 'When your event submission is rejected', 'default' => true],
                      ['key' => 'notif_new_member', 'label' => 'New Member Application', 'desc' => 'When someone applies to your club', 'default' => true],
                      ['key' => 'notif_announcement', 'label' => 'New Announcements', 'desc' => 'When a new announcement is posted', 'default' => false],
                      ['key' => 'notif_rsvp', 'label' => 'RSVP Updates', 'desc' => 'When attendees confirm or cancel RSVP', 'default' => false],
                      ['key' => 'notif_weekly_digest', 'label' => 'Weekly Summary', 'desc' => 'A weekly digest of club activity', 'default' => true],
                    ];
                    foreach ($email_notifs as $n): ?>
                      <div class="toggle-row">
                        <div class="toggle-row-info">
                          <span class="toggle-label"><?= htmlspecialchars($n['label']) ?></span>
                          <span class="toggle-desc"><?= htmlspecialchars($n['desc']) ?></span>
                        </div>
                        <label class="toggle">
                          <input type="checkbox" name="<?= htmlspecialchars($n['key']) ?>" <?= $n['default'] ? 'checked' : '' ?> />
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
                      ['key' => 'inapp_mentions', 'label' => 'Mentions &amp; Tags', 'desc' => 'When someone tags you in a post or comment', 'default' => true],
                      ['key' => 'inapp_reminders', 'label' => 'Event Reminders', 'desc' => '1 hour before an event you\'re attending', 'default' => true],
                      ['key' => 'inapp_approvals', 'label' => 'Pending Approvals', 'desc' => 'Remind me of unreviewed approval requests', 'default' => true],
                    ];
                    foreach ($app_notifs as $n): ?>
                      <div class="toggle-row">
                        <div class="toggle-row-info">
                          <span class="toggle-label"><?= $n['label'] ?></span>
                          <span class="toggle-desc"><?= htmlspecialchars($n['desc']) ?></span>
                        </div>
                        <label class="toggle">
                          <input type="checkbox" name="<?= htmlspecialchars($n['key']) ?>" <?= $n['default'] ? 'checked' : '' ?> />
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
            </div><!-- /tab-notifications -->

            <!-- ── TAB: My Clubs ─────────────────────────────────────────── -->
            <div id="tab-clubs" class="tab-panel" style="display:none;">
              <div class="section-card">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title">
                      <i class="fas fa-building-columns"></i> My Clubs
                    </div>
                    <div class="section-card-subtitle">Clubs you are currently active in</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <?php if (empty($clubs)): ?>
                    <p style="color:#888;font-size:13px;">You are not a member of any active clubs yet.</p>
                  <?php else: ?>
                    <div class="clubs-grid">
                      <?php foreach ($clubs as $club): ?>
                        <div class="club-chip">
                          <div class="club-chip-icon" style="background:<?= clubColor($club['category'] ?? '') ?>;">
                            <i class="fas <?= clubIcon($club['category'] ?? '') ?>"></i>
                          </div>
                          <div class="club-chip-info">
                            <div class="club-chip-name"><?= htmlspecialchars($club['name'] ?? '') ?></div>
                            <div class="club-chip-role">
                              <?= htmlspecialchars(ucfirst($club['role'] ?? '')) ?>
                            </div>
                          </div>
                          <span class="club-chip-badge <?= roleBadge($club['role'] ?? '') ?>">
                            <?= htmlspecialchars(ucfirst($club['role'] ?? '')) ?>
                          </span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div><!-- /tab-clubs -->

            <!-- ── TAB: Danger Zone ──────────────────────────────────────── -->
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
                    <button class="btn-gold" onclick="showToast('Data export coming soon.')">
                      <i class="fas fa-download"></i> Export
                    </button>
                  </div>

                  <div class="danger-action">
                    <div class="danger-action-info">
                      <div class="danger-action-title">Deactivate Account</div>
                      <div class="danger-action-desc">
                        Temporarily disable your account. You can reactivate it later.
                      </div>
                    </div>
                    <button class="btn-danger" onclick="confirmDeactivate()">
                      <i class="fas fa-user-slash"></i> Deactivate
                    </button>
                  </div>

                  <div class="danger-action">
                    <div class="danger-action-info">
                      <div class="danger-action-title">Delete Account</div>
                      <div class="danger-action-desc">
                        Permanently delete your account and all associated data.
                        <strong>This cannot be undone.</strong>
                      </div>
                    </div>
                    <button class="btn-danger" onclick="confirmDelete()">
                      <i class="fas fa-trash-can"></i> Delete
                    </button>
                  </div>

                </div>
              </div>
            </div><!-- /tab-danger -->

          </div><!-- /profile-right -->
        </div><!-- /profile-body -->
      </div><!-- /content -->
    </main>
  </div><!-- /app -->

  <!-- Toast -->
  <div class="toast" id="toast">
    <i class="fas fa-circle-check"></i>
    <span id="toast-msg">Changes saved!</span>
  </div>


  <!-- ── Attendance QR Modal ──────────────────────────────────── -->
  <div class="modal-overlay" id="modal-officer-qr" onclick="if(event.target===this)OfficerProfile.closeQrModal()" style="position:fixed;inset:0;background:rgba(10,40,24,0.5);
            backdrop-filter:blur(5px);display:flex;align-items:center;
            justify-content:center;z-index:1000;opacity:0;pointer-events:none;
            transition:opacity 0.22s ease;">
    <div style="background:#fff;border-radius:20px;border:1px solid #d0e8d8;
              box-shadow:0 24px 64px rgba(13,51,32,0.28);width:380px;
              max-width:calc(100vw - 32px);max-height:90vh;
              display:flex;flex-direction:column;
              transform:translateY(16px) scale(0.97);
              transition:transform 0.22s ease;" id="officer-qr-modal-box">

      <!-- Header -->
      <div style="display:flex;align-items:center;justify-content:space-between;
                padding:18px 22px 14px;border-bottom:1px solid #d0e8d8;">
        <div style="font-size:15px;font-weight:800;color:#0d3320;
                  display:flex;align-items:center;gap:8px;">
          <i class="fas fa-qrcode" style="color:#2a8a4a;"></i> My Attendance QR Code
        </div>
        <button onclick="OfficerProfile.closeQrModal()" style="width:32px;height:32px;border-radius:9px;border:1.5px solid #d0e8d8;
                     background:transparent;color:#3a6a45;font-size:15px;cursor:pointer;
                     display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-xmark"></i>
        </button>
      </div>

      <!-- Body -->
      <div style="padding:20px 24px 28px;display:flex;flex-direction:column;
                align-items:center;text-align:center;">
        <?php if ($student_id_raw): ?>
          <p style="font-size:13px;color:#7aaa85;margin-bottom:20px;line-height:1.6;">
            Present this QR code to your officer during club events to mark your attendance.
          </p>
          <div style="background:#fff;border:3px solid #d0e8d8;border-radius:16px;
                    padding:12px;margin-bottom:18px;
                    box-shadow:0 4px 20px rgba(13,43,26,0.10);display:inline-block;">
            <div id="officerQrCanvas"></div>
          </div>
          <div style="font-size:20px;font-weight:800;letter-spacing:2px;
                    color:#0d2b1a;margin-bottom:4px;">
            <?= htmlspecialchars($student_id_raw) ?>
          </div>
          <div style="font-size:13px;color:#7aaa85;margin-bottom:20px;">
            <?= htmlspecialchars($userName) ?>
          </div>
          <button onclick="
          var canvas = document.getElementById('officerQrCanvas').querySelector('canvas');
          if(canvas){
            var a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = 'attendance-qr-<?= htmlspecialchars($student_id_raw) ?>.png';
            a.click();
          }" style="display:inline-flex;align-items:center;gap:8px;
                 background:linear-gradient(135deg,#0d2b1a 0%,#2a7a48 100%);
                 color:#fff;border:none;border-radius:10px;padding:11px 28px;
                 font-size:13px;font-weight:700;cursor:pointer;
                 box-shadow:0 2px 10px rgba(13,43,26,0.2);transition:all 0.15s;">
            <i class="fas fa-download"></i> Download QR Code
          </button>
        <?php else: ?>
          <div style="padding:16px 8px;text-align:center;">
            <i class="fas fa-circle-exclamation"
              style="font-size:32px;color:#d4a017;margin-bottom:12px;display:block;"></i>
            <strong style="font-size:13px;color:#0d3320;">No Student ID on record</strong>
            <p style="margin-top:10px;font-size:12px;color:#7aaa85;line-height:1.7;">
              You need to set your Student ID before a QR code can be generated.
              Go to <strong>Personal Info</strong> and fill in your Student ID field.
            </p>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- QR library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    // Generate QR on page load (canvas stays hidden until modal opens)
    document.addEventListener('DOMContentLoaded', function () {
      var studentId = <?= json_encode($student_id_raw) ?>;
      if (studentId) {
        new QRCode(document.getElementById('officerQrCanvas'), {
          text: studentId,
          width: 220,
          height: 220,
          colorDark: '#0d2b1a',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.H,
        });
      }
    });

    // Modal open/close
    const OfficerProfile = {
      openQrModal() {
        const overlay = document.getElementById('modal-officer-qr');
        const box = document.getElementById('officer-qr-modal-box');
        overlay.style.opacity = '1';
        overlay.style.pointerEvents = 'all';
        box.style.transform = 'translateY(0) scale(1)';
      },
      closeQrModal() {
        const overlay = document.getElementById('modal-officer-qr');
        const box = document.getElementById('officer-qr-modal-box');
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';
        box.style.transform = 'translateY(16px) scale(0.97)';
      },
    };
  </script>

  <!-- ── Avatar Upload ──────────────────────────────────────── -->
  <script>
    async function uploadAvatar(input) {
      if (!input.files || !input.files[0]) return;

      const file = input.files[0];
      if (file.size > 3 * 1024 * 1024) {
        showToast('Image must be under 3 MB.');
        input.value = '';
        return;
      }

      // Instant local preview
      const reader = new FileReader();
      reader.onload = e => {
        const circle = document.getElementById('of-avatar-circle');
        if (circle) {
          circle.innerHTML =
            `<img src="${e.target.result}" alt="Preview" class="avatar-photo" id="of-avatar-img" />` +
            `<input type="file" id="of-avatar-input" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="uploadAvatar(this)" />`;
        }
      };
      reader.readAsDataURL(file);

      showToast('Uploading…');

      const formData = new FormData();
      formData.append('avatar', file);

      try {
        const res  = await fetch('index.php?page=upload_avatar', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
          showToast('Profile picture updated!');

          // Update sidebar avatar
          const sidebarEl = document.getElementById('of-sidebar-avatar');
          if (sidebarEl) {
            const img = document.createElement('img');
            img.src       = data.url;
            img.alt       = 'Avatar';
            img.className = 'profile-avatar-img';
            img.id        = 'of-sidebar-avatar';
            sidebarEl.replaceWith(img);
          }

          // Update topbar circle
          const topbar = document.getElementById('of-topbar-avatar');
          if (topbar) {
            topbar.innerHTML = `<img src="${data.url}" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" />`;
          }
        } else {
          showToast(data.message || 'Upload failed.');
          setTimeout(() => window.location.reload(), 1500);
        }
      } catch (err) {
        showToast('Network error: ' + err.message);
        setTimeout(() => window.location.reload(), 1500);
      } finally {
        input.value = '';
      }
    }
  </script>

  <script src="/unify/assets/javascripts/setup_2fa.js"></script>

  <script>
    // ── Tab switching ────────────────────────────────────────────────────────
    function switchTab(name, btn) {
      document.querySelectorAll('.tab-panel').forEach(p => {
        p.style.display = 'none';
      });
      const panel = document.getElementById('tab-' + name);
      if (panel) {
        panel.style.display = 'flex';
        panel.style.flexDirection = 'column';
        panel.style.gap = '0';
      }
      document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelector('.profile-right').scrollTop = 0;
    }

    // ── Password strength ────────────────────────────────────────────────────
    function checkStrength(val) {
      const bars = [1, 2, 3, 4].map(i => document.getElementById('bar' + i));
      const label = document.getElementById('strengthLabel');
      let score = 0;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      const levelClass = ['', 'weak', 'weak', 'medium', 'strong'];
      bars.forEach((b, i) => {
        b.className = 'strength-bar';
        if (i < score) b.classList.add(levelClass[score]);
      });

      const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
      const cls = ['', 'weak', 'medium', 'medium', 'strong'];
      label.textContent = val.length ? labels[score] : 'Enter a password';
      label.className = 'strength-label ' + (val.length ? cls[score] : '');
    }

    // ── Password match ───────────────────────────────────────────────────────
    function checkMatch() {
      const np = document.getElementById('newPass').value;
      const cp = document.getElementById('confirmPass').value;
      const hint = document.getElementById('matchHint');
      if (!cp) { hint.textContent = ''; return; }
      if (np === cp) {
        hint.textContent = '✓ Passwords match';
        hint.className = 'input-hint success';
      } else {
        hint.textContent = '✗ Passwords do not match';
        hint.className = 'input-hint error';
      }
    }

    // ── Reset form ───────────────────────────────────────────────────────────
    function resetForm(form) {
      form.reset();
      showToast('Form reset to original values.');
    }

    // ── Toast ────────────────────────────────────────────────────────────────
    function showToast(msg) {
      const t = document.getElementById('toast');
      document.getElementById('toast-msg').textContent = msg || 'Changes saved!';
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 3000);
    }

    // Auto-show toast on success from PHP
    <?php if (!empty($success_msg)): ?>
      window.addEventListener('DOMContentLoaded', () => showToast(<?= json_encode($success_msg) ?>));
    <?php endif; ?>

    // ── Danger zone ──────────────────────────────────────────────────────────
    function confirmDeactivate() {
      if (confirm('Are you sure you want to deactivate your account?')) {
        showToast('Account deactivation requested.');
      }
    }

    function confirmDelete() {
      const confirmed = prompt('Type DELETE to permanently remove your account:');
      if (confirmed === 'DELETE') {
        showToast('Account deletion scheduled. You will receive a confirmation email.');
      }
    }

    // ── Init: show personal tab on load ─────────────────────────────────────
    (function () {
      const panel = document.getElementById('tab-personal');
      if (panel) {
        panel.style.display = 'flex';
        panel.style.flexDirection = 'column';
        panel.style.gap = '0';
      }
    })();
  </script>
</body>

</html>