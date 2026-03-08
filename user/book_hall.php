<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

$error   = '';
$success = '';

// Get hall ID
if (!isset($_GET['hall_id'])) { redirect('halls.php'); }
$hall_id = intval($_GET['hall_id']);

// Get hall details
$stmt = $conn->prepare("SELECT * FROM halls WHERE id = ? AND status = 'available'");
$stmt->bind_param("i", $hall_id);
$stmt->execute();
$hall = $stmt->get_result()->fetch_assoc();
if (!$hall) { redirect('halls.php'); }

// Handle booking submission
if (isset($_POST['book_hall'])) {
    $user_id              = $_SESSION['user_id'];
    $event_name           = sanitize_input($_POST['event_name']);
    $event_date           = sanitize_input($_POST['event_date']);
    $event_type           = sanitize_input($_POST['event_type']);
    $guests_count         = intval($_POST['guests_count']);
    $special_requirements = sanitize_input($_POST['special_requirements']);
    $total_amount         = $hall['price_per_day'];

    if (strtotime($event_date) < strtotime(date('Y-m-d'))) {
        $error = 'Event date cannot be in the past.';
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE hall_id = ? AND event_date = ? AND status != 'cancelled'");
        $check_stmt->bind_param("is", $hall_id, $event_date);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'This hall is already booked for the selected date.';
        } else {
            $ins = $conn->prepare("INSERT INTO bookings (user_id, hall_id, event_name, event_date, event_type, guests_count, special_requirements, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param("iisssisd", $user_id, $hall_id, $event_name, $event_date, $event_type, $guests_count, $special_requirements, $total_amount);
            if ($ins->execute()) { $success = 'Booking submitted successfully. Awaiting confirmation.'; }
            else                 { $error   = 'Failed to submit booking. Please try again.'; }
        }
    }
}

$amenity_list = array_filter(array_map('trim', explode(',', $hall['amenities'] ?? '')));
$pending = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id={$_SESSION['user_id']} AND status='pending'")->fetch_assoc()['c'];

// Fetch booked dates for calendar (next 12 months)
$booked_dates  = [];
$pending_dates = [];
$my_dates      = [];
$date_from = date('Y-m-d');
$date_to   = date('Y-m-d', strtotime('+12 months'));
$bd_stmt = $conn->prepare("SELECT event_date, status, user_id FROM bookings WHERE hall_id = ? AND event_date BETWEEN ? AND ? AND status != 'cancelled'");
$bd_stmt->bind_param("iss", $hall_id, $date_from, $date_to);
$bd_stmt->execute();
$bd_res = $bd_stmt->get_result();
while ($row = $bd_res->fetch_assoc()) {
    $d = $row['event_date'];
    if ($row['user_id'] == $_SESSION['user_id']) { $my_dates[]      = $d; }
    elseif ($row['status'] === 'pending')         { $pending_dates[] = $d; }
    else                                          { $booked_dates[]  = $d; }
}
$booked_json  = json_encode(array_values(array_unique($booked_dates)));
$pending_json = json_encode(array_values(array_unique($pending_dates)));
$my_json      = json_encode(array_values(array_unique($my_dates)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Book <?= htmlspecialchars($hall['name']) ?></title>
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
.tb-left h1{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;color:var(--ivory);line-height:1}
.tb-left h1 em{font-style:italic;color:var(--goldl)}
.tb-sub{font-size:10px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-back{display:flex;align-items:center;gap:8px;border:1px solid var(--bsub);color:var(--ivory2);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.14em;text-transform:uppercase;padding:0 14px;height:34px;text-decoration:none;transition:all .2s}
.tb-back:hover{border-color:var(--gold);color:var(--gold)}
.tb-back i{font-size:10px}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

.content{flex:1;overflow-y:auto;padding:28px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

/* Animations */
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .44s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.12s}

/* Alert */
.alert{padding:14px 20px;margin-bottom:24px;font-size:12.5px;border-left:2px solid;display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap}
.alert i{font-size:14px;flex-shrink:0;margin-top:1px}
.alert-success{background:rgba(77,189,138,.08);border-color:var(--green);color:var(--green)}
.alert-danger {background:rgba(208,104,120,.08);border-color:var(--rose);color:var(--rose)}
.alert-cta{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--green);color:var(--green);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.18em;text-transform:uppercase;padding:7px 14px;text-decoration:none;transition:all .25s;margin-top:6px}
.alert-cta:hover{background:var(--green);color:var(--ink)}

/* ── Two-column layout ─────────────────────────────────────── */
.layout{display:grid;grid-template-columns:1fr 420px;gap:20px;align-items:start}

/* ── Hall Info Panel ───────────────────────────────────────── */
.info-panel{background:var(--ink3);border:1px solid var(--bsub);overflow:hidden}

.hall-img{height:240px;overflow:hidden;position:relative;background:var(--ink4)}
.hall-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.82);transition:filter .4s}
.info-panel:hover .hall-img img{filter:brightness(.92)}
.hall-img-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--ink3),var(--ink4))}
.hall-img-placeholder i{font-size:3rem;color:var(--ivory3);opacity:.28}

/* Price badge on image */
.img-price{position:absolute;bottom:16px;right:16px;background:rgba(0,0,0,.72);backdrop-filter:blur(8px);border:1px solid var(--border);padding:8px 14px}
.img-price-val{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:300;color:var(--goldl);line-height:1}
.img-price-lbl{font-size:8px;letter-spacing:.2em;text-transform:uppercase;color:var(--ivory3);margin-top:2px;text-align:right}

.info-body{padding:22px 22px 18px}
.info-name{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:400;color:var(--ivory);margin-bottom:8px}
.info-desc{font-size:12px;color:var(--ivory3);line-height:1.7;font-weight:300;margin-bottom:18px}

/* Hall detail rows */
.detail-block{background:var(--ink4);border:1px solid var(--bsub);margin-bottom:18px}
.detail-row{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--bsub)}
.detail-row:last-child{border-bottom:none}
.dr-icon{width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:11px;color:var(--gold);background:var(--goldp);flex-shrink:0}
.dr-label{font-size:9.5px;letter-spacing:.15em;text-transform:uppercase;color:var(--ivory3);width:76px;flex-shrink:0}
.dr-val{font-size:13px;color:var(--ivory);font-weight:400}

/* Amenity chips */
.amenities-wrap{display:flex;flex-wrap:wrap;gap:6px;margin-top:4px}
.atag{font-size:9.5px;padding:3px 9px;background:rgba(255,255,255,.05);color:var(--ivory3);letter-spacing:.06em}

/* ── Booking Form Panel ────────────────────────────────────── */
.form-panel{background:var(--ink3);border:1px solid var(--bsub)}

.form-hdr{padding:20px 24px;border-bottom:1px solid var(--bsub)}
.form-hdr-title{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;color:var(--ivory)}
.form-hdr-sub{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--ivory3);margin-top:4px}

.form-body{padding:24px}

/* Fields */
.fg{margin-bottom:20px}
.fg label{display:block;font-size:9px;letter-spacing:.25em;text-transform:uppercase;color:var(--ivory3);margin-bottom:9px;font-weight:400}
.fg input,.fg select,.fg textarea{
  width:100%;background:none;border:none;border-bottom:1px solid var(--bsub);
  color:var(--ivory);font-family:'Barlow',sans-serif;font-size:14px;font-weight:300;
  padding:9px 0;outline:none;transition:border-color .2s;resize:none
}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-bottom-color:var(--gold)}
.fg input::placeholder,.fg textarea::placeholder{color:rgba(110,101,96,.4)}
.fg select option{background:var(--ink3)}
.fg-hint{font-size:10px;color:var(--ivory3);margin-top:6px;letter-spacing:.04em}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}

/* ── Date input — kill browser chrome, full custom style ── */
.fg input[type="date"]{
  color-scheme:dark;                     /* dark calendar popup */
  appearance:none;-webkit-appearance:none;
  position:relative;cursor:pointer;
  padding-right:28px;                    /* room for custom icon */
}
/* placeholder text colour when no date selected */
.fg input[type="date"]:not(:valid),
.fg input[type="date"]:not([value]):not(:focus){color:rgba(110,101,96,.4)}
/* gold calendar icon via background */
.fg input[type="date"]{
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23c9a84c' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
  background-repeat:no-repeat;
  background-position:right 2px center;
  background-size:14px 14px;
}
/* hide the browser's built-in calendar icon */
.fg input[type="date"]::-webkit-calendar-picker-indicator{
  opacity:0;position:absolute;right:0;top:0;width:28px;height:100%;cursor:pointer;
}
/* date parts text colour */
.fg input[type="date"]::-webkit-datetime-edit{color:var(--ivory)}
.fg input[type="date"]::-webkit-datetime-edit-fields-wrapper{color:var(--ivory)}
.fg input[type="date"]::-webkit-datetime-edit-text{color:var(--ivory3)}
.fg input[type="date"]::-webkit-datetime-edit-day-field,
.fg input[type="date"]::-webkit-datetime-edit-month-field,
.fg input[type="date"]::-webkit-datetime-edit-year-field{
  color:var(--ivory);background:transparent;
  padding:1px 2px;border-radius:2px;transition:background .15s;
}
.fg input[type="date"]::-webkit-datetime-edit-day-field:focus,
.fg input[type="date"]::-webkit-datetime-edit-month-field:focus,
.fg input[type="date"]::-webkit-datetime-edit-year-field:focus{
  background:var(--goldp);color:var(--goldl);outline:none;
}
/* select dropdown arrow — gold */
.fg select{
  appearance:none;-webkit-appearance:none;cursor:pointer;padding-right:28px;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23c9a84c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 4px center;background-size:12px;
}

/* Booking summary box */
.summary{background:var(--ink4);border:1px solid var(--bsub);padding:18px 18px 14px;margin-bottom:20px}
.summary-title{font-size:9px;letter-spacing:.25em;text-transform:uppercase;color:var(--ivory3);margin-bottom:14px}
.summary-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--bsub)}
.summary-row:last-child{border-bottom:none;padding-top:12px;margin-top:4px}
.summary-row-label{font-size:12px;color:var(--ivory3)}
.summary-row-val{font-size:12.5px;color:var(--ivory2);font-weight:400}
.summary-total-label{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--ivory)}
.summary-total-val{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;color:var(--goldl)}

/* Submit button */
.submit-btn{
  width:100%;background:none;border:1px solid var(--gold);color:var(--gold);
  font-family:'Barlow',sans-serif;font-size:10.5px;letter-spacing:.28em;text-transform:uppercase;
  padding:15px;cursor:pointer;transition:all .3s;position:relative;overflow:hidden
}
.submit-btn::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.submit-btn:hover::before{transform:scaleX(1)}
.submit-btn:hover{color:var(--ink)}
.submit-btn span{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:10px}


/* ── Availability Calendar ─────────────────────────────────── */
.cal-wrap{margin-top:22px}
.cal-section-lbl{font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);margin-bottom:12px}
.cal-box{background:var(--ink4);border:1px solid var(--bsub);padding:18px 16px}
.cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.cal-nav-btn{width:28px;height:28px;background:var(--bsub);border:1px solid var(--bsub);color:var(--ivory2);font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;flex-shrink:0}
.cal-nav-btn:hover{border-color:var(--gold);color:var(--gold)}
.cal-month-lbl{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory);letter-spacing:.06em}
.cal-year-lbl{font-size:10px;letter-spacing:.18em;color:var(--ivory3);margin-top:2px;text-align:center}
.cal-dow{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px}
.cal-dow span{text-align:center;font-size:8.5px;letter-spacing:.12em;text-transform:uppercase;color:var(--ivory3);padding:4px 0;font-weight:400}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px}
.cal-day{
  aspect-ratio:1;display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:300;color:var(--ivory2);
  cursor:pointer;position:relative;transition:all .18s;border:1px solid transparent;
}
.cal-day:hover:not(.cal-empty):not(.cal-past):not(.cal-booked){
  border-color:var(--gold);color:var(--gold);background:var(--goldp);
}
.cal-day.cal-empty{cursor:default}
.cal-day.cal-past{color:var(--ivory3);opacity:.35;cursor:not-allowed}
.cal-day.cal-today{color:var(--gold);font-weight:500}
.cal-day.cal-today::after{content:'';position:absolute;bottom:3px;left:50%;transform:translateX(-50%);width:4px;height:4px;background:var(--gold);border-radius:50%}
/* Booked = confirmed/pending by others */
.cal-day.cal-booked{
  background:rgba(208,104,120,.12);color:rgba(208,104,120,.6);
  cursor:not-allowed;border-color:rgba(208,104,120,.2);
}
.cal-day.cal-booked::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(-45deg,transparent,transparent 3px,rgba(208,104,120,.06) 3px,rgba(208,104,120,.06) 4px)}
/* Pending by others */
.cal-day.cal-pending{
  background:rgba(232,168,58,.1);color:rgba(232,168,58,.7);
  cursor:not-allowed;border-color:rgba(232,168,58,.2);
}
/* My booking */
.cal-day.cal-mine{
  background:rgba(77,189,138,.1);color:var(--green);
  cursor:not-allowed;border-color:rgba(77,189,138,.25);
}
/* Selected */
.cal-day.cal-selected{
  background:var(--goldp);border-color:var(--gold);color:var(--goldl);font-weight:500;
}
/* Legend */
.cal-legend{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px;padding-top:12px;border-top:1px solid var(--bsub)}
.cal-leg-item{display:flex;align-items:center;gap:6px;font-size:9.5px;color:var(--ivory3)}
.cal-leg-dot{width:10px;height:10px;flex-shrink:0;border:1px solid}
.cal-leg-dot.ld-avail  {background:var(--goldp);border-color:var(--gold)}
.cal-leg-dot.ld-booked {background:rgba(208,104,120,.15);border-color:rgba(208,104,120,.3)}
.cal-leg-dot.ld-pending{background:rgba(232,168,58,.12);border-color:rgba(232,168,58,.3)}
.cal-leg-dot.ld-mine   {background:rgba(77,189,138,.12);border-color:rgba(77,189,138,.3)}
.cal-leg-dot.ld-today  {background:var(--goldp);border-color:var(--gold);border-radius:50%}

/* Responsive */
@media(max-width:1100px){.layout{grid-template-columns:1fr}}
@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.content{padding-left:20px;padding-right:20px}
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
  <a href="halls.php"     class="sb-link active"><i class="fas fa-building-columns"></i> Browse Halls</a>
  <a href="vendors.php"   class="sb-link"><i class="fas fa-store"></i> Vendors</a>
  <a href="bookings.php"  class="sb-link">
    <i class="fas fa-calendar-check"></i> My Bookings
    <?php if($pending > 0): ?><span class="sb-badge"><?= $pending ?></span><?php endif; ?>
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
      <h1>Reserve <em><?= htmlspecialchars($hall['name']) ?></em></h1>
      <div class="tb-sub">Complete your booking details below</div>
    </div>
    <div class="tb-right">
      <a href="halls.php" class="tb-back"><i class="fas fa-arrow-left"></i> Back to Halls</a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <div class="content">

    <!-- Success / Error alerts -->
    <?php if ($success): ?>
      <div class="alert alert-success ani d1">
        <i class="fas fa-circle-check"></i>
        <div>
          <div><?= htmlspecialchars($success) ?></div>
          <a href="bookings.php" class="alert-cta"><i class="fas fa-arrow-right"></i> View My Bookings</a>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger ani d1">
        <i class="fas fa-circle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="layout">

      <!-- ── Hall Info ── -->
      <div class="info-panel ani d1">
        <div class="hall-img">
          <?php if (!empty($hall['image'])): ?>
            <img src="../assets/images/<?= htmlspecialchars($hall['image']) ?>" alt="<?= htmlspecialchars($hall['name']) ?>">
          <?php else: ?>
            <div class="hall-img-placeholder"><i class="fas fa-building-columns"></i></div>
          <?php endif; ?>
          <div class="img-price">
            <div class="img-price-val"><?= format_currency($hall['price_per_day']) ?></div>
            <div class="img-price-lbl">per day</div>
          </div>
        </div>

        <div class="info-body">
          <div class="info-name"><?= htmlspecialchars($hall['name']) ?></div>
          <div class="info-desc"><?= htmlspecialchars($hall['description']) ?></div>

          <div class="detail-block">
            <div class="detail-row">
              <div class="dr-icon"><i class="fas fa-users"></i></div>
              <div class="dr-label">Capacity</div>
              <div class="dr-val"><?= number_format($hall['capacity']) ?> guests</div>
            </div>
            <div class="detail-row">
              <div class="dr-icon"><i class="fas fa-location-dot"></i></div>
              <div class="dr-label">Location</div>
              <div class="dr-val"><?= htmlspecialchars($hall['location']) ?></div>
            </div>
            <div class="detail-row">
              <div class="dr-icon"><i class="fas fa-coins"></i></div>
              <div class="dr-label">Price</div>
              <div class="dr-val" style="font-family:'Cormorant Garamond',serif;font-size:18px;color:var(--goldl)"><?= format_currency($hall['price_per_day']) ?> <span style="font-size:11px;color:var(--ivory3);font-family:'Barlow',sans-serif">/ day</span></div>
            </div>
          </div>

          <?php if (!empty($amenity_list)): ?>
          <div style="font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);margin-bottom:10px">Amenities</div>
          <div class="amenities-wrap">
            <?php foreach($amenity_list as $a): ?>
              <span class="atag"><?= htmlspecialchars($a) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- ── Availability Calendar ── -->
          <div class="cal-wrap">
            <div class="cal-section-lbl">Availability Calendar</div>
            <div class="cal-box">
              <div class="cal-nav">
                <button class="cal-nav-btn" onclick="calPrev()" type="button"><i class="fas fa-chevron-left"></i></button>
                <div style="text-align:center">
                  <div class="cal-month-lbl" id="cal-month-lbl"></div>
                  <div class="cal-year-lbl"  id="cal-year-lbl"></div>
                </div>
                <button class="cal-nav-btn" onclick="calNext()" type="button"><i class="fas fa-chevron-right"></i></button>
              </div>
              <div class="cal-dow">
                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span>
                <span>Th</span><span>Fr</span><span>Sa</span>
              </div>
              <div class="cal-grid" id="cal-grid"></div>
              <div class="cal-legend">
                <div class="cal-leg-item"><div class="cal-leg-dot ld-avail"></div> Available</div>
                <div class="cal-leg-item"><div class="cal-leg-dot ld-booked"></div> Booked</div>
                <div class="cal-leg-item"><div class="cal-leg-dot ld-pending"></div> Pending</div>
                <div class="cal-leg-item"><div class="cal-leg-dot ld-mine"></div> My Booking</div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ── Booking Form ── -->
      <div class="form-panel ani d2">
        <div class="form-hdr">
          <div class="form-hdr-title">Booking Details</div>
          <div class="form-hdr-sub">Fill in your event information</div>
        </div>
        <div class="form-body">
          <form method="POST" action="">

            <div class="fg">
              <label>Event Name</label>
              <input type="text" name="event_name" placeholder="e.g. Wedding Reception" required>
            </div>

            <div class="fg-row">
              <div class="fg">
                <label>Event Date</label>
                <input type="date" name="event_date" required min="<?= date('Y-m-d') ?>">
              </div>
              <div class="fg">
                <label>Event Type</label>
                <select name="event_type" required>
                  <option value="">Select type…</option>
                  <option value="Wedding">Wedding</option>
                  <option value="Birthday Party">Birthday Party</option>
                  <option value="Corporate Event">Corporate Event</option>
                  <option value="Conference">Conference</option>
                  <option value="Anniversary">Anniversary</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>

            <div class="fg">
              <label>Expected Guests</label>
              <input type="number" name="guests_count" min="1" max="<?= $hall['capacity'] ?>" placeholder="Number of attendees" required>
              <div class="fg-hint"><i class="fas fa-circle-info" style="margin-right:4px"></i>Maximum capacity: <?= number_format($hall['capacity']) ?> guests</div>
            </div>

            <div class="fg">
              <label>Special Requirements <span style="letter-spacing:0;text-transform:none;font-size:9px;opacity:.7">(optional)</span></label>
              <textarea name="special_requirements" rows="3" placeholder="Any special requests, dietary needs, setup preferences…"></textarea>
            </div>

            <!-- Booking summary -->
            <div class="summary">
              <div class="summary-title">Booking Summary</div>
              <div class="summary-row">
                <span class="summary-row-label">Venue</span>
                <span class="summary-row-val"><?= htmlspecialchars($hall['name']) ?></span>
              </div>
              <div class="summary-row">
                <span class="summary-row-label">Hall rental (1 day)</span>
                <span class="summary-row-val"><?= format_currency($hall['price_per_day']) ?></span>
              </div>
              <div class="summary-row">
                <span class="summary-total-label">Total Amount</span>
                <span class="summary-total-val"><?= format_currency($hall['price_per_day']) ?></span>
              </div>
            </div>

            <button type="submit" name="book_hall" class="submit-btn">
              <span><i class="fas fa-calendar-check"></i> Confirm Reservation</span>
            </button>

          </form>
        </div>
      </div>

    </div><!-- /layout -->
  </div><!-- /content -->
</main>


<script>
// ── Data from PHP ──────────────────────────────────────────
const BOOKED  = new Set(<?= $booked_json  ?>);
const PENDING = new Set(<?= $pending_json ?>);
const MINE    = new Set(<?= $my_json      ?>);
const TODAY   = '<?= date('Y-m-d') ?>';

// ── State ─────────────────────────────────────────────────
const now = new Date();
let calYear  = now.getFullYear();
let calMonth = now.getMonth(); // 0-based
let selectedDate = '';

const MONTHS = ['January','February','March','April','May','June',
                'July','August','September','October','November','December'];

function pad(n){ return String(n).padStart(2,'0'); }
function fmt(y,m,d){ return y+'-'+pad(m+1)+'-'+pad(d); }

function renderCal() {
  const grid  = document.getElementById('cal-grid');
  const mLbl  = document.getElementById('cal-month-lbl');
  const yLbl  = document.getElementById('cal-year-lbl');

  mLbl.textContent = MONTHS[calMonth];
  yLbl.textContent = calYear;

  const firstDay  = new Date(calYear, calMonth, 1).getDay(); // 0=Sun
  const daysInMon = new Date(calYear, calMonth+1, 0).getDate();

  let html = '';

  // Empty cells before first day
  for (let i = 0; i < firstDay; i++) {
    html += '<div class="cal-day cal-empty"></div>';
  }

  for (let d = 1; d <= daysInMon; d++) {
    const dateStr = fmt(calYear, calMonth, d);
    let cls = 'cal-day';
    let clickable = true;

    if (dateStr < TODAY)        { cls += ' cal-past';    clickable = false; }
    else if (MINE.has(dateStr)) { cls += ' cal-mine';    clickable = false; }
    else if (BOOKED.has(dateStr)){ cls += ' cal-booked'; clickable = false; }
    else if (PENDING.has(dateStr)){cls += ' cal-pending';clickable = false; }
    if (dateStr === TODAY)       { cls += ' cal-today'; }
    if (dateStr === selectedDate){ cls += ' cal-selected'; }

    const onclick = clickable ? `onclick="pickDate('${dateStr}')"` : '';
    const title   = clickable ? `title="Available — click to select"`
                  : MINE.has(dateStr)    ? `title="Your booking"`
                  : BOOKED.has(dateStr)  ? `title="Booked"`
                  : PENDING.has(dateStr) ? `title="Pending confirmation"`
                  : `title="Past date"`;

    html += `<div class="${cls}" ${onclick} ${title}>${d}</div>`;
  }

  grid.innerHTML = html;
}

function calPrev() {
  calMonth--;
  if (calMonth < 0) { calMonth = 11; calYear--; }
  renderCal();
}
function calNext() {
  calMonth++;
  if (calMonth > 11) { calMonth = 0; calYear++; }
  renderCal();
}

function pickDate(dateStr) {
  selectedDate = dateStr;
  // Set the date input value
  const input = document.querySelector('input[name="event_date"]');
  if (input) input.value = dateStr;
  renderCal();
  // Smooth scroll to form on mobile
  if (window.innerWidth < 1100) {
    document.querySelector('.form-panel').scrollIntoView({behavior:'smooth', block:'start'});
  }
}

// Sync: if user types in date input, reflect on calendar
document.addEventListener('DOMContentLoaded', () => {
  renderCal();
  const input = document.querySelector('input[name="event_date"]');
  if (input) {
    input.addEventListener('change', function() {
      if (!this.value) return;
      const [y, m, d] = this.value.split('-').map(Number);
      calYear  = y;
      calMonth = m - 1;
      selectedDate = this.value;
      renderCal();
    });
    // Restore selected date if form was re-submitted
    if (input.value) {
      const [y, m] = input.value.split('-').map(Number);
      calYear  = y;
      calMonth = m - 1;
      selectedDate = input.value;
      renderCal();
    }
  }
});
</script>

</body>
</html>