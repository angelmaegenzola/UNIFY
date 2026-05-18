<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

$user_id    = (int) $_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['first_name'] ?? 'Student');
$last_name  = htmlspecialchars($_SESSION['last_name']  ?? '');
$userInit   = strtoupper(substr($first_name, 0, 1));

// Check if user already has a pending request
$existing = $pdo->prepare("SELECT id, name, status, created_at FROM club_requests WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$existing->execute([$user_id]);
$myRequest = $existing->fetch(PDO::FETCH_ASSOC);

// Is user already a leader somewhere?
$leaderCheck = $pdo->prepare("SELECT m.role, c.name AS club_name FROM members m JOIN clubs c ON c.id=m.club_id WHERE m.user_id=? AND m.role IN ('president','vice president','officer','lead') AND m.status='active' LIMIT 1");
$leaderCheck->execute([$user_id]);
$leaderIn = $leaderCheck->fetch(PDO::FETCH_ASSOC);

$categories = ['Tech','Arts','Sports','Academic','Cultural','Environment','Health','Media','Business','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UNIFY — Propose a New Club</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/unify/assets/css/studenthome.css"/>
  <link rel="stylesheet" href="/unify/assets/css/club_request.css"/>
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
    <nav class="sidebar-nav">
     <div class="nav-section-label">STUDENT MENU</div>
<a href="index.php?page=explore"       class="nav-item"><i class="fas fa-compass"></i><span>Explore Clubs</span></a>
<div class="nav-section-label">MY SPACE</div>
<a href="index.php?page=studenthome"   class="nav-item"><i class="fas fa-house"></i><span>Home</span></a>
<a href="index.php?page=myclubs"       class="nav-item"><i class="fas fa-users"></i><span>My Clubs</span></a>
<a href="index.php?page=studentevents" class="nav-item"><i class="fas fa-calendar-days"></i><span>Events</span></a>
      <?php if ($leaderIn): ?>
      <a href="index.php?page=officer_dashboard" class="nav-item officer-link"><i class="fas fa-shield-halved"></i><span>Officer Dashboard</span></a>
      <?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <span class="profile-avatar-fallback"><?= $userInit ?></span>
          <span class="profile-online-dot"></span>
        </div>
        <a href="index.php?page=studentprofile" class="profile-link">
          <div class="profile-info">
            <span class="profile-name"><?= $first_name . ' ' . $last_name ?></span>
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
      <div class="topbar-left">
        <span class="topbar-page-title">Propose a New Club</span>
      </div>
      <div class="topbar-actions"></div>
    </header>

    <div class="content cr-content">

      <!-- If already has a pending/approved request -->
     <?php if ($myRequest && $myRequest['status'] !== 'rejected'): ?>
      <div class="cr-status-card <?= $myRequest['status'] ?>">
        <div class="cr-status-icon">
          <?php if ($myRequest['status'] === 'pending'): ?>
            <i class="fas fa-hourglass-half"></i>
          <?php elseif ($myRequest['status'] === 'approved'): ?>
            <i class="fas fa-check-circle"></i>
          <?php else: ?>
            <i class="fas fa-times-circle"></i>
          <?php endif; ?>
        </div>
        <div class="cr-status-info">
          <div class="cr-status-label">
            <?php if ($myRequest['status'] === 'pending'): ?>
              Your club request is under review
            <?php elseif ($myRequest['status'] === 'approved'): ?>
              Your club was approved! 🎉
            <?php else: ?>
              Your club request was not approved
            <?php endif; ?>
          </div>
          <div class="cr-status-name">"<?= htmlspecialchars($myRequest['name']) ?>"</div>
          <div class="cr-status-date">Submitted <?= date('F j, Y', strtotime($myRequest['created_at'])) ?></div>
          <?php if ($myRequest['status'] === 'approved'): ?>
            <a href="index.php?page=officer_dashboard" class="cr-goto-btn">Go to Officer Dashboard <i class="fas fa-arrow-right"></i></a>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($myRequest['status'] === 'pending'): ?>
      <div class="cr-pending-note"><i class="fas fa-info-circle"></i> You can only have one pending club request at a time. Once it is reviewed, you may submit a new one.</div>
      <?php endif; ?>
      <?php endif; ?>

      <?php if (!$myRequest || $myRequest['status'] === 'rejected'): ?>
      <!-- Club Proposal Form -->
      <div class="cr-hero">
        <div class="cr-hero-icon"><i class="fas fa-plus-circle"></i></div>
        <div class="cr-hero-text">
          <h2>Start Something New</h2>
          <p>Fill out the form below to propose a new student organization. Admin will review your request and notify you of the outcome.</p>
        </div>
      </div>

      <?php if ($leaderIn): ?>
      <div class="cr-leader-note">
        <i class="fas fa-shield-halved"></i>
        You are already a <strong><?= htmlspecialchars($leaderIn['role']) ?></strong> of <strong><?= htmlspecialchars($leaderIn['club_name']) ?></strong>. You may still propose a new club — if approved, you will also become its president.
      </div>
      <?php endif; ?>

      <div class="cr-form-card">
        <div class="cr-form-section-label">Club Information</div>
        <div class="cr-form-group">
          <label class="cr-label">Club Name <span class="req">*</span></label>
          <input type="text" id="crName" class="cr-input" placeholder="e.g. CHMSU Photography Club" maxlength="191"/>
        </div>
        <div class="cr-form-row">
          <div class="cr-form-group">
            <label class="cr-label">Acronym</label>
            <input type="text" id="crAcronym" class="cr-input" placeholder="e.g. CPC" maxlength="30"/>
          </div>
          <div class="cr-form-group">
            <label class="cr-label">Category <span class="req">*</span></label>
            <select id="crCategory" class="cr-select">
              <option value="">— Select category —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>"><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="cr-form-group">
          <label class="cr-label">Description <span class="req">*</span></label>
          <textarea id="crDescription" class="cr-textarea" placeholder="What is this club about? What are its goals and activities?" rows="4" maxlength="1000"></textarea>
          <div class="cr-char-count"><span id="crDescCount">0</span>/1000</div>
        </div>
        <div class="cr-form-row">
          <div class="cr-form-group">
            <label class="cr-label">Proposed Room / Venue</label>
            <input type="text" id="crRoom" class="cr-input" placeholder="e.g. Room 202, Main Building"/>
          </div>
          <div class="cr-form-group">
            <label class="cr-label">Founding Date / Year</label>
            <input type="text" id="crFounded" class="cr-input" placeholder="e.g. May 2026"/>
          </div>
        </div>

        <div class="cr-info-box">
          <i class="fas fa-info-circle"></i>
          <div>
            <strong>What happens next?</strong> Your request will be reviewed by the admin. If approved, your club will be listed on UNIFY and you will automatically become its <strong>president</strong>. You will receive a notification either way.
          </div>
        </div>

        <div class="cr-form-footer">
          <button class="cr-btn-cancel" onclick="window.location='index.php?page=studenthome'">Cancel</button>
          <button class="cr-btn-submit" id="crSubmitBtn" onclick="submitClubRequest()">
            <i class="fas fa-paper-plane"></i> Submit Request
          </button>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<div id="cr-toast" class="cr-toast"></div>


</body>
</html>
<script src="/unify/assets/javascripts/club_request.js"></script>
