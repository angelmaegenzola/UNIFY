<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/app/controllers/login_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — UNIFY</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/public/assets/css/login.css"/>
</head>
<body>

<div class="wrapper">

  <!-- ══════════════════════════════════════
       LEFT — Form
  ═══════════════════════════════════════ -->
  <div class="left">

    <div class="logo-row">
      <div class="logo-circle">
        <img src="/public/assets/pictures/chmsulogo.jpg" alt="CHMSU Logo"/>
      </div>
      <div class="logo-text">
        Club Management System
        <span>Carlos Hilado Memorial State University</span>
      </div>
    </div>

    <h1>Welcome back</h1>
    <p class="sub">Sign in to your account to continue</p>

    <?php if ($registered): ?>
    <div class="success-msg">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
      Account created! You can now log in.
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="err">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=login" id="loginForm">

      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        <input type="text" name="username" placeholder="Username"
               value="<?= htmlspecialchars($username) ?>"
               autocomplete="username" required/>
      </div>

      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" name="password" id="passwordInput"
               placeholder="Password"
               autocomplete="current-password" required/>
        <button type="button" class="toggle-pw" onclick="togglePassword()" title="Show/hide password">
          <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
      </div>

      <div class="form-options">
        <label class="remember-me">
          <input type="checkbox" name="remember"/> Remember me
        </label>
        <a href="#" class="forgot-link">Forgot password?</a>
      </div>

      <button class="btn-login" type="submit" id="loginBtn">
        <div class="spinner"></div>
        <span class="btn-label">Log In</span>
      </button>

    </form>

    <div class="divider">or sign in with</div>

    <div class="sso">
      <a href="#" class="sso-btn" onclick="return false;">
        <svg width="16" height="16" viewBox="0 0 48 48">
          <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
          <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
          <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        </svg>
        Google
      </a>
      <a href="#" class="sso-btn" onclick="return false;">
        <svg width="16" height="16" viewBox="0 0 24 24">
          <path fill="#1877F2" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.883v2.271h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
        </svg>
        Facebook
      </a>
    </div>

    <div class="signup-link">
      Don't have an account? <a href="index.php?page=signup">Sign Up</a>
    </div>

  </div><!-- /left -->


  <!-- ══════════════════════════════════════
       RIGHT — Brand Panel
  ═══════════════════════════════════════ -->
  <div class="right">

    <svg class="right-bg" viewBox="0 0 500 560" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
      <circle cx="400" cy="80"  r="180" fill="rgba(255,255,255,.07)"/>
      <circle cx="80"  cy="420" r="140" fill="rgba(255,255,255,.06)"/>
      <path fill="rgba(255,255,255,.05)" d="M0,320 C80,280 180,340 280,310 C380,280 440,320 500,300 L500,560 L0,560 Z"/>
      <path fill="rgba(255,255,255,.04)" d="M0,380 C100,350 200,400 310,370 C400,345 460,380 500,360 L500,560 L0,560 Z"/>
    </svg>

    <div class="emblem">
      <img src="/public/assets/pictures/chmsulogo.jpg" alt="CHMSU Logo"/>
    </div>

    <h2>UNIFY</h2>
    <p>Manage clubs, events, members and finances all in one place.</p>

    <div class="badge">
      <div class="badge-dot"></div>
      <span>Carlos Hilado Memorial State University</span>
    </div>

    <div class="stat-cards">
      <div class="stat">
        <div class="stat-num">18</div>
        <div class="stat-lbl">Active Clubs</div>
      </div>
      <div class="stat">
        <div class="stat-num">1,250</div>
        <div class="stat-lbl">Members</div>
      </div>
      <div class="stat">
        <div class="stat-num">5</div>
        <div class="stat-lbl">Events This Month</div>
      </div>
    </div>

  </div><!-- /right -->

</div><!-- /wrapper -->

<script src="/public/assets/javascripts/login.js"></script>
</body>
</html>
