<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('vendor');

$user_id = $_SESSION['user_id'];

// Get vendor information
$vendor_info = $conn->query("SELECT * FROM vendors WHERE user_id = $user_id")->fetch_assoc();
$vid = $vendor_info['id'] ?? 0;

// Get vendor bookings
$vendor_bookings = $conn->query(
    "SELECT bv.*, b.event_name, b.event_date, u.name as customer_name, u.phone as customer_phone
     FROM booking_vendors bv
     JOIN bookings b ON bv.booking_id = b.id
     JOIN users u ON b.user_id = u.id
     WHERE bv.vendor_id = $vid
     ORDER BY b.event_date DESC"
);

// Stats
$cnt_total     = $conn->query("SELECT COUNT(*) c FROM booking_vendors WHERE vendor_id=$vid")->fetch_assoc()['c'];
$cnt_confirmed = $conn->query("SELECT COUNT(*) c FROM booking_vendors WHERE vendor_id=$vid AND status='confirmed'")->fetch_assoc()['c'];
$cnt_requested = $conn->query("SELECT COUNT(*) c FROM booking_vendors WHERE vendor_id=$vid AND status='requested'")->fetch_assoc()['c'];
$cnt_declined  = $conn->query("SELECT COUNT(*) c FROM booking_vendors WHERE vendor_id=$vid AND status='declined'")->fetch_assoc()['c'];

$svc_icons  = ['catering'=>'fa-utensils','decoration'=>'fa-wand-magic-sparkles','photography'=>'fa-camera','music'=>'fa-music','other'=>'fa-star'];
$svc_labels = ['catering'=>'Catering','decoration'=>'Decoration','photography'=>'Photography','music'=>'Music / DJ','other'=>'Other'];

$hour     = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$first    = explode(' ', $_SESSION['user_name'])[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Vendor Dashboard</title>
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

/* ── Sidebar ──────────────────────────────────────────────── */
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

/* ── Main ─────────────────────────────────────────────────── */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:28px 40px 0;flex-shrink:0}
.tb-left h1{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:300;color:var(--ivory);line-height:1}
.tb-left h1 em{font-style:italic;color:var(--goldl)}
.tb-date{font-size:10px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);width:34px;height:34px;display:flex;align-items:center;justify-content:center;font-size:12px;transition:all .2s;text-decoration:none}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

.content{flex:1;overflow-y:auto;padding:28px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .44s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}
.d3{animation-delay:.15s}.d4{animation-delay:.20s}

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

/* ── Two-col layout ───────────────────────────────────────── */
.g2{display:grid;grid-template-columns:300px 1fr;gap:18px}

/* ── Profile Card ─────────────────────────────────────────── */
.profile-card{background:var(--ink3);border:1px solid var(--bsub);overflow:hidden}

.profile-banner{
  height:80px;
  background:linear-gradient(135deg,var(--ink4),var(--ink3));
  border-bottom:1px solid var(--bsub);position:relative
}
.profile-banner::after{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse at 30% 50%,var(--goldp),transparent 70%)
}

.profile-identity{
  padding:0 20px 20px;
  display:flex;flex-direction:column;align-items:center;
  text-align:center;margin-top:-28px;position:relative;z-index:1
}
.profile-avatar{
  width:56px;height:56px;border:2px solid var(--gold);
  background:var(--ink2);display:flex;align-items:center;justify-content:center;
  font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--gold);
  margin-bottom:10px
}
.profile-biz{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory);line-height:1.2;margin-bottom:5px}

/* Status badge */
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

/* Profile detail rows */
.profile-details{border-top:1px solid var(--bsub);margin-top:14px}
.prow{display:flex;align-items:center;gap:10px;padding:10px 20px;border-bottom:1px solid var(--bsub)}
.prow:last-child{border-bottom:none}
.prow i{font-size:10px;color:var(--ivory3);width:12px;text-align:center;flex-shrink:0}
.prow-label{font-size:9px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3);width:50px;flex-shrink:0}
.prow-val{font-size:12px;color:var(--ivory2);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.prow-val a{color:var(--ivory2);text-decoration:none;transition:color .18s}
.prow-val a:hover{color:var(--gold)}

/* ── Bookings Panel ───────────────────────────────────────── */
.panel{background:var(--ink3);border:1px solid var(--bsub)}
.ph{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--bsub)}
.pt{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory)}
.pt-sub{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--ivory3);margin-top:3px}

/* Filter pills inside panel */
.panel-pills{display:flex;gap:7px;padding:14px 22px;border-bottom:1px solid var(--bsub);flex-wrap:wrap}
.fpill{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:9.5px;letter-spacing:.16em;text-transform:uppercase;padding:5px 11px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px}
.fpill:hover{border-color:var(--border);color:var(--ivory2)}
.fpill.active{border-color:var(--gold);color:var(--gold);background:var(--goldp)}
.fpill-count{background:rgba(255,255,255,.07);padding:1px 5px;font-size:8.5px;font-weight:600}
.fpill.active .fpill-count{background:rgba(201,168,76,.2)}

/* Table */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid var(--bsub)}
th{padding:11px 22px;font-size:8.5px;letter-spacing:.24em;text-transform:uppercase;color:var(--ivory3);font-weight:400;text-align:left;white-space:nowrap}
td{padding:14px 22px;font-size:12.5px;color:var(--ivory2);border-bottom:1px solid var(--bsub);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr{transition:background .14s}
tbody tr:hover{background:rgba(201,168,76,.03)}
tbody tr.hidden-row{display:none}

.td-event{color:var(--ivory);font-weight:500}
.td-meta{font-size:10.5px;color:var(--ivory3);margin-top:2px}
.td-customer{color:var(--ivory)}

/* ── No-profile alert ─────────────────────────────────────── */
.alert-warn{
  background:rgba(232,168,58,.07);border:1px solid rgba(232,168,58,.25);
  color:var(--amber);padding:18px 22px;
  display:flex;align-items:center;gap:12px;font-size:13px
}
.alert-warn i{font-size:16px;flex-shrink:0}

/* Empty */
.empty{padding:48px 22px;text-align:center;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3)}
.empty i{display:block;font-size:1.6rem;opacity:.22;margin-bottom:12px}

@media(max-width:1000px){.g2{grid-template-columns:1fr}}
@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.content{padding-left:20px;padding-right:20px}
  .stat-grid{grid-template-columns:repeat(2,1fr)}
}
</style>
</head>
<body>

<!-- ── SIDEBAR ─────────────────────────────────────────────── -->
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-diamond"><i class="fas fa-gem"></i></div>
      <span class="sb-name">Eventique</span>
    </div>
    <div class="sb-sub">Vendor Portal</div>
  </div>
  <span class="sb-section">Menu</span>
  <a href="dashboard.php" class="sb-link active"><i class="fas fa-chart-tree-map"></i> Dashboard
    <?php if($cnt_requested > 0): ?><span class="sb-badge"><?= $cnt_requested ?></span><?php endif; ?>
  </a>
  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
      <div>
        <div class="sb-uname"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <div class="sb-urole">Vendor</div>
      </div>
      <a class="sb-logout" href="../logout.php" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
</aside>

<!-- ── MAIN ────────────────────────────────────────────────── -->
<main class="main">

  <div class="topbar">
    <div class="tb-left">
      <h1><?= $greeting ?>, <em><?= htmlspecialchars($first) ?></em>.</h1>
      <div class="tb-date"><?= date('l, F j, Y') ?></div>
    </div>
    <div class="tb-right">
      <a href="../logout.php" class="tb-btn" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <div class="content">

    <?php if ($vendor_info): ?>

      <!-- Stat cards -->
      <div class="stat-grid">
        <div class="sc ani d1" data-c="gold">
          <div class="sc-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="sc-val"><?= $cnt_total ?></div>
          <div class="sc-label">Total Requests</div>
        </div>
        <div class="sc ani d2" data-c="green">
          <div class="sc-icon"><i class="fas fa-circle-check"></i></div>
          <div class="sc-val"><?= $cnt_confirmed ?></div>
          <div class="sc-label">Confirmed</div>
        </div>
        <div class="sc ani d3" data-c="amber">
          <div class="sc-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="sc-val"><?= $cnt_requested ?></div>
          <div class="sc-label">Pending</div>
        </div>
        <div class="sc ani d4" data-c="rose">
          <div class="sc-icon"><i class="fas fa-ban"></i></div>
          <div class="sc-val"><?= $cnt_declined ?></div>
          <div class="sc-label">Declined</div>
        </div>
      </div>

      <!-- Profile + Bookings -->
      <div class="g2 ani d3">

        <!-- Profile card -->
        <div class="profile-card">
          <div class="profile-banner"></div>
          <div class="profile-identity">
            <div class="profile-avatar"><?= strtoupper(substr($vendor_info['business_name'],0,1)) ?></div>
            <div class="profile-biz"><?= htmlspecialchars($vendor_info['business_name']) ?></div>
            <?php
              $svc = $vendor_info['service_type'];
              $icon = $svc_icons[$svc] ?? 'fa-star';
              $svc_label = $svc_labels[$svc] ?? ucfirst($svc);
              $status_bc = $vendor_info['status'] === 'active' ? 'badge-success' : 'badge-danger';
            ?>
            <span class="badge badge-primary" style="margin-top:5px">
              <i class="fas <?= $icon ?>" style="font-size:8px"></i> <?= $svc_label ?>
            </span>
          </div>

          <div class="profile-details">
            <div class="prow">
              <i class="fas fa-circle-dot"></i>
              <span class="prow-label">Status</span>
              <span class="prow-val">
                <span class="badge <?= $status_bc ?>">
                  <span class="bdot"></span><?= ucfirst($vendor_info['status']) ?>
                </span>
              </span>
            </div>
            <div class="prow">
              <i class="fas fa-phone"></i>
              <span class="prow-label">Phone</span>
              <span class="prow-val"><a href="tel:<?= htmlspecialchars($vendor_info['contact_number']) ?>"><?= htmlspecialchars($vendor_info['contact_number']) ?></a></span>
            </div>
            <div class="prow">
              <i class="fas fa-envelope"></i>
              <span class="prow-label">Email</span>
              <span class="prow-val"><a href="mailto:<?= htmlspecialchars($vendor_info['email']) ?>"><?= htmlspecialchars($vendor_info['email']) ?></a></span>
            </div>
            <?php if (!empty($vendor_info['price_range'])): ?>
            <div class="prow">
              <i class="fas fa-tag"></i>
              <span class="prow-label">Pricing</span>
              <span class="prow-val" style="font-family:'Cormorant Garamond',serif;font-size:15px;color:var(--goldl)"><?= htmlspecialchars($vendor_info['price_range']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($vendor_info['description'])): ?>
            <div class="prow" style="align-items:flex-start">
              <i class="fas fa-align-left" style="margin-top:2px"></i>
              <span class="prow-label">About</span>
              <span class="prow-val" style="white-space:normal;font-size:11.5px;line-height:1.6"><?= htmlspecialchars($vendor_info['description']) ?></span>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Bookings panel -->
        <div class="panel">
          <div class="ph">
            <div>
              <div class="pt">Booking Requests</div>
              <div class="pt-sub"><?= $cnt_total ?> total · <?= $cnt_requested ?> pending</div>
            </div>
          </div>

          <div class="panel-pills">
            <button class="fpill active" onclick="filterRows('all',this)">All <span class="fpill-count"><?= $cnt_total ?></span></button>
            <button class="fpill" onclick="filterRows('confirmed',this)">Confirmed <span class="fpill-count"><?= $cnt_confirmed ?></span></button>
            <button class="fpill" onclick="filterRows('requested',this)">Pending <span class="fpill-count"><?= $cnt_requested ?></span></button>
            <button class="fpill" onclick="filterRows('declined',this)">Declined <span class="fpill-count"><?= $cnt_declined ?></span></button>
          </div>

          <?php if ($vendor_bookings->num_rows > 0): ?>
          <div class="tbl-wrap">
            <table id="bookings-table">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Customer</th>
                  <th>Contact</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($bk = $vendor_bookings->fetch_assoc()):
                  $bc = 'badge-primary';
                  if ($bk['status'] === 'confirmed')  $bc = 'badge-success';
                  elseif ($bk['status'] === 'requested') $bc = 'badge-warning';
                  elseif ($bk['status'] === 'declined')  $bc = 'badge-danger';
                ?>
                <tr data-status="<?= $bk['status'] ?>">
                  <td><div class="td-event"><?= htmlspecialchars($bk['event_name']) ?></div></td>
                  <td><span class="td-customer"><?= htmlspecialchars($bk['customer_name']) ?></span></td>
                  <td>
                    <?php if (!empty($bk['customer_phone'])): ?>
                      <a href="tel:<?= htmlspecialchars($bk['customer_phone']) ?>" style="color:var(--ivory3);text-decoration:none;font-size:12px;transition:color .18s" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--ivory3)'">
                        <?= htmlspecialchars($bk['customer_phone']) ?>
                      </a>
                    <?php else: ?>
                      <span style="color:var(--ivory3)">—</span>
                    <?php endif; ?>
                  </td>
                  <td><?= format_date($bk['event_date']) ?></td>
                  <td>
                    <span class="badge <?= $bc ?>">
                      <span class="bdot"></span><?= ucfirst($bk['status']) ?>
                    </span>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
            <div class="empty">
              <i class="fas fa-calendar-xmark"></i>
              No booking requests yet.
            </div>
          <?php endif; ?>
        </div>

      </div><!-- /g2 -->

    <?php else: ?>

      <!-- No profile warning -->
      <div class="alert-warn ani d1">
        <i class="fas fa-triangle-exclamation"></i>
        Your vendor profile has not been set up yet. Please contact the administrator to activate your account.
      </div>

    <?php endif; ?>

  </div><!-- /content -->
</main>

<script>
function filterRows(status, btn) {
  document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('#bookings-table tbody tr[data-status]').forEach(row => {
    row.classList.toggle('hidden-row', status !== 'all' && row.dataset.status !== status);
  });
}
</script>
</body>
</html>