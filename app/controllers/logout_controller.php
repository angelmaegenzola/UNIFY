<?php
// ============================================================
//  UNIFY — Logout
//  app/views/logout.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy everything
$_SESSION = [];
session_destroy();

// Redirect to landing page
header('Location: index.php?page=landing-page');
exit;