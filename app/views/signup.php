<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/signup_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up — UNIFY</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/public/assets/css/signup.css"/>
</head>
<body>

<div class="wrapper">

  <!-- LEFT — Form -->
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

    <h1>Create an account</h1>
    <p class="sub">Fill in your details to get started with UNIFY</p>

    <?php if (!empty($errors)): ?>
    <div class="err">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=signup" id="signupForm">

      <!-- Name -->
      <div class="row-2">
        <div class="field">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <input type="text" name="first_name" placeholder="First Name"
                 value="<?= htmlspecialchars($fields['first_name']) ?>"
                 autocomplete="given-name" required/>
        </div>
        <div class="field">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <input type="text" name="last_name" placeholder="Last Name"
                 value="<?= htmlspecialchars($fields['last_name']) ?>"
                 autocomplete="family-name" required/>
        </div>
      </div>

      <!-- Email -->
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="4" width="20" height="16" rx="2"/>
          <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
        </svg>
        <input type="email" name="email" placeholder="Email Address"
               value="<?= htmlspecialchars($fields['email']) ?>"
               autocomplete="email" required/>
      </div>

      <!-- Username -->
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
          <path d="M17 11l2 2 4-4"/>
        </svg>
        <input type="text" name="username" placeholder="Username (e.g. mariasantos)"
               value="<?= htmlspecialchars($fields['username']) ?>"
               autocomplete="username" required/>
      </div>

      <!-- Course -->
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
          <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
        <input type="text" name="course" placeholder="Course (e.g. BS Information Technology)"
               value="<?= htmlspecialchars($fields['course']) ?>" required/>
      </div>

      <!-- Year Level & Section -->
      <div class="row-2">
        <div class="field">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path d="M16 2v4M8 2v4M3 10h18"/>
          </svg>
          <select name="year_level" required>
            <option value="" disabled <?= empty($fields['year_level']) ? 'selected' : '' ?>>Year Level</option>
            <option value="1st Year" <?= $fields['year_level']==='1st Year' ? 'selected' : '' ?>>1st Year</option>
            <option value="2nd Year" <?= $fields['year_level']==='2nd Year' ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd Year" <?= $fields['year_level']==='3rd Year' ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th Year" <?= $fields['year_level']==='4th Year' ? 'selected' : '' ?>>4th Year</option>
            <option value="5th Year" <?= $fields['year_level']==='5th Year' ? 'selected' : '' ?>>5th Year</option>
          </select>
        </div>

        <!-- FIXED: Section icon changed from hamburger/list (M4 6h16M4 12h16M4 18h7)
             to a grid/layout icon that visually matches the calendar icon used by Year Level
             and the outlined stroke style used consistently across all other fields. -->
        <div class="field">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
          </svg>
          <input type="text" name="section" placeholder="Section (e.g. A, B, C)"
                 value="<?= htmlspecialchars($fields['section']) ?>"
                 maxlength="5" required/>
        </div>
      </div>

      <!-- Password -->
      <div class="field">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" id="password" name="password"
               placeholder="Password (min. 8 characters)"
               autocomplete="new-password" required/>
      </div>

      <!-- Confirm Password -->
      <div class="field field--confirm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Confirm Password"
               autocomplete="new-password"
               oninput="checkConfirm()" required/>
        <div class="strength-label" id="confirmMsg"></div>
      </div>

      <!-- Terms -->
      <div class="terms">
        <input type="checkbox" id="terms" name="terms"/>
        <label for="terms">
          I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
        </label>
      </div>

      <!-- Submit -->
      <button class="btn-signup" type="submit" id="signupBtn">
        <div class="spinner"></div>
        <span class="btn-label">Create Account</span>
      </button>

    </form>

    <div class="divider">or sign up with</div>

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

    <div class="signin">
      Already have an account? <a href="index.php?page=login">Log In</a>
    </div>

  </div><!-- /left -->


  <!-- RIGHT — Brand Panel -->
  <div class="right">

    <svg class="right-bg" viewBox="0 0 500 640" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
      <circle cx="400" cy="80"  r="180" fill="rgba(255,255,255,.07)"/>
      <circle cx="80"  cy="500" r="140" fill="rgba(255,255,255,.06)"/>
      <path fill="rgba(255,255,255,.05)" d="M0,380 C80,340 180,400 280,370 C380,340 440,380 500,360 L500,640 L0,640 Z"/>
    </svg>

    <div class="emblem">
      <img src="/public/assets/pictures/chmsulogo.jpg" alt="CHMSU Logo"/>
    </div>

    <h2>UNIFY</h2>
    <p>Join the platform and manage your clubs and activities.</p>

    <div class="badge">
      <div class="badge-dot"></div>
      <span>Carlos Hilado Memorial State University</span>
    </div>

    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-text">
          <strong>Create your account</strong>
          Fill in your details to register
        </div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-text">
          <strong>Explore &amp; join a club</strong>
          Apply to existing clubs
        </div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-text">
          <strong>Or propose your own club</strong>
          Become a student leader
        </div>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <div class="step-text">
          <strong>Get accepted &amp; start</strong>
          Manage events, members &amp; more
        </div>
      </div>
    </div>

  </div><!-- /right -->

</div><!-- /wrapper -->

<script src="/public/assets/javascripts/signup.js"></script>
</body>
</html>