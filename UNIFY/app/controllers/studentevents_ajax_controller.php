<?php
// ============================================================
//  UNIFY — Student Events AJAX Handler
//  app/controllers/studentevents_ajax_controller.php
//
//  Actions:
//    toggle_reminder  → set / remove a personal reminder
//    submit_feedback  → save star rating + optional review
//    get_feedback     → fetch user's own feedback for an event
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$user_id  = (int) $_SESSION['user_id'];
$action   = trim($_POST['action'] ?? $_GET['action'] ?? '');
$event_id = (int) ($_POST['event_id'] ?? $_GET['event_id'] ?? 0);

if ($event_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid event.']);
    exit;
}

try {
    switch ($action) {

        // ── Toggle Reminder ──────────────────────────────────
        case 'toggle_reminder': {
            $chk = $conn->prepare(
                "SELECT id FROM event_reminders WHERE user_id = ? AND event_id = ? LIMIT 1"
            );
            $chk->bind_param('ii', $user_id, $event_id);
            $chk->execute();
            $existing = $chk->get_result()->fetch_assoc();
            $chk->close();

            if ($existing) {
                $del = $conn->prepare(
                    "DELETE FROM event_reminders WHERE user_id = ? AND event_id = ?"
                );
                $del->bind_param('ii', $user_id, $event_id);
                $del->execute();
                $del->close();
                echo json_encode([
                    'success'  => true,
                    'reminded' => false,
                    'message'  => 'Reminder removed.',
                ]);
            } else {
                $ins = $conn->prepare(
                    "INSERT INTO event_reminders (user_id, event_id) VALUES (?, ?)"
                );
                $ins->bind_param('ii', $user_id, $event_id);
                $ins->execute();
                $ins->close();
                echo json_encode([
                    'success'  => true,
                    'reminded' => true,
                    'message'  => "Reminder set! You'll be notified before the event.",
                ]);
            }
            break;
        }

        // ── Submit / Update Feedback ─────────────────────────
        case 'submit_feedback': {
            $rating = (int) ($_POST['rating'] ?? 0);
            $review = trim($_POST['review'] ?? '');

            if ($rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Please select a star rating (1–5).']);
                break;
            }

            // Verify event is completed and user is an active member of that club
            $ev_chk = $conn->prepare("
                SELECT e.id FROM events e
                JOIN members m ON m.club_id = e.club_id
                WHERE e.id = ? AND e.status = 'completed'
                  AND m.user_id = ? AND m.status = 'active'
                LIMIT 1
            ");
            $ev_chk->bind_param('ii', $event_id, $user_id);
            $ev_chk->execute();
            $valid = $ev_chk->get_result()->fetch_assoc();
            $ev_chk->close();

            if (!$valid) {
                echo json_encode(['success' => false, 'message' => 'Feedback only allowed for completed events you belong to.']);
                break;
            }

            // Upsert
            $ups = $conn->prepare("
                INSERT INTO event_feedback (user_id, event_id, rating, review)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    rating     = VALUES(rating),
                    review     = VALUES(review),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $ups->bind_param('iiis', $user_id, $event_id, $rating, $review);
            $ups->execute();
            $ups->close();

            // Return updated community average
            $avg_row = $conn->query(
                "SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total
                 FROM event_feedback WHERE event_id = $event_id"
            )->fetch_assoc();

            echo json_encode([
                'success'    => true,
                'message'    => 'Thank you for your feedback!',
                'avg_rating' => (float) ($avg_row['avg_rating'] ?? 0),
                'total'      => (int)   ($avg_row['total']      ?? 0),
            ]);
            break;
        }

        // ── Get user's own feedback ──────────────────────────
        case 'get_feedback': {
            $row = $conn->query(
                "SELECT rating, review FROM event_feedback
                 WHERE user_id = $user_id AND event_id = $event_id LIMIT 1"
            )->fetch_assoc();

            $avg_row = $conn->query(
                "SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total
                 FROM event_feedback WHERE event_id = $event_id"
            )->fetch_assoc();

            echo json_encode([
                'success'    => true,
                'rating'     => $row ? (int)    $row['rating'] : 0,
                'review'     => $row ? (string) $row['review'] : '',
                'avg_rating' => (float) ($avg_row['avg_rating'] ?? 0),
                'total'      => (int)   ($avg_row['total']      ?? 0),
            ]);
            break;
        }

        // ── Respond to Assignment (accept / decline) ─────────
        case 'respond_assignment': {
            $response = trim($_POST['response'] ?? ''); // 'accepted' or 'declined'
            if (!in_array($response, ['accepted', 'declined'], true)) {
                echo json_encode(['success' => false, 'message' => 'Invalid response.']);
                break;
            }

            // Verify the assignment belongs to this user
            $chk = $conn->prepare("
                SELECT ea.event_id, ea.role_label, e.name AS event_name,
                       c.name AS club_name, c.id AS club_id,
                       ea.assigned_by
                FROM event_assignees ea
                JOIN events e ON e.id = ea.event_id
                JOIN clubs  c ON c.id = e.club_id
                WHERE ea.event_id = ? AND ea.user_id = ?
                LIMIT 1
            ");
            $chk->bind_param('ii', $event_id, $user_id);
            $chk->execute();
            $assignment = $chk->get_result()->fetch_assoc();
            $chk->close();

            if (!$assignment) {
                echo json_encode(['success' => false, 'message' => 'Assignment not found.']);
                break;
            }

            // Update status
            $upd = $conn->prepare("
                UPDATE event_assignees SET status = ? WHERE event_id = ? AND user_id = ?
            ");
            $upd->bind_param('sii', $response, $event_id, $user_id);
            $upd->execute();
            $upd->close();

            // Notify the officer who assigned them
            $myName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
            $assignedBy = (int) $assignment['assigned_by'];
            $roleLabel  = $assignment['role_label'] ?: 'volunteer';
            $evtName    = $assignment['event_name'];
            $clbName    = $assignment['club_name'];

            if ($response === 'accepted') {
                $nType  = 'assignment_accepted';
                $nTitle = '✅ Assignment Accepted';
                $nMsg   = $myName . ' accepted the role of ' . $roleLabel . ' for "' . $evtName . '" (' . $clbName . ').';
            } else {
                $nType  = 'assignment_declined';
                $nTitle = '❌ Assignment Declined';
                $nMsg   = $myName . ' declined the role of ' . $roleLabel . ' for "' . $evtName . '" (' . $clbName . ').';
            }

            // Notify all officers of that club
            $offStmt = $conn->prepare("
                SELECT user_id FROM members
                WHERE club_id = ? AND status = 'active'
                  AND role IN ('president','vice president','officer','lead')
            ");
            $offStmt->bind_param('i', $assignment['club_id']);
            $offStmt->execute();
            $officers = $offStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $offStmt->close();

            $notifLink = 'index.php?page=officer_events';
            $nIns = $conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, link)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($officers as $off) {
                $uid = (int)$off['user_id'];
                $nIns->bind_param('issss', $uid, $nType, $nTitle, $nMsg, $notifLink);
                $nIns->execute();
            }
            $nIns->close();

            echo json_encode([
                'success'  => true,
                'response' => $response,
                'message'  => $response === 'accepted'
                    ? 'You have accepted this assignment!'
                    : 'You have declined this assignment.',
            ]);
            break;
        }

        // ── Get my assignments ────────────────────────────────
        case 'get_my_assignments': {
            $stmt = $conn->prepare("
                SELECT ea.event_id, ea.role_label, ea.status AS assignment_status,
                       e.name AS event_name, e.event_date, e.start_time, e.end_time,
                       e.location, e.status AS event_status,
                       c.name AS club_name, c.id AS club_id, c.logo_path AS club_logo, c.acronym
                FROM event_assignees ea
                JOIN events e ON e.id = ea.event_id
                JOIN clubs  c ON c.id = e.club_id
                WHERE ea.user_id = ?
                  AND e.status IN ('upcoming','ongoing','completed')
                ORDER BY e.event_date ASC, e.start_time ASC
            ");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            echo json_encode(['success' => true, 'assignments' => $assignments]);
            break;
        }

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
exit;
