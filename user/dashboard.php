<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

$user_id = $_SESSION['user_id'];

// Get user bookings
$bookings = $conn->query(
    "SELECT b.*, h.name as hall_name, h.location
     FROM bookings b
     JOIN halls h ON b.hall_id = h.id
     WHERE b.user_id = $user_id
     ORDER BY b.created_at DESC"
);

// Booking statistics
$total_bookings     = get_booking_count($conn, $user_id);
$confirmed_bookings = get_booking_count($conn, $user_id, 'confirmed');
$pending_bookings   = get_booking_count($conn, $user_id, 'pending');
$cancelled_bookings = get_booking_count($conn, $user_id, 'cancelled');

// Greeting
$hour     = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$first    = explode(' ', $_SESSION['user_name'])[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — My Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ── Reset ─────────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --ink:   #0b0908; --ink2:  #131009; --ink3:  #1a1610; --ink4:  #201c15;
  --ivory: #f2ece0; --ivory2:#b5ad9f; --ivory3:#6e6560;
  --gold:  #c9a84c; --goldl: #e8d08a; --goldp: rgba(201,168,76,.11);
  --green: #4dbd8a; --amber: #e8a83a; --rose:  #d06878; --teal:  #4db8bd;
  --border:rgba(201,168,76,.15); --bsub:rgba(255,255,255,.055); --sw:256px;
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
.tb-date{font-size:10px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s;text-decoration:none;position:relative}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-dot{width:5px;height:5px;border-radius:50%;background:var(--gold);position:absolute;top:6px;right:6px}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

.content{flex:1;overflow-y:auto;padding:28px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

/* Animations */
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .44s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}.d3{animation-delay:.15s}
.d4{animation-delay:.20s}.d5{animation-delay:.25s}.d6{animation-delay:.30s}

/* ── Stat Cards ────────────────────────────────────────────── */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}

.sc{background:var(--ink3);border:1px solid var(--bsub);padding:22px 20px 18px;position:relative;overflow:hidden;cursor:default;transition:border-color .25s}
.sc:hover{border-color:var(--border)}
.sc::after{content:'';position:absolute;top:0;left:0;right:0;height:1px;opacity:0;transition:opacity .3s}
.sc:hover::after{opacity:1}

.sc[data-c="gold"]  .sc-icon{color:var(--gold);background:var(--goldp)}
.sc[data-c="green"] .sc-icon{color:var(--green);background:rgba(77,189,138,.1)}
.sc[data-c="amber"] .sc-icon{color:var(--amber);background:rgba(232,168,58,.1)}
.sc[data-c="rose"]  .sc-icon{color:var(--rose);background:rgba(208,104,120,.1)}
.sc[data-c="gold"]::after {background:var(--gold)}
.sc[data-c="green"]::after{background:var(--green)}
.sc[data-c="amber"]::after{background:var(--amber)}
.sc[data-c="rose"]::after {background:var(--rose)}

.sc-icon{width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:12px;margin-bottom:16px}
.sc-val{font-family:'Cormorant Garamond',serif;font-size:36px;font-weight:300;color:var(--ivory);line-height:1;margin-bottom:5px}
.sc-label{font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);font-weight:400}

/* ── Quick Actions ─────────────────────────────────────────── */
.quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px}

.qcard{
  background:var(--ink3);border:1px solid var(--bsub);
  padding:24px 26px;display:flex;align-items:center;gap:18px;
  text-decoration:none;transition:all .25s;position:relative;overflow:hidden
}
.qcard::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,var(--goldp),transparent);
  opacity:0;transition:opacity .3s
}
.qcard:hover{border-color:var(--border)}
.qcard:hover::before{opacity:1}

.qcard-icon{
  width:44px;height:44px;border:1px solid var(--border);flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:16px;color:var(--gold);position:relative;z-index:1;
  transition:background .2s
}
.qcard:hover .qcard-icon{background:var(--goldp)}

.qcard-text{position:relative;z-index:1}
.qcard-title{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory);line-height:1;margin-bottom:4px}
.qcard-sub{font-size:10.5px;color:var(--ivory3);font-weight:300}

.qcard-arrow{margin-left:auto;color:var(--ivory3);font-size:12px;transition:all .2s;position:relative;z-index:1}
.qcard:hover .qcard-arrow{color:var(--gold);transform:translateX(3px)}

/* ── Panel ─────────────────────────────────────────────────── */
.panel{background:var(--ink3);border:1px solid var(--bsub)}
.ph{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--bsub)}
.pt{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory)}
.pa{font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);text-decoration:none;font-weight:400;transition:opacity .2s}
.pa:hover{opacity:.65}

/* ── Table ─────────────────────────────────────────────────── */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid var(--bsub)}
th{padding:11px 22px;font-size:8.5px;letter-spacing:.24em;text-transform:uppercase;color:var(--ivory3);font-weight:400;text-align:left;white-space:nowrap}
td{padding:14px 22px;font-size:12.5px;color:var(--ivory2);border-bottom:1px solid var(--bsub);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr{transition:background .14s}
tbody tr:hover{background:rgba(201,168,76,.03)}

.td-id   {font-size:10px;letter-spacing:.1em;color:var(--ivory3)}
.td-name {color:var(--ivory);font-weight:500}
.td-hall {font-size:12px;color:var(--ivory2)}
.td-loc  {font-size:10.5px;color:var(--ivory3);margin-top:2px}
.td-amt  {font-family:'Cormorant Garamond',serif;font-size:16px;color:var(--ivory)}

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

/* Detail button */
.abtn{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.07em;text-transform:uppercase;padding:5px 12px;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:5px}
.abtn:hover{border-color:var(--gold);color:var(--gold)}
.abtn i{font-size:9px}

/* ── Empty state ───────────────────────────────────────────── */
.empty{
  padding:56px 24px;text-align:center;
  display:flex;flex-direction:column;align-items:center;gap:16px
}
.empty-icon{
  width:60px;height:60px;border:1px solid var(--bsub);
  display:flex;align-items:center;justify-content:center;
  font-size:1.5rem;color:var(--ivory3);opacity:.5;margin-bottom:4px
}
.empty h3{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:300;color:var(--ivory2)}
.empty p{font-size:12px;color:var(--ivory3);font-weight:300}
.empty-cta{
  display:inline-flex;align-items:center;gap:8px;
  border:1px solid var(--gold);color:var(--gold);
  font-family:'Barlow',sans-serif;font-size:10px;
  letter-spacing:.22em;text-transform:uppercase;
  padding:11px 22px;text-decoration:none;
  transition:all .3s;position:relative;overflow:hidden;margin-top:4px
}
.empty-cta::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.empty-cta:hover::before{transform:scaleX(1)}
.empty-cta:hover{color:var(--ink)}
.empty-cta span{position:relative;z-index:1}

/* Responsive */
@media(max-width:1000px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.content{padding-left:20px;padding-right:20px}
  .quick-grid{grid-template-columns:1fr}
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
    <div class="sb-sub">My Account</div>
  </div>

  <span class="sb-section">Navigation</span>
  <a href="dashboard.php" class="sb-link active"><i class="fas fa-chart-tree-map"></i> Dashboard</a>
  <a href="halls.php"     class="sb-link"><i class="fas fa-building-columns"></i> Browse Halls</a>
  <a href="vendors.php"   class="sb-link"><i class="fas fa-store"></i> Vendors</a>
  <a href="bookings.php"  class="sb-link">
    <i class="fas fa-calendar-check"></i> My Bookings
    <?php if($pending_bookings > 0): ?>
      <span class="sb-badge"><?= $pending_bookings ?></span>
    <?php endif; ?>
  </a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
      <div>
        <div class="sb-uname"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <div class="sb-urole">Member</div>
      </div>
      <a class="sb-logout" href="../logout.php" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
</aside>

<!-- ── MAIN ─────────────────────────────────────────────────── -->
<main class="main">

  <div class="topbar">
    <div class="tb-left">
      <h1><?= $greeting ?>, <em><?= htmlspecialchars($first) ?></em>.</h1>
      <div class="tb-date"><?= date('l, F j, Y') ?></div>
    </div>
    <div class="tb-right">
      <a href="bookings.php" class="tb-btn" title="My bookings">
        <i class="fas fa-calendar-check"></i>
        <?php if($pending_bookings > 0): ?><span class="tb-dot"></span><?php endif; ?>
      </a>
      <a href="../logout.php" class="tb-btn" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <div class="content">

    <!-- Stat cards -->
    <div class="stat-grid">
      <div class="sc ani d1" data-c="gold">
        <div class="sc-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="sc-val"><?= $total_bookings ?></div>
        <div class="sc-label">Total Bookings</div>
      </div>
      <div class="sc ani d2" data-c="green">
        <div class="sc-icon"><i class="fas fa-circle-check"></i></div>
        <div class="sc-val"><?= $confirmed_bookings ?></div>
        <div class="sc-label">Confirmed</div>
      </div>
      <div class="sc ani d3" data-c="amber">
        <div class="sc-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="sc-val"><?= $pending_bookings ?></div>
        <div class="sc-label">Pending</div>
      </div>
      <div class="sc ani d4" data-c="rose">
        <div class="sc-icon"><i class="fas fa-ban"></i></div>
        <div class="sc-val"><?= $cancelled_bookings ?></div>
        <div class="sc-label">Cancelled</div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="quick-grid ani d4">
      <a href="halls.php" class="qcard">
        <div class="qcard-icon"><i class="fas fa-building-columns"></i></div>
        <div class="qcard-text">
          <div class="qcard-title">Browse Halls</div>
          <div class="qcard-sub">Explore available event venues</div>
        </div>
        <i class="fas fa-arrow-right qcard-arrow"></i>
      </a>
      <a href="vendors.php" class="qcard">
        <div class="qcard-icon"><i class="fas fa-store"></i></div>
        <div class="qcard-text">
          <div class="qcard-title">View Vendors</div>
          <div class="qcard-sub">Catering, décor, photography &amp; more</div>
        </div>
        <i class="fas fa-arrow-right qcard-arrow"></i>
      </a>
    </div>

    <!-- Recent bookings -->
    <div class="panel ani d5">
      <div class="ph">
        <span class="pt">Recent Bookings</span>
        <a href="bookings.php" class="pa">View All →</a>
      </div>

      <?php if ($bookings->num_rows > 0): ?>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Hall</th>
                <th>Event</th>
                <th>Date</th>
                <th>Status</th>
                <th>Amount</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php while ($b = $bookings->fetch_assoc()):
                $bc = 'badge-primary';
                if ($b['status'] === 'confirmed') $bc = 'badge-success';
                elseif ($b['status'] === 'pending')   $bc = 'badge-warning';
                elseif ($b['status'] === 'cancelled') $bc = 'badge-danger';
              ?>
              <tr>
                <td><span class="td-id">#<?= $b['id'] ?></span></td>
                <td>
                  <div class="td-name"><?= htmlspecialchars($b['hall_name']) ?></div>
                  <div class="td-loc"><i class="fas fa-location-dot" style="color:var(--gold);font-size:9px;margin-right:4px"></i><?= htmlspecialchars($b['location']) ?></div>
                </td>
                <td><span class="td-name"><?= htmlspecialchars($b['event_name']) ?></span></td>
                <td><?= format_date($b['event_date']) ?></td>
                <td>
                  <span class="badge <?= $bc ?>">
                    <span class="bdot"></span><?= ucfirst($b['status']) ?>
                  </span>
                </td>
                <td><span class="td-amt"><?= format_currency($b['total_amount']) ?></span></td>
                <td>
                  <a href="booking_details.php?id=<?= $b['id'] ?>" class="abtn">
                    <i class="fas fa-arrow-right"></i> Details
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

      <?php else: ?>
        <div class="empty">
          <div class="empty-icon"><i class="fas fa-calendar-xmark"></i></div>
          <h3>No bookings yet</h3>
          <p>You haven't made any reservations. Browse our venues to get started.</p>
          <a href="halls.php" class="empty-cta"><span><i class="fas fa-building-columns" style="margin-right:8px;font-size:10px"></i>Browse Halls</span></a>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /content -->
</main>

</body>
</html>