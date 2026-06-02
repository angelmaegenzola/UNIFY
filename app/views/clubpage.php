<?php require_once __DIR__ . '/../../app/controllers/clubpage_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UNIFY — Clubs</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/public/assets/css/clubpage.css"/>
  <link rel="stylesheet" href="/public/assets/css/transitions.css" />
</head>
<body>
<div class="app">


  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
  <div class="sheet-overlay" id="sheetOverlay" onclick="closeSheet()"></div>
    <aside class="sidebar" id="mainSidebar">
<div class="sidebar-brand">
  <img src="/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
  <div class="brand-text">
    <div class="brand-name">UNIFY</div>
    <div class="brand-tagline">Club Management System</div>
  </div>
</div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">MAIN MENU</div>
      <a href="index.php?page=dashboard" class="nav-item"><i class="fas fa-house"></i><span>Dashboard</span></a>
      <a href="index.php?page=members"   class="nav-item"><i class="fas fa-users"></i><span>Members</span></a>
      <a href="index.php?page=clubpage"  class="nav-item active"><i class="fas fa-building-columns"></i><span>Clubs</span></a>
      <a href="index.php?page=events"    class="nav-item"><i class="fas fa-calendar-days"></i><span>Events</span></a>
      <div class="nav-section-label">REPORTS</div>
      <a href="index.php?page=reports"   class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
    </nav>
    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <?php if(!empty($avatar_url)): ?><img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" /><?php else: ?><span class="profile-avatar-fallback"><?= $adminInitial ?></span><?php endif; ?>
          <span class="profile-online-dot"></span>
        </div>
        <a href="index.php?page=adminprofile" class="profile-link">
          <div class="profile-info">
            <span class="profile-name"><?= htmlspecialchars($adminName) ?></span>
            <span class="profile-role">Club Admin</span>
          </div>
        </a>
        <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="#" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
</aside>



  <main class="main">
    <header class="topbar">
      <div class="topbar-left">
        <button class="topbar-hamburger" onclick="toggleSidebar()" title="Menu">
          <img src="/assets/pictures/unifylogo.png" alt="Menu" class="topbar-logo-btn" />
        </button>
        <div class="topbar-title-group">
          <span class="topbar-page-title">Clubs</span>
          <span class="topbar-date"><?= date('l, F j, Y') ?></span>
        </div>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search clubs, categories…" id="globalSearch" oninput="filterClubs()"/>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications"><i class="fas fa-bell"></i></button>
        <button class="hamburger-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <div class="topbar-profile">
            <div class="topbar-avatar">
              <?php if (!empty($avatar_url)): ?>
                <img src="<?= $avatar_url ?>" alt="Avatar" class="topbar-avatar-img" />
              <?php else: ?>
                <?= $adminInitial ?>
              <?php endif; ?>
            </div>
            <div class="topbar-profile-info">
              <span class="tp-name"><?= htmlspecialchars($adminName) ?></span>
              <span class="tp-role">Club Admin</span>
            </div>
            <i class="fas fa-chevron-down tp-caret"></i>
          </div>
      </div>
    </header>

    <div class="content">
      <div class="clubs-layout">


        <div class="clubs-left-panel">
          <div class="panel-header">
            <div class="panel-header-top">
              <h2 class="panel-title">All Clubs</h2>
              <button class="add-club-btn" onclick="openAddModal()"><i class="fas fa-plus"></i> New Club</button>
            </div>
            <div class="filter-tabs">
              <button class="filter-tab active" data-filter="all">All <span class="tab-count"><?= $total ?></span></button>
              <button class="filter-tab" data-filter="active">Active <span class="tab-count"><?= $active ?></span></button>
              <button class="filter-tab" data-filter="pending">Pending <span class="tab-count"><?= $pending ?></span></button>
            </div>
            <div class="panel-search">
              <i class="fas fa-magnifying-glass"></i>
              <input type="text" placeholder="Filter clubs…" id="clubFilterInput" oninput="filterClubs()"/>
              <button class="sort-btn" title="Sort"><i class="fas fa-arrow-up-wide-short"></i></button>
            </div>
          </div>

          <div class="club-list" id="clubList">
            <?php foreach ($clubs as $i => $club):
                $officers_json = buildOfficersJson($officersMap[$club['id']] ?? []);
                $upcoming_json = buildUpcomingJson($upcomingMap[$club['id']] ?? []);
                $dot_class     = $club['status'] === 'active' ? 'active-dot' : ($club['status'] === 'pending' ? 'pending-dot' : 'inactive-dot');
                $logo_detail   = !empty($club['logo_path']) ? htmlspecialchars($club['logo_path']) : '';
            ?>
            <div class="club-item <?= $i === 0 ? 'selected' : '' ?>"
              data-id="<?= $club['id'] ?>"
              data-status="<?= htmlspecialchars($club['status']) ?>"
              data-name="<?= htmlspecialchars($club['name']) ?>"
              data-logo="<?= $logo_detail ?>"
              data-acronym="<?= htmlspecialchars($club['acronym'] ?? '') ?>"
              data-category="<?= htmlspecialchars($club['category'] ?? '') ?>"
              data-founded="<?= htmlspecialchars($club['founded'] ?? '') ?>"
              data-room="<?= htmlspecialchars($club['room'] ?? '') ?>"
              data-desc="<?= htmlspecialchars($club['description'] ?? '') ?>"
              data-members="<?= (int)$club['member_count'] ?>"
              data-events="<?= (int)$club['event_count'] ?>"
              data-officers='<?= htmlspecialchars($officers_json) ?>'
              data-upcoming='<?= htmlspecialchars($upcoming_json) ?>'>
              <?= logoOrInitial($club) ?>
              <div class="club-item-info">
                <span class="club-item-name"><?= htmlspecialchars($club['name']) ?></span>
                <span class="club-item-meta"><?= (int)$club['member_count'] ?> members · <?= htmlspecialchars($club['category'] ?? '') ?></span>
              </div>
              <div class="club-item-right"><span class="ci-status-dot <?= $dot_class ?>"></span></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>


        <div class="clubs-right-panel" id="rightPanel">
          <div class="sheet-handle"></div>

          <?php if (!empty($clubs)): $c = $clubs[0]; ?>
          <div class="club-detail-hero" id="detailHero">
            <?php if (!empty($c['logo_path'])): ?>
              <img class="cdh-logo-img" id="detailLogo" src="<?= htmlspecialchars($c['logo_path']) ?>" alt="Club logo">
            <?php else: ?>
              <div class="cdh-logo-img club-item-logo-initial" id="detailLogo"><?= strtoupper(substr($c['name'],0,1)) ?></div>
            <?php endif; ?>
            <div class="cdh-info">
              <div class="cdh-top">
                <h1 class="cdh-name" id="detailName"><?= htmlspecialchars($c['name']) ?></h1>
                <span class="cdh-status-badge status-<?= $c['status'] ?>" id="detailStatus"><?= ucfirst($c['status']) ?></span>
              </div>
              <p class="cdh-desc" id="detailDesc"><?= htmlspecialchars($c['description'] ?? '') ?></p>
              <div class="cdh-meta-row">
                <span class="cdh-meta-pill"><i class="fas fa-tag"></i> <span id="detailCategory"><?= htmlspecialchars($c['category'] ?? '') ?></span></span>
                <span class="cdh-meta-pill"><i class="fas fa-calendar-plus"></i> Founded <span id="detailFounded"><?= htmlspecialchars($c['founded'] ?? '') ?></span></span>
                <span class="cdh-meta-pill"><i class="fas fa-location-dot"></i> <span id="detailRoom"><?= htmlspecialchars($c['room'] ?? '') ?></span></span>
              </div>
            </div>
            <div class="cdh-actions">
              <button class="cdh-btn-secondary" id="btnEditClub" onclick="openEditModal()"><i class="fas fa-pen"></i> Edit</button>
              <button class="cdh-btn-danger"    id="btnDeleteClub" onclick="openDeleteModal()" ><i class="fas fa-trash"></i> Delete</button>
            </div>
          </div>

          <!-- STAT ROW: Budget removed, only Members and Upcoming Events -->
          <div class="cd-stat-row">
            <div class="cd-stat"><span class="cd-stat-value" id="statMembers"><?= (int)$c['member_count'] ?></span><span class="cd-stat-label">Members</span></div>
            <div class="cd-stat-divider"></div>
            <div class="cd-stat"><span class="cd-stat-value" id="statEvents"><?= (int)$c['event_count'] ?></span><span class="cd-stat-label">Upcoming Events</span></div>
          </div>

          <div class="cd-details-grid">
            <div class="cd-detail-card">
              <div class="cd-card-header">
                <h3 class="cd-card-title"><i class="fas fa-user-tie"></i> Club Officers</h3>
              </div>
              <div class="officer-list" id="officerList"></div>
            </div>
            <div class="cd-detail-card">
              <div class="cd-card-header">
                <h3 class="cd-card-title"><i class="fas fa-calendar-days"></i> Upcoming Events</h3>
              </div>
              <div class="club-events-list" id="eventsList"></div>
            </div>
          </div>
          <?php endif; ?>

        </div>

      </div>
    </div>
  </main>
</div>


<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <div class="mh">
      <span class="mh-title"><i class="fas fa-building-columns"></i> Create New Club</span>
      <button class="mh-close" onclick="closeModal('addModal')">&times;</button>
    </div>
    <form method="POST" action="index.php?page=clubpage" enctype="multipart/form-data">
      <input type="hidden" name="act" value="add"/>

      <div class="logo-upload-area">
        <div class="logo-preview" id="addLogoPreview">
          <i class="fas fa-image"></i>
        </div>
        <div class="logo-upload-right">
          <label class="logo-upload-btn" for="addLogoInput">
            <i class="fas fa-upload"></i> Upload Logo
          </label>
          <input class="logo-upload-input" type="file" id="addLogoInput" name="logo" accept="image/*" onchange="previewLogo(this,'addLogoPreview')"/>
          <span class="logo-upload-hint">JPG, PNG, WEBP · max 2 MB</span>
        </div>
      </div>
      <div class="fg">
        <label>Club Name <span>*</span></label>
        <input type="text" name="name" placeholder="e.g. Photography Club" required/>
      </div>
      <div class="form-row-2">
        <div class="fg">
          <label>Acronym</label>
          <input type="text" name="acronym" placeholder="e.g. PC"/>
        </div>
        <div class="fg">
          <label>Category <span>*</span></label>
          <div class="custom-select-wrap" id="addCatWrap" style="position:relative;">
            <button type="button" class="custom-select-btn" id="addCatBtn" onclick="toggleClubDrop('addCat',event)">— Select —</button>
            <input type="hidden" name="category" id="addCat" required />
            <div class="custom-select-list" id="addCatList">
              <div class="custom-select-option" onclick="setClubDrop('addCat','','— Select —')">— Select —</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Tech','Tech')">Tech</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Business','Business')">Business</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Sports','Sports')">Sports</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Academic','Academic')">Academic</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Arts','Arts')">Arts</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Science','Science')">Science</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Advocacy','Advocacy')">Advocacy</div>
              <div class="custom-select-option" onclick="setClubDrop('addCat','Engineering','Engineering')">Engineering</div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-row-2">
        <div class="fg">
          <label>Founded</label>
          <input type="text" name="founded" placeholder="e.g. Jan 2024"/>
        </div>
        <div class="fg">
          <label>Room / Location</label>
          <input type="text" name="room" placeholder="e.g. Room 201"/>
        </div>
      </div>
      <div class="fg">
        <label>Status</label>
        <div class="custom-select-wrap" id="addStatusWrap" style="position:relative;">
          <button type="button" class="custom-select-btn" id="addStatusBtn" onclick="toggleClubDrop('addStatus',event)">Pending</button>
          <input type="hidden" name="status" id="addStatus" value="pending" />
          <div class="custom-select-list" id="addStatusList">
            <div class="custom-select-option" onclick="setClubDrop('addStatus','active','Active')">Active</div>
            <div class="custom-select-option selected" onclick="setClubDrop('addStatus','pending','Pending')">Pending</div>
          </div>
        </div>
      </div>
      <div class="fg">
        <label>Description <span>*</span></label>
        <textarea name="description" placeholder="Briefly describe the club's mission…" required></textarea>
      </div>
      <div class="mf">
        <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-plus"></i> Create Club</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="mh">
      <span class="mh-title"><i class="fas fa-pen"></i> Edit Club</span>
      <button class="mh-close" onclick="closeModal('editModal')">&times;</button>
    </div>
    <form method="POST" action="index.php?page=clubpage" enctype="multipart/form-data">
      <input type="hidden" name="act" value="edit"/>
      <input type="hidden" name="id"  id="editId"/>

      <div class="logo-upload-area">
        <div class="logo-preview" id="editLogoPreview">
          <i class="fas fa-image"></i>
        </div>
        <div class="logo-upload-right">
          <label class="logo-upload-btn" for="editLogoInput">
            <i class="fas fa-upload"></i> Change Logo
          </label>
          <input class="logo-upload-input" type="file" id="editLogoInput" name="logo" accept="image/*" onchange="previewLogo(this,'editLogoPreview')"/>
          <span class="logo-upload-hint">Leave blank to keep existing · max 2 MB</span>
        </div>
      </div>
      <div class="fg">
        <label>Club Name <span>*</span></label>
        <input type="text" name="name" id="editName" required/>
      </div>
      <div class="form-row-2">
        <div class="fg">
          <label>Acronym</label>
          <input type="text" name="acronym" id="editAcronym"/>
        </div>
        <div class="fg">
          <label>Category</label>
          <div class="custom-select-wrap" id="editCatWrap" style="position:relative;">
            <button type="button" class="custom-select-btn" id="editCatBtn" onclick="toggleClubDrop('editCat',event)">Tech</button>
            <input type="hidden" name="category" id="editCategory" />
            <div class="custom-select-list" id="editCatList">
              <div class="custom-select-option selected" onclick="setClubDrop('editCat','Tech','Tech')">Tech</div>
              <div class="custom-select-option" onclick="setClubDrop('editCat','Business','Business')">Business</div>
              <div class="custom-select-option" onclick="setClubDrop('editCat','Sports','Sports')">Sports</div>
              <div class="custom-select-option" onclick="setClubDrop('editCat','Academic','Academic')">Academic</div>
              <div class="custom-select-option" onclick="setClubDrop('editCat','Arts','Arts')">Arts</div>
              <div class="custom-select-option" onclick="setClubDrop('editCat','Science','Science')">Science</div>
              <div class="custom-select-option" onclick="setClubDrop('editCat','Advocacy','Advocacy')">Advocacy</div>
              <div class="custom-select-option" onclick="setClubDrop('editCat','Engineering','Engineering')">Engineering</div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-row-2">
        <div class="fg">
          <label>Founded</label>
          <input type="text" name="founded" id="editFounded"/>
        </div>
        <div class="fg">
          <label>Room / Location</label>
          <input type="text" name="room" id="editRoom"/>
        </div>
      </div>
      <div class="form-row-2">
        <div class="fg">
          <label>Status</label>
          <div class="custom-select-wrap" id="editStatusWrap" style="position:relative;">
            <button type="button" class="custom-select-btn" id="editStatusBtn" onclick="toggleClubDrop('editStatus',event)">Active</button>
            <input type="hidden" name="status" id="editStatus" value="active" />
            <div class="custom-select-list" id="editStatusList">
              <div class="custom-select-option selected" onclick="setClubDrop('editStatus','active','Active')">Active</div>
              <div class="custom-select-option" onclick="setClubDrop('editStatus','pending','Pending')">Pending</div>
              <div class="custom-select-option" onclick="setClubDrop('editStatus','inactive','Inactive')">Inactive</div>
            </div>
          </div>
        </div>
        <div class="fg">
          <label>Budget (₱)</label>
          <input type="number" name="budget" id="editBudget" min="0" step="0.01"/>
        </div>
      </div>
      <div class="fg">
        <label>Description</label>
        <textarea name="description" id="editDesc"></textarea>
      </div>
      <div class="mf">
        <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-floppy-disk"></i> Save</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <div class="mh">
      <span class="mh-title mh-title-danger"><i class="fas fa-trash"></i> Delete Club</span>
      <button class="mh-close" onclick="closeModal('deleteModal')">&times;</button>
    </div>
    <p class="del-confirm-text">
      Are you sure you want to delete <strong id="deleteClubName"></strong>?
      This will also remove all its members, events, and finances. <strong>This cannot be undone.</strong>
    </p>
    <form method="POST" action="index.php?page=clubpage">
      <input type="hidden" name="act" value="delete"/>
      <input type="hidden" name="id"  id="deleteId"/>
      <div class="mf">
        <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
        <button type="submit" class="btn-del-confirm"><i class="fas fa-trash"></i> Delete</button>
      </div>
    </form>
  </div>
</div>


<div class="toast-bar" id="toastBar"></div>



<script>
// ── Current selected club data ──
let currentClub = null;

// ── Modal helpers ──
function openModal(id)  { document.getElementById(id).classList.add('modal-visible'); }
function closeModal(id) { document.getElementById(id).classList.remove('modal-visible'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('modal-visible'); });
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.modal-visible').forEach(o => o.classList.remove('modal-visible')); });

function openAddModal() { openModal('addModal'); }

// ── Logo preview ──
function previewLogo(input, previewId) {
  const preview = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.innerHTML = `<img src="${e.target.result}" alt="Preview"/>`;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function openEditModal() {
  if (!currentClub) return;
  const d = currentClub.dataset;
  document.getElementById('editId').value       = d.id;
  document.getElementById('editName').value     = d.name;
  document.getElementById('editAcronym').value  = d.acronym  || '';
  document.getElementById('editCategory').value = d.category || '';
  document.getElementById('editFounded').value  = d.founded  || '';
  document.getElementById('editRoom').value     = d.room     || '';
  document.getElementById('editStatus').value   = d.status;
  document.getElementById('editBudget').value   = d.budget   ? d.budget.replace(/[^\d.]/g,'') : '0';
  document.getElementById('editDesc').value     = d.desc     || '';
  // Show existing logo in preview
  const prev = document.getElementById('editLogoPreview');
  if (d.logo) {
    prev.innerHTML = `<img src="${d.logo}" alt="Current logo"/>`;
  } else {
    prev.innerHTML = `<i class="fas fa-image"></i>`;
  }
  // Clear file input
  document.getElementById('editLogoInput').value = '';
  openModal('editModal');
}

function openDeleteModal() {
  if (!currentClub) return;
  document.getElementById('deleteId').value             = currentClub.dataset.id;
  document.getElementById('deleteClubName').textContent = currentClub.dataset.name;
  openModal('deleteModal');
}

// ── Render helpers ──
function renderOfficers(officers) {
  const el = document.getElementById('officerList');
  if (!officers || !officers.length) {
    el.innerHTML = '<p class="empty-msg">No officers listed.</p>';
    return;
  }
  el.innerHTML = officers.map(o => `
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
  const el = document.getElementById('eventsList');
  if (!events || !events.length) {
    el.innerHTML = '<p class="empty-msg">No upcoming events.</p>';
    return;
  }
  el.innerHTML = events.map(e => `
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

// ── Update right panel ──
function updateRightPanel(item) {
  currentClub = item;
  const d = item.dataset;

  document.getElementById('rightPanel').classList.remove('panel-fade');
  void document.getElementById('rightPanel').offsetWidth;
  document.getElementById('rightPanel').classList.add('panel-fade');

  // Logo
  const logoEl = document.getElementById('detailLogo');
  if (d.logo) {
    logoEl.outerHTML = `<img class="cdh-logo-img" id="detailLogo" src="${d.logo}" alt="${d.name} logo">`;
  } else {
    logoEl.outerHTML = `<div class="cdh-logo-img club-item-logo-initial" id="detailLogo">${d.name.charAt(0).toUpperCase()}</div>`;
  }

  document.getElementById('detailName').textContent     = d.name;
  document.getElementById('detailDesc').textContent     = d.desc;
  document.getElementById('detailCategory').textContent = d.category;
  document.getElementById('detailFounded').textContent  = d.founded;
  document.getElementById('detailRoom').textContent     = d.room;
  document.getElementById('statMembers').textContent    = d.members;
  document.getElementById('statEvents').textContent     = d.events;
  // Budget stat removed — no longer updated

  const statusEl = document.getElementById('detailStatus');
  statusEl.textContent = d.status.charAt(0).toUpperCase() + d.status.slice(1);
  statusEl.className   = 'cdh-status-badge status-' + d.status;

  try { renderOfficers(JSON.parse(d.officers || '[]')); } catch(e) {}
  try { renderEvents(JSON.parse(d.upcoming   || '[]')); } catch(e) {}
}

// ── Club item binding ──
function getAllItems() { return document.querySelectorAll('.club-item'); }

function bindItem(item) {
  item.addEventListener('click', () => {
    getAllItems().forEach(i => i.classList.remove('selected'));
    item.classList.add('selected');
    updateRightPanel(item);
    if (window.innerWidth <= 768) openSheet();
  });
}

getAllItems().forEach(bindItem);

// ── Filter tabs ──
document.querySelectorAll('.filter-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const f = tab.dataset.filter;
    getAllItems().forEach(item => {
      item.style.display = (f === 'all' || item.dataset.status === f) ? 'flex' : 'none';
    });
  });
});

// ── Search ──
function filterClubs() {
  const q = (document.getElementById('clubFilterInput').value || document.getElementById('globalSearch').value).toLowerCase();
  getAllItems().forEach(item => {
    const name = item.dataset.name.toLowerCase();
    const cat  = (item.dataset.category || '').toLowerCase();
    item.style.display = (name.includes(q) || cat.includes(q)) ? 'flex' : 'none';
  });
}

// ── Init right panel ──
const firstItem = document.querySelector('.club-item.selected');
if (firstItem) { currentClub = firstItem; updateRightPanel(firstItem); }

// ── Toast ──
<?php if ($toast): ?>
(function(){
  const raw  = <?= json_encode($toast) ?>;
  const [type, msg] = raw.split(':');
  const bar = document.getElementById('toastBar');
  bar.textContent = msg;
  bar.className   = 'toast-bar ' + (type === 'error' ? 'err' : '') + ' show';
  setTimeout(() => bar.classList.remove('show'), 3500);
})();
<?php endif; ?>
</script>

<script>
function preventScroll(e) {
  if (document.getElementById('rightPanel') && document.getElementById('rightPanel').contains(e.target)) return;
  e.preventDefault();
}
function toggleSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  const open = sidebar.classList.toggle('open');
  sidebar.style.setProperty('left', open ? '0px' : '-280px', 'important');
  document.getElementById('sidebarOverlay').classList.toggle('open');
  document.body.classList.toggle('sidebar-open', open);
  const fab = document.getElementById('fabMenuBtn');
  if (fab) { fab.style.opacity = open ? '0' : '1'; fab.style.pointerEvents = open ? 'none' : ''; }
  const mainEl = document.querySelector('.main');
  if (open) {
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
    document.body.style.top = '-' + window.scrollY + 'px';
    document.body.dataset.scrollY = window.scrollY;
    if (mainEl) { mainEl.style.overflow = 'hidden'; mainEl.style.pointerEvents = 'none'; }
  } else {
    const scrollY = document.body.dataset.scrollY || 0;
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
    document.body.style.top = '';
    window.scrollTo(0, parseInt(scrollY));
    if (mainEl) { mainEl.style.overflow = ''; mainEl.style.pointerEvents = ''; }
  }
}
function closeSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  sidebar.classList.remove('open');
  sidebar.style.setProperty('left', '-280px', 'important');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.body.classList.remove('sidebar-open');
  const scrollY = document.body.dataset.scrollY || 0;
  document.body.style.overflow = '';
  document.body.style.position = '';
  document.body.style.width = '';
  document.body.style.top = '';
  window.scrollTo(0, parseInt(scrollY));
  const mainEl = document.querySelector('.main');
  if (mainEl) { mainEl.style.overflow = ''; mainEl.style.pointerEvents = ''; }
  const fab = document.getElementById('fabMenuBtn');
  if (fab) { fab.style.opacity = '1'; fab.style.pointerEvents = ''; }
}
var _tsx = 0;
document.addEventListener('touchstart', function(e) { _tsx = e.touches[0].clientX; }, {passive:true});
document.addEventListener('touchend', function(e) {
  var dx = e.changedTouches[0].clientX - _tsx;
  if (dx > 60 && _tsx < 40) toggleSidebar();
  if (dx < -60) closeSidebar();
}, {passive:true});
</script>

<script>
function openSheet() {
  document.getElementById('rightPanel').classList.add('mobile-visible');
  document.getElementById('sheetOverlay').classList.add('open');
  const fab = document.getElementById('fabMenuBtn');
  if (fab) { fab.style.opacity = '0'; fab.style.pointerEvents = 'none'; }
  document.body.classList.add('sidebar-open');
  document.addEventListener('touchmove', preventScroll, {passive: false});
}
function closeSheet() {
  document.getElementById('rightPanel').classList.remove('mobile-visible');
  const fab2 = document.getElementById('fabMenuBtn');
  if (fab2) { fab2.style.opacity = '1'; fab2.style.pointerEvents = ''; }
  document.body.classList.remove('sidebar-open');
  document.removeEventListener('touchmove', preventScroll);
  document.getElementById('sheetOverlay').classList.remove('open');
}

</script>
<button class="fab-menu-btn" id="fabMenuBtn" onclick="toggleSidebar()" title="Menu">
  <i class="fas fa-bars"></i>
</button>
<script>
function toggleClubDrop(id, e) {
  e.stopPropagation();
  const list = document.getElementById(id + 'List');
  const btn  = document.getElementById(id + 'Btn');
  const isOpen = list.classList.contains('open');
  document.querySelectorAll('.custom-select-list.open').forEach(el => el.classList.remove('open'));
  document.querySelectorAll('.custom-select-btn.open').forEach(el => el.classList.remove('open'));
  if (!isOpen) { list.classList.add('open'); btn.classList.add('open'); }
}
function setClubDrop(id, val, label) {
  document.getElementById(id).value = val;
  document.getElementById(id + 'Btn').textContent = label;
  document.getElementById(id + 'List').classList.remove('open');
  document.getElementById(id + 'Btn').classList.remove('open');
  document.querySelectorAll('#' + id + 'List .custom-select-option').forEach(o => {
    o.classList.toggle('selected', o.textContent.trim() === label);
  });
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.custom-select-wrap')) {
    document.querySelectorAll('.custom-select-list.open').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.custom-select-btn.open').forEach(el => el.classList.remove('open'));
  }
});
</script>
</body>
</html>