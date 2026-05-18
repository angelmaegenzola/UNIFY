<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/officer_messages_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Club Chat</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/public/assets/css/officer_dashboard.css" />
  <link rel="stylesheet" href="/public/assets/css/officer_messages.css" />
  <link rel="stylesheet" href="/public/assets/css/transitions.css" />
</head>
<body>
<div class="app">

  <!-- ── SIDEBAR ────────────────────────────────────────── -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
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
      <a href="index.php?page=officer_messages" class="nav-item active">
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
            <img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" />
          <?php else: ?>
            <span class="profile-avatar-fallback"><?= htmlspecialchars($userInit) ?></span>
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

  <!-- ── MAIN ───────────────────────────────────────────── -->
  <main class="main">

    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Club Chat</span>
        <span class="topbar-date" id="topbarDate"></span>
      </div>
      <div class="topbar-center"></div>
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

    <div class="content">
      <div class="msg-layout">

        <!-- ── LEFT SIDEBAR ─────────────────────────────── -->
        <div class="msg-sidebar">

          <!-- Club header -->
          <div class="msg-sidebar-header">
            <?php if (count($allClubs) > 1): ?>
              <div class="msg-club-switcher" id="clubSwitcher">
                <button class="msg-club-switcher-btn" onclick="OM.toggleClubSwitcher(event)">
                  <div class="msg-club-logo-wrap">
                    <?php if (!empty($officerClub['logo_path'])): ?>
                      <img src="<?= htmlspecialchars($officerClub['logo_path']) ?>" alt="" class="msg-club-logo" />
                    <?php else: ?>
                      <div class="msg-club-initial"><?= htmlspecialchars($clubInitial) ?></div>
                    <?php endif; ?>
                  </div>
                  <div class="msg-club-info">
                    <div class="msg-club-name"><?= htmlspecialchars($clubName) ?></div>
                    <div class="msg-club-sub"><?= count($allClubs) ?> clubs · tap to switch</div>
                  </div>
                  <i class="fas fa-chevron-down msg-club-caret"></i>
                </button>
                <div class="msg-club-dropdown">
                  <div class="msg-club-dropdown-label">Your Clubs</div>
                  <?php foreach ($allClubs as $c):
                    $isActive = ((int)$c['club_id'] === $clubId);
                    $ddInit   = strtoupper(substr($c['club_name'], 0, 1));
                  ?>
                  <a href="index.php?page=officer_messages&club_id=<?= (int)$c['club_id'] ?>"
                     class="msg-club-dropdown-item<?= $isActive ? ' active' : '' ?>">
                    <div class="msg-dd-avatar">
                      <?php if (!empty($c['logo_path'])): ?>
                        <img src="<?= htmlspecialchars($c['logo_path']) ?>" alt="" />
                      <?php else: ?>
                        <?= htmlspecialchars($ddInit) ?>
                      <?php endif; ?>
                    </div>
                    <div class="msg-dd-info">
                      <div class="msg-dd-name"><?= htmlspecialchars($c['club_name']) ?></div>
                      <div class="msg-dd-role"><?= ucfirst(htmlspecialchars($c['role'])) ?></div>
                    </div>
                    <?php if ($isActive): ?><i class="fas fa-check msg-dd-check"></i><?php endif; ?>
                  </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php else: ?>
              <div class="msg-club-logo-wrap">
                <?php if (!empty($officerClub['logo_path'])): ?>
                  <img src="<?= htmlspecialchars($officerClub['logo_path']) ?>" alt="" class="msg-club-logo" />
                <?php else: ?>
                  <div class="msg-club-initial"><?= htmlspecialchars($clubInitial) ?></div>
                <?php endif; ?>
              </div>
              <div class="msg-club-info">
                <div class="msg-club-name"><?= htmlspecialchars($clubName) ?></div>
                <div class="msg-club-sub">Club Chat</div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Channels -->
          <div class="msg-channel-list">
            <div class="msg-channel-label">CHANNELS</div>
            <div class="msg-channel-item <?= !$isDM ? 'active' : '' ?>"
                 onclick="OM.switchToGroup()">
              <i class="fas fa-hashtag"></i>
              <span>Group Chat</span>
              <span class="msg-online-dot"></span>
            </div>
          </div>

          <!-- Members / DM list -->
          <div class="msg-members-section">
            <div class="msg-channel-label" style="flex-shrink:0;">MEMBERS — <?= $totalMembers ?> <span style="font-size:9px;color:var(--text-light);font-weight:400;margin-left:4px;">· click to DM</span></div>
            <div class="msg-member-list" id="msgMemberList" style="overflow-y:auto;flex:1;min-height:0;">
              <?php
                $colors = ['av-green','av-teal','av-red','av-yellow','av-purple'];
                foreach ($dbMembers as $i => $m):
                  if ((int)$m['user_id'] === $userId) continue;
                  $color    = $colors[$i % 5];
                  $init     = strtoupper(substr($m['first_name'], 0, 1));
                  $fullName = htmlspecialchars($m['first_name'] . ' ' . $m['last_name']);
                  $role     = htmlspecialchars($m['role']);
                  $memberPic = !empty($m['profile_picture'])
                    ? '/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($m['profile_picture']))
                    : '';
                  $isActiveDM = ($isDM && (int)$m['user_id'] === $dmUserId);
              ?>
              <div class="msg-member-item <?= $isActiveDM ? 'dm-active' : '' ?>"
                   onclick="OM.openDM(<?= (int)$m['user_id'] ?>, '<?= $fullName ?>')"
                   data-uid="<?= (int)$m['user_id'] ?>"
                   title="Message <?= $fullName ?>">
                <div class="msg-member-avatar <?= $color ?>" style="overflow:hidden;">
                  <?php if ($memberPic): ?>
                    <img src="<?= $memberPic ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" />
                  <?php else: ?>
                    <?= $init ?>
                  <?php endif; ?>
                </div>
                <div class="msg-member-info">
                  <div class="msg-member-name"><?= $fullName ?></div>
                  <div class="msg-member-role"><?= ucfirst($role) ?></div>
                </div>
                <span class="dm-unread-badge" id="dmBadge-<?= (int)$m['user_id'] ?>" style="display:none;"></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div><!-- /msg-sidebar -->

        <!-- ── RIGHT CHAT PANE ───────────────────────────── -->
        <div class="msg-chat-pane">

          <div class="msg-chat-header">
            <div class="msg-chat-header-left">
              <div class="msg-hash-icon">
                <i class="fas <?= $isDM ? 'fa-user' : 'fa-hashtag' ?>"></i>
              </div>
              <div>
                <div class="msg-chat-title" id="chatTitle">
                  <?= $isDM && $dmUser
                      ? htmlspecialchars($dmUser['first_name'] . ' ' . $dmUser['last_name'])
                      : 'Group Chat' ?>
                </div>
                <div class="msg-chat-subtitle" id="chatSubtitle">
                  <?= $isDM && $dmUser
                      ? 'Direct message · ' . htmlspecialchars($clubName)
                      : 'All ' . $totalMembers . ' members · ' . htmlspecialchars($clubName) ?>
                </div>
              </div>
            </div>
            <div class="msg-live-badge">
              <span class="msg-live-dot"></span> Live
            </div>
          </div>

          <div class="msg-messages" id="msgMessages">
            <div class="msg-spinner"><i class="fas fa-spinner fa-spin"></i> Loading messages…</div>
          </div>

          <div class="msg-input-area">
            <div class="msg-input-wrap">
              <input type="text" id="msgInput" class="msg-input"
                     placeholder="<?= $isDM && $dmUser ? 'Message ' . htmlspecialchars($dmUser['first_name']) . '…' : 'Message the group…' ?>"
                     maxlength="1000" />
              <div class="msg-input-actions">
                <span class="msg-char-count" id="msgCharCount"></span>
                <button class="msg-send-btn" onclick="OM.sendMessage()" title="Send (Enter)">
                  <i class="fas fa-paper-plane"></i>
                </button>
              </div>
            </div>
          </div>

        </div><!-- /msg-chat-pane -->
      </div><!-- /msg-layout -->
    </div><!-- /content -->
  </main>
</div>

<div id="toast" class="toast"></div>

<!-- Delete confirm modal -->
<div class="msg-delete-overlay" id="msgDeleteModal">
  <div class="msg-delete-box">
    <div class="msg-del-icon-wrap"><i class="fas fa-trash-can"></i></div>
    <div class="msg-del-title">Delete Message?</div>
    <p class="msg-del-sub">This cannot be undone.</p>
    <div class="msg-del-actions">
      <button class="msg-del-cancel" onclick="OM.closeDeleteModal()">Cancel</button>
      <button class="msg-del-confirm" id="msgDelConfirmBtn"><i class="fas fa-trash-can"></i> Delete</button>
    </div>
  </div>
</div>

<script>
window.OM_CONFIG = {
  userId:     <?= (int)$userId ?>,
  userName:   <?= json_encode($userName) ?>,
  userInit:   <?= json_encode($userInit) ?>,
  userAvatar: <?= json_encode($avatar_url) ?>,
  isMod:      <?= json_encode($isMod) ?>,
  clubId:     <?= $clubId ?>,
  clubName:   <?= json_encode($clubName) ?>,
  isDM:       <?= json_encode($isDM) ?>,
  dmUserId:   <?= $isDM && $dmUser ? (int)$dmUser['id'] : 0 ?>,
  dmUserName: <?= $isDM && $dmUser ? json_encode($dmUser['first_name'] . ' ' . $dmUser['last_name']) : json_encode('') ?>,
  pageBase:   'index.php?page=officer_messages&club_id=<?= $clubId ?>',
};
</script>
<script src="/public/assets/javascripts/officer_messages.js"></script>
</body>
</html>