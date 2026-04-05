<?php
// clubpage.php
// Place this file in your project root alongside clubpage.css
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Clubs</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/clubpage.css" />
</head>
<body>
<div class="app">

  <!-- SIDEBAR -->
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
      <a href="dashboard.php" class="nav-item"><i class="fas fa-house"></i><span>Dashboard</span></a>
      <a href="#" class="nav-item"><i class="fas fa-users"></i><span>Members</span></a>
      <a href="clubpage.php" class="nav-item active"><i class="fas fa-building-columns"></i><span>Clubs</span></a>
      <a href="#" class="nav-item"><i class="fas fa-calendar-days"></i><span>Events</span></a>
      <a href="#" class="nav-item"><i class="fas fa-coins"></i><span>Finances</span></a>
      <div class="nav-section-label">COMMUNICATE</div>
      <a href="#" class="nav-item"><i class="fas fa-bullhorn"></i><span>Announcements</span></a>
      <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
    </nav>
    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <span class="profile-avatar-fallback">A</span>
          <span class="profile-online-dot"></span>
        </div>
        <div class="profile-info">
          <span class="profile-name">Alex Santos</span>
          <span class="profile-role">Club Admin</span>
        </div>
        <a href="#" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="#" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Clubs</span>
        <span class="topbar-date">Tuesday, March 31, 2026</span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search clubs, officers, categories…" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications"><i class="fas fa-bell"></i><span class="badge red">4</span></button>
        <button class="icon-btn" title="Sync"><i class="fas fa-rotate"></i><span class="badge green">2</span></button>
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
      <div class="clubs-layout">

        <!-- LEFT PANEL -->
        <div class="clubs-left-panel">
          <div class="panel-header">
            <div class="panel-header-top">
              <h2 class="panel-title">All Clubs</h2>
              <button class="add-club-btn"><i class="fas fa-plus"></i> New Club</button>
            </div>
            <div class="filter-tabs">
              <button class="filter-tab active" data-filter="all">All <span class="tab-count">8</span></button>
              <button class="filter-tab" data-filter="active">Active <span class="tab-count">8</span></button>
              <button class="filter-tab" data-filter="pending">Pending <span class="tab-count">0</span></button>
            </div>
            <div class="panel-search">
              <i class="fas fa-magnifying-glass"></i>
              <input type="text" placeholder="Filter clubs…" id="clubFilterInput" />
              <button class="sort-btn" title="Sort"><i class="fas fa-arrow-up-wide-short"></i></button>
            </div>
          </div>

          <div class="club-list" id="clubList">

            <!-- ITS -->
            <div class="club-item selected" data-status="active"
              data-name="Information Technology Society"
              data-logo="./assets/pictures/ITSlogo.jpg"
              data-category="Tech"
              data-founded="Mar 2021"
              data-room="ITS Office"
              data-desc="Information Technology Society is a group that promotes IT skills and knowledge through activities, helping students prepare for tech careers."
              data-members="96" data-events="7" data-budget="&#8369;18,400" data-attendance="94%"
              data-officers='[{"name":"James Dela Cruz","pos":"President","color":"oa-blue","lead":true},{"name":"Sofia Reyes","pos":"Vice President","color":"oa-teal"},{"name":"Marco Lim","pos":"Secretary","color":"oa-green"},{"name":"Ana Torres","pos":"Treasurer","color":"oa-yellow"}]'
              data-upcoming='[{"day":"31","mon":"MAR","title":"General Assembly","time":"11:30 - 13:00","color":"cev-blue"},{"day":"04","mon":"APR","title":"Web Dev Hackathon","time":"08:00 - 17:00","color":"cev-teal"},{"day":"10","mon":"APR","title":"Tech Talk: AI in Practice","time":"14:00 - 16:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-user-plus","color":"act-green","text":"3 new members joined","time":"2 hours ago"},{"icon":"fa-calendar-check","color":"act-blue","text":"Web Dev Hackathon was approved","time":"1 day ago"},{"icon":"fa-coins","color":"act-yellow","text":"Budget request of P5,000 submitted","time":"2 days ago"},{"icon":"fa-bullhorn","color":"act-teal","text":"Announcement posted by James Dela Cruz","time":"3 days ago"},{"icon":"fa-user-minus","color":"act-red","text":"1 member left the club","time":"5 days ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/ITSlogo.jpg" alt="ITS logo">
              <div class="club-item-info">
                <span class="club-item-name">Information Technology Society</span>
                <span class="club-item-meta">96 members · Tech</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

            <!-- ISS -->
            <div class="club-item" data-status="active"
              data-name="Information System Society"
              data-logo="./assets/pictures/ISSlogo.png"
              data-category="Tech"
              data-founded="Jan 2019"
              data-room="ISS Office"
              data-desc="Information Systems Society is a group that focuses on using technology to manage and improve business processes, helping students understand how IT supports organizations."
              data-members="87" data-events="5" data-budget="&#8369;14,200" data-attendance="88%"
              data-officers='[{"name":"Maria Santos","pos":"President","color":"oa-teal","lead":true},{"name":"Luis Garcia","pos":"Vice President","color":"oa-blue"},{"name":"Carla Mendoza","pos":"Secretary","color":"oa-yellow"},{"name":"Ray Buenaventura","pos":"Treasurer","color":"oa-green"}]'
              data-upcoming='[{"day":"02","mon":"APR","title":"IS Forum 2026","time":"09:00 - 12:00","color":"cev-teal"},{"day":"08","mon":"APR","title":"Systems Design Workshop","time":"13:00 - 17:00","color":"cev-blue"},{"day":"15","mon":"APR","title":"Alumni Talk: IS in Industry","time":"10:00 - 12:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-file-circle-check","color":"act-teal","text":"IS Forum proposal approved","time":"1 day ago"},{"icon":"fa-user-plus","color":"act-green","text":"5 new members joined this week","time":"2 days ago"},{"icon":"fa-coins","color":"act-yellow","text":"Budget of P3,500 released for forum","time":"3 days ago"},{"icon":"fa-bullhorn","color":"act-blue","text":"Workshop announcement posted by Maria Santos","time":"4 days ago"},{"icon":"fa-calendar-check","color":"act-green","text":"Systems Design Workshop confirmed","time":"5 days ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/ISSlogo.png" alt="ISS logo">
              <div class="club-item-info">
                <span class="club-item-name">Information System Society</span>
                <span class="club-item-meta">87 members · Tech</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

            <!-- Artisan Society -->
            <div class="club-item" data-status="active"
              data-name="Artisan Society"
              data-logo="./assets/pictures/artisan.jpg"
              data-category="Arts"
              data-founded="Aug 2017"
              data-room="ArcTech Building"
              data-desc="Artisan Society is an art club where students express creativity through various forms of art like drawing, painting, and crafts. It encourages talent, collaboration, and artistic growth."
              data-members="80" data-events="9" data-budget="&#8369;22,000" data-attendance="91%"
              data-officers='[{"name":"Elena Cruz","pos":"President","color":"oa-yellow","lead":true},{"name":"Diego Ramos","pos":"Vice President","color":"oa-blue"},{"name":"Tessa Villanueva","pos":"Secretary","color":"oa-teal"},{"name":"Josh Navarro","pos":"Treasurer","color":"oa-green"}]'
              data-upcoming='[{"day":"05","mon":"APR","title":"Spring Art Exhibition","time":"09:00 - 18:00","color":"cev-teal"},{"day":"12","mon":"APR","title":"Mural Painting Workshop","time":"13:00 - 16:00","color":"cev-blue"},{"day":"20","mon":"APR","title":"Crafts Fair","time":"08:00 - 17:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-palette","color":"act-yellow","text":"Spring Art Exhibition finalized","time":"3 hours ago"},{"icon":"fa-user-plus","color":"act-green","text":"7 new members joined","time":"1 day ago"},{"icon":"fa-coins","color":"act-yellow","text":"Materials budget of P8,000 approved","time":"2 days ago"},{"icon":"fa-bullhorn","color":"act-teal","text":"Mural workshop announced by Elena Cruz","time":"4 days ago"},{"icon":"fa-star","color":"act-blue","text":"Club received Best Arts Club nomination","time":"1 week ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/artisan.jpg" alt="Artisan logo">
              <div class="club-item-info">
                <span class="club-item-name">Artisan Society</span>
                <span class="club-item-meta">80 members · Arts</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

            <!-- CTS -->
            <div class="club-item" data-status="active"
              data-name="Computer Technology Society"
              data-logo="./assets/pictures/comptechlogo.jpg"
              data-category="Tech"
              data-founded="Feb 2018"
              data-room="Room 305, Liberal Arts"
              data-desc="An organization focused on developing skills in computer systems, hardware, and software through hands-on learning."
              data-members="65" data-events="4" data-budget="&#8369;9,800" data-attendance="90%"
              data-officers='[{"name":"Patrick Soriano","pos":"President","color":"oa-blue","lead":true},{"name":"Hannah Lee","pos":"Vice President","color":"oa-green"},{"name":"Kevin Tan","pos":"Secretary","color":"oa-teal"},{"name":"Rina Flores","pos":"Treasurer","color":"oa-yellow"}]'
              data-upcoming='[{"day":"03","mon":"APR","title":"Hardware Teardown Lab","time":"13:00 - 16:00","color":"cev-blue"},{"day":"09","mon":"APR","title":"Network Setup Bootcamp","time":"08:00 - 12:00","color":"cev-teal"},{"day":"17","mon":"APR","title":"CTS Tech Quiz Bee","time":"14:00 - 17:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-screwdriver-wrench","color":"act-blue","text":"Hardware Teardown Lab materials prepared","time":"5 hours ago"},{"icon":"fa-user-plus","color":"act-green","text":"2 new members joined","time":"1 day ago"},{"icon":"fa-calendar-check","color":"act-teal","text":"Network Bootcamp confirmed","time":"2 days ago"},{"icon":"fa-coins","color":"act-yellow","text":"Equipment fund of P2,000 released","time":"3 days ago"},{"icon":"fa-bullhorn","color":"act-blue","text":"Quiz Bee announced by Patrick Soriano","time":"5 days ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/comptechlogo.jpg" alt="CTS logo">
              <div class="club-item-info">
                <span class="club-item-name">Computer Technology Society</span>
                <span class="club-item-meta">65 members · Tech</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

            <!-- CAPA -->
            <div class="club-item" data-status="active"
              data-name="CHMSU-Alijis Performing Arts"
              data-logo="./assets/pictures/capalogo.jpg"
              data-category="Arts"
              data-founded="Sep 2019"
              data-room="Studio 1, Arts Center"
              data-desc="A club for students passionate about dance, music, and theater, showcasing talent through performances and events."
              data-members="25" data-events="6" data-budget="&#8369;12,500" data-attendance="85%"
              data-officers='[{"name":"Jasmine Uy","pos":"President","color":"oa-teal","lead":true},{"name":"Ramon Dela Torre","pos":"Vice President","color":"oa-yellow"},{"name":"Mia Castillo","pos":"Secretary","color":"oa-blue"},{"name":"Nico Bautista","pos":"Treasurer","color":"oa-green"}]'
              data-upcoming='[{"day":"06","mon":"APR","title":"Spring Stage Performance","time":"18:00 - 21:00","color":"cev-teal"},{"day":"13","mon":"APR","title":"Dance Intensive Workshop","time":"09:00 - 13:00","color":"cev-blue"},{"day":"22","mon":"APR","title":"Open Mic Night","time":"17:00 - 20:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-music","color":"act-teal","text":"Spring Stage rehearsal schedule posted","time":"1 hour ago"},{"icon":"fa-user-plus","color":"act-green","text":"4 new members auditioned successfully","time":"2 days ago"},{"icon":"fa-coins","color":"act-yellow","text":"Costume budget of P4,500 approved","time":"3 days ago"},{"icon":"fa-calendar-check","color":"act-blue","text":"Open Mic Night venue confirmed","time":"4 days ago"},{"icon":"fa-star","color":"act-yellow","text":"Club won Best Performance at Lantern Fest","time":"1 week ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/capalogo.jpg" alt="CAPA logo">
              <div class="club-item-info">
                <span class="club-item-name">CHMSU-Alijis Performing Arts</span>
                <span class="club-item-meta">25 members · Arts</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

            <!-- Pythons Esports -->
            <div class="club-item" data-status="active"
              data-name="CHMSU Python Esports"
              data-logo="./assets/pictures/chmsupythons.jpg"
              data-category="Sports"
              data-founded="Apr 2018"
              data-room="Lab 3, Science Building"
              data-desc="A gaming community that brings students together through competitive esports, teamwork, and tournaments."
              data-members="19" data-events="8" data-budget="&#8369;20,000" data-attendance="93%"
              data-officers='[{"name":"Zack Mendez","pos":"Team Captain","color":"oa-blue","lead":true},{"name":"Trisha Lim","pos":"Vice Captain","color":"oa-teal"},{"name":"Gab Santos","pos":"Secretary","color":"oa-green"},{"name":"Iris Navarro","pos":"Treasurer","color":"oa-yellow"}]'
              data-upcoming='[{"day":"01","mon":"APR","title":"Inter-School MLBB Tournament","time":"10:00 - 18:00","color":"cev-blue"},{"day":"07","mon":"APR","title":"Valorant Scrimmage","time":"14:00 - 18:00","color":"cev-teal"},{"day":"14","mon":"APR","title":"Esports Recruitment Day","time":"09:00 - 12:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-gamepad","color":"act-blue","text":"MLBB Tournament bracket released","time":"30 minutes ago"},{"icon":"fa-trophy","color":"act-yellow","text":"Team placed 2nd at regional qualifier","time":"1 day ago"},{"icon":"fa-user-plus","color":"act-green","text":"3 new players joined the roster","time":"2 days ago"},{"icon":"fa-coins","color":"act-yellow","text":"Tournament fund of P6,000 allocated","time":"3 days ago"},{"icon":"fa-calendar-check","color":"act-teal","text":"Valorant Scrimmage schedule confirmed","time":"4 days ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/chmsupythons.jpg" alt="Pythons logo">
              <div class="club-item-info">
                <span class="club-item-name">CHMSU Python Esports</span>
                <span class="club-item-meta">19 members · Sports</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

            <!-- RECA -->
            <div class="club-item" data-status="active"
              data-name="Research Enthusiasts of CHMSU Alijis"
              data-logo="./assets/pictures/recalogo.jpg"
              data-category="Academic"
              data-founded="Jun 2020"
              data-room="Press Room, Main Building"
              data-desc="A group that encourages students to engage in research, develop critical thinking, and explore new ideas."
              data-members="22" data-events="3" data-budget="&#8369;8,500" data-attendance="87%"
              data-officers='[{"name":"Dr. Anna Villafuerte","pos":"Faculty Adviser","color":"oa-blue","lead":true},{"name":"Lester Ocampo","pos":"President","color":"oa-teal"},{"name":"Bianca Reyes","pos":"Secretary","color":"oa-green"},{"name":"Jomar Cruz","pos":"Treasurer","color":"oa-yellow"}]'
              data-upcoming='[{"day":"05","mon":"APR","title":"Research Paper Defense","time":"09:00 - 12:00","color":"cev-blue"},{"day":"11","mon":"APR","title":"Data Analysis Workshop","time":"13:00 - 16:00","color":"cev-teal"},{"day":"18","mon":"APR","title":"Publication Drive","time":"10:00 - 13:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-book-open","color":"act-blue","text":"2 papers submitted to regional journal","time":"6 hours ago"},{"icon":"fa-user-plus","color":"act-green","text":"1 new member joined RECA","time":"2 days ago"},{"icon":"fa-calendar-check","color":"act-teal","text":"Paper Defense schedule confirmed","time":"3 days ago"},{"icon":"fa-coins","color":"act-yellow","text":"Research grant of P2,500 received","time":"4 days ago"},{"icon":"fa-bullhorn","color":"act-blue","text":"Publication drive announced by Lester Ocampo","time":"6 days ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/recalogo.jpg" alt="RECA logo">
              <div class="club-item-info">
                <span class="club-item-name">Research Enthusiasts of CHMSU Alijis</span>
                <span class="club-item-meta">22 members · Academic</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

            <!-- Engineering Society -->
            <div class="club-item" data-status="active"
              data-name="Engineering Society"
              data-logo="./assets/pictures/engineeringlogo.jpg"
              data-category="Academic"
              data-founded="Nov 2018"
              data-room="Room 210, Science Wing"
              data-desc="A group for engineering students that promotes technical skills, innovation, and collaboration through projects and activities."
              data-members="78" data-events="4" data-budget="&#8369;7,200" data-attendance="92%"
              data-officers='[{"name":"Carlo Delos Reyes","pos":"President","color":"oa-blue","lead":true},{"name":"Felicia Tan","pos":"Vice President","color":"oa-teal"},{"name":"Aldrin Campos","pos":"Secretary","color":"oa-yellow"},{"name":"Sheila Macaraeg","pos":"Treasurer","color":"oa-green"}]'
              data-upcoming='[{"day":"04","mon":"APR","title":"Bridge Building Challenge","time":"08:00 - 17:00","color":"cev-blue"},{"day":"10","mon":"APR","title":"Engineering Symposium","time":"09:00 - 15:00","color":"cev-teal"},{"day":"19","mon":"APR","title":"Plant Visit: SM Steel","time":"07:00 - 17:00","color":"cev-green"}]'
              data-activity='[{"icon":"fa-wrench","color":"act-blue","text":"Bridge Challenge materials distributed","time":"4 hours ago"},{"icon":"fa-user-plus","color":"act-green","text":"6 new members enrolled this semester","time":"1 day ago"},{"icon":"fa-calendar-check","color":"act-teal","text":"Plant Visit approved by administration","time":"2 days ago"},{"icon":"fa-coins","color":"act-yellow","text":"Tools budget of P3,200 released","time":"3 days ago"},{"icon":"fa-bullhorn","color":"act-blue","text":"Symposium speakers confirmed by Carlo Delos Reyes","time":"5 days ago"}]'>
              <img class="club-item-logo" src="./assets/pictures/engineeringlogo.jpg" alt="Engineering logo">
              <div class="club-item-info">
                <span class="club-item-name">Engineering Society</span>
                <span class="club-item-meta">78 members · Academic</span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot active-dot"></span></div>
            </div>

          </div>
        </div>
        <!-- END LEFT PANEL -->

        <!-- RIGHT PANEL -->
        <div class="clubs-right-panel" id="rightPanel">

          <div class="club-detail-hero" id="detailHero">
            <img class="cdh-logo-img" id="detailLogo" src="./assets/pictures/ITSlogo.jpg" alt="Club logo">
            <div class="cdh-info">
              <div class="cdh-top">
                <h1 class="cdh-name" id="detailName">Information Technology Society</h1>
                <span class="cdh-status-badge status-active" id="detailStatus">Active</span>
              </div>
              <p class="cdh-desc" id="detailDesc">Information Technology Society is a group that promotes IT skills and knowledge through activities, helping students prepare for tech careers.</p>
              <div class="cdh-meta-row">
                <span class="cdh-meta-pill"><i class="fas fa-tag"></i> <span id="detailCategory">Tech</span></span>
                <span class="cdh-meta-pill"><i class="fas fa-calendar-plus"></i> Founded <span id="detailFounded">Mar 2021</span></span>
                <span class="cdh-meta-pill"><i class="fas fa-location-dot"></i> <span id="detailRoom">ITS Office</span></span>
              </div>
            </div>
            <div class="cdh-actions">
              <button class="cdh-btn-secondary"><i class="fas fa-pen"></i> Edit</button>
              <button class="cdh-btn-primary"><i class="fas fa-envelope"></i> Message</button>
            </div>
          </div>

          <div class="cd-stat-row">
            <div class="cd-stat"><span class="cd-stat-value" id="statMembers">96</span><span class="cd-stat-label">Members</span></div>
            <div class="cd-stat-divider"></div>
            <div class="cd-stat"><span class="cd-stat-value" id="statEvents">7</span><span class="cd-stat-label">Events This Month</span></div>
            <div class="cd-stat-divider"></div>
            <div class="cd-stat"><span class="cd-stat-value" id="statBudget">&#8369;18,400</span><span class="cd-stat-label">Budget</span></div>
            <div class="cd-stat-divider"></div>
            <div class="cd-stat"><span class="cd-stat-value" id="statAttendance">94%</span><span class="cd-stat-label">Attendance Rate</span></div>
          </div>

          <div class="cd-details-grid">
            <!-- Officers card -->
            <div class="cd-detail-card">
              <div class="cd-card-header">
                <h3 class="cd-card-title"><i class="fas fa-user-tie"></i> Club Officers</h3>
                <a href="#" class="see-all-link">View All <i class="fas fa-chevron-right"></i></a>
              </div>
              <div class="officer-list" id="officerList"></div>
            </div>

            <!-- Upcoming Events card -->
            <div class="cd-detail-card">
              <div class="cd-card-header">
                <h3 class="cd-card-title"><i class="fas fa-calendar-days"></i> Upcoming Events</h3>
                <a href="#" class="see-all-link">View All <i class="fas fa-chevron-right"></i></a>
              </div>
              <div class="club-events-list" id="eventsList"></div>
            </div>

            <!-- Recent Activity card -->
            <div class="cd-detail-card cd-wide-card">
              <div class="cd-card-header">
                <h3 class="cd-card-title"><i class="fas fa-clock-rotate-left"></i> Recent Activity</h3>
              </div>
              <div class="activity-list" id="activityList"></div>
            </div>
          </div>

        </div>
        <!-- END RIGHT PANEL -->

      </div>
    </div>
  </main>
</div>

<!-- NEW CLUB MODAL -->
<div class="modal-overlay" id="newClubOverlay">
  <div class="modal" id="newClubModal">
    <div class="modal-header">
      <div class="modal-title-group">
        <div class="modal-icon"><i class="fas fa-building-columns"></i></div>
        <div>
          <h2 class="modal-title">Create New Club</h2>
          <p class="modal-subtitle">Fill in the details to register a new club</p>
        </div>
      </div>
      <button class="modal-close" id="modalClose"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="modal-logo-upload" id="logoUploadArea">
        <div class="logo-upload-inner">
          <i class="fas fa-cloud-arrow-up"></i>
          <span>Upload Club Logo</span>
          <small>PNG, JPG up to 2MB</small>
        </div>
        <input type="file" id="logoFileInput" accept="image/*" style="display:none">
        <img id="logoPreview" class="logo-preview-img" src="" alt="Logo preview" style="display:none">
      </div>
      <div class="modal-fields">
        <div class="field-group field-full">
          <label class="field-label">Club Name <span class="field-required">*</span></label>
          <input type="text" class="field-input" id="newClubName" placeholder="e.g. Photography Club" />
        </div>
        <div class="field-group">
          <label class="field-label">Category <span class="field-required">*</span></label>
          <div class="field-select-wrap">
            <select class="field-input field-select" id="newClubCategory">
              <option value="" disabled selected>Select category</option>
              <option>Tech</option><option>Business</option><option>Sports</option>
              <option>Academic</option><option>Arts</option><option>Science</option>
              <option>Media</option><option>Advocacy</option>
            </select>
            <i class="fas fa-chevron-down field-select-arrow"></i>
          </div>
        </div>
        <div class="field-group">
          <label class="field-label">Status</label>
          <div class="field-select-wrap">
            <select class="field-input field-select" id="newClubStatus">
              <option value="active">Active</option>
              <option value="pending" selected>Pending</option>
            </select>
            <i class="fas fa-chevron-down field-select-arrow"></i>
          </div>
        </div>
        <div class="field-group">
          <label class="field-label">Founded</label>
          <input type="text" class="field-input" id="newClubFounded" placeholder="e.g. Jan 2024" />
        </div>
        <div class="field-group">
          <label class="field-label">Meeting Room / Location</label>
          <input type="text" class="field-input" id="newClubRoom" placeholder="e.g. Room 201, Main Building" />
        </div>
        <div class="field-group field-full">
          <label class="field-label">Description <span class="field-required">*</span></label>
          <textarea class="field-input field-textarea" id="newClubDesc" placeholder="Briefly describe the club's mission and activities..."></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="modal-btn-cancel" id="modalCancel">Cancel</button>
      <button class="modal-btn-submit" id="modalSubmit"><i class="fas fa-plus"></i> Create Club</button>
    </div>
  </div>
</div>

<script>
// ─── ELEMENTS ───────────────────────────────────────────────────────────────
const filterTabs   = document.querySelectorAll('.filter-tab');
const filterInput  = document.getElementById('clubFilterInput');
const rightPanel   = document.getElementById('rightPanel');
const detailLogo     = document.getElementById('detailLogo');
const detailName     = document.getElementById('detailName');
const detailStatus   = document.getElementById('detailStatus');
const detailDesc     = document.getElementById('detailDesc');
const detailCategory = document.getElementById('detailCategory');
const detailFounded  = document.getElementById('detailFounded');
const detailRoom     = document.getElementById('detailRoom');
const statMembers    = document.getElementById('statMembers');
const statEvents     = document.getElementById('statEvents');
const statBudget     = document.getElementById('statBudget');
const statAttendance = document.getElementById('statAttendance');
const officerList    = document.getElementById('officerList');
const eventsList     = document.getElementById('eventsList');
const activityList   = document.getElementById('activityList');

// ─── RENDER HELPERS ─────────────────────────────────────────────────────────
function renderOfficers(officers) {
  if (!officers || officers.length === 0) {
    officerList.innerHTML = '<p style="font-size:12px;color:var(--text-light);padding:8px 0;">No officers listed.</p>';
    return;
  }
  officerList.innerHTML = officers.map(o => `
    <div class="officer-item">
      <div class="officer-avatar ${o.color || 'oa-blue'}">${o.name.charAt(0)}</div>
      <div class="officer-info">
        <span class="officer-name">${o.name}</span>
        <span class="officer-pos">${o.pos}</span>
      </div>
      ${o.lead ? '<span class="officer-badge">Lead</span>' : ''}
    </div>
  `).join('');
}

function renderEvents(events) {
  if (!events || events.length === 0) {
    eventsList.innerHTML = '<p style="font-size:12px;color:var(--text-light);padding:8px 0;">No upcoming events.</p>';
    return;
  }
  eventsList.innerHTML = events.map(e => `
    <div class="club-event-item ${e.color}">
      <div class="cev-date-block">
        <span class="cev-day">${e.day}</span>
        <span class="cev-mon">${e.mon}</span>
      </div>
      <div class="cev-info">
        <span class="cev-title">${e.title}</span>
        <span class="cev-time"><i class="fas fa-clock"></i> ${e.time}</span>
      </div>
    </div>
  `).join('');
}

function renderActivity(activities) {
  if (!activities || activities.length === 0) {
    activityList.innerHTML = '<p style="font-size:12px;color:var(--text-light);padding:8px 0;">No recent activity.</p>';
    return;
  }
  activityList.innerHTML = activities.map(a => `
    <div class="activity-item">
      <div class="act-icon ${a.color}"><i class="fas ${a.icon}"></i></div>
      <div class="act-info">
        <span class="act-text">${a.text}</span>
        <span class="act-time">${a.time}</span>
      </div>
    </div>
  `).join('');
}

// ─── UPDATE RIGHT PANEL ─────────────────────────────────────────────────────
function updateRightPanel(item) {
  const d = item.dataset;

  rightPanel.classList.remove('panel-fade');
  void rightPanel.offsetWidth;
  rightPanel.classList.add('panel-fade');

  const leftLogo = item.querySelector('.club-item-logo');
  if (leftLogo && leftLogo.tagName === 'IMG') {
    detailLogo.src = leftLogo.src;
    detailLogo.alt = leftLogo.alt;
  } else {
    detailLogo.src = '';
    detailLogo.alt = d.name;
  }

  detailName.textContent     = d.name;
  detailDesc.textContent     = d.desc;
  detailCategory.textContent = d.category;
  detailFounded.textContent  = d.founded;
  detailRoom.textContent     = d.room;
  statMembers.textContent    = d.members;
  statEvents.textContent     = d.events;
  statBudget.textContent     = d.budget;
  statAttendance.textContent = d.attendance;

  const isActive  = d.status === 'active';
  const isPending = d.status === 'pending';
  detailStatus.textContent = isActive ? 'Active' : isPending ? 'Pending' : 'Inactive';
  detailStatus.className   = 'cdh-status-badge ' +
    (isActive ? 'status-active' : isPending ? 'status-pending' : 'status-inactive');

  try { renderOfficers(JSON.parse(d.officers || '[]')); }  catch(e) { officerList.innerHTML = ''; }
  try { renderEvents(JSON.parse(d.upcoming || '[]')); }    catch(e) { eventsList.innerHTML = ''; }
  try { renderActivity(JSON.parse(d.activity || '[]')); }  catch(e) { activityList.innerHTML = ''; }
}

// ─── CLUB ITEM BINDING ──────────────────────────────────────────────────────
function getAllClubItems() { return document.querySelectorAll('.club-item'); }

function bindClubItem(item) {
  item.addEventListener('click', () => {
    getAllClubItems().forEach(i => i.classList.remove('selected'));
    item.classList.add('selected');
    updateRightPanel(item);
  });
}

getAllClubItems().forEach(bindClubItem);

// ─── FILTER TABS ────────────────────────────────────────────────────────────
filterTabs.forEach(tab => {
  tab.addEventListener('click', () => {
    filterTabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const filter = tab.dataset.filter;
    getAllClubItems().forEach(item => {
      item.style.display = (filter === 'all' || item.dataset.status === filter) ? 'flex' : 'none';
    });
  });
});

// ─── INLINE SEARCH ──────────────────────────────────────────────────────────
filterInput.addEventListener('input', function () {
  const q = this.value.toLowerCase();
  getAllClubItems().forEach(item => {
    const name = item.querySelector('.club-item-name').textContent.toLowerCase();
    item.style.display = name.includes(q) ? 'flex' : 'none';
  });
});

// ─── INIT ───────────────────────────────────────────────────────────────────
const initialSelected = document.querySelector('.club-item.selected');
if (initialSelected) updateRightPanel(initialSelected);

// ─── NEW CLUB MODAL ─────────────────────────────────────────────────────────
const addClubBtn     = document.querySelector('.add-club-btn');
const overlay        = document.getElementById('newClubOverlay');
const modalClose     = document.getElementById('modalClose');
const modalCancel    = document.getElementById('modalCancel');
const modalSubmit    = document.getElementById('modalSubmit');
const logoUploadArea = document.getElementById('logoUploadArea');
const logoFileInput  = document.getElementById('logoFileInput');
const logoPreview    = document.getElementById('logoPreview');

function openModal()  { overlay.classList.add('modal-visible');    document.body.style.overflow = 'hidden'; }
function closeModal() { overlay.classList.remove('modal-visible'); document.body.style.overflow = ''; }

addClubBtn.addEventListener('click', openModal);
modalClose.addEventListener('click', closeModal);
modalCancel.addEventListener('click', closeModal);
overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

logoUploadArea.addEventListener('click', () => logoFileInput.click());
logoFileInput.addEventListener('change', () => {
  const file = logoFileInput.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.src = e.target.result;
    logoPreview.style.display = 'block';
    logoUploadArea.querySelector('.logo-upload-inner').style.display = 'none';
  };
  reader.readAsDataURL(file);
});

modalSubmit.addEventListener('click', () => {
  const nameEl     = document.getElementById('newClubName');
  const categoryEl = document.getElementById('newClubCategory');
  const descEl     = document.getElementById('newClubDesc');
  const name       = nameEl.value.trim();
  const category   = categoryEl.value;
  const status     = document.getElementById('newClubStatus').value;
  const founded    = document.getElementById('newClubFounded').value.trim() || 'N/A';
  const room       = document.getElementById('newClubRoom').value.trim() || 'TBA';
  const desc       = descEl.value.trim();

  [nameEl, categoryEl, descEl].forEach(el => el.classList.toggle('field-error', !el.value.trim()));
  if (!name || !category || !desc) return;

  const logoSrc = logoPreview.style.display !== 'none' ? logoPreview.src : '';
  const initial = name.charAt(0).toUpperCase();
  const dotClass = status === 'active' ? 'active-dot' : 'pending-dot';

  const newItem = document.createElement('div');
  newItem.className = 'club-item';
  Object.assign(newItem.dataset, {
    status, name, logo: logoSrc, category, founded, room, desc,
    members: '0', events: '0', budget: '&#8369;0', attendance: '-',
    officers: '[]', upcoming: '[]', activity: '[]'
  });
  newItem.innerHTML = `
    ${logoSrc
      ? `<img class="club-item-logo" src="${logoSrc}" alt="${name} logo">`
      : `<div class="club-item-logo club-item-logo-initial">${initial}</div>`}
    <div class="club-item-info">
      <span class="club-item-name">${name}</span>
      <span class="club-item-meta">0 members &middot; ${category}</span>
    </div>
    <div class="club-item-right"><span class="ci-status-dot ${dotClass}"></span></div>
  `;

  bindClubItem(newItem);
  document.getElementById('clubList').appendChild(newItem);
  getAllClubItems().forEach(i => i.classList.remove('selected'));
  newItem.classList.add('selected');
  updateRightPanel(newItem);
  closeModal();

  // Reset form
  nameEl.value = ''; categoryEl.value = '';
  document.getElementById('newClubStatus').value = 'pending';
  document.getElementById('newClubFounded').value = '';
  document.getElementById('newClubRoom').value = '';
  descEl.value = '';
  logoPreview.style.display = 'none'; logoPreview.src = '';
  logoUploadArea.querySelector('.logo-upload-inner').style.display = 'flex';
  logoFileInput.value = '';
});
</script>
</body>
</html>