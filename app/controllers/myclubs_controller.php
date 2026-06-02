<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }
$user_id = (int) $_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['first_name'] ?? 'Student');
$last_name  = htmlspecialchars($_SESSION['last_name']  ?? '');
$full_name  = trim($first_name . ' ' . $last_name);
$avatar     = strtoupper(substr($first_name, 0, 1));

require_once __DIR__ . '/../../config/db.php';


// Membership guard — redirect non-members to explore
$mem_check = $conn->prepare("SELECT id FROM members WHERE user_id = ? AND status = 'active' LIMIT 1");
$mem_check->bind_param('i', $user_id); $mem_check->execute(); $mem_check->store_result();
$has_club = $mem_check->num_rows > 0; $mem_check->close();
if (!$has_club) { $conn->close(); header('Location: index.php?page=explore'); exit; }

// Fetch member role (highest role across all clubs)
$role_stmt = $conn->prepare(
    "SELECT role FROM members
     WHERE user_id = ? AND status = 'active'
     ORDER BY FIELD(role,'president','vice president','officer','member')
     LIMIT 1"
);
$role_stmt->bind_param('i', $user_id); $role_stmt->execute();
$my_role = $role_stmt->get_result()->fetch_assoc()['role'] ?? 'member'; $role_stmt->close();

// Profile picture
$picStmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->execute([$user_id]);
$picFile    = $picStmt->fetchColumn();
$avatar_url = $picFile
    ? '/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';

// Officer check (for sidebar MANAGEMENT link)
$is_officer = in_array($my_role, ['president', 'vice president', 'officer', 'lead']);


// ============================================================
//  AJAX / POST ACTIONS
//  JS posts back to this same page URL with action=load|leave|rsvp
// ============================================================
$action = $_POST['action'] ?? '';

if ($action !== '') {
    header('Content-Type: application/json');

    switch ($action) {

        // ── Load all clubs for this user ──────────────────
        case 'load':
            $clubs = get_my_clubs($conn, $user_id);

            foreach ($clubs as &$club) {
                $cid              = (int) $club['club_id'];
                $club['officers']   = get_club_officers($conn, $cid);
                $club['clubEvents'] = get_club_events($conn, $cid, $user_id);
            }

            echo json_encode(['success' => true, 'clubs' => $clubs]);
            break;

        // ── Leave a club ──────────────────────────────────
        case 'leave':
            $club_id = (int) ($_POST['club_id'] ?? 0);
            if ($club_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid club.']);
                break;
            }
            $ok = leave_club($conn, $user_id, $club_id);
            echo json_encode($ok
                ? ['success' => true,  'message' => 'You have left the club.']
                : ['success' => false, 'message' => 'Membership not found.']
            );
            break;

        // ── Toggle RSVP ───────────────────────────────────
        case 'rsvp':
            $event_id = (int) ($_POST['event_id'] ?? 0);
            if ($event_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid event.']);
                break;
            }
            $result = toggle_rsvp($conn, $user_id, $event_id);
            echo json_encode(['success' => true] + $result);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

    $conn->close();
    exit;
}

// ============================================================
//  NORMAL PAGE RENDER
// ============================================================
$conn->close();
?>
