<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/../app/controllers/officer_events_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Events</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/unify/assets/css/officer_events.css" />
  <link rel="stylesheet" href="/unify/assets/css/transitions.css" />
</head>

<body>
  <div class="app">

    <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="/unify/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
        <div class="brand-text">
          <div class="brand-name">UNIFY</div>
          <div class="brand-tagline">Club Management System</div>
        </div>
      </div>

      <div class="sidebar-club-badge">
        <div class="club-badge-icon">
          <?php if (!empty($officerClub['logo_path'])): ?>
            <img src="<?= htmlspecialchars($officerClub['logo_path']) ?>" alt="Club Logo"
              style="width:34px;height:34px;border-radius:9px;object-fit:cover;display:block;" />
          <?php else: ?>
            <?= htmlspecialchars($clubInitial) ?>
          <?php endif; ?>
        </div>
        <div class="club-badge-info">
          <div class="club-badge-name"><?= htmlspecialchars($clubName) ?></div>
          <div class="club-badge-role"><?= ucfirst(htmlspecialchars($officerRole)) ?></div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-label">MY CLUB</div>
        <a href="index.php?page=officer_dashboard" class="nav-item">
          <i class="fas fa-house"></i><span>Dashboard</span>
        </a>
        <a href="index.php?page=officer_members" class="nav-item ">
          <i class="fas fa-users"></i><span>Members</span>
        </a>
        <a href="index.php?page=officer_events" class="nav-item active">
          <i class="fas fa-calendar-days"></i><span>Events</span>
        </a>
        <a href="index.php?page=officer_messages" class="nav-item">
          <i class="fas fa-comments"></i><span>Club Chat</span>
          <?php if (!empty($unreadChat) && $unreadChat > 0): ?>
            <span class="nav-chat-badge"><?= $unreadChat ?></span>
          <?php endif; ?>
        </a>
        <div class="nav-section-label">GENERAL</div>
        <a href="index.php?page=officer_explore" class="nav-item">
          <i class="fas fa-compass"></i><span>Explore Clubs</span>
        </a>
        <a href="index.php?page=officer_home" class="nav-item">
          <i class="fas fa-house-chimney"></i><span>Student Home</span>
        </a>
      </nav>

      <div class="sidebar-bottom">
        <div class="sidebar-profile">
          <div class="profile-avatar-wrap">
            <?php if ($avatar_url): ?>
              <img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" id="of-sidebar-avatar" />
            <?php else: ?>
              <span class="profile-avatar-fallback" id="of-sidebar-avatar"><?= htmlspecialchars($userInit) ?></span>
            <?php endif; ?>
            <span class="profile-online-dot"></span>
          </div>
          <a href="index.php?page=profile" class="profile-link">
            <div class="profile-info">
              <span class="profile-name"><?= htmlspecialchars($userName) ?></span>
              <span class="profile-role"><?= ucfirst(htmlspecialchars($officerRole)) ?></span>
            </div>
          </a>
          <a href="index.php?page=logout" class="sidebar-logout" title="Logout">
            <i class="fas fa-arrow-right-from-bracket"></i>
          </a>
        </div>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <header class="topbar">
        <div class="topbar-left">
          <span class="topbar-page-title">Events</span>
          <span class="topbar-date" id="topbarDate"></span>
        </div>
        <div class="topbar-center">
          <div class="topbar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="eventSearchInput" placeholder="Search events…"
              oninput="OE.filterEvents(this.value)" />
          </div>
        </div>
        <div class="topbar-actions">
          <button class="icon-btn" id="notifBtn" title="Notifications" onclick="OE.toggleNotif()">
            <i class="fas fa-bell"></i>
            <?php if ($unreadNotifs > 0): ?>
              <span class="notif-badge"><?= $unreadNotifs ?></span>
            <?php endif; ?>
          </button>
          <a href="index.php?page=profile" class="topbar-profile" style="text-decoration:none;cursor:pointer;">
            <div class="topbar-avatar"><?php if (!empty($avatar_url)): ?><img src="<?= $avatar_url ?>" alt="Avatar"
                  style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" /><?php else: ?><?= htmlspecialchars($userInit) ?><?php endif; ?>
            </div>
            <div class="topbar-profile-info">
              <span class="tp-name"><?= htmlspecialchars($userName) ?></span>
              <span class="tp-role"><?= ucfirst(htmlspecialchars($officerRole)) ?></span>
            </div>
            <i class="fas fa-chevron-down tp-caret"></i>
          </a>
        </div>
      </header>

      <div class="content">
        <!-- TABS -->
        <div class="tabs-bar">
          <button class="tab-btn" onclick="OE.switchTab('overview')" data-tab="overview">Overview</button>
          <button class="tab-btn active" onclick="OE.switchTab('events')" data-tab="events">Events</button>
          <button class="tab-btn" onclick="OE.switchTab('members')" data-tab="members">Members</button>
          <button class="tab-btn" onclick="OE.switchTab('reports')" data-tab="reports">Reports</button>
          
        </div>

        <!-- ══════════════════════════════════════════
           TAB: EVENTS
      ══════════════════════════════════════════ -->
        <div id="tab-events" class="tab-pane active">
          <div class="events-scroll" id="eventsScroll">

            <!-- Section header with Add Event button aligned -->
            <div class="section-header-row">
              <div class="section-label">UPCOMING &amp; ONGOING EVENTS</div>
              <button class="btn-add-event-inline" onclick="OE.openAddModal()">
                <i class="fas fa-plus"></i> Add Event
              </button>
            </div>

            <div class="event-group" id="group-upcoming">
              <?php if (empty($upcomingOngoing)): ?>
                <div class="empty-state">
                  <i class="fas fa-calendar-plus"></i>
                  <span>No upcoming events yet. Click <strong>Add Event</strong> to create one.</span>
                </div>
              <?php else: ?>
                <?php foreach ($upcomingOngoing as $e):
                  $today = (date('Y-m-d') === $e['event_date']);
                  $dateLabel = $today
                    ? 'Today — ' . date('M j, Y', strtotime($e['event_date']))
                    : date('M j, Y', strtotime($e['event_date']));
                  $st = $e['start_time'] ? date('g:i A', strtotime($e['start_time'])) : '';
                  $et = $e['end_time'] ? date('g:i A', strtotime($e['end_time'])) : '';
                  $statusStr = $e['status'];
                  $isOngoing = ($statusStr === 'ongoing');
                  ?>
                  <div class="event-card-row" id="ec-<?= $e['id'] ?>">
                    <span class="evt-dot dot-<?= $statusStr ?>"></span>
                    <div class="evt-info">
                      <div class="evt-title">
                        <?= htmlspecialchars($e['title']) ?>
                        <?php if (!empty($e['is_mandatory'])): ?>
                          <span class="badge-pill pill-mandatory"><i class="fas fa-star" style="font-size:8px;"></i>
                            mandatory</span>
                        <?php endif; ?>
                      </div>
                      <div class="evt-meta">
                        <?= $dateLabel ?>
                        <?= $st ? " · $st" : '' ?>
                        <?= ($st && $et) ? " – $et" : '' ?>
                        <?= !empty($e['venue']) ? ' · ' . htmlspecialchars($e['venue']) : '' ?>
                      </div>
                    </div>
                    <div class="evt-actions">
                      <span
                        class="status-chip chip-<?= $statusStr ?>"><?= ucfirst(str_replace('_', ' ', $statusStr)) ?></span>
                      <?php if ($isOngoing): ?>
                        <button class="btn-evt btn-scan"
                          onclick="OE.openScanner(<?= $e['id'] ?>, <?= htmlspecialchars(json_encode($e['title'])) ?>)"
                          title="Scan QR Attendance">
                          <i class="fas fa-qrcode"></i> Scan
                        </button>
                      <?php endif; ?>
                      <button class="btn-evt btn-outline" onclick="OE.openEditModal(<?= $e['id'] ?>)">
                        <?= $isOngoing ? 'Details' : 'View details' ?>
                      </button>
                      <button class="btn-icon-sm" onclick="OE.openCtx(event, <?= $e['id'] ?>)" title="More options">
                        <i class="fas fa-ellipsis"></i>
                      </button>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div><!-- #group-upcoming -->

            <!-- Completed Events -->
            <div class="section-header-row" style="margin-top:28px;">
              <div class="section-label">COMPLETED EVENTS</div>
            </div>
            <div class="event-group" id="group-completed">
              <?php if (empty($completedEvents)): ?>
                <div class="empty-state">
                  <i class="fas fa-calendar-check"></i>
                  <span>No completed events yet</span>
                </div>
              <?php else: ?>
                <?php foreach ($completedEvents as $e): ?>
                  <div class="event-card-row" id="ec-<?= $e['id'] ?>">
                    <span class="evt-dot dot-completed"></span>
                    <div class="evt-info">
                      <div class="evt-title">
                        <?= htmlspecialchars($e['title']) ?>
                        <?php if (!empty($e['is_mandatory'])): ?>
                          <span class="badge-pill pill-mandatory"><i class="fas fa-star" style="font-size:8px;"></i>
                            mandatory</span>
                        <?php endif; ?>
                      </div>
                      <div class="evt-meta">
                        <?= date('M j, Y', strtotime($e['event_date'])) ?> · Attendance: <?= $e['att_count'] ?> /
                        <?= $memberCount ?> members
                      </div>
                    </div>
                    <div class="evt-actions">
                      <span class="status-chip chip-completed">Completed</span>
                      <button class="btn-evt btn-outline"
                        onclick="OE.openAttView(<?= $e['id'] ?>, <?= htmlspecialchars(json_encode($e['title'])) ?>)">
                        <i class="fas fa-chart-bar"></i> View attendance
                      </button>
                      <button class="btn-icon-sm" onclick="OE.openCtx(event, <?= $e['id'] ?>)" title="More options">
                        <i class="fas fa-ellipsis"></i>
                      </button>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div><!-- #group-completed -->

            <!-- ── Attendance Scanner (inline, appears below list) ── -->
            <div id="scannerSection" style="display:none;">
              <div class="section-header-row" style="margin-top:28px;">
                <div class="section-label" id="scannerLabel">ATTENDANCE SCANNER</div>
                <button class="btn-evt btn-outline" style="font-size:11px;padding:5px 12px;"
                  onclick="OE.closeScanner()">
                  <i class="fas fa-times"></i> Close scanner
                </button>
              </div>
              <div class="scanner-wrap">

                <!-- QR card -->
                <div class="scanner-qr-card">
                  <div class="sqc-header">
                    <div>
                      <div class="sqc-title"><i class="fas fa-qrcode"></i> QR Scanner</div>
                      <div class="sqc-hint">Point camera at member's QR code or type LRN manually</div>
                    </div>
                    <div class="sqc-header-btns">
                      <button class="btn-camera" id="btnCamera" onclick="OE.toggleCamera()">
                        <i class="fas fa-camera"></i> Open camera
                      </button>
                      <button class="btn-fullscreen" onclick="OE.openFullscreen()" title="Fullscreen">
                        <i class="fas fa-expand"></i>
                      </button>
                    </div>
                  </div>

                  <div class="sqc-viewport" id="sqcViewport">
                    <div class="sqc-corners">
                      <span class="corner tl"></span><span class="corner tr"></span>
                      <span class="corner bl"></span><span class="corner br"></span>
                    </div>
                    <video id="scanVideo" playsinline muted style="display:none;"></video>
                    <canvas id="scanCanvas" style="display:none;"></canvas>
                    <div class="sqc-placeholder" id="sqcPlaceholder">
                      <div class="scan-line"></div>
                      <i class="fas fa-camera" style="font-size:24px;opacity:.3;margin-bottom:4px;"></i>
                      <span>Camera preview appears here</span>
                    </div>
                  </div>

                  <div class="sqc-stats">
                    <div class="sqc-stat">
                      <span class="sqc-num green" id="statPresent">0</span>
                      <span class="sqc-lbl">Present</span>
                    </div>
                    <div class="sqc-stat">
                      <span class="sqc-num red" id="statAbsent">0</span>
                      <span class="sqc-lbl">Absent</span>
                    </div>
                    <div class="sqc-stat">
                      <span class="sqc-num" id="statTotal">0</span>
                      <span class="sqc-lbl">Total</span>
                    </div>
                  </div>

                  <p class="sqc-note">Scanner auto-detects QR codes. No button needed.</p>

                  <div class="sqc-manual">
                    <input type="text" id="manualLrn" placeholder="Type LRN / Student ID manually…"
                      onkeydown="if(event.key==='Enter') OE.manualScan()" />
                    <button onclick="OE.manualScan()" title="Submit">
                      <i class="fas fa-arrow-right"></i>
                    </button>
                  </div>
                </div><!-- .scanner-qr-card -->

                <!-- Attendance list card -->
                <div class="scanner-list-card">
                  <div class="slc-header">
                    Member Attendance List
                  </div>
                  <div class="slc-tabs">
                    <button class="slc-tab active" onclick="OE.setAttTab('all',this)">All</button>
                    <button class="slc-tab" onclick="OE.setAttTab('present',this)">Present</button>
                    <button class="slc-tab" onclick="OE.setAttTab('absent',this)">Absent</button>
                  </div>
                  <div class="slc-body" id="attListBody">
                    <div class="slc-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
                  </div>
                  <div class="slc-footer">Live — updates automatically as QR codes are scanned.</div>
                </div><!-- .scanner-list-card -->

              </div><!-- .scanner-wrap -->
            </div><!-- #scannerSection -->

            <!-- Scroll-to-scanner arrow -->
            <div class="scroll-arrow" id="scrollArrow" style="display:none;" onclick="OE.scrollToScanner()">
              <i class="fas fa-arrow-down"></i>
            </div>

            <!-- ── Incoming Partnership Requests ── -->
            <div id="incomingRequestsSection" style="display:none;">
              <div class="section-header-row" style="margin-top:28px;">
                <div class="section-label">
                  INCOMING PARTNERSHIP REQUESTS
                  <span class="collab-tab-badge" id="collabTabBadge" style="display:none;margin-left:6px;"></span>
                </div>
              </div>
              <div class="event-group">
                <div id="collabIncomingList"></div>
              </div>
            </div>

            <!-- ── Partnered Events ── -->
            <div class="section-header-row" style="margin-top:28px;">
              <div class="section-label">PARTNERED EVENTS</div>
            </div>
            <div class="event-group" id="group-partnered">
              <div id="partneredEventsContainer">
                <div class="empty-state">
                  <i class="fas fa-handshake"></i>
                  <span>No partnered events yet. When another club accepts your collaboration request, their events appear here.</span>
                </div>
              </div>
            </div>

          </div><!-- .events-scroll -->
        </div><!-- #tab-events -->

        <!-- ══════════════════════════════════════════
           TAB: OVERVIEW
      ══════════════════════════════════════════ -->
        <div id="tab-overview" class="tab-pane">
          <div class="overview-scroll">
            <div class="ov-stat-grid">
              <div class="ov-stat-card sc-green">
                <div class="ov-sc-icon"><i class="fas fa-calendar-days"></i></div>
                <div class="ov-sc-val"><?= $totalEvents ?></div>
                <div class="ov-sc-lbl">Total Events</div>
              </div>
              <div class="ov-stat-card sc-blue">
                <div class="ov-sc-icon"><i class="fas fa-clock"></i></div>
                <div class="ov-sc-val"><?= $upcomingCount ?></div>
                <div class="ov-sc-lbl">Upcoming</div>
              </div>
              <div class="ov-stat-card sc-teal">
                <div class="ov-sc-icon"><i class="fas fa-circle-play"></i></div>
                <div class="ov-sc-val"><?= $ongoingCount ?></div>
                <div class="ov-sc-lbl">Ongoing</div>
              </div>
              <div class="ov-stat-card sc-gray">
                <div class="ov-sc-icon"><i class="fas fa-circle-check"></i></div>
                <div class="ov-sc-val"><?= $completedCount ?></div>
                <div class="ov-sc-lbl">Completed</div>
              </div>
              <div class="ov-stat-card sc-gold">
                <div class="ov-sc-icon"><i class="fas fa-users"></i></div>
                <div class="ov-sc-val"><?= $totalAttendance ?></div>
                <div class="ov-sc-lbl">Total Attendees</div>
              </div>
              <div class="ov-stat-card sc-red">
                <div class="ov-sc-icon"><i class="fas fa-ban"></i></div>
                <div class="ov-sc-val"><?= $cancelledCount ?></div>
                <div class="ov-sc-lbl">Cancelled</div>
              </div>
            </div>

            <div class="ov-section-title">Upcoming Events</div>
            <?php if (empty($upcomingOngoing)): ?>
              <div class="ov-empty"><i class="fas fa-calendar"></i> No upcoming events</div>
            <?php else: ?>
              <div class="ov-event-list">
                <?php foreach (array_slice($upcomingOngoing, 0, 5) as $e):
                  $st = $e['start_time'] ? date('g:i A', strtotime($e['start_time'])) : '';
                  ?>
                  <div class="ov-event-item">
                    <div class="ov-event-dot dot-<?= $e['status'] ?>"></div>
                    <div class="ov-event-info">
                      <div class="ov-event-title"><?= htmlspecialchars($e['title']) ?></div>
                      <div class="ov-event-meta">
                        <?= date('M j, Y', strtotime($e['event_date'])) ?>
                        <?= $st ? " · $st" : '' ?>
                        <?= !empty($e['venue']) ? ' · ' . htmlspecialchars($e['venue']) : '' ?>
                      </div>
                    </div>
                    <span
                      class="status-chip chip-<?= $e['status'] ?>"><?= ucfirst(str_replace('_', ' ', $e['status'])) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="ov-section-title" style="margin-top:24px;">Recent Completions</div>
            <?php if (empty($completedEvents)): ?>
              <div class="ov-empty"><i class="fas fa-calendar-check"></i> No completed events</div>
            <?php else: ?>
              <div class="ov-event-list">
                <?php foreach (array_slice($completedEvents, 0, 4) as $e): ?>
                  <div class="ov-event-item">
                    <div class="ov-event-dot dot-completed"></div>
                    <div class="ov-event-info">
                      <div class="ov-event-title"><?= htmlspecialchars($e['title']) ?></div>
                      <div class="ov-event-meta"><?= date('M j, Y', strtotime($e['event_date'])) ?> · <?= $e['att_count'] ?>
                        / <?= $memberCount ?> attended</div>
                    </div>
                    <span class="status-chip chip-completed">Completed</span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div><!-- #tab-overview -->

        <!-- ══════════════════════════════════════════
           TAB: MEMBERS
      ══════════════════════════════════════════ -->
        <div id="tab-members" class="tab-pane">
          <div class="members-scroll">
            <div class="mem-header-row">
              <div class="mem-count-badge">
                <i class="fas fa-users"></i> <?= $memberCount ?> active members
              </div>

            </div>
            <div class="mem-table-wrap">
              <table class="mem-table">
                <thead>
                  <tr>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Course</th>
                    <th>Year / Sec</th>
                    <th>Joined</th>
                  </tr>
                </thead>
                <tbody id="membersTableBody">
                  <?php foreach ($membersList as $m): ?>
                    <tr class="mem-row"
                      data-name="<?= htmlspecialchars(strtolower($m['first_name'] . ' ' . $m['last_name'])) ?>">
                      <td>
                        <div class="mem-cell-user">
                          <div class="mem-avatar"><?= strtoupper(substr($m['first_name'], 0, 1)) ?></div>
                          <div>
                            <div class="mem-name"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></div>
                            <div class="mem-email"><?= htmlspecialchars($m['email'] ?? '') ?></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <?php
                        $roleKey = strtolower(str_replace(' ', '-', $m['role']));
                        ?>
                        <span
                          class="mem-role-chip role-<?= $roleKey ?>"><?= ucfirst(htmlspecialchars($m['role'])) ?></span>
                      </td>
                      <td><?= htmlspecialchars($m['course'] ?? '—') ?></td>
                      <td>
                        <?= htmlspecialchars(($m['year'] ?? '') . (isset($m['section']) && $m['section'] ? '-' . $m['section'] : '')) ?: '—' ?>
                      </td>
                      <td><?= $m['joined_at'] ? date('M j, Y', strtotime($m['joined_at'])) : '—' ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($membersList)): ?>
                    <tr>
                      <td colspan="5" class="mem-empty">No members found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div><!-- #tab-members -->

        <!-- ══════════════════════════════════════════
           TAB: REPORTS
      ══════════════════════════════════════════ -->
        <div id="tab-reports" class="tab-pane">
          <div class="reports-scroll">
            <div class="rpt-section-title">Event Summary</div>
            <div class="rpt-summary-grid">
              <div class="rpt-card">
                <div class="rpt-card-label">Total Events</div>
                <div class="rpt-card-val"><?= $totalEvents ?></div>
              </div>
              <div class="rpt-card">
                <div class="rpt-card-label">Completed</div>
                <div class="rpt-card-val text-green"><?= $completedCount ?></div>
              </div>
              <div class="rpt-card">
                <div class="rpt-card-label">Upcoming</div>
                <div class="rpt-card-val text-blue"><?= $upcomingCount ?></div>
              </div>
              <div class="rpt-card">
                <div class="rpt-card-label">Cancelled</div>
                <div class="rpt-card-val text-red"><?= $cancelledCount ?></div>
              </div>
            </div>

            <div class="rpt-section-title" style="margin-top:24px;">Attendance per Completed Event</div>
            <?php if (empty($completedEvents)): ?>
              <div class="ov-empty"><i class="fas fa-chart-bar"></i> No completed events to report</div>
            <?php else: ?>
              <div class="rpt-att-list">
                <?php foreach ($completedEvents as $e):
                  $pct = $memberCount > 0 ? round($e['att_count'] / $memberCount * 100) : 0;
                  ?>
                  <div class="rpt-att-row">
                    <div class="rpt-att-info">
                      <div class="rpt-att-name"><?= htmlspecialchars($e['title']) ?></div>
                      <div class="rpt-att-date"><?= date('M j, Y', strtotime($e['event_date'])) ?></div>
                    </div>
                    <div class="rpt-att-bar-wrap">
                      <div class="rpt-att-bar">
                        <div class="rpt-att-fill" style="width:<?= $pct ?>%"></div>
                      </div>
                      <span class="rpt-att-pct"><?= $e['att_count'] ?>/<?= $memberCount ?> (<?= $pct ?>%)</span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="rpt-section-title" style="margin-top:24px;">Overall Attendance Rate</div>
            <?php
            $possibleAtt = $completedCount * $memberCount;
            $overallPct = $possibleAtt > 0 ? round($totalAttendance / $possibleAtt * 100) : 0;
            ?>
            <div class="rpt-overall-wrap">
              <div class="rpt-overall-circle">
                <svg viewBox="0 0 36 36">
                  <path class="rpt-circle-bg" d="M18 2.0845a15.9155 15.9155 0 0 1 0 31.831" />
                  <path class="rpt-circle-fill" stroke-dasharray="<?= $overallPct ?>, 100"
                    d="M18 2.0845a15.9155 15.9155 0 0 1 0 31.831" />
                </svg>
                <span class="rpt-pct-label"><?= $overallPct ?>%</span>
              </div>
              <div class="rpt-overall-info">
                <div class="rpt-overall-title">Overall Attendance Rate</div>
                <div class="rpt-overall-sub">
                  <?= $totalAttendance ?> total check-ins across <?= $completedCount ?> completed events
                </div>
              </div>
            </div>
          </div>
        </div><!-- #tab-reports -->

      </div><!-- .content -->
    </main>
  </div><!-- .app -->

  <!-- ══════════════════════════════════════════
     NOTIFICATION PANEL
══════════════════════════════════════════ -->
  <div id="notifPanel" class="notif-panel" style="display:none;">
    <div class="notif-panel-header">
      <span>Notifications</span>
      <button onclick="OE.markAllRead()" class="notif-mark-all">Mark all read</button>
    </div>
    <div id="notifList" class="notif-list">
      <div class="notif-empty">Loading…</div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
     FULLSCREEN SCANNER OVERLAY
══════════════════════════════════════════ -->
  <div class="fs-overlay" id="fsOverlay">
    <div class="fs-header">
      <div class="fs-title"><i class="fas fa-qrcode"></i> <span id="fsTitle">Scanning attendance</span></div>
      <button class="fs-close" onclick="OE.closeFullscreen()"><i class="fas fa-times"></i> Close</button>
    </div>
    <div class="fs-viewport">
      <div class="fs-corners">
        <span class="fs-corner tl"></span><span class="fs-corner tr"></span>
        <span class="fs-corner bl"></span><span class="fs-corner br"></span>
      </div>
      <video id="scanVideoFs" playsinline muted></video>
      <div class="fs-placeholder" id="fsPlaceholder">
        <i class="fas fa-camera" style="font-size:32px;opacity:.4;"></i>
        <span>Camera preview appears here</span>
      </div>
      <div class="fs-scan-line"></div>
    </div>
    <div class="fs-stats">
      <div class="fs-stat">
        <span class="fs-stat-num green" id="fsPresent">0</span>
        <span class="fs-stat-lbl">Present</span>
      </div>
      <div class="fs-stat">
        <span class="fs-stat-num red" id="fsAbsent">0</span>
        <span class="fs-stat-lbl">Absent</span>
      </div>
      <div class="fs-stat">
        <span class="fs-stat-num" id="fsTotal">0</span>
        <span class="fs-stat-lbl">Total</span>
      </div>
    </div>
    <div class="fs-hint">QR codes are detected automatically — no button needed.</div>
  </div>

  <!-- ══════════════════════════════════════════
     ADD / EDIT EVENT MODAL
══════════════════════════════════════════ -->
  <div class="modal-overlay" id="eventModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title" id="modalTitle">Add Event</span>
        <button class="modal-close" onclick="OE.closeModal('eventModal')"><i class="fas fa-times"></i></button>
      </div>
      <input type="hidden" id="modalEventId" />

      <div class="form-group">
        <label class="form-label">Event Name <span class="req">*</span></label>
        <input type="text" class="form-input" id="fName" placeholder="e.g. General Assembly Q2" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Date <span class="req">*</span></label>
          <input type="date" class="form-input" id="fDate" />
        </div>
        <div class="form-group">
          <label class="form-label">Location</label>
          <input type="text" class="form-input" id="fLocation" placeholder="e.g. Tech Bldg Room 201" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Start Time</label>
          <input type="time" class="form-input" id="fStart" />
        </div>
        <div class="form-group">
          <label class="form-label">End Time</label>
          <input type="time" class="form-input" id="fEnd" />
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea class="form-textarea" id="fDesc" rows="3" placeholder="What is this event about?"></textarea>
      </div>
      <input type="hidden" id="fStatus" value="upcoming" />
      <input type="hidden" id="fMandatory" value="0" />

      <!-- ── Assignees + Collaboration row ── -->
      <div class="assignee-collab-row">
        <!-- Assignees column -->
        <div class="assignee-col">
          <!-- ── Assignees ── -->
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label"><i class="fas fa-user-check"
                style="color:var(--green-accent);margin-right:5px;"></i>Assignees <span
                style="font-weight:500;color:var(--text-light)">(optional)</span></label>

            <!-- Search input -->
            <div class="assignee-search-wrap">
              <i class="fas fa-magnifying-glass assignee-search-icon"></i>
              <input type="text" id="assigneeSearch" class="assignee-search-input"
                placeholder="Type a member name to assign…" autocomplete="off"
                oninput="OE.assigneeSearch(this.value)" />
              <div class="assignee-dropdown" id="assigneeDropdown"></div>
            </div>

            <!-- Assigned chips -->
            <div class="assignee-chips" id="assigneeChips">
              <span class="assignee-chips-empty" id="assigneeEmpty">No assignees yet.</span>
            </div>
          </div><!-- /form-group assignees -->
        </div><!-- /assignee-col -->

        <!-- Collaboration column -->
        <div class="collab-col" id="collabColSection">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label"><i class="fas fa-handshake"
                style="color:var(--green-accent);margin-right:5px;"></i>Club Collaboration <span
                style="font-weight:500;color:var(--text-light)">(optional)</span></label>

            <!-- Club search — same style as assignee search -->
            <div class="assignee-search-wrap" id="collabCustomPicker">
              <i class="fas fa-magnifying-glass assignee-search-icon"></i>
              <input type="text" id="collabSearchInput" class="assignee-search-input"
                placeholder="Type a club name to collaborate…"
                autocomplete="off"
                oninput="OE.collabSearch(this.value)"
                onfocus="OE.collabSearch(this.value)" />
              <div class="assignee-dropdown" id="collabPickerDropdown">
                <div id="collabPickerList"></div>
              </div>
            </div>

            <!-- Selected club chip with inline message -->
            <div id="collabSelectedClub"></div>

            <!-- Sent collab chips -->
            <div class="collab-chips" id="collabChips">
              <span class="collab-chips-empty" id="collabChipsEmpty">No collaborations yet.</span>
            </div>
          </div>
        </div><!-- /collab-col -->
      </div><!-- /assignee-collab-row -->

      <div class="modal-footer">
        <button class="btn-secondary" onclick="OE.closeModal('eventModal')">Cancel</button>
        <button class="btn-primary" id="btnSave" onclick="OE.saveEvent()">
          <i class="fas fa-save"></i>
          <span id="btnSaveText">Save Event</span>
        </button>
      </div>
    </div>
  </div>

  

  <!-- ══════════════════════════════════════════
     EVENT OVERVIEW MODAL
══════════════════════════════════════════ -->
  <div class="modal-overlay" id="eventOverviewModal">
    <div class="modal-box" style="width:680px;max-height:88vh;overflow-y:auto;">
      <div class="modal-header">
        <span class="modal-title" id="overviewTitle">Event Overview</span>
        <button class="modal-close" onclick="OE.closeModal('eventOverviewModal')"><i class="fas fa-times"></i></button>
      </div>
      <div id="overviewBody" style="padding:0 4px;">
        <div class="slc-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="OE.closeModal('eventOverviewModal')">Close</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
     COLLAB REQUEST MODAL
══════════════════════════════════════════ -->
  <div class="modal-overlay" id="collabRequestModal">
    <div class="modal-box" style="width:480px;">
      <div class="modal-header">
        <span class="modal-title">Send Collaboration Request</span>
        <button class="modal-close" onclick="OE.closeModal('collabRequestModal')"><i class="fas fa-times"></i></button>
      </div>
      <div style="padding:16px 0 4px;">
        <div class="form-group">
          <label class="form-label">Club</label>
          <div id="collabReqClubName" style="font-weight:700;color:var(--text-dark);font-size:15px;padding:6px 0;">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Message <span style="font-weight:400;color:var(--text-light)">(what you need from
              them)</span></label>
          <textarea class="form-textarea" id="collabReqMessage" rows="4"
            placeholder="E.g. We need 5 members to help with registration and setup on the event day…"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="OE.closeModal('collabRequestModal')">Cancel</button>
        <button class="btn-primary" onclick="OE.sendCollabRequest()">
          <i class="fas fa-paper-plane"></i> Send Request
        </button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
     COLLAB RESPOND MODAL (for incoming)
══════════════════════════════════════════ -->
  <div class="modal-overlay" id="collabRespondModal">
    <div class="modal-box" style="width:560px;max-height:86vh;overflow-y:auto;">
      <div class="modal-header">
        <span class="modal-title" id="collabRespondTitle">Respond to Collaboration</span>
        <button class="modal-close" onclick="OE.closeModal('collabRespondModal')"><i class="fas fa-times"></i></button>
      </div>
      <div style="padding:12px 0 4px;">
        <div id="collabRespondEventInfo"
          style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:14px 16px;margin-bottom:16px;">
        </div>
        <div id="collabRespondRequestMsg" style="margin-bottom:16px;"></div>

        <!-- Member picker for accepted collabs -->
        <div id="collabMemberPickSection">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-users"
                style="color:var(--green-accent);margin-right:5px;"></i>Pick members to assign</label>
            <div class="collab-member-search-wrap">
              <i class="fas fa-magnifying-glass"
                style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-light);font-size:13px;pointer-events:none;"></i>
              <input type="text" id="collabMemberSearch" class="assignee-search-input" style="padding-left:32px;"
                placeholder="Type a member name…" autocomplete="off" oninput="OE.collabMemberSearch(this.value)" />
              <div class="assignee-dropdown" id="collabMemberDropdown"></div>
            </div>
            <div class="assignee-chips" id="collabMemberChips">
              <span class="assignee-chips-empty" id="collabMemberEmpty">No members selected yet.</span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Message (optional)</label>
          <textarea class="form-textarea" id="collabRespondMsg" rows="3"
            placeholder="Add a note to the requesting club…"></textarea>
        </div>
      </div>
      <div class="modal-footer" style="justify-content:space-between;">
        <button class="btn-danger-outline" onclick="OE.submitCollabResponse('rejected')">
          <i class="fas fa-times-circle"></i> Decline
        </button>
        <div style="display:flex;gap:8px;">
          <button class="btn-secondary" onclick="OE.closeModal('collabRespondModal')">Cancel</button>
          <button class="btn-primary" onclick="OE.submitCollabResponse('accepted')">
            <i class="fas fa-check-circle"></i> Accept &amp; Assign Members
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
     ATTENDANCE VIEW MODAL (completed events)
══════════════════════════════════════════ -->
  <div class="modal-overlay" id="attViewModal">
    <div class="modal-box" style="width:540px;">
      <div class="modal-header">
        <span class="modal-title" id="attViewTitle">Attendance</span>
        <button class="modal-close" onclick="OE.closeModal('attViewModal')"><i class="fas fa-times"></i></button>
      </div>
      <div id="attViewBody">
        <div class="slc-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="OE.closeModal('attViewModal')">Close</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
     CONTEXT MENU
══════════════════════════════════════════ -->
  <div class="ctx-menu" id="ctxMenu">
    <button onclick="OE.ctxOverview()"><i class="fas fa-eye"></i> Event Overview</button>
    <button onclick="OE.ctxEdit()"><i class="fas fa-pen"></i> Edit event</button>
    <button onclick="OE.ctxToggleMandatory()"><i class="fas fa-star"></i> Toggle Mandatory</button>
    <button onclick="OE.ctxScanAtt()"><i class="fas fa-qrcode"></i> Open Scanner</button>
    <button class="danger" onclick="OE.ctxDelete()"><i class="fas fa-trash"></i> Delete event</button>
  </div>
  <div class="ctx-backdrop" id="ctxBackdrop" onclick="OE.closeCtx()"></div>

  <!-- ══════════════════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════════════════ -->
  <div class="delete-modal-overlay" id="deleteConfirmModal">
    <div class="delete-modal-box">
      <div class="del-icon-wrap">
        <i class="fas fa-trash-can"></i>
      </div>
      <div class="del-title">Delete Event?</div>
      <p class="del-sub">
        You're about to delete <span class="del-event-name" id="delEventName">"this event"</span>.
        This action <strong>cannot be undone</strong>.
      </p>
      <div class="del-actions">
        <button class="del-btn-cancel" onclick="OE.closeDeleteModal()">
          Cancel
        </button>
        <button class="del-btn-confirm" id="delConfirmBtn">
          <i class="fas fa-trash-can"></i> Yes, Delete
        </button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div id="toast" class="toast"></div>
  <!-- Data bridge -->
  <script>
    window._raw_incoming = <?= $jsIncoming ?? '[]' ?>;
    window.OE_DATA = {
      page: 'officer_events',
      club_id: <?= $clubId ?>,
      total_members: <?= $memberCount ?>,
      events: <?= $jsEvents ?>,
      members: <?= $jsMembers ?>,
      other_clubs: <?= $jsOtherClubs ?? '[]' ?>,
      incoming_collabs: <?= $jsIncoming ?? '[]' ?>,
      incoming_pending: <?= $incomingPendingCount ?? 0 ?>,
      partnered_events: <?= $jsPartnered ?? '[]' ?>
    };
    try { JSON.parse(JSON.stringify(window.OE_DATA)); } catch(e) { console.error('OE_DATA broken:', e); }
    console.log('incoming:', window._raw_incoming);
    console.log('club_id:', window.OE_DATA.club_id);
  </script>
  <script src="/unify/assets/javascripts/jsQR.min.js"></script>
  <script src="/unify/assets/javascripts/officer_events.js"></script>


</body>

</html>