<?php require_once __DIR__ . '/../../app/controllers/adminprofile_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — My Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/public/assets/css/adminprofile.css" />
  <link rel="stylesheet" href="/public/assets/css/transitions.css" />
</head>
<body>
<main class="main" style="position:relative;">

  

  <!-- Content -->
  <div class="content">

  <!-- ── Sidebar ── -->
 <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
  <aside class="sidebar" id="mainSidebar">
<div class="sidebar-brand">
  <img src="/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
  <div class="brand-text">
    <div class="brand-name">UNIFY</div>
    <div class="brand-tagline">Club Management System</div>
  </div>
</div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">MAIN MENU</div>
      <a href="index.php?page=dashboard" class="nav-item "><i class="fas fa-house"></i><span>Dashboard</span></a>
      <a href="index.php?page=members"   class="nav-item"><i class="fas fa-users"></i><span>Members</span></a>
      <a href="index.php?page=clubpage"  class="nav-item"><i class="fas fa-building-columns"></i><span>Clubs</span></a>
      <a href="index.php?page=events"    class="nav-item"><i class="fas fa-calendar-days"></i><span>Events</span></a>
      <div class="nav-section-label">REPORTS</div>
      <a href="index.php?page=reports"   class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
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
            <span class="profile-role"><?= htmlspecialchars($user['role']) ?></span>
          </div>
        </a>
        <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="#" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
</aside>

  <!-- ── Main ── -->
  <main class="main">



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
                <div class="avatar-circle" id="ap-avatar-circle">
                  <?php if (!empty($avatar_url)): ?>
                    <img src="<?= $avatar_url ?>" alt="Profile" class="avatar-photo" id="ap-avatar-img" />
                  <?php else: ?>
                    <span id="ap-avatar-initials"><?= $adminInitial ?></span>
                  <?php endif; ?>
                </div>
                <input type="file" id="ap-avatar-input" accept="image/jpeg,image/png,image/gif,image/webp"
                  style="display:none;" onchange="AdminProfile.uploadAvatar(this)" />
                <button class="avatar-edit-btn" title="Change photo"
                  onclick="document.getElementById('ap-avatar-input').click()">
                  <i class="fas fa-camera"></i>
                </button>
              </div>
              <div class="avatar-name"><?= htmlspecialchars($adminName) ?></div>
              <div class="avatar-role">Club Administrator</div>
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

        </div><!-- /profile-left -->

        <!-- RIGHT PANEL -->
        <div class="profile-right">

          <?php if ($success_msg): ?>
          <div class="alert-success">
            <i class="fas fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
          </div>
          <?php endif; ?>

          <?php if ($error_msg): ?>
          <div class="alert-error">
            <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error_msg) ?>
          </div>
          <?php endif; ?>

          <!-- ── Personal Info Tab ── -->
          <div id="tab-personal" class="tab-panel">
            <form method="POST" action="">
              <input type="hidden" name="action" value="update_profile" />

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title"><i class="fas fa-user"></i> Basic Information</div>
                    <div class="section-card-subtitle">Your name and account details</div>
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
                      <label class="form-label">Role</label>
                      <input type="text" class="form-input readonly" readonly value="Club Admin" />
                    </div>
                    <div class="form-group span-2">
                      <label class="form-label">Bio</label>
                      <textarea name="bio" class="form-textarea"
                                placeholder="Tell us a bit about yourself...">Club Admin at UNIFY. Managing student organizations and campus events.</textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title"><i class="fas fa-envelope"></i> Contact Information</div>
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
                        <input type="tel" name="phone" class="form-input" placeholder="+63 9XX XXX XXXX" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Member Since</label>
                      <input type="text" class="form-input readonly" readonly
                             value="<?= htmlspecialchars($user['joined']) ?>" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Account ID</label>
                      <input type="text" class="form-input readonly" readonly
                             value="#<?= str_pad($user['first_name'][0] ?? 'A', 6, '0', STR_PAD_LEFT) . $userId ?>" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="btn-row">
                <button type="button" class="btn-secondary" onclick="this.closest('form').reset()">
                  <i class="fas fa-rotate-left"></i> Reset
                </button>
                <button type="submit" class="btn-primary">
                  <i class="fas fa-floppy-disk"></i> Save Changes
                </button>
              </div>
            </form>
          </div>

          <!-- ── Password Tab ── -->
          <div id="tab-password" class="tab-panel" style="display:none;">
            <form method="POST" action="">
              <input type="hidden" name="action" value="update_password" />

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title"><i class="fas fa-lock"></i> Change Password</div>
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

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title"><i class="fas fa-shield-halved"></i> Security Overview</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <div class="form-grid">
                    <div class="form-group">
                      <label class="form-label">Account Created</label>
                      <input type="text" class="form-input readonly" readonly
                             value="<?= htmlspecialchars($user['joined']) ?>" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Last Login</label>
                      <input type="text" class="form-input readonly" readonly value="Today" />
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
          </div>

          <!-- ── Notifications Tab ── -->
          <div id="tab-notifications" class="tab-panel" style="display:none;">
            <form method="POST" action="">
              <input type="hidden" name="action" value="update_notifications" />

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title"><i class="fas fa-bell"></i> Email Notifications</div>
                    <div class="section-card-subtitle">Choose what updates you receive by email</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <?php foreach ([
                    ['notif_event_approved', 'Event Approved',        'When your event submission gets approved',  true],
                    ['notif_event_rejected', 'Event Rejected',         'When your event submission is rejected',    true],
                    ['notif_new_member',     'New Member Application', 'When someone applies to your club',         true],
                    ['notif_announcement',   'New Announcements',      'When a new announcement is posted',         false],
                    ['notif_weekly_digest',  'Weekly Summary',         'A weekly digest of club activity',          true],
                  ] as [$key, $label, $desc, $default]): ?>
                  <div class="toggle-row">
                    <div class="toggle-row-info">
                      <span class="toggle-label"><?= $label ?></span>
                      <span class="toggle-desc"><?= $desc ?></span>
                    </div>
                    <label class="toggle">
                      <input type="checkbox" name="<?= $key ?>" <?= $default ? 'checked' : '' ?> />
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="section-card" style="margin-bottom:14px;">
                <div class="section-card-header">
                  <div>
                    <div class="section-card-title"><i class="fas fa-mobile-screen"></i> In-App Notifications</div>
                    <div class="section-card-subtitle">Alerts shown inside UNIFY</div>
                  </div>
                </div>
                <div class="section-card-body">
                  <?php foreach ([
                    ['inapp_mentions',  'Mentions & Tags',   'When someone tags you in a post',        true],
                    ['inapp_reminders', 'Event Reminders',   '1 hour before an event you\'re managing', true],
                    ['inapp_approvals', 'Pending Approvals', 'Remind me of unreviewed approval requests', true],
                  ] as [$key, $label, $desc, $default]): ?>
                  <div class="toggle-row">
                    <div class="toggle-row-info">
                      <span class="toggle-label"><?= $label ?></span>
                      <span class="toggle-desc"><?= $desc ?></span>
                    </div>
                    <label class="toggle">
                      <input type="checkbox" name="<?= $key ?>" <?= $default ? 'checked' : '' ?> />
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

          <!-- ── My Clubs Tab ── -->
          <div id="tab-clubs" class="tab-panel" style="display:none;">
            <div class="section-card">
              <div class="section-card-header">
                <div>
                  <div class="section-card-title"><i class="fas fa-building-columns"></i> My Clubs</div>
                  <div class="section-card-subtitle">Clubs you are currently managing</div>
                </div>
              </div>
              <div class="section-card-body">
                <?php if (empty($clubs)): ?>
                <div style="text-align:center;padding:20px;color:var(--text-light);font-size:12px;">
                  No active clubs found.
                </div>
                <?php else: ?>
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
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- ── Activity Log Tab ── -->
          <div id="tab-activity" class="tab-panel" style="display:none;">
            <div class="section-card">
              <div class="section-card-header">
                <div>
                  <div class="section-card-title"><i class="fas fa-clock-rotate-left"></i> Recent Activity</div>
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
          </div>

          <!-- ── Danger Zone Tab ── -->
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
                    <div class="danger-action-desc">Temporarily disable your account. You can reactivate it by logging in again.</div>
                  </div>
                  <button class="btn-danger" onclick="confirmDeactivate()">
                    <i class="fas fa-user-slash"></i> Deactivate
                  </button>
                </div>
                <div class="danger-action">
                  <div class="danger-action-info">
                    <div class="danger-action-title">Delete Account</div>
                    <div class="danger-action-desc">Permanently delete your account and all data. <strong>This cannot be undone.</strong></div>
                  </div>
                  <form method="POST" action="" style="display:inline;" onsubmit="return confirmDelete()">
                    <input type="hidden" name="action" value="delete_account" />
                    <button type="submit" class="btn-danger">
                      <i class="fas fa-trash-can"></i> Delete
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>

        </div><!-- /profile-right -->
      </div><!-- /profile-body -->
    </div><!-- /content -->
  </main>
</div>

<div class="toast" id="toast">
  <i class="fas fa-circle-check"></i>
  <span id="toast-msg">Changes saved!</span>
</div>

<?php if ($success_msg): ?>
<script>
window.addEventListener('DOMContentLoaded', () => showToast(<?= json_encode($success_msg) ?>));
</script>
<?php endif; ?>

<script src="/public/assets/javascripts/adminprofile.js"></script>

<script>
function toggleSidebar() {
  const open = document.getElementById('mainSidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
  document.body.classList.toggle('sidebar-open', open);
}
function closeSidebar() {
  document.getElementById('mainSidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.body.classList.remove('sidebar-open');
}
var _tsx = 0, _tsy = 0, _swiping = false;
document.addEventListener('touchstart', function(e) {
  _tsx = e.touches[0].clientX;
  _tsy = e.touches[0].clientY;
  _swiping = _tsx < 80;
  if (_swiping) e.preventDefault();
}, {passive:false});
document.addEventListener('touchmove', function(e) {
  if (_swiping) e.preventDefault();
}, {passive:false});
document.addEventListener('touchend', function(e) {
  var dx = e.changedTouches[0].clientX - _tsx;
  var dy = e.changedTouches[0].clientY - _tsy;
  if (Math.abs(dy) > Math.abs(dx)) return;
  if (dx > 40 && _tsx < 80) toggleSidebar();
  if (dx < -40) closeSidebar();
  _swiping = false;
}, {passive:true});
</script>
</body>
</html>