<?php
// ============================================================
//  UNIFY — Landing Page
//  app/views/landing-page.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: index.php?page=dashboard');
    } else {
        header('Location: index.php?page=explore');
    }
    exit;
}
?>
