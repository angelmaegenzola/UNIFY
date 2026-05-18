  <?php
  // Model: page-load queries for events

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';

  $eventsStmt = $pdo->query("
    SELECT e.*, c.name AS club_name, c.acronym AS club_acronym
    FROM events e
    JOIN clubs c ON c.id = e.club_id
    ORDER BY e.event_date ASC, e.start_time ASC
  ");
  $dbEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
  $clubs = $pdo->query("SELECT id, name, acronym FROM clubs WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
