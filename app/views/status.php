<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/status_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UNIFY — Application Status</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/assets/css/status.css"/>
</head>
<body>
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
      <div class="brand-text"><div class="brand-name">UNIFY</div><div class="brand-tagline">Club Management System</div></div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">STUDENT MENU</div>
      <a href="index.php?page=explore" class="nav-item"><i class="fas fa-compass"></i><span>Explore Clubs</span></a>
      <?php if ($app): ?>

      <?php endif; ?>
      <div class="nav-section-label">MY SPACE</div>
      <?php if ($has_club): ?>
        <a href="index.php?page=studenthome" class="nav-item"><i class="fas fa-house"></i><span>Home</span></a>
        <a href="index.php?page=myclubs"     class="nav-item"><i class="fas fa-users"></i><span>My Clubs</span></a>
        <a href="index.php?page=events"      class="nav-item"><i class="fas fa-calendar-days"></i><span>Events</span></a>
      <?php else: ?>
        <a href="index.php?page=studenthome" class="nav-item"><i class="fas fa-house"></i><span>Home</span></a>
        <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-users"></i><span>My Clubs</span><i class="fas fa-lock nav-lock-icon"></i></a>
        <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-calendar-days"></i><span>Events</span><i class="fas fa-lock nav-lock-icon"></i></a>
      <?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <?php if(!empty($avatar_url)): ?><img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" /><?php else: ?><span class="profile-avatar-fallback"><?= $avatar ?></span><?php endif; ?>
          <span class="profile-online-dot"></span>
        </div>
        <a href="index.php?page=studentprofile" class="profile-link">
          <div class="profile-info">
            <span class="profile-name"><?= $full_name ?></span>
            <span class="profile-role">Student</span>
          </div>
        </a>
        <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <header class="topbar">
      <div class="topbar-left"><span class="topbar-page-title">Application Status</span></div>
      <div class="topbar-actions">
        <a href="index.php?page=studentprofile" class="topbar-profile" title="View Profile">
          <div class="topbar-avatar">
  <?php if (!empty($avatar_url)): ?>
    <img src="<?= $avatar_url ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" />
  <?php else: ?>
    <?= $avatar ?>
  <?php endif; ?>
</div>
          <div class="topbar-profile-info"><span class="tp-name"><?= $full_name ?></span><span class="tp-role">Student</span></div>
        </a>
      </div>
    </header>

    <div class="status-content">

      <?php if (!$app): ?>
      <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:80px 20px;text-align:center;color:#7a9a85;gap:14px;width:100%;">
        <i class="fas fa-inbox" style="font-size:48px;opacity:.4;"></i>
        <p style="font-size:15px;font-weight:700;color:#3a5a45;">No application found</p>
        <span style="font-size:13px;">You haven't applied to any club yet.</span>
        <a href="index.php?page=explore" style="margin-top:8px;padding:10px 20px;background:#0d2b1a;color:#fff;border-radius:10px;text-decoration:none;font-weight:700;font-size:13px;">
          <i class="fas fa-compass"></i> Browse Clubs
        </a>
      </div>

      <?php else: ?>

      <!-- LEFT -->
      <div class="status-left">

        <div class="status-hero-card">
          <div class="shc-club-info">
            <img class="shc-club-logo" src="<?= $a_logo ?>" alt="<?= $a_club ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                 <?= $a_logo?'':'style="display:none"'?>>
            <div class="shc-club-logo-fallback" style="<?= $a_logo?'display:none':'display:flex'?>"><i class="fas fa-users"></i></div>
            <div class="shc-club-text">
              <div class="shc-club-name"><?= $a_club ?></div>
              <div class="shc-club-cat"><i class="fas fa-tag"></i> <?= $a_cat ?> &nbsp;·&nbsp; <i class="fas fa-users"></i> <?= $a_members ?> members</div>
            </div>
          </div>

          <!-- Status Badge -->
          <div class="shc-status-badge <?= $a_status ?>">
            <?php if ($a_status === 'approved'): ?>
              <i class="fas fa-circle-check"></i> Approved
            <?php elseif ($a_status === 'rejected'): ?>
              <i class="fas fa-circle-xmark"></i> Not Approved
            <?php else: ?>
              <i class="fas fa-hourglass-half"></i> Under Review
            <?php endif; ?>
          </div>

          <div class="shc-meta-grid">
            <div class="shc-meta-item"><div class="shc-meta-label">Submitted On</div><div class="shc-meta-value"><?= $a_applied ?></div></div>
            <div class="shc-meta-item"><div class="shc-meta-label">Reference No.</div><div class="shc-meta-value"><?= $a_ref ?></div></div>
            <div class="shc-meta-item"><div class="shc-meta-label">Course Applied</div><div class="shc-meta-value"><?= $a_course ?: '—' ?></div></div>
          </div>

          <div class="shc-notice">
            <i class="fas fa-circle-info"></i>
            <?php if ($a_status === 'approved'): ?>
              Congratulations! Your application has been approved. You now have full access.
            <?php elseif ($a_status === 'rejected'): ?>
              Your application was not approved this time. You're welcome to apply to another club.
            <?php else: ?>
              Your application is being reviewed. This usually takes 3–5 business days.
            <?php endif; ?>
          </div>

          <?php if ($a_status === 'approved'): ?>
          <a href="index.php?page=studenthome" class="withdraw-btn" style="background:var(--green-dark,#0d2b1a);color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px;justify-content:center;border-color:transparent;">
            <i class="fas fa-house"></i> Go to My Dashboard
          </a>
          <?php elseif ($a_status === 'pending'): ?>
          <button class="withdraw-btn" onclick="openWithdraw()">
            <i class="fas fa-xmark"></i> Withdraw Application
          </button>
          <?php endif; ?>
        </div>

        <div class="another-club-card">
          <div class="ac-text">
            <div class="ac-title">Want to explore more clubs?</div>
            <div class="ac-sub">Browse other clubs while you wait. Note: only one pending application at a time.</div>
          </div>
          <a href="index.php?page=explore" class="ac-btn"><i class="fas fa-compass"></i> Browse Clubs</a>
        </div>

      </div><!-- /status-left -->

      <!-- RIGHT -->
      <div class="status-right">

        <div class="timeline-card">
          <div class="tc-header"><i class="fas fa-list-check"></i><span>Application Progress</span></div>
          <div class="timeline">

            <div class="tl-item done">
              <div class="tl-dot-wrap"><div class="tl-dot"><i class="fas fa-check"></i></div><div class="tl-line"></div></div>
              <div class="tl-body">
                <div class="tl-title">Account Created</div>
                <div class="tl-sub">You signed up and logged in to UNIFY.</div>
                <div class="tl-time"><?= $created_at ? date('F j, Y', strtotime($created_at)) : '—' ?></div>
              </div>
            </div>

            <div class="tl-item done">
              <div class="tl-dot-wrap"><div class="tl-dot"><i class="fas fa-check"></i></div><div class="tl-line"></div></div>
              <div class="tl-body">
                <div class="tl-title">Application Submitted</div>
                <div class="tl-sub">Your application to <strong><?= $a_club ?></strong> was sent successfully.</div>
                <div class="tl-time"><?= $a_applied_short ?></div>
              </div>
            </div>

            <div class="tl-item <?= $tl_decided ? 'done' : 'active' ?>">
              <div class="tl-dot-wrap"><div class="tl-dot"><i class="fas <?= $tl_decided ? 'fa-check' : 'fa-hourglass-half' ?>"></i></div><div class="tl-line"></div></div>
              <div class="tl-body">
                <div class="tl-title">Under Review <?= !$tl_decided ? '<span class="tl-badge">Current</span>' : '' ?></div>
                <div class="tl-sub">The club admin is reviewing your application.</div>
                <div class="tl-time"><?= $tl_decided ? 'Completed' : 'In progress…' ?></div>
              </div>
            </div>

            <div class="tl-item <?= $tl_decided ? 'done' : 'pending' ?>">
              <div class="tl-dot-wrap">
                <div class="tl-dot">
                  <?php if ($a_status==='approved'): ?><i class="fas fa-circle-check"></i>
                  <?php elseif ($a_status==='rejected'): ?><i class="fas fa-circle-xmark"></i>
                  <?php else: ?><i class="fas fa-envelope"></i><?php endif; ?>
                </div>
                <div class="tl-line"></div>
              </div>
              <div class="tl-body">
                <div class="tl-title">Decision Made <?= $tl_decided ? '<span class="tl-badge '.$a_status.'">'.ucfirst($a_status).'</span>' : '' ?></div>
                <div class="tl-sub">You'll be notified here once the admin approves or declines.</div>
                <div class="tl-time"><?= $tl_decided && $app['reviewed_at'] ? date('F j, Y', strtotime($app['reviewed_at'])) : 'Pending' ?></div>
              </div>
            </div>

            <div class="tl-item <?= $tl_access ? 'done' : 'pending' ?>">
              <div class="tl-dot-wrap"><div class="tl-dot"><i class="fas <?= $tl_access ? 'fa-check' : 'fa-door-open' ?>"></i></div></div>
              <div class="tl-body">
                <div class="tl-title">Full Access Granted</div>
                <div class="tl-sub">Once accepted, your student dashboard and club features unlock.</div>
                <div class="tl-time"><?= $tl_access ? 'Unlocked ✓' : 'Pending' ?></div>
              </div>
            </div>

          </div>
        </div>

        <!-- Application Summary -->
        <div class="summary-card">
          <div class="tc-header"><i class="fas fa-file-lines"></i><span>Application Summary</span></div>
          <div class="summary-fields">
            <div class="sf-row"><span class="sf-label">Full Name</span><span class="sf-value"><?= $full_name ?></span></div>
            <div class="sf-row"><span class="sf-label">Student ID</span><span class="sf-value"><?= $student_id ?></span></div>
            <div class="sf-row"><span class="sf-label">Course & Year</span><span class="sf-value"><?= $a_course ?: ($course_val.($year_level!=='—'?' – '.$year_level:'')) ?></span></div>
            <div class="sf-row"><span class="sf-label">Contact</span><span class="sf-value"><?= $phone_val ?></span></div>
            <?php if ($a_extras): ?>
            <div class="sf-row sf-row-full">
              <span class="sf-label">Reason / Notes</span>
              <span class="sf-value" style="white-space:pre-wrap;"><?= $a_extras ?></span>
            </div>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- /status-right -->

      <!-- Application History (if more than one) -->
      <?php if (count($all_apps) > 1): ?>
      <div class="history-section">
        <div class="tc-header" style="margin-bottom:12px;"><i class="fas fa-clock-rotate-left"></i><span>Application History</span></div>
        <div class="history-list">
          <?php foreach ($all_apps as $i => $ha):
            if ($i === 0) continue;
            $h_stat  = $ha['status'];
            $h_club  = htmlspecialchars($ha['club_name']);
            $h_date  = date('M j, Y', strtotime($ha['applied_at']));
            $h_badge = $h_stat==='approved'?'approved':($h_stat==='rejected'?'rejected':'pending');
            $h_icon  = $h_stat==='approved'?'fa-circle-check':($h_stat==='rejected'?'fa-circle-xmark':'fa-hourglass-half');
          ?>
          <div class="history-item">
            <div class="hi-club"><?= $h_club ?></div>
            <div class="hi-date"><?= $h_date ?></div>
            <span class="asc-badge <?= $h_badge ?>"><i class="fas <?= $h_icon ?>"></i> <?= ucfirst($h_stat) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php endif; ?>
    </div><!-- /status-content -->
  </main>
</div>

<!-- WITHDRAW MODAL -->
<div class="modal-overlay" id="withdrawOverlay" onclick="closeWithdraw(event)">
  <div class="confirm-box">
    <div class="confirm-icon"><i class="fas fa-triangle-exclamation"></i></div>
    <div class="confirm-title">Withdraw Application?</div>
    <div class="confirm-msg">
      Are you sure you want to withdraw your application to <strong><?= $app ? $a_club : '' ?></strong>?
      You can re-apply anytime from the Explore page.
    </div>
    <div class="confirm-btns">
      <button class="confirm-cancel" onclick="closeWithdraw()">Keep It</button>
      <button class="confirm-ok" onclick="doWithdraw()">Yes, Withdraw</button>
    </div>
  </div>
</div>

<div class="crud-toast" id="crudToast"></div>

<script>
const APP_ID = <?= $app ? (int)$app['id'] : 'null' ?>;
</script>
</body>
</html>
<script src="/assets/javascripts/status.js"></script>
