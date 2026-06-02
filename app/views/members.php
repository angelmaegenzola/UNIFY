<?php require_once __DIR__ . '/../../app/controllers/members_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UNIFY — Members</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/public/assets/css/members.css"/>
  <link rel="stylesheet" href="/public/assets/css/transitions.css" />
</head>
<body>
<div class="app">


 <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
 <aside class="sidebar" id="mainSidebar">
<div class="sidebar-brand">
  <img src="assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
  <div class="brand-text">
    <div class="brand-name">UNIFY</div>
    <div class="brand-tagline">Club Management System</div>
  </div>
</div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">MAIN MENU</div>
      <a href="index.php?page=dashboard" class="nav-item"><i class="fas fa-house"></i><span>Dashboard</span></a>
      <a href="index.php?page=members"   class="nav-item active"><i class="fas fa-users"></i><span>Members</span></a>
      <a href="index.php?page=clubpage"  class="nav-item"><i class="fas fa-building-columns"></i><span>Clubs</span></a>
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
          <span class="topbar-page-title">Members</span>
          <span class="topbar-date"><?= date('l, F j, Y') ?></span>
        </div>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" id="globalSearch" placeholder="Search members, clubs…" oninput="filterTable()"/>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications"><i class="fas fa-bell"></i></button>
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
            <button class="hamburger-btn" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
    </header>


    <div class="content">


      <div class="stat-cards-grid">
        <div class="stat-card-new sc-green">
          <div class="sc-top"><div class="sc-icon-wrap"><i class="fas fa-users"></i></div></div>
          <div class="sc-value"><?= $total ?></div>
          <div class="sc-label">Total Members</div>
        </div>
        <div class="stat-card-new sc-yellow">
          <div class="sc-top"><div class="sc-icon-wrap"><i class="fas fa-user-check"></i></div></div>
          <div class="sc-value"><?= $active ?></div>
          <div class="sc-label">Active Members</div>
        </div>
        <div class="stat-card-new sc-teal">
          <div class="sc-top"><div class="sc-icon-wrap"><i class="fas fa-user-plus"></i></div></div>
          <div class="sc-value"><?= $pending ?></div>
          <div class="sc-label">Pending</div>
        </div>
        <div class="stat-card-new sc-red">
          <div class="sc-top"><div class="sc-icon-wrap"><i class="fas fa-user"></i></div></div>
          <div class="sc-value"><?= $inactive ?></div>
          <div class="sc-label">Inactive</div>
        </div>
      </div>


      <div class="table-container">

        <div class="table-toolbar">
          <span class="toolbar-title">All Members</span>
          <span class="member-count" id="memberCount"><?= $total ?> members</span>
          <div class="search-box">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="tableSearch" placeholder="Search by name, course, club…" oninput="filterTable()"/>
          </div>
          <div class="custom-select-wrap" id="clubSelectWrap">
            <button class="custom-select-btn" id="clubSelectBtn" onclick="toggleDrop('club')">All Clubs</button>
            <div class="custom-select-list" id="clubDropList">
              <div class="custom-select-option selected" onclick="setFilter('club','')">All Clubs</div>
              <?php foreach ($clubs as $c): ?>
              <div class="custom-select-option" onclick="setFilter('club','<?= htmlspecialchars($c['name']) ?>')"><?= htmlspecialchars($c['name']) ?></div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="clubFilter" value=""/>
          </div>
          <div class="custom-select-wrap" id="roleSelectWrap">
            <button class="custom-select-btn" id="roleSelectBtn" onclick="toggleDrop('role')">All Roles</button>
            <div class="custom-select-list" id="roleDropList">
              <div class="custom-select-option selected" onclick="setFilter('role','')">All Roles</div>
              <div class="custom-select-option" onclick="setFilter('role','member')">Member</div>
              <div class="custom-select-option" onclick="setFilter('role','officer')">Officer</div>
              <div class="custom-select-option" onclick="setFilter('role','vice president')">Vice President</div>
              <div class="custom-select-option" onclick="setFilter('role','president')">President</div>
            </div>
            <input type="hidden" id="roleFilter" value=""/>
          </div>
          <button class="btn-add" onclick="openAdd()">
            <i class="fas fa-plus"></i> Add Member
          </button>
        </div>


        <div class="filter">
          <button class="tab active" onclick="setTab('all',this)">All</button>
          <button class="tab" onclick="setTab('active',this)">Active</button>
          <button class="tab" onclick="setTab('inactive',this)">Inactive</button>
          <button class="tab" onclick="setTab('pending',this)">Pending</button>
        </div>


        <div class="table-wrap">
          <table class="student-table">
            <thead>
              <tr>
                <th><input type="checkbox" id="checkAll" onchange="toggleAll(this)"/></th>
                <th>Full Name</th>
                <th>Course</th>
                <th>Year &amp; Section</th>
                <th>Clubs</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="membersTbody">
            <?php foreach ($members as $m):
              $yearSection = trim(($m['year'] ?? '') . ' ' . ($m['section'] ?? '')) ?: '—';
              // Build a searchable string from all clubs + roles
              $clubNames = implode(' ', array_column($m['memberships'], 'club_name'));
              $roles     = implode(' ', array_column($m['memberships'], 'role'));
              $searchStr = strtolower($m['first_name'].' '.$m['last_name'].' '.($m['course'] ?? '').' '.$clubNames.' '.$roles);
              // For club filter: pipe-separated club names
              $clubNamesAttr = implode('|', array_column($m['memberships'], 'club_name'));
              // For role filter: pipe-separated roles
              $rolesAttr = implode('|', array_column($m['memberships'], 'role'));
              // Primary membership for edit/delete (first one)
              $primary = $m['memberships'][0];
            ?>
              <tr data-status="<?= $m['status'] ?>"
                  data-clubs="<?= htmlspecialchars($clubNamesAttr) ?>"
                  data-roles="<?= htmlspecialchars($rolesAttr) ?>"
                  data-search="<?= htmlspecialchars($searchStr) ?>">
                <td><input type="checkbox" class="row-check"/></td>
                <td>
                  <div class="name-cell">
                    <div class="avatar"><?= initials($m['first_name'], $m['last_name']) ?></div>
                    <div>
                      <div class="name-text"><?= htmlspecialchars($m['first_name'].' '.$m['last_name']) ?></div>
                      <div style="font-size:11px;color:#7a9a85;"><?= htmlspecialchars($m['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="td-mid"><?= htmlspecialchars($m['course'] ?? '—') ?></td>
                <td class="td-mid td-bold"><?= htmlspecialchars($yearSection) ?></td>
                <td>
                  <div class="club-chips">
                    <?php foreach ($m['memberships'] as $ms): ?>
                      <span class="club-chip <?= $ms['role'] !== 'member' ? 'role-badge officer' : '' ?>"
                            title="<?= htmlspecialchars($ms['club_name']) ?> — <?= ucfirst($ms['role']) ?>">
                        <?= htmlspecialchars($ms['club_name']) ?>
                        <?php if ($ms['role'] !== 'member'): ?>
                          <i class="fas fa-star" style="font-size:8px;opacity:.7;"></i>
                        <?php endif; ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td><span class="status-badge status-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span></td>
                <td>
                  <div class="actions-cell">
                    <button class="act-btn view" title="View"
                      onclick="openView(<?= htmlspecialchars(json_encode($m)) ?>)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="act-btn edit" title="Edit primary membership"
                      onclick="openEdit(<?= htmlspecialchars(json_encode(array_merge($primary, ['first_name'=>$m['first_name'],'last_name'=>$m['last_name'],'club_name'=>$primary['club_name']]))) ?>)">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button class="act-btn del" title="Remove from club"
                      onclick="openDelete(<?= $primary['id'] ?>, '<?= htmlspecialchars($m['first_name'].' '.$m['last_name']) ?>', <?= count($m['memberships']) ?>)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>


        <div class="table-footer">
          <span class="showing-text" id="showingText">Showing <?= $total ?> of <?= $total ?> members</span>
        </div>

      </div>
    </div>
  </main>
</div>


<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-user-plus"></i> Add Member</span>
      <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
    </div>
    <form method="POST" action="index.php?page=members">
      <input type="hidden" name="act" value="add"/>
      <div class="form-row">
        <div class="fg" style="grid-column:1/-1">
          <label>User</label>
          <select name="user_id" required>
            <option value="">— Select user —</option>
            <?php foreach ($users as $u): ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?> (@<?= htmlspecialchars($u['username']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg" style="grid-column:1/-1">
          <label>Club</label>
          <select name="club_id" required>
            <option value="">— Select club —</option>
            <?php foreach ($clubs as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-row cols-3">
        <div class="fg">
          <label>Course</label>
          <input type="text" name="course" placeholder="e.g. BSIT"/>
        </div>
        <div class="fg">
          <label>Year Level</label>
          <input type="text" name="year" placeholder="e.g. 2nd Year"/>
        </div>
        <div class="fg">
          <label>Section</label>
          <input type="text" name="section" placeholder="e.g. C"/>
        </div>
      </div>
      <div class="form-row">
        <div class="fg">
          <label>Role</label>
          <select name="role">
            <option value="member">Member</option>
            <option value="officer">Officer</option>
            <option value="vice president">Vice President</option>
            <option value="president">President</option>
          </select>
        </div>
        <div class="fg">
          <label>Status</label>
          <select name="status">
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-plus"></i> Add</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-pen"></i> Edit Membership</span>
      <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
    </div>
    <form method="POST" action="index.php?page=members">
      <input type="hidden" name="act" value="edit"/>
      <input type="hidden" name="id" id="editId"/>
      <div class="form-row cols-1">
        <div class="fg">
          <label>Member — Club</label>
          <input type="text" id="editName" readonly style="background:#f5f5f5;"/>
        </div>
      </div>
      <div class="form-row cols-3">
        <div class="fg">
          <label>Course</label>
          <input type="text" name="course" id="editCourse" placeholder="e.g. BSIT"/>
        </div>
        <div class="fg">
          <label>Year Level</label>
          <input type="text" name="year" id="editYear" placeholder="e.g. 2nd Year"/>
        </div>
        <div class="fg">
          <label>Section</label>
          <input type="text" name="section" id="editSection" placeholder="e.g. C"/>
        </div>
      </div>
      <div class="form-row">
        <div class="fg">
          <label>Role</label>
          <select name="role" id="editRole">
            <option value="member">Member</option>
            <option value="officer">Officer</option>
            <option value="vice president">Vice President</option>
            <option value="president">President</option>
          </select>
        </div>
        <div class="fg">
          <label>Status</label>
          <select name="status" id="editStatus">
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-floppy-disk"></i> Save</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="viewModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-eye"></i> Member Details</span>
      <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
    </div>
    <div id="viewContent"></div>
    <div class="modal-footer">
      <button type="button" class="btn-save" onclick="closeModal('viewModal')">Close</button>
    </div>
  </div>
</div>


<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" style="color:#dc2626"><i class="fas fa-trash"></i> Remove Member</span>
      <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
    </div>
    <p class="del-confirm-msg" id="deleteMsg">Are you sure you want to remove <span class="del-confirm-name" id="deleteName"></span>? This cannot be undone.</p>
    <form method="POST" action="index.php?page=members">
      <input type="hidden" name="act" value="delete"/>
      <input type="hidden" name="id" id="deleteId"/>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
        <button type="submit" class="btn-save" style="background:#dc2626"><i class="fas fa-trash"></i> Remove</button>
      </div>
    </form>
  </div>
</div>


<div class="toast-bar" id="toastBar"></div>



<script>
/* ── Modal helpers ── */
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

/* ── Add ── */
function openAdd() { openModal('addModal'); }

/* ── Edit (edits the primary/first membership record) ── */
function openEdit(m) {
  document.getElementById('editId').value      = m.id;
  document.getElementById('editName').value    = m.first_name + ' ' + m.last_name + ' — ' + m.club_name;
  document.getElementById('editCourse').value  = m.course   || '';
  document.getElementById('editYear').value    = m.year     || '';
  document.getElementById('editSection').value = m.section  || '';
  document.getElementById('editRole').value    = m.role;
  document.getElementById('editStatus').value  = m.status;
  openModal('editModal');
}

/* ── View (shows all memberships) ── */
function openView(m) {
  const yearSection = [m.year, m.section].filter(Boolean).join(' ') || '—';

  // Basic info grid
  let html = `
    <div class="view-grid">
      <div class="view-item"><label>Full Name</label><p>${m.first_name} ${m.last_name}</p></div>
      <div class="view-item"><label>Email</label><p>${m.email || '—'}</p></div>
      <div class="view-item"><label>Course</label><p>${m.course || '—'}</p></div>
      <div class="view-item"><label>Year &amp; Section</label><p>${yearSection}</p></div>
      <div class="view-item"><label>Overall Status</label><p>${m.status.charAt(0).toUpperCase()+m.status.slice(1)}</p></div>
      <div class="view-item"><label>Club Count</label><p>${m.memberships.length} club${m.memberships.length !== 1 ? 's' : ''}</p></div>
    </div>`;

  // Memberships list
  html += `<div class="view-section-title"><i class="fas fa-building-columns" style="margin-right:5px;"></i>Club Memberships</div>`;
  html += `<div class="club-memberships-list">`;
  m.memberships.forEach(ms => {
    const joined = ms.joined_at ? ms.joined_at.split(' ')[0] : '—';
    const roleClass = ms.role === 'president' || ms.role === 'vice president' ? 'role-badge exec'
                    : ms.role === 'officer' ? 'role-badge officer' : 'role-badge';
    html += `
      <div class="club-membership-item">
        <div>
          <div class="cmi-name">${ms.club_name}</div>
          <div class="cmi-meta">Joined: ${joined}</div>
        </div>
        <div class="cmi-badges">
          <span class="${roleClass}">${ms.role.charAt(0).toUpperCase()+ms.role.slice(1)}</span>
          <span class="status-badge status-${ms.status}">${ms.status.charAt(0).toUpperCase()+ms.status.slice(1)}</span>
        </div>
      </div>`;
  });
  html += `</div>`;

  document.getElementById('viewContent').innerHTML = html;
  openModal('viewModal');
}

/* ── Delete ── */
function openDelete(id, name, clubCount) {
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteName').textContent = name;
  const extra = clubCount > 1
    ? ` This will remove their primary club membership only (they have ${clubCount} memberships total).`
    : ' This will remove them from the club entirely.';
  document.getElementById('deleteMsg').innerHTML =
    `Are you sure you want to remove <span class="del-confirm-name">${name}</span>?${extra} This cannot be undone.`;
  openModal('deleteModal');
}

/* ── Checkbox all ── */
function toggleAll(cb) {
  document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked);
}

/* ── Filter/search ── */
let activeTab = 'all';

function setTab(tab, btn) {
  activeTab = tab;
  document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  filterTable();
}

function filterTable() {
  const search = (document.getElementById('tableSearch').value || document.getElementById('globalSearch').value).toLowerCase();
  const clubF  = document.getElementById('clubFilter').value.toLowerCase();
  const roleF  = document.getElementById('roleFilter').value.toLowerCase();
  const rows   = document.querySelectorAll('#membersTbody tr');
  let visible  = 0;

  rows.forEach(row => {
    const matchTab    = activeTab === 'all' || row.dataset.status === activeTab;
    const matchSearch = !search   || row.dataset.search.includes(search);
    // clubs is pipe-separated e.g. "CHMSU Python Esports|Artisan Society"
    const clubs = row.dataset.clubs.toLowerCase();
    const roles = row.dataset.roles.toLowerCase();
    const matchClub   = !clubF    || clubs.split('|').some(c => c === clubF);
    const matchRole   = !roleF    || roles.split('|').some(r => r === roleF);
    const show = matchTab && matchSearch && matchClub && matchRole;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  document.getElementById('memberCount').textContent = visible + ' members';
  document.getElementById('showingText').textContent = `Showing ${visible} of <?= $total ?> members`;
}

/* ── Toast ── */
<?php if ($toast): ?>
(function() {
  const raw   = <?= json_encode($toast) ?>;
  const [type, msg] = raw.split(':');
  const bar = document.getElementById('toastBar');
  bar.textContent = msg;
  bar.className   = 'toast-bar ' + (type === 'error' ? 'err' : '') + ' show';
  setTimeout(() => bar.classList.remove('show'), 3500);
})();
<?php endif; ?>
</script>
<script>
function preventScroll(e) { e.preventDefault(); }
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
document.querySelectorAll('.nav-item').forEach(function(item) {
  item.addEventListener('click', function() {
    if (window.innerWidth <= 768) closeSidebar();
  });
});
var _tsx = 0, _tsy = 0;
document.addEventListener('touchstart', function(e) {
  _tsx = e.touches[0].clientX;
  _tsy = e.touches[0].clientY;
}, {passive: true});
document.addEventListener('touchend', function(e) {
  var dx = e.changedTouches[0].clientX - _tsx;
  var dy = e.changedTouches[0].clientY - _tsy;
  if (Math.abs(dx) < Math.abs(dy)) return;
  if (dx > 60 && _tsx < 40) toggleSidebar();
  if (dx < -60) closeSidebar();
}, {passive: true});
</script>

<script>
function toggleDrop(type) {
  var listId = type === 'club' ? 'clubDropList' : 'roleDropList';
  var btnId  = type === 'club' ? 'clubSelectBtn' : 'roleSelectBtn';
  var list = document.getElementById(listId);
  var btn  = document.getElementById(btnId);
  var isOpen = list.classList.contains('open');
  document.querySelectorAll('.custom-select-list').forEach(function(l) { l.classList.remove('open'); });
  document.querySelectorAll('.custom-select-btn').forEach(function(b) { b.classList.remove('open'); });
  if (!isOpen) { list.classList.add('open'); btn.classList.add('open'); }
}
function setFilter(type, value) {
  var inputId = type === 'club' ? 'clubFilter' : 'roleFilter';
  var btnId   = type === 'club' ? 'clubSelectBtn' : 'roleSelectBtn';
  var listId  = type === 'club' ? 'clubDropList' : 'roleDropList';
  document.getElementById(inputId).value = value;
  document.getElementById(btnId).textContent = value || (type === 'club' ? 'All Clubs' : 'All Roles');
  document.getElementById(listId).querySelectorAll('.custom-select-option').forEach(function(o) {
    o.classList.toggle('selected', o.textContent.toLowerCase() === (value || ('all ' + type + 's')).toLowerCase() || (!value && o.textContent.includes('All')));
  });
  document.getElementById(listId).classList.remove('open');
  document.getElementById(btnId).classList.remove('open');
  filterTable();
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.custom-select-wrap')) {
    document.querySelectorAll('.custom-select-list').forEach(function(l) { l.classList.remove('open'); });
    document.querySelectorAll('.custom-select-btn').forEach(function(b) { b.classList.remove('open'); });
  }
});
</script>
<button class="fab-menu-btn" id="fabMenuBtn" onclick="toggleSidebar()" title="Menu">
  <i class="fas fa-bars"></i>
</button>
</body>
</html>