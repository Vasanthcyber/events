<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

$user_id = $_SESSION['user_id'];

// Get user's bookings
$bookings = $conn->query(
    "SELECT b.*, h.name as hall_name, h.location, h.image
     FROM bookings b
     JOIN halls h ON b.hall_id = h.id
     WHERE b.user_id = $user_id
     ORDER BY b.created_at DESC"
);

// Counts for filter tabs
$cnt_all       = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$user_id")->fetch_assoc()['c'];
$cnt_pending   = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$user_id AND status='pending'")->fetch_assoc()['c'];
$cnt_confirmed = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$user_id AND status='confirmed'")->fetch_assoc()['c'];
$cnt_cancelled = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$user_id AND status='cancelled'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — My Bookings</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#0b0908;--ink2:#131009;--ink3:#1a1610;--ink4:#201c15;
  --ivory:#f2ece0;--ivory2:#b5ad9f;--ivory3:#6e6560;
  --gold:#c9a84c;--goldl:#e8d08a;--goldp:rgba(201,168,76,.11);
  --green:#4dbd8a;--amber:#e8a83a;--rose:#d06878;--teal:#4db8bd;
  --border:rgba(201,168,76,.15);--bsub:rgba(255,255,255,.055);--sw:256px;
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
.tb-new{height:34px;display:flex;align-items:center;gap:8px;border:1px solid var(--gold);color:var(--gold);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.16em;text-transform:uppercase;padding:0 16px;text-decoration:none;transition:all .25s}
.tb-new:hover{background:var(--goldp)}
.tb-new i{font-size:10px}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

/* Filter bar */
.filter-bar{display:flex;align-items:center;gap:8px;padding:20px 40px 0;flex-shrink:0}
.fpill{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.18em;text-transform:uppercase;padding:7px 14px;cursor:pointer;transition:all .22s;display:flex;align-items:center;gap:7px}
.fpill:hover{border-color:var(--border);color:var(--ivory2)}
.fpill.active{border-color:var(--gold);color:var(--gold);background:var(--goldp)}
.fpill-count{background:rgba(255,255,255,.07);padding:1px 6px;font-size:9px;font-weight:600}
.fpill.active .fpill-count{background:rgba(201,168,76,.2)}

.content{flex:1;overflow-y:auto;padding:20px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .44s ease both}

/* ── Booking Cards ─────────────────────────────────────────── */
.booking-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:16px}

.bcard{background:var(--ink3);border:1px solid var(--bsub);display:flex;flex-direction:column;transition:border-color .25s;position:relative;overflow:hidden}
.bcard:hover{border-color:var(--border)}

/* Left colour strip by status */
.bcard::before{content:'';position:absolute;left:0;top:0;bottom:0;width:2px}
.bcard[data-status="confirmed"]::before {background:var(--green)}
.bcard[data-status="pending"]::before   {background:var(--amber)}
.bcard[data-status="cancelled"]::before {background:var(--rose)}
.bcard[data-status="completed"]::before {background:var(--teal)}

/* Card header */
.bcard-hdr{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 20px 14px 22px;border-bottom:1px solid var(--bsub)}
.bcard-event{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;color:var(--ivory);line-height:1.2}
.bcard-type{font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ivory3);margin-top:4px}

/* Status badge */
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;font-size:9px;letter-spacing:.08em;text-transform:uppercase;font-weight:500;flex-shrink:0}
.bdot{width:4px;height:4px;border-radius:50%}
.badge-success{color:var(--green);background:rgba(77,189,138,.1)}
.badge-success .bdot{background:var(--green)}
.badge-warning{color:var(--amber);background:rgba(232,168,58,.1)}
.badge-warning .bdot{background:var(--amber)}
.badge-danger {color:var(--rose); background:rgba(208,104,120,.1)}
.badge-danger  .bdot{background:var(--rose)}
.badge-primary{color:var(--teal); background:rgba(77,184,189,.1)}
.badge-primary .bdot{background:var(--teal)}

/* Detail rows */
.bcard-body{padding:14px 20px 14px 22px;flex:1;display:flex;flex-direction:column;gap:0}
.bcard-meta{display:flex;flex-direction:column;gap:0;margin-bottom:14px}
.mrow{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--bsub)}
.mrow:last-child{border-bottom:none}
.mrow i{font-size:10px;color:var(--ivory3);width:12px;text-align:center;flex-shrink:0}
.mrow-label{font-size:9.5px;letter-spacing:.12em;text-transform:uppercase;color:var(--ivory3);width:64px;flex-shrink:0}
.mrow-val{font-size:12.5px;color:var(--ivory2)}
.mrow-val.hall{color:var(--ivory);font-weight:500}

/* Amount highlight */
.bcard-amount{
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 20px 12px 22px;
  background:var(--ink4);border-top:1px solid var(--bsub);
  margin-top:auto
}
.amount-label{font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:var(--ivory3)}
.amount-val{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:300;color:var(--goldl)}

/* Special requirements */
.bcard-req{padding:10px 20px 12px 22px;border-top:1px solid var(--bsub)}
.req-label{font-size:8.5px;letter-spacing:.2em;text-transform:uppercase;color:var(--ivory3);margin-bottom:5px}
.req-text{font-size:12px;color:var(--ivory3);line-height:1.6;font-weight:300}

/* Footer */
.bcard-footer{padding:10px 20px 12px 22px;border-top:1px solid var(--bsub);display:flex;align-items:center;justify-content:space-between}
.bcard-ts{font-size:10px;color:var(--ivory3)}
.bcard-ts i{margin-right:5px;font-size:9px}
.view-btn{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.07em;text-transform:uppercase;padding:5px 12px;text-decoration:none;transition:all .2s}
.view-btn:hover{border-color:var(--gold);color:var(--gold)}
.view-btn i{font-size:9px}

/* ── Empty state ────────────────────────────────────────────── */
.empty{padding:72px 24px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px;background:var(--ink3);border:1px solid var(--bsub)}
.empty-icon{width:64px;height:64px;border:1px solid var(--bsub);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--ivory3);opacity:.4}
.empty h3{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:300;color:var(--ivory2)}
.empty p{font-size:12px;color:var(--ivory3);font-weight:300;max-width:340px;line-height:1.7}
.empty-cta{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--gold);color:var(--gold);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.22em;text-transform:uppercase;padding:12px 24px;text-decoration:none;transition:all .3s;position:relative;overflow:hidden;margin-top:4px}
.empty-cta::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.empty-cta:hover::before{transform:scaleX(1)}
.empty-cta:hover{color:var(--ink)}
.empty-cta span{position:relative;z-index:1;display:flex;align-items:center;gap:8px}

@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.filter-bar,.content{padding-left:20px;padding-right:20px}
  .booking-grid{grid-template-columns:1fr}
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
  <a href="dashboard.php" class="sb-link"><i class="fas fa-chart-tree-map"></i> Dashboard</a>
  <a href="halls.php"     class="sb-link"><i class="fas fa-building-columns"></i> Browse Halls</a>
  <a href="vendors.php"   class="sb-link"><i class="fas fa-store"></i> Vendors</a>
  <a href="bookings.php"  class="sb-link active">
    <i class="fas fa-calendar-check"></i> My Bookings
    <?php if($cnt_pending > 0): ?><span class="sb-badge"><?= $cnt_pending ?></span><?php endif; ?>
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
      <h1>My <em>Bookings</em></h1>
      <div class="tb-sub"><?= $cnt_all ?> total reservation<?= $cnt_all != 1 ? 's' : '' ?></div>
    </div>
    <div class="tb-right">
      <a href="halls.php" class="tb-new"><i class="fas fa-plus"></i> New Booking</a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <!-- Filter pills -->
  <div class="filter-bar">
    <button class="fpill active" onclick="filterCards('all',this)">All <span class="fpill-count"><?= $cnt_all ?></span></button>
    <button class="fpill" onclick="filterCards('pending',this)">Pending <span class="fpill-count"><?= $cnt_pending ?></span></button>
    <button class="fpill" onclick="filterCards('confirmed',this)">Confirmed <span class="fpill-count"><?= $cnt_confirmed ?></span></button>
    <button class="fpill" onclick="filterCards('cancelled',this)">Cancelled <span class="fpill-count"><?= $cnt_cancelled ?></span></button>
  </div>

  <div class="content">
    <?php if ($bookings->num_rows > 0): ?>

      <div class="booking-grid" id="booking-grid">
        <?php $i=0; while ($b = $bookings->fetch_assoc()):
          $i++;
          $bc = 'badge-primary';
          if ($b['status'] === 'confirmed') $bc = 'badge-success';
          elseif ($b['status'] === 'pending')   $bc = 'badge-warning';
          elseif ($b['status'] === 'cancelled') $bc = 'badge-danger';
        ?>
        <div class="bcard ani" style="animation-delay:<?= $i*0.065 ?>s" data-status="<?= $b['status'] ?>">

          <!-- Header -->
          <div class="bcard-hdr">
            <div>
              <div class="bcard-event"><?= htmlspecialchars($b['event_name']) ?></div>
              <div class="bcard-type"><?= htmlspecialchars($b['event_type']) ?></div>
            </div>
            <span class="badge <?= $bc ?>"><span class="bdot"></span><?= ucfirst($b['status']) ?></span>
          </div>

          <!-- Meta rows -->
          <div class="bcard-body">
            <div class="bcard-meta">
              <div class="mrow">
                <i class="fas fa-building-columns"></i>
                <span class="mrow-label">Venue</span>
                <span class="mrow-val hall"><?= htmlspecialchars($b['hall_name']) ?></span>
              </div>
              <div class="mrow">
                <i class="fas fa-location-dot"></i>
                <span class="mrow-label">Location</span>
                <span class="mrow-val"><?= htmlspecialchars($b['location']) ?></span>
              </div>
              <div class="mrow">
                <i class="fas fa-calendar"></i>
                <span class="mrow-label">Date</span>
                <span class="mrow-val"><?= format_date($b['event_date']) ?></span>
              </div>
              <div class="mrow">
                <i class="fas fa-users"></i>
                <span class="mrow-label">Guests</span>
                <span class="mrow-val"><?= $b['guests_count'] ?> attendees</span>
              </div>
            </div>

            <?php if (!empty($b['special_requirements'])): ?>
            <div class="bcard-req">
              <div class="req-label">Special Requirements</div>
              <div class="req-text"><?= htmlspecialchars($b['special_requirements']) ?></div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Amount -->
          <div class="bcard-amount">
            <span class="amount-label">Total Amount</span>
            <span class="amount-val"><?= format_currency($b['total_amount']) ?></span>
          </div>

          <!-- Footer -->
          <div class="bcard-footer">
            <span class="bcard-ts"><i class="fas fa-clock"></i>Booked <?= format_date($b['created_at']) ?></span>
            <div style="display:flex;gap:8px;align-items:center">
              <a href="send_invitation.php?booking_id=<?= $b['id'] ?>" class="view-btn" style="background:var(--goldp);border-color:rgba(201,168,76,.3);color:var(--gold)">
                <i class="fas fa-envelope-open-text"></i> Invitation
              </a>
              <a href="booking_details.php?id=<?= $b['id'] ?>" class="view-btn">
                <i class="fas fa-arrow-right"></i> Details
              </a>
            </div>
          </div>

        </div>
        <?php endwhile; ?>
      </div>

    <?php else: ?>

      <div class="empty ani">
        <div class="empty-icon"><i class="fas fa-calendar-xmark"></i></div>
        <h3>No reservations yet</h3>
        <p>You haven't made any bookings. Explore our curated venues and reserve your perfect event space.</p>
        <a href="halls.php" class="empty-cta">
          <span><i class="fas fa-building-columns"></i> Browse Halls</span>
        </a>
      </div>

    <?php endif; ?>
  </div>
</main>

<script>
function filterCards(status, btn) {
  document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('#booking-grid .bcard').forEach(c => {
    c.style.display = (status === 'all' || c.dataset.status === status) ? '' : 'none';
  });
}
</script>
</body>
</html>












































