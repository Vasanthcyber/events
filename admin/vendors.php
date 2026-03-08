<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

$success = '';
$error   = '';

// Handle vendor status update
if (isset($_POST['update_status'])) {
    $vendor_id = intval($_POST['vendor_id']);
    $status    = sanitize_input($_POST['status']);
    $stmt = $conn->prepare("UPDATE vendors SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $vendor_id);
    if ($stmt->execute()) { $success = 'Vendor status updated successfully.'; }
    else                  { $error   = 'Failed to update vendor status.'; }
}

// Handle vendor deletion
if (isset($_GET['delete'])) {
    $vendor_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $vendor_id);
    if ($stmt->execute()) { $success = 'Vendor deleted successfully.'; }
    else                  { $error   = 'Failed to delete vendor.'; }
}

// Handle new vendor addition
if (isset($_POST['add_vendor'])) {
    $business_name  = sanitize_input($_POST['business_name']);
    $service_type   = sanitize_input($_POST['service_type']);
    $description    = sanitize_input($_POST['description']);
    $contact_number = sanitize_input($_POST['contact_number']);
    $email          = sanitize_input($_POST['email']);
    $price_range    = sanitize_input($_POST['price_range']);
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = upload_image($_FILES['image']);
    }
    $stmt = $conn->prepare("INSERT INTO vendors (business_name, service_type, description, contact_number, email, price_range, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $business_name, $service_type, $description, $contact_number, $email, $price_range, $image);
    if ($stmt->execute()) { $success = 'Vendor added successfully.'; }
    else                  { $error   = 'Failed to add vendor.'; }
}

// Get all vendors
$vendors    = $conn->query("SELECT v.*, u.name as user_name FROM vendors v LEFT JOIN users u ON v.user_id = u.id ORDER BY v.created_at DESC");
$cnt_all    = $conn->query("SELECT COUNT(*) c FROM vendors")->fetch_assoc()['c'];
$cnt_active = $conn->query("SELECT COUNT(*) c FROM vendors WHERE status='active'")->fetch_assoc()['c'];

$svc_icons  = ['catering'=>'fa-utensils','decoration'=>'fa-wand-magic-sparkles','photography'=>'fa-camera','music'=>'fa-music','other'=>'fa-star'];
$svc_colors = ['catering'=>'amber','decoration'=>'rose','photography'=>'teal','music'=>'gold','other'=>'ivory'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Manage Vendors</title>
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
.tb-sub{font-size:10px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;text-decoration:none;padding:0 14px;gap:8px;font-family:'Barlow',sans-serif;letter-spacing:.12em;text-transform:uppercase;font-size:10px}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-btn.gold{border-color:var(--gold);color:var(--gold)}
.tb-btn.gold:hover{background:var(--goldp)}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

/* Filter bar */
.filter-bar{display:flex;align-items:center;gap:8px;padding:20px 40px 0;flex-shrink:0;flex-wrap:wrap}
.fpill{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.16em;text-transform:uppercase;padding:7px 13px;cursor:pointer;transition:all .22s;display:flex;align-items:center;gap:6px}
.fpill:hover{border-color:var(--border);color:var(--ivory2)}
.fpill.active{border-color:var(--gold);color:var(--gold);background:var(--goldp)}
.fpill-count{background:rgba(255,255,255,.07);padding:1px 6px;font-size:9px;font-weight:600}
.fpill.active .fpill-count{background:rgba(201,168,76,.2)}
.search-wrap{margin-left:auto;display:flex;align-items:center;gap:10px;background:var(--ink3);border:1px solid var(--bsub);padding:0 14px;height:34px;min-width:210px}
.search-wrap i{color:var(--ivory3);font-size:12px;flex-shrink:0}
.search-wrap input{background:none;border:none;color:var(--ivory);font-family:'Barlow',sans-serif;font-size:12px;outline:none;flex:1}
.search-wrap input::placeholder{color:var(--ivory3)}

.content{flex:1;overflow-y:auto;padding:20px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .42s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}

.alert{padding:13px 18px;margin-bottom:20px;font-size:12px;border-left:2px solid;display:flex;align-items:center;gap:9px}
.alert-success{background:rgba(77,189,138,.08);border-color:var(--green);color:var(--green)}
.alert-danger {background:rgba(208,104,120,.08);border-color:var(--rose);color:var(--rose)}

/* ── Vendor Cards ─────────────────────────────────────────── */
.vendor-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px}

.vcard{background:var(--ink3);border:1px solid var(--bsub);display:flex;flex-direction:column;transition:border-color .25s;position:relative;overflow:hidden}
.vcard:hover{border-color:var(--border)}
.vcard.inactive{opacity:.58}
.vcard::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;opacity:0;transition:opacity .3s}
.vcard:hover::before{opacity:1}
.vcard[data-svc="catering"]::before   {background:var(--amber)}
.vcard[data-svc="decoration"]::before {background:var(--rose)}
.vcard[data-svc="photography"]::before{background:var(--teal)}
.vcard[data-svc="music"]::before      {background:var(--gold)}
.vcard[data-svc="other"]::before      {background:var(--ivory3)}

.vcard-img{height:156px;overflow:hidden;background:var(--ink4);position:relative;flex-shrink:0}
.vcard-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.82);transition:filter .3s}
.vcard:hover .vcard-img img{filter:brightness(1)}
.vcard-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem}
.vcard-placeholder.amber{color:var(--amber);background:rgba(232,168,58,.06)}
.vcard-placeholder.rose {color:var(--rose); background:rgba(208,104,120,.06)}
.vcard-placeholder.teal {color:var(--teal); background:rgba(77,184,189,.06)}
.vcard-placeholder.gold {color:var(--gold); background:var(--goldp)}
.vcard-placeholder.ivory{color:var(--ivory3);background:rgba(255,255,255,.03)}

.vcard-overlay{position:absolute;top:12px;right:12px;display:inline-flex;align-items:center;gap:5px;padding:3px 9px;font-size:9px;letter-spacing:.1em;text-transform:uppercase;font-weight:500;backdrop-filter:blur(8px)}
.vcard-overlay.active  {color:var(--green);background:rgba(0,0,0,.55);border:1px solid rgba(77,189,138,.3)}
.vcard-overlay.inactive{color:var(--rose); background:rgba(0,0,0,.55);border:1px solid rgba(208,104,120,.3)}
.vcard-overlay .sdot{width:4px;height:4px;border-radius:50%}
.vcard-overlay.active   .sdot{background:var(--green)}
.vcard-overlay.inactive .sdot{background:var(--rose)}

.vcard-body{padding:18px 18px 14px;flex:1;display:flex;flex-direction:column}

.svc-chip{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;font-size:9px;letter-spacing:.1em;text-transform:uppercase;font-weight:500;margin-bottom:10px;align-self:flex-start}
.svc-chip.amber{color:var(--amber);background:rgba(232,168,58,.1)}
.svc-chip.rose {color:var(--rose); background:rgba(208,104,120,.1)}
.svc-chip.teal {color:var(--teal); background:rgba(77,184,189,.1)}
.svc-chip.gold {color:var(--gold); background:var(--goldp)}
.svc-chip.ivory{color:var(--ivory3);background:rgba(255,255,255,.05)}

.vcard-name{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;color:var(--ivory);line-height:1.2;margin-bottom:8px}
.vcard-desc{font-size:11.5px;color:var(--ivory3);line-height:1.6;font-weight:300;margin-bottom:14px;flex:1}

.vcard-meta{display:flex;flex-direction:column;gap:6px;margin-bottom:16px;padding:11px 13px;background:var(--ink4);border:1px solid var(--bsub)}
.vmeta-row{display:flex;align-items:center;gap:8px;font-size:11.5px;color:var(--ivory2)}
.vmeta-row i{font-size:10px;color:var(--ivory3);width:12px;text-align:center;flex-shrink:0}
.vmeta-lbl{color:var(--ivory3);font-size:9.5px;letter-spacing:.1em;text-transform:uppercase;width:48px;flex-shrink:0}
.price-val{font-family:'Cormorant Garamond',serif;font-size:15px;font-weight:300;color:var(--gold)}

.vcard-actions{display:flex;gap:8px;padding-top:14px;border-top:1px solid var(--bsub)}
.abtn{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.07em;text-transform:uppercase;padding:7px 12px;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.abtn i{font-size:9px}
.abtn:hover           {border-color:var(--gold); color:var(--gold)}
.abtn.activate:hover  {border-color:var(--green);color:var(--green)}
.abtn.deactivate:hover{border-color:var(--amber);color:var(--amber)}
.abtn.del:hover       {border-color:var(--rose); color:var(--rose)}
.abtn.stretch         {flex:1;justify-content:center}

.empty-state{grid-column:1/-1;padding:64px 24px;text-align:center;border:1px solid var(--bsub);background:var(--ink3)}
.empty-state i{font-size:2rem;color:var(--ivory3);opacity:.28;margin-bottom:14px;display:block}
.empty-state p{font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--ivory3)}

/* ── Modal ────────────────────────────────────────────────── */
.modal{display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.78);backdrop-filter:blur(4px);align-items:center;justify-content:center}
.modal.open{display:flex}
.modal-box{background:var(--ink2);border:1px solid var(--border);width:100%;max-width:500px;max-height:90vh;display:flex;flex-direction:column;animation:up .3s ease both}
.modal-hdr{display:flex;align-items:center;justify-content:space-between;padding:22px 28px;border-bottom:1px solid var(--bsub);flex-shrink:0}
.modal-title{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:300;color:var(--ivory)}
.modal-close{background:none;border:none;color:var(--ivory3);font-size:18px;cursor:pointer;transition:color .2s;line-height:1}
.modal-close:hover{color:var(--rose)}
.modal-body{padding:28px;overflow-y:auto;flex:1}
.modal-body::-webkit-scrollbar{width:3px}
.modal-body::-webkit-scrollbar-thumb{background:var(--border)}
.fg{margin-bottom:20px}
.fg label{display:block;font-size:9px;letter-spacing:.25em;text-transform:uppercase;color:var(--ivory3);margin-bottom:9px;font-weight:400}
.fg input,.fg textarea,.fg select{width:100%;background:var(--ink3);border:none;border-bottom:1px solid var(--bsub);color:var(--ivory);font-family:'Barlow',sans-serif;font-size:14px;font-weight:300;padding:9px 0;outline:none;transition:border-color .2s;resize:none}
.fg input:focus,.fg textarea:focus,.fg select:focus{border-bottom-color:var(--gold)}
.fg input::placeholder,.fg textarea::placeholder{color:rgba(110,101,96,.45)}
.fg select option{background:var(--ink3)}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.file-label{display:flex;align-items:center;gap:10px;border:1px dashed var(--bsub);padding:13px 16px;cursor:pointer;transition:border-color .2s;color:var(--ivory3);font-size:12px}
.file-label:hover{border-color:var(--gold);color:var(--ivory2)}
.file-label i{font-size:15px;color:var(--gold)}
input[type=file]{display:none}
.btn-submit{width:100%;background:none;border:1px solid var(--gold);color:var(--gold);font-family:'Barlow',sans-serif;font-size:10.5px;letter-spacing:.28em;text-transform:uppercase;padding:14px;cursor:pointer;transition:all .3s;position:relative;overflow:hidden;margin-top:8px}
.btn-submit::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.btn-submit:hover::before{transform:scaleX(1)}
.btn-submit:hover{color:var(--ink)}
.btn-submit span{position:relative;z-index:1}

@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.filter-bar,.content{padding-left:20px;padding-right:20px}
  .vendor-grid{grid-template-columns:1fr}
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
    <div class="sb-sub">Admin Console</div>
  </div>
  <span class="sb-section">Overview</span>
  <a href="dashboard.php" class="sb-link"><i class="fas fa-chart-tree-map"></i> Dashboard</a>
  <a href="bookings.php"  class="sb-link"><i class="fas fa-calendar-check"></i> Bookings</a>
  <span class="sb-section">Manage</span>
  <a href="halls.php"   class="sb-link"><i class="fas fa-building-columns"></i> Halls</a>
  <a href="vendors.php" class="sb-link active">
    <i class="fas fa-store"></i> Vendors
    <span class="sb-badge"><?= $cnt_all ?></span>
  </a>
  <a href="users.php"   class="sb-link"><i class="fas fa-users"></i> Users</a>
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

<!-- ── MAIN ────────────────────────────────────────────────── -->
<main class="main">
  <div class="topbar">
    <div class="tb-left">
      <h1>Service <em>Vendors</em></h1>
      <div class="tb-sub"><?= $cnt_all ?> total &nbsp;·&nbsp; <?= $cnt_active ?> active</div>
    </div>
    <div class="tb-right">
      <button class="tb-btn gold" onclick="openModal()">
        <i class="fas fa-plus" style="font-size:10px"></i> Add Vendor
      </button>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <!-- Filter bar -->
  <div class="filter-bar">
    <button class="fpill active" onclick="filterCards('all',this)">All <span class="fpill-count"><?= $cnt_all ?></span></button>
    <button class="fpill" onclick="filterCards('active',this)">Active <span class="fpill-count"><?= $cnt_active ?></span></button>
    <button class="fpill" onclick="filterCards('inactive',this)">Inactive <span class="fpill-count"><?= $cnt_all - $cnt_active ?></span></button>
    <?php
    $svc_res = $conn->query("SELECT service_type, COUNT(*) c FROM vendors GROUP BY service_type");
    while ($r = $svc_res->fetch_assoc()):
      $s = $r['service_type'];
    ?>
    <button class="fpill" onclick="filterCards('svc:<?= $s ?>',this)">
      <i class="fas <?= $svc_icons[$s] ?? 'fa-star' ?>" style="font-size:9px"></i>
      <?= ucfirst($s) ?> <span class="fpill-count"><?= $r['c'] ?></span>
    </button>
    <?php endwhile; ?>
    <div class="search-wrap">
      <i class="fas fa-magnifying-glass"></i>
      <input type="text" placeholder="Search vendors…" oninput="searchCards(this.value)">
    </div>
  </div>

  <div class="content">
    <?php if ($success): ?>
      <div class="alert alert-success ani d1"><i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger ani d1"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="vendor-grid" id="vendor-grid">
      <?php if ($vendors->num_rows > 0):
        $i = 0;
        while ($v = $vendors->fetch_assoc()):
          $i++;
          $svc      = $v['service_type'];
          $icon     = $svc_icons[$svc]  ?? 'fa-star';
          $color    = $svc_colors[$svc] ?? 'ivory';
          $is_active= $v['status'] === 'active';
      ?>
      <div class="vcard ani <?= !$is_active ? 'inactive' : '' ?>"
           style="animation-delay:<?= $i * 0.055 ?>s"
           data-status="<?= $v['status'] ?>"
           data-svc="<?= $svc ?>"
           data-name="<?= strtolower(htmlspecialchars($v['business_name'])) ?>">

        <div class="vcard-img">
          <?php if (!empty($v['image'])): ?>
            <img src="../assets/images/<?= htmlspecialchars($v['image']) ?>" alt="<?= htmlspecialchars($v['business_name']) ?>">
          <?php else: ?>
            <div class="vcard-placeholder <?= $color ?>"><i class="fas <?= $icon ?>"></i></div>
          <?php endif; ?>
          <span class="vcard-overlay <?= $v['status'] ?>">
            <span class="sdot"></span><?= ucfirst($v['status']) ?>
          </span>
        </div>

        <div class="vcard-body">
          <span class="svc-chip <?= $color ?>">
            <i class="fas <?= $icon ?>"></i><?= ucfirst($svc) ?>
          </span>
          <div class="vcard-name"><?= htmlspecialchars($v['business_name']) ?></div>
          <?php if (!empty($v['description'])): ?>
            <div class="vcard-desc"><?= htmlspecialchars(substr($v['description'],0,100)) ?>…</div>
          <?php endif; ?>

          <div class="vcard-meta">
            <div class="vmeta-row"><i class="fas fa-phone"></i><span class="vmeta-lbl">Phone</span><?= htmlspecialchars($v['contact_number']) ?></div>
            <div class="vmeta-row"><i class="fas fa-envelope"></i><span class="vmeta-lbl">Email</span><?= htmlspecialchars($v['email']) ?></div>
            <?php if (!empty($v['price_range'])): ?>
            <div class="vmeta-row"><i class="fas fa-tag"></i><span class="vmeta-lbl">Price</span><span class="price-val"><?= htmlspecialchars($v['price_range']) ?></span></div>
            <?php endif; ?>
          </div>

          <div class="vcard-actions">
            <form method="POST" style="flex:1;display:flex">
              <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
              <input type="hidden" name="status"    value="<?= $is_active ? 'inactive' : 'active' ?>">
              <button type="submit" name="update_status"
                      class="abtn stretch <?= $is_active ? 'deactivate' : 'activate' ?>">
                <i class="fas fa-<?= $is_active ? 'ban' : 'check' ?>"></i>
                <?= $is_active ? 'Deactivate' : 'Activate' ?>
              </button>
            </form>
            <a href="?delete=<?= $v['id'] ?>" class="abtn del"
               onclick="return confirm('Delete <?= htmlspecialchars($v['business_name']) ?> permanently?')">
              <i class="fas fa-trash"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endwhile; else: ?>
        <div class="empty-state">
          <i class="fas fa-store"></i>
          <p>No vendors yet. Click "Add Vendor" to get started.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- ── ADD VENDOR MODAL ────────────────────────────────────── -->
<div class="modal" id="addVendorModal">
  <div class="modal-box">
    <div class="modal-hdr">
      <div class="modal-title">Add New Vendor</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="fg-row">
          <div class="fg">
            <label>Business Name</label>
            <input type="text" name="business_name" placeholder="e.g. Le Bon Traiteur" required>
          </div>
          <div class="fg">
            <label>Service Type</label>
            <select name="service_type" required>
              <option value="catering">Catering</option>
              <option value="decoration">Decoration</option>
              <option value="photography">Photography</option>
              <option value="music">Music / DJ</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="fg">
          <label>Description</label>
          <textarea name="description" rows="3" placeholder="Brief description of services…"></textarea>
        </div>
        <div class="fg-row">
          <div class="fg">
            <label>Contact Number</label>
            <input type="tel" name="contact_number" placeholder="+1 000 000 0000" required>
          </div>
          <div class="fg">
            <label>Email</label>
            <input type="email" name="email" placeholder="vendor@example.com" required>
          </div>
        </div>
        <div class="fg">
          <label>Price Range</label>
          <input type="text" name="price_range" placeholder="e.g. $500 – $2,000">
        </div>
        <div class="fg">
          <label>Vendor Image</label>
          <label class="file-label" for="vendor_image">
            <i class="fas fa-cloud-arrow-up"></i>
            <span id="vfile-name">Click to upload an image…</span>
          </label>
          <input type="file" id="vendor_image" name="image" accept="image/*"
                 onchange="document.getElementById('vfile-name').textContent=this.files[0]?.name||'Click to upload an image…'">
        </div>
        <button type="submit" name="add_vendor" class="btn-submit">
          <span><i class="fas fa-plus" style="margin-right:8px;font-size:10px"></i>Add Vendor</span>
        </button>
      </form>
    </div>
  </div>
</div>

<script>
function openModal()  { document.getElementById('addVendorModal').classList.add('open') }
function closeModal() { document.getElementById('addVendorModal').classList.remove('open') }
window.addEventListener('click', e => { if (e.target===document.getElementById('addVendorModal')) closeModal() });

function filterCards(filter, btn) {
  document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('#vendor-grid .vcard').forEach(card => {
    let show = true;
    if      (filter === 'active')       show = card.dataset.status === 'active';
    else if (filter === 'inactive')     show = card.dataset.status === 'inactive';
    else if (filter.startsWith('svc:')) show = card.dataset.svc === filter.slice(4);
    card.style.display = show ? '' : 'none';
  });
}

function searchCards(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('#vendor-grid .vcard').forEach(card => {
    card.style.display = (!q || card.dataset.name.includes(q)) ? '' : 'none';
  });
  if (q) document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
}
</script>
</body>
</html>