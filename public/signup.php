<?php

class SignupPage {

    private string $title;
    private string $subtitle;
    private array  $errors;
    private array  $fields;

    public function __construct(
        string $title    = 'UNIFY',
        string $subtitle = 'Create your account to get started'
    ) {
        $this->title    = $title;
        $this->subtitle = $subtitle;
        $this->errors   = [];
        $this->fields   = [];
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->fields['first_name'] = htmlspecialchars(trim($_POST['first_name'] ?? ''));
            $this->fields['last_name']  = htmlspecialchars(trim($_POST['last_name']  ?? ''));
            $this->fields['email']      = htmlspecialchars(trim($_POST['email']      ?? ''));
            $this->fields['username']   = htmlspecialchars(trim($_POST['username']   ?? ''));
            $password                   = $_POST['password']         ?? '';
            $confirm                    = $_POST['confirm_password'] ?? '';

            if (empty($this->fields['first_name']))  $this->errors[] = 'First name is required.';
            if (empty($this->fields['last_name']))   $this->errors[] = 'Last name is required.';
            if (empty($this->fields['email']))       $this->errors[] = 'Email address is required.';
            elseif (!filter_var($this->fields['email'], FILTER_VALIDATE_EMAIL))
                                                     $this->errors[] = 'Please enter a valid email address.';
            if (empty($this->fields['username']))    $this->errors[] = 'Username is required.';
            if (empty($password))                    $this->errors[] = 'Password is required.';
            elseif (strlen($password) < 8)           $this->errors[] = 'Password must be at least 8 characters.';
            if ($password !== $confirm)              $this->errors[] = 'Passwords do not match.';

            if (empty($this->errors)) {
                header('Location: login.php?registered=1');
                exit;
            }
        }
    }

    public function render(): void {
        $this->handleRequest();
        $errors   = $this->errors;
        $fields   = $this->fields;
        $title    = $this->title;
        $subtitle = $this->subtitle;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sign Up — <?= htmlspecialchars($title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="./assets/css/signup.css"/>
</head>
<body>

<div class="wrapper">

    <!-- ── LEFT: form ── -->
    <div class="left">

        <!-- Logo -->
        <div class="logo-row">
            <div class="logo-circle">
                <img src="./assets/pictures/logo.png" alt="Logo"
                     style="width:38px;height:38px;border-radius:50%;object-fit:cover;display:block;"/>
            </div>
            <div class="logo-text">
                Club Management System
                <span>Carlos Hilado Memorial State University</span>
            </div>
        </div>

        <h1>Create an account</h1>
        <p class="sub"><?= htmlspecialchars($subtitle) ?></p>

        <?php if (!empty($errors)): ?>
            <div class="err">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <!-- Name row -->
            <div class="row-2">
                <div class="field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" name="first_name" placeholder="First Name"
                           value="<?= htmlspecialchars($fields['first_name'] ?? '') ?>"
                           autocomplete="given-name"/>
                </div>
                <div class="field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" name="last_name" placeholder="Last Name"
                           value="<?= htmlspecialchars($fields['last_name'] ?? '') ?>"
                           autocomplete="family-name"/>
                </div>
            </div>

            <!-- Email -->
            <div class="field">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
                <input type="email" name="email" placeholder="Email Address"
                       value="<?= htmlspecialchars($fields['email'] ?? '') ?>"
                       autocomplete="email"/>
            </div>

            <!-- Username -->
            <div class="field">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="4"/><path d="M12 2a10 10 0 1 0 10 10"/>
                    <path d="M12 8v1m0 6v1M8 12H7m10 0h-1"/>
                </svg>
                <input type="text" name="username" placeholder="Username"
                       value="<?= htmlspecialchars($fields['username'] ?? '') ?>"
                       autocomplete="username"/>
            </div>

            <!-- Password -->
            <div class="field">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" id="password" name="password"
                       placeholder="Password (min. 8 characters)"
                       autocomplete="new-password"
                       oninput="updateStrength(this.value)"/>
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="field">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" name="confirm_password"
                       placeholder="Confirm Password"
                       autocomplete="new-password"/>
            </div>

            <!-- Terms -->
            <div class="terms">
                <input type="checkbox" id="terms" name="terms" required/>
                <label for="terms">
                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </label>
            </div>

            <button class="btn-signup" type="submit">Create Account</button>

        </form>

        <div class="divider">or sign up with</div>

        <div class="sso">
            <a href="#" class="sso-btn" onclick="return false;">
                <svg width="16" height="16" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Google
            </a>
            <a href="#" class="sso-btn" onclick="return false;">
                <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#1877F2" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.883v2.271h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                </svg>
                Facebook
            </a>
        </div>

        <div class="signin">
            Already have an account? <a href="login.php">Log In</a>
        </div>

    </div>

    <!-- ── RIGHT: brand panel ── -->
    <div class="right">

        <svg class="right-bg" viewBox="0 0 500 620" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
            <circle cx="400" cy="80"  r="180" fill="rgba(255,255,255,.07)"/>
            <circle cx="80"  cy="480" r="140" fill="rgba(255,255,255,.06)"/>
            <path fill="rgba(255,255,255,.05)" d="M0,360 C80,320 180,380 280,350 C380,320 440,360 500,340 L500,620 L0,620 Z"/>
            <path fill="rgba(255,255,255,.04)" d="M0,420 C100,390 200,440 310,410 C400,385 460,420 500,400 L500,620 L0,620 Z"/>
        </svg>

        <div class="emblem">
            <img src="./assets/pictures/logo.png" alt="Logo"
                 style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;"/>
        </div>

        <h2><?= htmlspecialchars($title) ?></h2>
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
                    <strong>Join or create a club</strong>
                    Find your organization and connect
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">
                    <strong>Start collaborating</strong>
                    Manage events, members &amp; finances
                </div>
            </div>
        </div>

    </div>

</div>

<script>
function updateStrength(val) {
    const fill = document.getElementById('strengthFill');
    let score = 0;
    if (val.length >= 8)               score++;
    if (/[A-Z]/.test(val))             score++;
    if (/[0-9]/.test(val))             score++;
    if (/[^A-Za-z0-9]/.test(val))      score++;

    const pct    = ['0%','25%','50%','75%','100%'][score];
    const colors = ['#eee','#ef4444','#f97316','#eab308','#22c55e'];
    fill.style.width      = pct;
    fill.style.background = colors[score];
}
</script>

</body>
</html>
<?php
    }
}

$page = new SignupPage();
$page->render();