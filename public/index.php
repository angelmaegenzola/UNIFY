<?php
$page = $_GET['page'] ?? 'landing-page';

$allowed_pages = [
    'landing-page',
    'login',
    'logout',
    'signup',
    'dashboard',
    'clubpage',
    'events',
    'members',
    'profile_handler',
    'adminprofile',
    'finance',
    'reports',
    'explore',
    'apply_handler',
    'already_member_handler',
    'status',
    'studentevents',
    'studentprofile',
    'studentprofile_save',
    'myclubs',
    'studenthome',
    'officer_dashboard',
    'club_request',
    'club_request_handler',
    'notifications',
    'officer_messages',
    'profile',
    'club_messages',
    'student_messages',
    'officer_explore',
    'officer_members',
    'officer_home',
    'officer_events',
    'verify_2fa',
    'setup_2fa',
    'upload_avatar',
    'studentevents_ajax',
];

if (in_array($page, $allowed_pages)) {
    if ($page === 'club_messages') {
        require_once __DIR__ . '/../app/controllers/officer_messages_controller.php';
    } elseif ($page === 'setup_2fa') {
        require_once __DIR__ . '/../app/controllers/setup_2fa_controller.php';
    } elseif ($page === 'studentprofile_save') {
        require_once __DIR__ . '/../app/controllers/studentprofile_save_controller.php';
    } elseif ($page === 'upload_avatar') {
        require_once __DIR__ . '/../app/controllers/upload_avatar_controller.php';
    } elseif ($page === 'studentevents_ajax') {
        require_once __DIR__ . '/../app/controllers/studentevents_ajax_controller.php';
    } else {
        require_once __DIR__ . '/../app/views/' . $page . '.php';
    }
} else {
    echo "404 - Page not found";
}