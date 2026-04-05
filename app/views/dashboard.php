<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/dashboard.css" />
</head>
<body>
<div class="app">

  <!--SIDEBAR-->
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
      <a href="index.php?page=dashboard" class="nav-item active">
        <i class="fas fa-house"></i><span>Dashboard</span>
      </a>
      <a href="index.php?page=members" class="nav-item">
        <i class="fas fa-users"></i><span>Members</span>
      </a>
      <a href="index.php?page=clubpage" class="nav-item">
        <i class="fas fa-building-columns"></i><span>Clubs</span>
      </a>
      <a href="index.php?page=events" class="nav-item">
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
        <a href="#" class="sidebar-logout" title="Logout">
          <i class="fas fa-arrow-right-from-bracket"></i>
        </a>
        <a href="#" class="sidebar-settings-btn" title="Settings">
          <i class="fas fa-gear"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Dashboard</span>
        <span class="topbar-date">Tuesday, March 31, 2026</span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search members, clubs, events…" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="badge red">4</span>
        </button>
        <button class="icon-btn" title="Sync">
          <i class="fas fa-rotate"></i>
          <span class="badge green">2</span>
        </button>
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
      <div class="dashboard-grid">

        <!-- LEFT COLUMN -->
        <div class="left-col">

        <div class="wb-wrapper">
    <div class="welcome-banner">
       <div class="wb-text">
        <span class="wb-greeting">Good morning 👋</span>
        <h2 class="wb-name">Welcome back, Alex!</h2>
        <p class="wb-sub">Let's get things done today. Be Productive</p>.
    </div>
        <div class="wb-deco">
            <div class="wb-deco-rings">
                <div class="wb-deco-ring r1"></div>
                <div class="wb-deco-ring r2"></div>
                <div class="wb-deco-ring r3"></div>
            </div>
        </div>
    </div>

    <img src="./assets/pictures/visuals.png" alt="Banner character" class="wb-char-img" />
</div>


          <!-- Stat Cards -->
          <div class="stat-cards-grid">
            <div class="stat-card-new sc-green">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-users"></i></div>
                <span class="sc-trend up">↑ 12</span>
              </div>
              <div class="sc-value">1,250</div>
              <div class="sc-label">Total Members</div>
            </div>
            <div class="stat-card-new sc-yellow">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-building-columns"></i></div>
                <span class="sc-trend neutral">2 pending</span>
              </div>
              <div class="sc-value">18</div>
              <div class="sc-label">Active Clubs</div>
            </div>
            <div class="stat-card-new sc-teal">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-calendar-check"></i></div>
                <span class="sc-trend up">Today</span>
              </div>
              <div class="sc-value">5</div>
              <div class="sc-label">Upcoming Events</div>
            </div>
            <div class="stat-card-new sc-red">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-bell"></i></div>
                <span class="sc-trend urgent">Urgent</span>
              </div>
              <div class="sc-value">3</div>
              <div class="sc-label">Pending Requests</div>
            </div>
          </div>

          <!-- Announcements -->
          <div class="section-row">
            <h3 class="section-title">Announcements</h3>
            <a href="#" class="see-all-link">View All <i class="fas fa-chevron-right"></i></a>
          </div>

          <div class="card announce-card">
            <div class="table-header-row">
              <span class="th-col">Title</span>
              <span class="th-col">Category</span>
              <span class="th-col">Status</span>
              <span class="th-col th-right">Date</span>
            </div>
            <div class="table-body">
              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-dot red-dot"></div>
                  <span class="tr-title">Meeting Reminder: Club Officers</span>
                </div>
                <span class="tr-category">General</span>
                <span class="tr-status-badge urgent">Urgent</span>
                <span class="tr-date">Today</span>
              </div>
              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-dot green-dot"></div>
                  <span class="tr-title">Event Approval: Leadership Training</span>
                </div>
                <span class="tr-category">Events</span>
                <span class="tr-status-badge approved">Approved</span>
                <span class="tr-date">1d ago</span>
              </div>
              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-dot yellow-dot"></div>
                  <span class="tr-title">Club Funds Update</span>
                </div>
                <span class="tr-category">Finance</span>
                <span class="tr-status-badge info">Info</span>
                <span class="tr-date">3d ago</span>
              </div>
              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-dot green-dot"></div>
                  <span class="tr-title">Membership Application Approved</span>
                </div>
                <span class="tr-category">Members</span>
                <span class="tr-status-badge approved">Approved</span>
                <span class="tr-date">5d ago</span>
              </div>
              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-dot green-dot"></div>
                  <span class="tr-title">Science Quiz Bowl — Results Out</span>
                </div>
                <span class="tr-category">Achievement</span>
                <span class="tr-status-badge approved">Approved</span>
                <span class="tr-date">6d ago</span>
              </div>
              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-dot red-dot"></div>
                  <span class="tr-title">Deadline: Club Activity Report</span>
                </div>
                <span class="tr-category">Admin</span>
                <span class="tr-status-badge urgent">Deadline</span>
                <span class="tr-date">6d ago</span>
              </div>
            </div>
          </div>

        </div>



        <div class="right-col">

          <!-- Upcoming Events -->
          <div class="card events-card">
            <div class="card-header">
              <div>
                <h2>Upcoming Events</h2>
                <div class="calendar-subtitle">5 events scheduled today</div>
              </div>
              <button class="today-btn">Today <i class="fas fa-chevron-down"></i></button>
            </div>

            <div class="timeline">
              <div class="timeline-row">
                <span class="timeline-time">10:00</span>
                <div class="timeline-line-col">
                  <div class="timeline-dot active"></div>
                  <div class="timeline-vline active-line"></div>
                </div>
                <div class="timeline-event-wrap">
                  <div class="timeline-event active-event">
                    <div class="tl-event-icon"><i class="fas fa-bolt"></i></div>
                    <div class="tl-event-info">
                      <span class="tl-event-title">Financial Literacy Seminar</span>
                      <span class="tl-event-meta"><i class="fas fa-clock"></i> 9:45 – 10:30 · Business Mgmt</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="timeline-row">
                <span class="timeline-time">11:30</span>
                <div class="timeline-line-col">
                  <div class="timeline-dot"></div>
                  <div class="timeline-vline"></div>
                </div>
                <div class="timeline-event-wrap">
                  <div class="timeline-event inactive-event">
                    <div class="tl-event-icon"><i class="fas fa-users"></i></div>
                    <div class="tl-event-info">
                      <span class="tl-event-title">CS Society General Assembly</span>
                      <span class="tl-event-meta"><i class="fas fa-clock"></i> 11:30 – 13:00 · CS Society</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="timeline-row">
                <span class="timeline-time">13:00</span>
                <div class="timeline-line-col">
                  <div class="timeline-dot"></div>
                  <div class="timeline-vline"></div>
                </div>
                <div class="timeline-event-wrap">
                  <div class="timeline-event inactive-event">
                    <div class="tl-event-icon"><i class="fas fa-trophy"></i></div>
                    <div class="tl-event-info">
                      <span class="tl-event-title">Inter-Club Sports Fest</span>
                      <span class="tl-event-meta"><i class="fas fa-clock"></i> 13:00 – 17:00 · Athletics</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="timeline-row">
                <span class="timeline-time">14:30</span>
                <div class="timeline-line-col">
                  <div class="timeline-dot"></div>
                  <div class="timeline-vline"></div>
                </div>
                <div class="timeline-event-wrap">
                  <div class="timeline-event inactive-event">
                    <div class="tl-event-icon"><i class="fas fa-chalkboard-user"></i></div>
                    <div class="tl-event-info">
                      <span class="tl-event-title">Leadership Training Workshop</span>
                      <span class="tl-event-meta"><i class="fas fa-clock"></i> 14:30 – 16:00 · Admin</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="timeline-row last-row">
                <span class="timeline-time">16:00</span>
                <div class="timeline-line-col">
                  <div class="timeline-dot"></div>
                </div>
                <div class="timeline-event-wrap">
                  <div class="timeline-event inactive-event">
                    <div class="tl-event-icon"><i class="fas fa-star"></i></div>
                    <div class="tl-event-info">
                      <span class="tl-event-title">Club Awards Night Prep</span>
                      <span class="tl-event-meta"><i class="fas fa-clock"></i> 16:00 – 18:00 · All Clubs</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- New Applicants -->
          <div class="card applicants-card">
            <div class="card-header">
              <h2>New Applicants</h2>
              <a href="#" class="see-all-link">View All <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="applicant-list">
              <div class="applicant-item">
                <div class="applicant-avatar av-green">M</div>
                <div class="applicant-info">
                  <span class="applicant-name">Mike Tyson</span>
                  <span class="applicant-role">iOS Developer</span>
                </div>
                <button class="file-btn" title="View Application Form"><i class="fas fa-paperclip"></i></button>
              </div>
              <div class="applicant-item">
                <div class="applicant-avatar av-teal">Z</div>
                <div class="applicant-info">
                  <span class="applicant-name">Zara Thomas</span>
                  <span class="applicant-role">Content Designer</span>
                </div>
                <button class="file-btn" title="View Application Form"><i class="fas fa-paperclip"></i></button>
              </div>
              <div class="applicant-item">
                <div class="applicant-avatar av-red">J</div>
                <div class="applicant-info">
                  <span class="applicant-name">Jana Reyes</span>
                  <span class="applicant-role">Marketing Manager</span>
                </div>
                <button class="file-btn" title="View Application Form"><i class="fas fa-paperclip"></i></button>
              </div>
              <div class="applicant-item">
                <div class="applicant-avatar av-yellow">T</div>
                <div class="applicant-info">
                  <span class="applicant-name">Timothy Cruz</span>
                  <span class="applicant-role">iOS Developer</span>
                </div>
                <button class="file-btn" title="View Application Form"><i class="fas fa-paperclip"></i></button>
              </div>
              <div class="applicant-item">
                <div class="applicant-avatar av-purple">K</div>
                <div class="applicant-info">
                  <span class="applicant-name">Kimberly Lim</span>
                  <span class="applicant-role">Junior UI Designer</span>
                </div>
                <button class="file-btn" title="View Application Form"><i class="fas fa-paperclip"></i></button>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </main>
</div>
</body>
</html>