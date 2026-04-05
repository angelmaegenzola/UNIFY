<?php

class LoginPage {

    private string $title;
    private string $subtitle;
    private string $error;
    private string $username;

    public function __construct(
        string $title    = 'UNIFY',
        string $subtitle = 'Sign in to your account to continue'
    ) {
        $this->title    = $title;
        $this->subtitle = $subtitle;
        $this->error    = '';
        $this->username = '';
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->username = htmlspecialchars(trim($_POST['username'] ?? ''));
            $password       = $_POST['password'] ?? '';
            if (empty($this->username) || empty($password)) {
                $this->error = 'Please fill in all fields.';
            } else {
                header('Location: dashboard.php');
                exit;
            }
        }
    }

    public function render(): void {
        $this->handleRequest();
        $error    = $this->error;
        $username = $this->username;
        $title    = $this->title;
        $subtitle = $this->subtitle;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login — <?= htmlspecialchars($title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="./assets/css/login.css"/>
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

        <h1>Welcome back</h1>
        <p class="sub"><?= htmlspecialchars($subtitle) ?></p>

        <?php if ($error): ?>
            <div class="err"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="field">
                <!-- user icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <input type="text" id="username" name="username"
                       placeholder="Username"
                       value="<?= htmlspecialchars($username) ?>"
                       autocomplete="username"/>
            </div>

            <div class="field">
                <!-- lock icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" id="password" name="password"
                       placeholder="Password"
                       autocomplete="current-password"/>
            </div>

            <button class="btn-login" type="submit">Log In</button>

        </form>

        <div class="divider">or sign in with</div>

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

        <div class="signup">
            Don't have an account? <a href="#">Sign Up</a>
        </div>

    </div>

    <!-- ── RIGHT: brand panel ── -->
    <div class="right">

        <!-- Decorative wave SVG -->
        <svg class="right-bg" viewBox="0 0 500 560" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
            <circle cx="400" cy="80"  r="180" fill="rgba(255,255,255,.07)"/>
            <circle cx="80"  cy="420" r="140" fill="rgba(255,255,255,.06)"/>
            <path fill="rgba(255,255,255,.05)" d="M0,320 C80,280 180,340 280,310 C380,280 440,320 500,300 L500,560 L0,560 Z"/>
            <path fill="rgba(255,255,255,.04)" d="M0,380 C100,350 200,400 310,370 C400,345 460,380 500,360 L500,560 L0,560 Z"/>
        </svg>

        <div class="emblem">
            <img src="./assets/pictures/logo.png" alt="Logo"
                 style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;"/>
        </div>

        <h2><?= htmlspecialchars($title) ?></h2>
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
                <div class="stat-lbl">Events</div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
<?php
    }
}

$page = new LoginPage();
$page->render();