<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

$success = '';
$error   = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status     = sanitize_input($_POST['status']);

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        $success = 'Booking status updated successfully.';
    } else {
        $error = 'Failed to update booking status.';
    }
}

// Get all bookings
$bookings = $conn->query(
    "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone, h.name as hall_name
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     JOIN halls h ON b.hall_id = h.id
     ORDER BY b.created_at DESC"
);

// Counts for filter tabs
$cnt_all       = $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];
$cnt_pending   = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='pending'")->fetch_assoc()['c'];
$cnt_confirmed = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='confirmed'")->fetch_assoc()['c'];
$cnt_cancelled = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='cancelled'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Manage Bookings</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ── Reset ─────────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --ink:   #0b0908;
  --ink2:  #131009;
  --ink3:  #1a1610;
  --ink4:  #201c15;
  --ivory: #f2ece0;
  --ivory2:#b5ad9f;
  --ivory3:#6e6560;
  --gold:  #c9a84c;
  --goldl: #e8d08a;
  --goldp: rgba(201,168,76,.11);
  --green: #4dbd8a;
  --amber: #e8a83a;
  --rose:  #d06878;
  --teal:  #4db8bd;
  --border:rgba(201,168,76,.15);
  --bsub:  rgba(255,255,255,.055);
  --sw:    256px;
}

html,body{height:100%;overflow:hidden}
body{font-family:'Barlow',sans-serif;background:var(--ink);color:var(--ivory);display:flex;min-height:100vh}

body::after{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.032'/%3E%3C/svg%3E")
}

/* ── Sidebar ───────────────────────────────────────────────── */
.sidebar{
  width:var(--sw);flex-shrink:0;
  background:var(--ink2);border-right:1px solid var(--border);
  display:flex;flex-direction:column;padding:0 0 28px;
  position:relative;overflow:hidden
}
.sidebar::before{
  content:'';position:absolute;bottom:-80px;left:-60px;
  width:260px;height:260px;border-radius:50%;
  background:radial-gradient(circle,rgba(201,168,76,.07) 0%,transparent 70%);pointer-events:none
}

.sb-brand{padding:32px 26px 26px;border-bottom:1px solid var(--bsub);margin-bottom:6px}
.sb-logo{display:flex;align-items:center;gap:11px}
.sb-diamond{
  width:32px;height:32px;border:1px solid var(--gold);
  display:flex;align-items:center;justify-content:center;
  color:var(--gold);font-size:12px;transform:rotate(45deg);flex-shrink:0
}
.sb-diamond i{transform:rotate(-45deg)}
.sb-name{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;letter-spacing:.1em;color:var(--ivory);text-transform:uppercase}
.sb-sub{font-size:8px;letter-spacing:.3em;color:var(--gold);text-transform:uppercase;margin-top:5px;padding-left:43px;font-weight:300}

.sb-section{font-size:8px;letter-spacing:.28em;text-transform:uppercase;color:var(--ivory3);padding:18px 26px 7px;font-weight:400}
.sb-link{
  display:flex;align-items:center;gap:12px;
  padding:11px 26px;color:var(--ivory2);text-decoration:none;
  font-size:12.5px;font-weight:400;letter-spacing:.03em;
  transition:all .2s;position:relative
}
.sb-link i{font-size:13px;width:14px;text-align:center;transition:color .2s}
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
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s;text-decoration:none;position:relative}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-dot{width:5px;height:5px;border-radius:50%;background:var(--gold);position:absolute;top:6px;right:6px}

.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

/* Filter pills */
.filter-bar{display:flex;align-items:center;gap:8px;padding:20px 40px 0;flex-shrink:0}
.fpill{
  background:none;border:1px solid var(--bsub);
  color:var(--ivory3);font-family:'Barlow',sans-serif;
  font-size:10px;letter-spacing:.18em;text-transform:uppercase;
  padding:7px 16px;cursor:pointer;transition:all .22s;
  display:flex;align-items:center;gap:7px
}
.fpill:hover{border-color:var(--border);color:var(--ivory2)}
.fpill.active{border-color:var(--gold);color:var(--gold);background:var(--goldp)}
.fpill-count{
  background:rgba(255,255,255,.07);padding:1px 6px;
  font-size:9px;font-weight:600;min-width:18px;text-align:center
}
.fpill.active .fpill-count{background:rgba(201,168,76,.2)}

.content{flex:1;overflow-y:auto;padding:20px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

/* Animations */
@keyframes up{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .4s ease both}
.d1{animation-delay:.06s}.d2{animation-delay:.12s}.d3{animation-delay:.18s}

/* Alert */
.alert{
  padding:13px 18px;margin-bottom:18px;
  font-size:12px;letter-spacing:.03em;
  border-left:2px solid;display:flex;align-items:center;gap:10px
}
.alert i{font-size:13px}
.alert-success{background:rgba(77,189,138,.08);border-color:var(--green);color:var(--green)}
.alert-danger {background:rgba(208,104,120,.08);border-color:var(--rose);color:var(--rose)}

/* Panel */
.panel{background:var(--ink3);border:1px solid var(--bsub)}
.ph{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--bsub)}
.pt{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory)}
.pt-sub{font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3);margin-top:3px}

/* Table */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid var(--bsub)}
th{padding:11px 18px;font-size:8.5px;letter-spacing:.24em;text-transform:uppercase;color:var(--ivory3);font-weight:400;text-align:left;white-space:nowrap}
td{padding:14px 18px;font-size:12.5px;color:var(--ivory2);border-bottom:1px solid var(--bsub);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr{transition:background .14s}
tbody tr:hover{background:rgba(201,168,76,.03)}

/* hidden rows for filter */
tr.hidden-row{display:none}

.td-id{font-size:10px;letter-spacing:.1em;color:var(--ivory3)}
.td-name{color:var(--ivory);font-weight:500;font-size:13px}
.td-meta{font-size:10.5px;color:var(--ivory3);margin-top:2px;letter-spacing:.02em}
.td-amt{font-family:'Cormorant Garamond',serif;font-size:16px;color:var(--ivory)}
.td-event{color:var(--ivory);font-size:12.5px}
.td-type{font-size:10px;color:var(--ivory3);margin-top:2px;text-transform:capitalize}

/* Badges */
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;font-size:9px;letter-spacing:.08em;text-transform:uppercase;font-weight:500}
.bdot{width:4px;height:4px;border-radius:50%}
.badge-success{color:var(--green);background:rgba(77,189,138,.1)}
.badge-success .bdot{background:var(--green)}
.badge-warning{color:var(--amber);background:rgba(232,168,58,.1)}
.badge-warning .bdot{background:var(--amber)}
.badge-danger {color:var(--rose); background:rgba(208,104,120,.1)}
.badge-danger  .bdot{background:var(--rose)}
.badge-primary{color:var(--teal); background:rgba(77,184,189,.1)}
.badge-primary .bdot{background:var(--teal)}

/* Action buttons */
.acts{display:flex;gap:6px;flex-wrap:nowrap}
.abtn{
  background:none;border:1px solid var(--bsub);
  color:var(--ivory3);font-family:'Barlow',sans-serif;
  font-size:10px;letter-spacing:.06em;
  padding:5px 12px;cursor:pointer;transition:all .2s;white-space:nowrap
}
.abtn:hover{border-color:var(--gold);color:var(--gold)}
.abtn.confirm:hover{border-color:var(--green);color:var(--green)}
.abtn.cancel:hover{border-color:var(--rose);color:var(--rose)}
.abtn i{margin-right:4px;font-size:9px}

/* Empty */
.empty{padding:48px 22px;text-align:center;font-size:11px;color:var(--ivory3);letter-spacing:.14em;text-transform:uppercase}

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
  <a href="bookings.php"  class="sb-link active">
    <i class="fas fa-calendar-check"></i> Bookings
    <?php if($cnt_pending > 0): ?>
      <span class="sb-badge"><?= $cnt_pending ?></span>
    <?php endif; ?>
  </a>

  <span class="sb-section">Manage</span>
  <a href="halls.php"   class="sb-link"><i class="fas fa-building-columns"></i> Halls</a>
  <a href="vendors.php" class="sb-link"><i class="fas fa-store"></i> Vendors</a>
  <a href="users.php"   class="sb-link"><i class="fas fa-users"></i> Users</a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
      <div>
        <div class="sb-uname"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <div class="sb-urole">Administrator</div>
      </div>
      <a class="sb-logout" href="../logout.php" title="Sign out">
        <i class="fas fa-arrow-right-from-bracket"></i>
      </a>
    </div>
  </div>
</aside>

<!-- ── MAIN ─────────────────────────────────────────────────── -->
<main class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div class="tb-left">
      <h1>Manage <em>Bookings</em></h1>
      <div class="tb-sub"><?= $cnt_all ?> total &nbsp;·&nbsp; <?= $cnt_pending ?> pending review</div>
    </div>
    <div class="tb-right">
      <a href="dashboard.php" class="tb-btn" title="Dashboard"><i class="fas fa-chart-tree-map"></i></a>
      <a href="../logout.php"  class="tb-btn" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <!-- Filter pills -->
  <div class="filter-bar">
    <button class="fpill active" onclick="filterRows('all',this)">
      All <span class="fpill-count"><?= $cnt_all ?></span>
    </button>
    <button class="fpill" onclick="filterRows('pending',this)">
      Pending <span class="fpill-count"><?= $cnt_pending ?></span>
    </button>
    <button class="fpill" onclick="filterRows('confirmed',this)">
      Confirmed <span class="fpill-count"><?= $cnt_confirmed ?></span>
    </button>
    <button class="fpill" onclick="filterRows('cancelled',this)">
      Cancelled <span class="fpill-count"><?= $cnt_cancelled ?></span>
    </button>
  </div>

  <!-- Content -->
  <div class="content">

    <?php if ($success): ?>
      <div class="alert alert-success ani d1"><i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger ani d1"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="panel ani d2">
      <div class="tbl-wrap">
        <table id="bookings-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Hall</th>
              <th>Event</th>
              <th>Date</th>
              <th>Guests</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($bookings->num_rows > 0): ?>
              <?php while ($b = $bookings->fetch_assoc()):
                $bc = 'badge-primary';
                if ($b['status'] === 'confirmed') $bc = 'badge-success';
                elseif ($b['status'] === 'pending')   $bc = 'badge-warning';
                elseif ($b['status'] === 'cancelled') $bc = 'badge-danger';
              ?>
              <tr data-status="<?= $b['status'] ?>">
                <td><span class="td-id">#<?= $b['id'] ?></span></td>
                <td>
                  <div class="td-name"><?= htmlspecialchars($b['user_name']) ?></div>
                  <div class="td-meta"><?= htmlspecialchars($b['user_email']) ?></div>
                  <?php if (!empty($b['user_phone'])): ?>
                    <div class="td-meta"><?= htmlspecialchars($b['user_phone']) ?></div>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($b['hall_name']) ?></td>
                <td>
                  <div class="td-event"><?= htmlspecialchars($b['event_name']) ?></div>
                  <?php if (!empty($b['event_type'])): ?>
                    <div class="td-type"><?= htmlspecialchars($b['event_type']) ?></div>
                  <?php endif; ?>
                </td>
                <td><?= format_date($b['event_date']) ?></td>
                <td><?= $b['guests_count'] ?? '—' ?></td>
                <td><span class="td-amt"><?= format_currency($b['total_amount']) ?></span></td>
                <td>
                  <span class="badge <?= $bc ?>">
                    <span class="bdot"></span><?= ucfirst($b['status']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($b['status'] === 'pending'): ?>
                    <div class="acts">
                      <form method="POST" style="display:inline">
                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                        <input type="hidden" name="status"     value="confirmed">
                        <button type="submit" name="update_status" class="abtn confirm">
                          <i class="fas fa-check"></i>Confirm
                        </button>
                      </form>
                      <form method="POST" style="display:inline">
                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                        <input type="hidden" name="status"     value="cancelled">
                        <button type="submit" name="update_status" class="abtn cancel">
                          <i class="fas fa-xmark"></i>Cancel
                        </button>
                      </form>
                    </div>
                  <?php else: ?>
                    <span style="font-size:10px;color:var(--ivory3);letter-spacing:.08em;">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="9"><div class="empty">No bookings found</div></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->
</main>

<script>
function filterRows(status, btn) {
  // toggle pill
  document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');

  // toggle rows
  document.querySelectorAll('#bookings-table tbody tr[data-status]').forEach(row => {
    if (status === 'all' || row.dataset.status === status) {
      row.classList.remove('hidden-row');
    } else {
      row.classList.add('hidden-row');
    }
  });
}
</script>
</body>
</html>