<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Events</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/events.css" />
</head>
<body>
<div class="app">

  <!-- SIDEBAR-->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon"><i class="fas fa-layer-group"></i></div>
      <div class="brand-text">
        <div class="brand-name">UNIFY</div>
        <div class="brand-tagline">Club Management System</div>
      </div>
    </div>
   <nav class="sidebar-nav">
      <div class="nav-section-label">MAIN MENU</div>
      <a href="index.php?page=dashboard" class="nav-item">
        <i class="fas fa-house"></i><span>Dashboard</span>
      </a>
      <a href="index.php?page=members" class="nav-item">
        <i class="fas fa-users"></i><span>Members</span>
      </a>
      <a href="index.php?page=clubpage" class="nav-item">
        <i class="fas fa-building-columns"></i><span>Clubs</span>
      </a>
      <a href="index.php?page=events" class="nav-item active">
        <i class="fas fa-calendar-days"></i><span>Events</span>
      </a>
      <a href="index.php?page=finance" class="nav-item">
        <i class="fas fa-coins"></i><span>Finances</span>
      </a>
      <div class="nav-section-label">REPORTS</div>
      <a href="index.php?page=reports" class="nav-item">
        <i class="fas fa-chart-bar"></i><span>Reports</span>
      </a>
    </nav>

    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <span class="profile-avatar-fallback">A</span>
          <span class="profile-online-dot"></span>
        </div>
        <a href="index.php?page=profile" class="profile-link">
  <div class="profile-info">
    <span class="profile-name">Alex Santos</span>
    <span class="profile-role">Club Admin</span>
  </div>
</a>
        <a href="#" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Events</span>
        <span class="topbar-date">Tuesday, March 31, 2026</span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search events, clubs…" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="badge red">4</span>
        </button>
        <button class="icon-btn" title="Sync"><i class="fas fa-rotate"></i></button>
        <div class="topbar-profile">
          <div class="topbar-avatar">A</div>
          <div class="topbar-profile-info">
            <span class="tp-name">Alex Santos</span>
            <span class="tp-role">Club Admin</span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </div>
      </div>
    </header>

    <div class="content">

      <!-- Toolbar -->
      <div class="events-toolbar">
        <div class="filter-tabs">
          <button class="filter-tab active" onclick="setTab(this)">All Events</button>
          <button class="filter-tab" onclick="setTab(this)">Today</button>
          <button class="filter-tab" onclick="setTab(this)">Upcoming</button>
          <button class="filter-tab" onclick="setTab(this)">Past</button>
        </div>
        <div class="toolbar-right">
          <button class="date-range-btn">
            <i class="fas fa-calendar"></i> Mar 31 – Apr 14, 2026 <i class="fas fa-chevron-down"></i>
          </button>
          <button class="add-event-btn" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add Event
          </button>
        </div>
      </div>


      <div class="events-body">

        <!-- LEFT: Event Cards-->
        <div class="events-main-col">
          <div class="section-row">
            <h3 class="section-title">Today's Events <span style="font-size:11px;color:var(--text-light);font-weight:500;margin-left:6px;">March 31</span></h3>
          </div>
          <div class="event-cards-grid">

            <!-- Card 1 -->
            <div class="event-card color-gold" onclick="openEventDetail('financial')">
              <div class="event-card-top">
                <div class="event-icon-wrap icon-gold"><i class="fas fa-bolt"></i></div>
                <span class="event-status-badge badge-today">Today</span>
              </div>
              <div>
                <div class="event-title">Financial Literacy Seminar</div>
                <div class="event-club">Business Management Club</div>
              </div>
              <div class="event-card-footer">
                <div class="event-meta-item"><i class="fas fa-clock"></i> 9:45 – 10:30 AM</div>
                <div class="event-attendees">
                  <div class="attendee-stack">
                    <div class="attendee-dot ad-g">A</div>
                    <div class="attendee-dot ad-t">Z</div>
                    <div class="attendee-dot ad-r">J</div>
                  </div>
                  <span class="attendee-count">+12</span>
                </div>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="event-card color-green" onclick="openEventDetail('cs')">
              <div class="event-card-top">
                <div class="event-icon-wrap icon-green"><i class="fas fa-users"></i></div>
                <span class="event-status-badge badge-ongoing">Ongoing</span>
              </div>
              <div>
                <div class="event-title">CS Society General Assembly</div>
                <div class="event-club">Computer Science Society</div>
              </div>
              <div class="event-card-footer">
                <div class="event-meta-item"><i class="fas fa-clock"></i> 11:30 – 1:00 PM</div>
                <div class="event-attendees">
                  <div class="attendee-stack">
                    <div class="attendee-dot ad-p">K</div>
                    <div class="attendee-dot ad-g">M</div>
                    <div class="attendee-dot ad-y">T</div>
                  </div>
                  <span class="attendee-count">+28</span>
                </div>
              </div>
            </div>

            <!-- Card 3 -->
            <div class="event-card color-teal" onclick="openEventDetail('sports')">
              <div class="event-card-top">
                <div class="event-icon-wrap icon-teal"><i class="fas fa-trophy"></i></div>
                <span class="event-status-badge badge-upcoming">Upcoming</span>
              </div>
              <div>
                <div class="event-title">Inter-Club Sports Fest</div>
                <div class="event-club">Athletics Department</div>
              </div>
              <div class="event-card-footer">
                <div class="event-meta-item"><i class="fas fa-clock"></i> 1:00 – 5:00 PM</div>
                <div class="event-attendees">
                  <div class="attendee-stack">
                    <div class="attendee-dot ad-r">J</div>
                    <div class="attendee-dot ad-t">Z</div>
                    <div class="attendee-dot ad-g">A</div>
                  </div>
                  <span class="attendee-count">+45</span>
                </div>
              </div>
            </div>

            <!-- Card 4 -->
            <div class="event-card color-orange" onclick="openEventDetail('leadership')">
              <div class="event-card-top">
                <div class="event-icon-wrap icon-orange"><i class="fas fa-chalkboard-user"></i></div>
                <span class="event-status-badge badge-upcoming">Upcoming</span>
              </div>
              <div>
                <div class="event-title">Leadership Training Workshop</div>
                <div class="event-club">Student Administration</div>
              </div>
              <div class="event-card-footer">
                <div class="event-meta-item"><i class="fas fa-clock"></i> 2:30 – 4:00 PM</div>
                <div class="event-attendees">
                  <div class="attendee-stack">
                    <div class="attendee-dot ad-y">T</div>
                    <div class="attendee-dot ad-o">K</div>
                    <div class="attendee-dot ad-g">M</div>
                  </div>
                  <span class="attendee-count">+18</span>
                </div>
              </div>
            </div>

            <!-- Card 5 -->
            <div class="event-card color-red" onclick="openEventDetail('awards')">
              <div class="event-card-top">
                <div class="event-icon-wrap icon-red"><i class="fas fa-star"></i></div>
                <span class="event-status-badge badge-upcoming">Upcoming</span>
              </div>
              <div>
                <div class="event-title">Club Awards Night Prep</div>
                <div class="event-club">All Clubs</div>
              </div>
              <div class="event-card-footer">
                <div class="event-meta-item"><i class="fas fa-clock"></i> 4:00 – 6:00 PM</div>
                <div class="event-attendees">
                  <div class="attendee-stack">
                    <div class="attendee-dot ad-r">J</div>
                    <div class="attendee-dot ad-g">A</div>
                    <div class="attendee-dot ad-t">Z</div>
                  </div>
                  <span class="attendee-count">+32</span>
                </div>
              </div>
            </div>

            <!-- Card 6 -->
            <div class="event-card color-blue" onclick="openEventDetail('quiz')">
              <div class="event-card-top">
                <div class="event-icon-wrap icon-blue"><i class="fas fa-microscope"></i></div>
                <span class="event-status-badge badge-upcoming">Upcoming</span>
              </div>
              <div>
                <div class="event-title">Science Quiz Bowl Finals</div>
                <div class="event-club">Science & Tech Club</div>
              </div>
              <div class="event-card-footer">
                <div class="event-meta-item"><i class="fas fa-calendar"></i> Apr 2</div>
                <div class="event-attendees">
                  <div class="attendee-stack">
                    <div class="attendee-dot ad-g">M</div>
                    <div class="attendee-dot ad-t">Z</div>
                  </div>
                  <span class="attendee-count">+9</span>
                </div>
              </div>
            </div>



          </div>
        </div>
<div class="events-side-col">
  <!-- Mini Calendar -->
  <div class="card">
    <div class="card-header">
      <h2>March 2026</h2>
      <div class="calendar-nav">
        <button class="cal-nav-btn"><i class="fas fa-chevron-left"></i></button>
        <button class="cal-nav-btn"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
    <div class="mini-calendar">
      <div class="cal-weekdays">
        <div class="cal-wd">Su</div><div class="cal-wd">Mo</div><div class="cal-wd">Tu</div>
        <div class="cal-wd">We</div><div class="cal-wd">Th</div><div class="cal-wd">Fr</div><div class="cal-wd">Sa</div>
      </div>
      <div class="cal-days">
        <div class="cal-day other-month">23</div><div class="cal-day other-month">24</div>
        <div class="cal-day other-month">25</div><div class="cal-day other-month">26</div>
        <div class="cal-day other-month">27</div><div class="cal-day other-month">28</div>
        <div class="cal-day">1</div><div class="cal-day">2</div><div class="cal-day">3</div>
        <div class="cal-day has-event">4</div><div class="cal-day">5</div>
        <div class="cal-day has-event">6</div><div class="cal-day">7</div><div class="cal-day">8</div>
        <div class="cal-day">9</div><div class="cal-day has-event">10</div><div class="cal-day">11</div>
        <div class="cal-day has-event">12</div><div class="cal-day">13</div><div class="cal-day">14</div>
        <div class="cal-day">15</div><div class="cal-day">16</div><div class="cal-day has-event">17</div>
        <div class="cal-day">18</div><div class="cal-day">19</div><div class="cal-day">20</div>
        <div class="cal-day has-event">21</div><div class="cal-day">22</div><div class="cal-day">23</div>
        <div class="cal-day">24</div><div class="cal-day">25</div><div class="cal-day">26</div>
        <div class="cal-day">27</div><div class="cal-day">28</div><div class="cal-day">29</div>
        <div class="cal-day">30</div><div class="cal-day today has-event">31</div>
        <div class="cal-day other-month">1</div><div class="cal-day other-month">2</div>
        <div class="cal-day other-month">3</div><div class="cal-day other-month">4</div>
      </div>
    </div>
  </div>


          <div class="card approvals-card">
            <div class="card-header">
              <h2>Pending Approvals</h2>
              <span class="approval-badge-count">4 pending</span>
            </div>
            <div class="approvals-scroll">

              <!-- Approval 1 -->
              <div class="approval-item" onclick="openApprovalDetail('debate')">
                <div class="approval-item-top">
                  <div class="approval-item-icon icon-red"><i class="fas fa-microphone-lines"></i></div>
                  <div class="approval-title">Inter-Club Debate Competition</div>
                  <span class="approval-urgency urgency-high">High</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-building-columns"></i> Debate Society</span>
                  <span><i class="fas fa-calendar"></i> Apr 5, 2026</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-user-check"></i> 24 confirmed RSVPs</span>
                </div>
                <div class="approval-actions">
                  <button class="btn-view-details" onclick="event.stopPropagation(); openApprovalDetail('debate')"><i class="fas fa-eye"></i> Details</button>
                  <button class="btn-approve" onclick="event.stopPropagation(); quickApprove(this)">Approve</button>
                  <button class="btn-reject" onclick="event.stopPropagation(); quickReject(this)">Reject</button>
                </div>
              </div>

              <!-- Approval 2 -->
              <div class="approval-item" onclick="openApprovalDetail('art')">
                <div class="approval-item-top">
                  <div class="approval-item-icon icon-orange"><i class="fas fa-palette"></i></div>
                  <div class="approval-title">Campus Art Exhibition</div>
                  <span class="approval-urgency urgency-medium">Medium</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-building-columns"></i> Fine Arts Club</span>
                  <span><i class="fas fa-calendar"></i> Apr 10, 2026</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-user-check"></i> 18 confirmed RSVPs</span>
                </div>
                <div class="approval-actions">
                  <button class="btn-view-details" onclick="event.stopPropagation(); openApprovalDetail('art')"><i class="fas fa-eye"></i> Details</button>
                  <button class="btn-approve" onclick="event.stopPropagation(); quickApprove(this)">Approve</button>
                  <button class="btn-reject" onclick="event.stopPropagation(); quickReject(this)">Reject</button>
                </div>
              </div>

              <!-- Approval 3 -->
              <div class="approval-item" onclick="openApprovalDetail('coding')">
                <div class="approval-item-top">
                  <div class="approval-item-icon icon-blue"><i class="fas fa-code"></i></div>
                  <div class="approval-title">Hackathon 2026: Build for Good</div>
                  <span class="approval-urgency urgency-high">High</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-building-columns"></i> CS Society</span>
                  <span><i class="fas fa-calendar"></i> Apr 12, 2026</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-user-check"></i> 36 confirmed RSVPs</span>
                </div>
                <div class="approval-actions">
                  <button class="btn-view-details" onclick="event.stopPropagation(); openApprovalDetail('coding')"><i class="fas fa-eye"></i> Details</button>
                  <button class="btn-approve" onclick="event.stopPropagation(); quickApprove(this)">Approve</button>
                  <button class="btn-reject" onclick="event.stopPropagation(); quickReject(this)">Reject</button>
                </div>
              </div>

              <!-- Approval 4 -->
              <div class="approval-item" onclick="openApprovalDetail('cleanup')">
                <div class="approval-item-top">
                  <div class="approval-item-icon icon-green"><i class="fas fa-leaf"></i></div>
                  <div class="approval-title">Campus Cleanup Drive</div>
                  <span class="approval-urgency urgency-low">Low</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-building-columns"></i> Env. Society</span>
                  <span><i class="fas fa-calendar"></i> Apr 18, 2026</span>
                </div>
                <div class="approval-meta">
                  <span><i class="fas fa-user-check"></i> 12 confirmed RSVPs</span>
                </div>
                <div class="approval-actions">
                  <button class="btn-view-details" onclick="event.stopPropagation(); openApprovalDetail('cleanup')"><i class="fas fa-eye"></i> Details</button>
                  <button class="btn-approve" onclick="event.stopPropagation(); quickApprove(this)">Approve</button>
                  <button class="btn-reject" onclick="event.stopPropagation(); quickReject(this)">Reject</button>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<!-- ADD EVENT MODAL -->
<div class="modal-overlay" id="addEventModal" onclick="handleOverlayClick(event, 'addEventModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Event</span>
      <button class="modal-close" onclick="closeModal('addEventModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="form-group">
      <label class="form-label">Event Name</label>
      <input type="text" class="form-input" placeholder="e.g. Club General Assembly" />
    </div>
    <div class="form-group">
      <label class="form-label">Event Category</label>
      <select class="form-select">
        <option>Academic</option>
        <option>Sports</option>
        <option>Social</option>
        <option>Workshop</option>
        <option>Competition</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Priority</label>
      <select class="form-select">
        <option>Medium</option><option>Low</option><option>High</option><option>Urgent</option>
      </select>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Date</label>
        <input type="date" class="form-input" value="2026-03-31" />
      </div>
      <div class="form-group">
        <label class="form-label">Time</label>
        <input type="time" class="form-input" />
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-textarea" placeholder="Add a brief description of the event..."></textarea>
    </div>
    <div class="toggle-row">
      <span class="toggle-label">Repeat Event</span>
      <label class="toggle">
        <input type="checkbox" />
        <span class="toggle-slider"></span>
      </label>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('addEventModal')">Cancel</button>
      <button class="btn-primary">Save Event</button>
    </div>
  </div>
</div>

<!-- EVENT DETAIL MODAL -->
<div class="modal-overlay" id="eventDetailModal" onclick="handleOverlayClick(event, 'eventDetailModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Event Details</span>
      <button class="modal-close" onclick="closeModal('eventDetailModal')"><i class="fas fa-times"></i></button>
    </div>

    <!-- Hero -->
    <div class="event-detail-hero" id="detailHero">
      <div class="event-detail-icon" id="detailIcon"><i class="fas fa-calendar"></i></div>
      <div class="event-detail-hero-info">
        <div class="event-detail-hero-title" id="detailTitle">Event Title</div>
        <div class="event-detail-hero-club" id="detailClub">Club Name</div>
      </div>
      <div class="event-detail-hero-badge" id="detailStatus">Upcoming</div>
    </div>

    <!-- Info Grid -->
    <div class="detail-info-grid">
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-calendar"></i> Date</div>
        <div class="detail-info-value" id="detailDate">—</div>
      </div>
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-clock"></i> Time</div>
        <div class="detail-info-value" id="detailTime">—</div>
      </div>
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-map-pin"></i> Venue</div>
        <div class="detail-info-value" id="detailVenue">—</div>
      </div>
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-users"></i> Attendees</div>
        <div class="detail-info-value" id="detailCount">—</div>
      </div>
    </div>

    <!-- Description -->
    <div>
      <div class="detail-section-label">About this Event</div>
      <p style="font-size:12.5px;color:var(--text-mid);line-height:1.7;margin-top:8px;" id="detailDesc">—</p>
    </div>

    <!-- Attendees -->
    <div>
      <div class="detail-section-label">RSVP List</div>
      <div class="attendee-list" id="detailAttendees" style="margin-top:8px;"></div>
    </div>

    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('eventDetailModal')">Close</button>
      <button class="btn-primary"><i class="fas fa-pen"></i> Edit Event</button>
    </div>
  </div>
</div>

<!-- APPROVAL DETAIL MODAL -->
<div class="modal-overlay" id="approvalDetailModal" onclick="handleOverlayClick(event, 'approvalDetailModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Approval Request</span>
      <button class="modal-close" onclick="closeModal('approvalDetailModal')"><i class="fas fa-times"></i></button>
    </div>

    <!-- Approval header -->
    <div class="approval-detail-header">
      <div class="approval-detail-icon" id="approvalIcon"><i class="fas fa-calendar"></i></div>
      <div>
        <div class="approval-detail-title" id="approvalTitle">Event Title</div>
        <div class="approval-detail-sub" id="approvalSub">Club · Date</div>
      </div>
    </div>

    <!-- Info Grid -->
    <div class="detail-info-grid">
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-calendar"></i> Date</div>
        <div class="detail-info-value" id="approvalDate">—</div>
      </div>
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-clock"></i> Time</div>
        <div class="detail-info-value" id="approvalTime">—</div>
      </div>
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-map-pin"></i> Venue</div>
        <div class="detail-info-value" id="approvalVenue">—</div>
      </div>
      <div class="detail-info-item">
        <div class="detail-info-label"><i class="fas fa-flag"></i> Priority</div>
        <div class="detail-info-value" id="approvalPriority">—</div>
      </div>
    </div>

    <!-- Description -->
    <div>
      <div class="detail-section-label">Event Description</div>
      <p style="font-size:12.5px;color:var(--text-mid);line-height:1.7;margin-top:8px;" id="approvalDesc">—</p>
    </div>

    <!-- RSVP Summary -->
    <div>
      <div class="detail-section-label">RSVP Summary</div>
      <div class="rsvp-summary" style="margin-top:8px;">
        <div class="rsvp-stat">
          <div class="rsvp-stat-number green" id="rsvpConfirmed">0</div>
          <div class="rsvp-stat-label">Confirmed</div>
        </div>
        <div class="rsvp-stat">
          <div class="rsvp-stat-number orange" id="rsvpPending">0</div>
          <div class="rsvp-stat-label">Pending</div>
        </div>
        <div class="rsvp-stat">
          <div class="rsvp-stat-number red" id="rsvpDeclined">0</div>
          <div class="rsvp-stat-label">Declined</div>
        </div>
      </div>
    </div>

    <!-- RSVP Attendee List -->
    <div>
      <div class="detail-section-label">Attendees</div>
      <div class="attendee-list" id="approvalAttendees" style="margin-top:8px;"></div>
    </div>

    <!-- Footer actions -->
    <div class="approval-modal-footer">
      <button class="btn-reject-lg" onclick="closeModal('approvalDetailModal')"><i class="fas fa-times"></i> Reject</button>
      <button class="btn-approve-lg" onclick="closeModal('approvalDetailModal')"><i class="fas fa-check"></i> Approve Event</button>
    </div>
  </div>
</div>

<script>
  /* ---- Data ---- */
  const events = {
    financial: {
      title: 'Financial Literacy Seminar',
      club: 'Business Management Club',
      hero: 'hero-gold',
      icon: 'fa-bolt',
      status: 'Today',
      date: 'March 31, 2026',
      time: '9:45 – 10:30 AM',
      venue: 'Auditorium A',
      count: '15 attending',
      desc: 'A seminar designed to equip students with essential financial knowledge covering budgeting, savings, and investment basics. Open to all club members and interested students.',
      attendees: [
        { name: 'Alex Santos',  role: 'Club Admin',       color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Zara Thomas',  role: 'Content Designer', color: 'ad-t', rsvp: 'confirmed' },
        { name: 'Jana Reyes',   role: 'Marketing Head',   color: 'ad-r', rsvp: 'confirmed' },
        { name: 'Tim Cruz',     role: 'iOS Developer',    color: 'ad-y', rsvp: 'pending'   },
        { name: 'Kim Lim',      role: 'UI Designer',      color: 'ad-p', rsvp: 'declined'  },
      ]
    },
    cs: {
      title: 'CS Society General Assembly',
      club: 'Computer Science Society',
      hero: 'hero-green',
      icon: 'fa-users',
      status: 'Ongoing',
      date: 'March 31, 2026',
      time: '11:30 AM – 1:00 PM',
      venue: 'Tech Building Room 201',
      count: '31 attending',
      desc: 'The quarterly general assembly for all CS Society members to discuss upcoming projects, elections, and club direction for the semester.',
      attendees: [
        { name: 'Kimberly Lim', role: 'President',     color: 'ad-p', rsvp: 'confirmed' },
        { name: 'Mike Santos',  role: 'Vice President', color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Tim Cruz',     role: 'Secretary',      color: 'ad-y', rsvp: 'confirmed' },
        { name: 'Ana Rivera',   role: 'Treasurer',      color: 'ad-o', rsvp: 'pending'   },
      ]
    },
    sports: {
      title: 'Inter-Club Sports Fest',
      club: 'Athletics Department',
      hero: 'hero-teal',
      icon: 'fa-trophy',
      status: 'Upcoming',
      date: 'March 31, 2026',
      time: '1:00 – 5:00 PM',
      venue: 'University Gymnasium',
      count: '48 attending',
      desc: 'Annual inter-club sports competition featuring basketball, volleyball, and badminton. All registered clubs are encouraged to field a team.',
      attendees: [
        { name: 'Jana Reyes',   role: 'Event Coordinator', color: 'ad-r', rsvp: 'confirmed' },
        { name: 'Zara Thomas',  role: 'Referee',            color: 'ad-t', rsvp: 'confirmed' },
        { name: 'Alex Santos',  role: 'Organizer',          color: 'ad-g', rsvp: 'pending'   },
      ]
    },
    leadership: {
      title: 'Leadership Training Workshop',
      club: 'Student Administration',
      hero: 'hero-orange',
      icon: 'fa-chalkboard-user',
      status: 'Upcoming',
      date: 'March 31, 2026',
      time: '2:30 – 4:00 PM',
      venue: 'Conference Room B',
      count: '21 attending',
      desc: 'An immersive leadership development workshop for club officers and student leaders, covering communication, conflict resolution, and strategic planning.',
      attendees: [
        { name: 'Tim Cruz',     role: 'Participant',   color: 'ad-y', rsvp: 'confirmed' },
        { name: 'Kimberly Lim', role: 'Participant',   color: 'ad-o', rsvp: 'confirmed' },
        { name: 'Mike Santos',  role: 'Facilitator',   color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Jana Reyes',   role: 'Participant',   color: 'ad-r', rsvp: 'declined'  },
      ]
    },
    awards: {
      title: 'Club Awards Night Prep',
      club: 'All Clubs',
      hero: 'hero-red',
      icon: 'fa-star',
      status: 'Upcoming',
      date: 'March 31, 2026',
      time: '4:00 – 6:00 PM',
      venue: 'Main Lobby',
      count: '35 attending',
      desc: 'Preparation and rehearsal for the annual Club Awards Night. All club presidents and officers are required to attend.',
      attendees: [
        { name: 'Jana Reyes',  role: 'Coordinator',  color: 'ad-r', rsvp: 'confirmed' },
        { name: 'Alex Santos', role: 'Emcee',        color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Zara Thomas', role: 'Decorator',    color: 'ad-t', rsvp: 'pending'   },
      ]
    },
    quiz: {
      title: 'Science Quiz Bowl Finals',
      club: 'Science & Tech Club',
      hero: 'hero-blue',
      icon: 'fa-microscope',
      status: 'Upcoming',
      date: 'April 2, 2026',
      time: '10:00 AM – 12:00 PM',
      venue: 'Science Hall Room 104',
      count: '11 attending',
      desc: 'The final round of the annual Science Quiz Bowl. Top 3 teams from the eliminations will compete for the championship title.',
      attendees: [
        { name: 'Mike Santos', role: 'Contestant',  color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Zara Thomas', role: 'Contestant',  color: 'ad-t', rsvp: 'confirmed' },
        { name: 'Tim Cruz',    role: 'Contestant',  color: 'ad-y', rsvp: 'pending'   },
      ]
    }
  };

  const approvals = {
    debate: {
      title: 'Inter-Club Debate Competition',
      icon: 'fa-microphone-lines',
      sub: 'Debate Society · April 5, 2026',
      date: 'April 5, 2026',
      time: '1:00 – 5:00 PM',
      venue: 'Main Auditorium',
      priority: '🔴 High',
      desc: 'A formal inter-club debate competition on current national issues. Teams of 3 from each participating club. Judges will be faculty members.',
      confirmed: 24, pending: 6, declined: 2,
      attendees: [
        { name: 'Leo Villanueva', role: 'Team Captain',  color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Sofia Cruz',     role: 'Debater',        color: 'ad-r', rsvp: 'confirmed' },
        { name: 'Marco Reyes',    role: 'Debater',        color: 'ad-t', rsvp: 'pending'   },
        { name: 'Pia Santos',     role: 'Timekeeper',     color: 'ad-y', rsvp: 'confirmed' },
        { name: 'Dan Lim',        role: 'Researcher',     color: 'ad-p', rsvp: 'declined'  },
      ]
    },
    art: {
      title: 'Campus Art Exhibition',
      icon: 'fa-palette',
      sub: 'Fine Arts Club · April 10, 2026',
      date: 'April 10, 2026',
      time: '9:00 AM – 4:00 PM',
      venue: 'Arts Building Gallery',
      priority: '🟠 Medium',
      desc: 'An exhibition showcasing student artwork from the Fine Arts Club including paintings, sculptures, and digital art pieces.',
      confirmed: 18, pending: 4, declined: 1,
      attendees: [
        { name: 'Ella Gomez',   role: 'Curator',     color: 'ad-o', rsvp: 'confirmed' },
        { name: 'Carl Navarro', role: 'Artist',      color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Mia Torres',   role: 'Artist',      color: 'ad-r', rsvp: 'pending'   },
        { name: 'Sam Bautista', role: 'Volunteer',   color: 'ad-t', rsvp: 'confirmed' },
      ]
    },
    coding: {
      title: 'Hackathon 2026: Build for Good',
      icon: 'fa-code',
      sub: 'CS Society · April 12, 2026',
      date: 'April 12–13, 2026',
      time: '8:00 AM (24 hrs)',
      venue: 'Computer Lab 3 & 4',
      priority: '🔴 High',
      desc: 'A 24-hour hackathon where teams build tech solutions for real community problems. Open to all CS and IT students. Prizes for top 3 teams.',
      confirmed: 36, pending: 8, declined: 3,
      attendees: [
        { name: 'Ryan Aquino',  role: 'Team Lead',   color: 'ad-p', rsvp: 'confirmed' },
        { name: 'Issa Flores',  role: 'Developer',   color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Dave Tan',     role: 'Designer',    color: 'ad-t', rsvp: 'confirmed' },
        { name: 'Lara Ong',     role: 'Developer',   color: 'ad-y', rsvp: 'pending'   },
        { name: 'Eli Santos',   role: 'Data Analyst',color: 'ad-r', rsvp: 'declined'  },
      ]
    },
    cleanup: {
      title: 'Campus Cleanup Drive',
      icon: 'fa-leaf',
      sub: 'Environmental Society · April 18, 2026',
      date: 'April 18, 2026',
      time: '7:00 – 10:00 AM',
      venue: 'Main Campus Grounds',
      priority: '🟢 Low',
      desc: 'A campus-wide environmental awareness cleanup activity open to all students. Gloves, trash bags, and refreshments will be provided.',
      confirmed: 12, pending: 5, declined: 0,
      attendees: [
        { name: 'Gab Reyes',   role: 'Organizer',   color: 'ad-g', rsvp: 'confirmed' },
        { name: 'Nina Cruz',   role: 'Volunteer',   color: 'ad-t', rsvp: 'confirmed' },
        { name: 'Karl Diaz',   role: 'Volunteer',   color: 'ad-y', rsvp: 'pending'   },
        { name: 'Rina Santos', role: 'Volunteer',   color: 'ad-o', rsvp: 'confirmed' },
      ]
    }
  };

  /* ---- Modal helpers ---- */
  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  function handleOverlayClick(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }
  function openAddModal() { openModal('addEventModal'); }
  function setTab(el) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
  }

  /* ---- Render RSVP badge ---- */
  function rsvpBadge(rsvp) {
    const map = { confirmed: ['rsvp-confirmed','Confirmed'], pending: ['rsvp-pending','Pending'], declined: ['rsvp-declined','Declined'] };
    const [cls, label] = map[rsvp] || ['rsvp-pending','Pending'];
    return `<span class="rsvp-badge ${cls}">${label}</span>`;
  }

  /* ---- Render attendee rows ---- */
  function renderAttendees(list, containerId) {
    const el = document.getElementById(containerId);
    el.innerHTML = list.map(a => `
      <div class="attendee-row">
        <div class="attendee-avatar ${a.color}">${a.name[0]}</div>
        <div class="attendee-info">
          <div class="attendee-name">${a.name}</div>
          <div class="attendee-role">${a.role}</div>
        </div>
        ${rsvpBadge(a.rsvp)}
      </div>
    `).join('');
  }

  /* ---- Open event detail ---- */
  function openEventDetail(key) {
    const e = events[key];
    document.getElementById('detailHero').className = `event-detail-hero ${e.hero}`;
    document.getElementById('detailIcon').innerHTML = `<i class="fas ${e.icon}"></i>`;
    document.getElementById('detailTitle').textContent = e.title;
    document.getElementById('detailClub').textContent = e.club;
    document.getElementById('detailStatus').textContent = e.status;
    document.getElementById('detailDate').textContent = e.date;
    document.getElementById('detailTime').textContent = e.time;
    document.getElementById('detailVenue').textContent = e.venue;
    document.getElementById('detailCount').textContent = e.count;
    document.getElementById('detailDesc').textContent = e.desc;
    renderAttendees(e.attendees, 'detailAttendees');
    openModal('eventDetailModal');
  }

  /* ---- Open approval detail ---- */
  function openApprovalDetail(key) {
    const a = approvals[key];
    document.getElementById('approvalIcon').innerHTML = `<i class="fas ${a.icon}"></i>`;
    document.getElementById('approvalTitle').textContent = a.title;
    document.getElementById('approvalSub').textContent = a.sub;
    document.getElementById('approvalDate').textContent = a.date;
    document.getElementById('approvalTime').textContent = a.time;
    document.getElementById('approvalVenue').textContent = a.venue;
    document.getElementById('approvalPriority').textContent = a.priority;
    document.getElementById('approvalDesc').textContent = a.desc;
    document.getElementById('rsvpConfirmed').textContent = a.confirmed;
    document.getElementById('rsvpPending').textContent = a.pending;
    document.getElementById('rsvpDeclined').textContent = a.declined;
    renderAttendees(a.attendees, 'approvalAttendees');
    openModal('approvalDetailModal');
  }

  /* ---- Quick approve / reject ---- */
  function quickApprove(btn) {
    const item = btn.closest('.approval-item');
    item.style.opacity = '0';
    item.style.transform = 'translateX(10px)';
    item.style.transition = 'all 0.3s';
    setTimeout(() => item.remove(), 300);
  }

  function quickReject(btn) {
    const item = btn.closest('.approval-item');
    item.style.opacity = '0';
    item.style.transform = 'translateX(-10px)';
    item.style.transition = 'all 0.3s';
    setTimeout(() => item.remove(), 300);
  }
</script>
</body>
</html>