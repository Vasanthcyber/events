
Copy

<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

// Handle status toggle
if (isset($_GET['toggle'])) {
    $uid     = intval($_GET['toggle']);
    $current = $conn->query("SELECT status FROM users WHERE id=$uid")->fetch_assoc()['status'];
    $new     = $current === 'active' ? 'inactive' : 'active';
    $conn->query("UPDATE users SET status='$new' WHERE id=$uid");
    header("Location: users.php?msg=" . urlencode("User status updated to $new."));
    exit();
}

$msg = $_GET['msg'] ?? '';

// Counts
$cnt_all    = $conn->query("SELECT COUNT(*) c FROM users WHERE user_type='user'")->fetch_assoc()['c'];
$cnt_active = $conn->query("SELECT COUNT(*) c FROM users WHERE user_type='user' AND status='active'")->fetch_assoc()['c'];

// Get all users
$users = $conn->query("SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Manage Users</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ── Reset ─────────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --ink:   #0b0908;  --ink2:  #131009;  --ink3:  #1a1610;  --ink4:  #201c15;
  --ivory: #f2ece0;  --ivory2:#b5ad9f;  --ivory3:#6e6560;
  --gold:  #c9a84c;  --goldl: #e8d08a;  --goldp: rgba(201,168,76,.11);
  --green: #4dbd8a;  --amber: #e8a83a;  --rose:  #d06878;  --teal:  #4db8bd;
  --border:rgba(201,168,76,.15);  --bsub:rgba(255,255,255,.055);  --sw:256px;
}

html,body{height:100%;overflow:hidden}
body{font-family:'Barlow',sans-serif;background:var(--ink);color:var(--ivory);display:flex;min-height:100vh}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.032'/%3E%3C/svg%3E")}

/* ── Sidebar ───────────────────────────────────────────────── */
.sidebar{width:var(--sw);flex-shrink:0;background:var(--ink2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:0 0 28px;position:relative;overflow:hidden}
.sidebar::before{content:'';position:absolute;bottom:-80px;left:-60px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.07) 0%,transparent 70%);pointer-events:none}
.sb-brand{padding:32px 26px 26px;border-bottom:1px solid var(--bsub);margin-bottom:6px}
.sb-logo{display:flex;align-items:center;gap:11px}
.sb-diamond{width:32px;height:32px;border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:12px;transform:rotate(45deg);flex-shrink:0}
.sb-diamond i{transform:rotate(-45deg)}
.sb-name{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;letter-spacing:.1em;color:var(--ivory);text-transform:uppercase}
.sb-sub{font-size:8px;letter-spacing:.3em;color:var(--gold);text-transform:uppercase;margin-top:5px;padding-left:43px;font-weight:300}
.sb-section{font-size:8px;letter-spacing:.28em;text-transform:uppercase;color:var(--ivory3);padding:18px 26px 7px;font-weight:400}
.sb-link{display:flex;align-items:center;gap:12px;padding:11px 26px;color:var(--ivory2);text-decoration:none;font-size:12.5px;font-weight:400;letter-spacing:.03em;transition:all .2s;position:relative}
.sb-link i{font-size:13px;width:14px;text-align:center}
.sb-link:hover{color:var(--ivory);background:rgba(201,168,76,.05)}
.sb-link.active{color:var(--ivory);background:rgba(201,168,76,.08)}
.sb-link.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:2px;background:var(--gold)}
.sb-link.active i{color:var(--gold)}
.sb-badge{margin-left:auto;background:var(--goldp);border:1px solid rgba(201,168,76,.38);color:var(--gold);font-size:9px;font-weight:500;padding:1px 6px}
.sb-footer{margin-top:auto;padding:18px 26px 0;border-top:1px solid var(--bsub)}
.sb-user{display:flex;align-items:center;gap:10px}
.sb-avatar{width:30px;height:30px;border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:13px;color:var(--gold)}
.sb-uname{font-size:12.5px;font-weight:500;color:var(--ivory)}
.sb-urole{font-size:8.5px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3);margin-top:2px}
.sb-logout{color:var(--ivory3);font-size:13px;margin-left:auto;transition:color .2s;text-decoration:none}
.sb-logout:hover{color:var(--rose)}

/* ── Main ──────────────────────────────────────────────────── */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}

.topbar{display:flex;align-items:center;justify-content:space-between;padding:28px 40px 0;flex-shrink:0}
.tb-left h1{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:300;color:var(--ivory);line-height:1}
.tb-left h1 em{font-style:italic;color:var(--goldl)}
.tb-sub{font-size:10px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s;text-decoration:none;width:34px}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

/* Filter bar */
.filter-bar{display:flex;align-items:center;gap:8px;padding:20px 40px 0;flex-shrink:0}
.fpill{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.18em;text-transform:uppercase;padding:7px 16px;cursor:pointer;transition:all .22s;display:flex;align-items:center;gap:7px}
.fpill:hover{border-color:var(--border);color:var(--ivory2)}
.fpill.active{border-color:var(--gold);color:var(--gold);background:var(--goldp)}
.fpill-count{background:rgba(255,255,255,.07);padding:1px 6px;font-size:9px;font-weight:600;min-width:18px;text-align:center}
.fpill.active .fpill-count{background:rgba(201,168,76,.2)}

/* Search */
.search-wrap{margin-left:auto;display:flex;align-items:center;gap:10px;background:var(--ink3);border:1px solid var(--bsub);padding:0 14px;height:34px;min-width:210px}
.search-wrap i{color:var(--ivory3);font-size:12px;flex-shrink:0}
.search-wrap input{background:none;border:none;color:var(--ivory);font-family:'Barlow',sans-serif;font-size:12px;outline:none;flex:1;width:100%}
.search-wrap input::placeholder{color:var(--ivory3)}

.content{flex:1;overflow-y:auto;padding:20px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

/* Animations */
@keyframes up{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .4s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}

/* Alert / msg */
.alert{padding:12px 18px;margin-bottom:18px;font-size:12px;border-left:2px solid;display:flex;align-items:center;gap:9px}
.alert-success{background:rgba(77,189,138,.08);border-color:var(--green);color:var(--green)}
.alert-info   {background:rgba(201,168,76,.08);border-color:var(--gold);color:var(--gold)}

/* Panel */
.panel{background:var(--ink3);border:1px solid var(--bsub)}

/* ── User Table ────────────────────────────────────────────── */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid var(--bsub)}
th{padding:12px 20px;font-size:8.5px;letter-spacing:.24em;text-transform:uppercase;color:var(--ivory3);font-weight:400;text-align:left;white-space:nowrap;cursor:pointer;user-select:none;transition:color .15s}
th:hover{color:var(--ivory2)}
th .sort-icon{margin-left:5px;font-size:8px;opacity:.4}
th.sorted .sort-icon{opacity:1;color:var(--gold)}

td{padding:0;border-bottom:1px solid var(--bsub);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr{transition:background .14s}
tbody tr:hover{background:rgba(201,168,76,.03)}
tbody tr.hidden-row{display:none}

/* Cell styles */
.cell{padding:14px 20px}
.td-id{font-size:10px;letter-spacing:.1em;color:var(--ivory3)}

.user-cell{display:flex;align-items:center;gap:12px;padding:12px 20px}
.u-avatar{
  width:34px;height:34px;border-radius:0;
  border:1px solid var(--bsub);
  display:flex;align-items:center;justify-content:center;
  font-family:'Cormorant Garamond',serif;font-size:15px;
  color:var(--gold);background:var(--goldp);flex-shrink:0
}
.u-name{font-size:13px;font-weight:500;color:var(--ivory)}
.u-email{font-size:10.5px;color:var(--ivory3);margin-top:2px}

.td-phone{font-size:12px;color:var(--ivory2)}
.td-date {font-size:11.5px;color:var(--ivory2)}

/* Booking pill */
.booking-pill{
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 10px;font-size:11px;font-family:'Barlow',sans-serif;
}
.booking-pill.has-bookings{color:var(--gold);background:var(--goldp);border:1px solid rgba(201,168,76,.25)}
.booking-pill.no-bookings {color:var(--ivory3);background:rgba(255,255,255,.04);border:1px solid var(--bsub)}
.booking-pill i{font-size:9px}

/* Status badge */
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;font-size:9px;letter-spacing:.09em;text-transform:uppercase;font-weight:500}
.bdot{width:4px;height:4px;border-radius:50%}
.badge-success{color:var(--green);background:rgba(77,189,138,.1)}
.badge-success .bdot{background:var(--green)}
.badge-danger {color:var(--rose); background:rgba(208,104,120,.1)}
.badge-danger  .bdot{background:var(--rose)}

/* Action buttons */
.acts{display:flex;gap:6px;padding:10px 20px}
.abtn{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.06em;padding:5px 11px;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.abtn i{font-size:9px}
.abtn:hover       {border-color:var(--gold);color:var(--gold)}
.abtn.tog-off:hover{border-color:var(--rose);color:var(--rose)}
.abtn.tog-on:hover {border-color:var(--green);color:var(--green)}

/* Empty */
.empty{padding:52px 24px;text-align:center;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3)}
.empty i{display:block;font-size:1.8rem;opacity:.25;margin-bottom:14px}

/* Responsive */
@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.filter-bar,.content{padding-left:20px;padding-right:20px}
}
</style>
</head>
<body>

<!-- ── SIDEBAR ──────────────────────────────────────────────── -->
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-diamond"><i class="fas fa-gem"></i></div>
      <span class="sb-name">Eventique</span>
    </div>
    <div class="sb-sub">Admin Console</div>
  </div>

  <span class="sb-section">Overview</span>
  <a href="dashboard.php" class="sb-link"><i class="fas fa-chart-tree-map"></i> Dashboard</a>
  <a href="bookings.php"  class="sb-link"><i class="fas fa-calendar-check"></i> Bookings</a>

  <span class="sb-section">Manage</span>
  <a href="halls.php"   class="sb-link"><i class="fas fa-building-columns"></i> Halls</a>
  <a href="vendors.php" class="sb-link"><i class="fas fa-store"></i> Vendors</a>
  <a href="users.php"   class="sb-link active">
    <i class="fas fa-users"></i> Users
    <span class="sb-badge"><?= $cnt_all ?></span>
  </a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
      <div>
        <div class="sb-uname"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <div class="sb-urole">Administrator</div>
      </div>
      <a class="sb-logout" href="../logout.php" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
</aside>

<!-- ── MAIN ─────────────────────────────────────────────────── -->
<main class="main">

  <div class="topbar">
    <div class="tb-left">
      <h1>Registered <em>Users</em></h1>
      <div class="tb-sub"><?= $cnt_all ?> total &nbsp;·&nbsp; <?= $cnt_active ?> active</div>
    </div>
    <div class="tb-right">
      <a href="dashboard.php" class="tb-btn" title="Dashboard"><i class="fas fa-chart-tree-map"></i></a>
      <a href="../logout.php" class="tb-btn" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <!-- Filter + Search bar -->
  <div class="filter-bar">
    <button class="fpill active" onclick="filterRows('all',this)">
      All <span class="fpill-count"><?= $cnt_all ?></span>
    </button>
    <button class="fpill" onclick="filterRows('active',this)">
      Active <span class="fpill-count"><?= $cnt_active ?></span>
    </button>
    <button class="fpill" onclick="filterRows('inactive',this)">
      Inactive <span class="fpill-count"><?= $cnt_all - $cnt_active ?></span>
    </button>

    <div class="search-wrap">
      <i class="fas fa-magnifying-glass"></i>
      <input type="text" id="search-input" placeholder="Search by name or email…" oninput="searchRows(this.value)">
    </div>
  </div>

  <div class="content">

    <?php if ($msg): ?>
      <div class="alert alert-info ani d1"><i class="fas fa-circle-info"></i><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="panel ani d2">
      <div class="tbl-wrap">
        <table id="users-table">
          <thead>
            <tr>
              <th onclick="sortTable(0)">ID <i class="fas fa-sort sort-icon"></i></th>
              <th onclick="sortTable(1)">User <i class="fas fa-sort sort-icon"></i></th>
              <th onclick="sortTable(2)">Phone <i class="fas fa-sort sort-icon"></i></th>
              <th onclick="sortTable(3)">Status <i class="fas fa-sort sort-icon"></i></th>
              <th onclick="sortTable(4)">Registered <i class="fas fa-sort sort-icon"></i></th>
              <th onclick="sortTable(5)">Bookings <i class="fas fa-sort sort-icon"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="users-body">
            <?php if ($users->num_rows > 0):
              while ($user = $users->fetch_assoc()):
                $booking_count = get_booking_count($conn, $user['id']);
                $is_active     = $user['status'] === 'active';
                $bc            = $is_active ? 'badge-success' : 'badge-danger';
            ?>
            <tr data-status="<?= $user['status'] ?>"
                data-name="<?= strtolower(htmlspecialchars($user['name'])) ?>"
                data-email="<?= strtolower(htmlspecialchars($user['email'])) ?>">

              <td><div class="cell"><span class="td-id">#<?= $user['id'] ?></span></div></td>

              <td>
                <div class="user-cell">
                  <div class="u-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
                  <div>
                    <div class="u-name"><?= htmlspecialchars($user['name']) ?></div>
                    <div class="u-email"><?= htmlspecialchars($user['email']) ?></div>
                  </div>
                </div>
              </td>

              <td><div class="cell td-phone"><?= htmlspecialchars($user['phone'] ?: '—') ?></div></td>

              <td>
                <div class="cell">
                  <span class="badge <?= $bc ?>">
                    <span class="bdot"></span><?= ucfirst($user['status']) ?>
                  </span>
                </div>
              </td>

              <td><div class="cell td-date"><?= format_date($user['created_at']) ?></div></td>

              <td>
                <div class="cell">
                  <span class="booking-pill <?= $booking_count > 0 ? 'has-bookings' : 'no-bookings' ?>">
                    <i class="fas fa-calendar-check"></i>
                    <?= $booking_count ?> booking<?= $booking_count != 1 ? 's' : '' ?>
                  </span>
                </div>
              </td>

              <td>
                <div class="acts">
                  <a href="?toggle=<?= $user['id'] ?>"
                     class="abtn <?= $is_active ? 'tog-off' : 'tog-on' ?>">
                    <i class="fas fa-<?= $is_active ? 'ban' : 'check' ?>"></i>
                    <?= $is_active ? 'Suspend' : 'Activate' ?>
                  </a>
                </div>
              </td>

            </tr>
            <?php endwhile; else: ?>
              <tr><td colspan="7"><div class="empty"><i class="fas fa-users"></i>No users registered yet.</div></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<script>
// ── Filter by status pill ──────────────────────────────────────
function filterRows(status, btn) {
  document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('#users-body tr[data-status]').forEach(row => {
    const match = status === 'all' || row.dataset.status === status;
    row.classList.toggle('hidden-row', !match);
  });
}

// ── Live search ───────────────────────────────────────────────
function searchRows(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('#users-body tr[data-status]').forEach(row => {
    const name  = row.dataset.name  || '';
    const email = row.dataset.email || '';
    row.classList.toggle('hidden-row', q && !name.includes(q) && !email.includes(q));
  });
  // reset pills
  if (q) {
    document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
  }
}

// ── Column sort ────────────────────────────────────────────────
let sortDir = {};
function sortTable(col) {
  const tbody = document.getElementById('users-body');
  const rows  = Array.from(tbody.querySelectorAll('tr[data-status]'));
  sortDir[col] = !sortDir[col];

  rows.sort((a, b) => {
    const at = a.cells[col]?.innerText.trim().toLowerCase() || '';
    const bt = b.cells[col]?.innerText.trim().toLowerCase() || '';
    const n1 = parseFloat(at), n2 = parseFloat(bt);
    const cmp = !isNaN(n1) && !isNaN(n2) ? n1 - n2 : at.localeCompare(bt);
    return sortDir[col] ? cmp : -cmp;
  });

  rows.forEach(r => tbody.appendChild(r));

  document.querySelectorAll('th').forEach((th, i) => {
    th.classList.toggle('sorted', i === col);
    const ic = th.querySelector('.sort-icon');
    if (ic) ic.className = `fas fa-sort${i === col ? (sortDir[col] ? '-up' : '-down') : ''} sort-icon`;
  });
}
</script>
</body>
</html>