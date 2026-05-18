<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/verify_2fa_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verify Identity — UNIFY</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/unify/assets/css/login.css"/>
  <link rel="stylesheet" href="/unify/assets/css/verify_2fa.css">
</head>
<body>

<div class="wrapper">

  <!-- ══ LEFT — Verify Form ══ -->
  <div class="left">

    <div class="logo-row">
      <div class="logo-circle">
        <img src="/unify/assets/pictures/chmsulogo.jpg" alt="CHMSU Logo"/>
      </div>
      <div class="logo-text">
        Club Management System
        <span>Carlos Hilado Memorial State University</span>
      </div>
    </div>


    <h1>Verify Identity</h1>
    <p class="sub">Enter the 6-digit code from your authenticator app</p>

    <!-- Show who is logging in -->
    <div class="greeting">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      Logging in as <strong>&nbsp;<?= htmlspecialchars($_SESSION['2fa_pending_username'] ?? '') ?></strong>
    </div>

    <?php if ($error): ?>
    <div class="err">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=verify_2fa" id="otpForm">

      <!-- 30-second countdown bar -->
      <div class="totp-timer">
        <span id="timerSec">--s</span>
        <div class="timer-bar-wrap">
          <div class="timer-bar" id="timerBar" style="width:100%"></div>
        </div>
        <span>code refreshes</span>
      </div>

      <!-- 6 digit boxes -->
      <div class="otp-boxes" id="otpBoxes">
        <?php for ($i = 0; $i < 6; $i++): ?>
        <input type="text" class="otp-box" maxlength="1"
               inputmode="numeric" pattern="[0-9]"
               autocomplete="off" <?= $i === 0 ? 'autofocus' : '' ?>/>
        <?php endfor; ?>
      </div>

      <input type="hidden" name="otp_code" id="otp_code"/>

      <button class="btn-login" type="submit" id="verifyBtn">
        <div class="spinner" style="display:none;"></div>
        <span class="btn-label">Verify &amp; Sign In</span>
      </button>
    </form>

    <div class="back-link">
      <a href="index.php?page=login">← Use a different account</a>
    </div>

    <p class="hint">
      Open <strong>Google Authenticator</strong> or <strong>Authy</strong> on your phone
      and enter the current code for <strong>UNIFY</strong>.
    </p>

  </div><!-- /left -->


  <!-- ══ RIGHT — Brand Panel ══ -->
  <div class="right">
    <svg class="right-bg" viewBox="0 0 500 560" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
      <circle cx="400" cy="80"  r="180" fill="rgba(255,255,255,.07)"/>
      <circle cx="80"  cy="420" r="140" fill="rgba(255,255,255,.06)"/>
      <path fill="rgba(255,255,255,.05)" d="M0,320 C80,280 180,340 280,310 C380,280 440,320 500,300 L500,560 L0,560 Z"/>
      <path fill="rgba(255,255,255,.04)" d="M0,380 C100,350 200,400 310,370 C400,345 460,380 500,360 L500,560 L0,560 Z"/>
    </svg>
    <div class="emblem">
      <img src="/unify/assets/pictures/chmsulogo.jpg" alt="CHMSU Logo"/>
    </div>
    <h2>UNIFY</h2>
    <p>Manage clubs, events, members and finances all in one place.</p>
    <div class="badge">
      <div class="badge-dot"></div>
      <span>Carlos Hilado Memorial State University</span>
    </div>
    <div class="stat-cards">
      <div class="stat"><div class="stat-num">18</div><div class="stat-lbl">Active Clubs</div></div>
      <div class="stat"><div class="stat-num">1,250</div><div class="stat-lbl">Members</div></div>
      <div class="stat"><div class="stat-num">5</div><div class="stat-lbl">Events This Month</div></div>
    </div>
  </div>

</div><!-- /wrapper -->


</body>
</html>
<script src="/unify/assets/javascripts/verify_2fa.js"></script>
