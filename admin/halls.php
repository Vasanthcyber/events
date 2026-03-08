
Copy

<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

$success = '';
$error   = '';

// Handle hall addition
if (isset($_POST['add_hall'])) {
    $name        = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $capacity    = intval($_POST['capacity']);
    $location    = sanitize_input($_POST['location']);
    $price       = floatval($_POST['price_per_day']);
    $amenities   = sanitize_input($_POST['amenities']);
    $status      = sanitize_input($_POST['status'] ?? 'available');

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = upload_image($_FILES['image']);
    }

    $stmt = $conn->prepare("INSERT INTO halls (name, description, capacity, location, price_per_day, amenities, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssss", $name, $description, $capacity, $location, $price, $amenities, $image, $status);

    if ($stmt->execute()) {
        $success = 'Hall added successfully.';
    } else {
        $error = 'Failed to add hall.';
    }
}

// Handle hall deletion
if (isset($_GET['delete'])) {
    $hall_id = intval($_GET['delete']);
    $stmt    = $conn->prepare("DELETE FROM halls WHERE id = ?");
    $stmt->bind_param("i", $hall_id);
    if ($stmt->execute()) {
        $success = 'Hall deleted successfully.';
    } else {
        $error = 'Failed to delete hall.';
    }
}

// Toggle status
if (isset($_GET['toggle'])) {
    $hall_id    = intval($_GET['toggle']);
    $cur_status = $conn->query("SELECT status FROM halls WHERE id = $hall_id")->fetch_assoc()['status'];
    $new_status = $cur_status === 'available' ? 'unavailable' : 'available';
    $conn->query("UPDATE halls SET status='$new_status' WHERE id=$hall_id");
    $success = "Hall marked as $new_status.";
}

// Get all halls
$halls    = $conn->query("SELECT * FROM halls ORDER BY created_at DESC");
$cnt_all  = $conn->query("SELECT COUNT(*) c FROM halls")->fetch_assoc()['c'];
$cnt_avail= $conn->query("SELECT COUNT(*) c FROM halls WHERE status='available'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Manage Halls</title>
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
  --border:rgba(201,168,76,.15);  --bsub: rgba(255,255,255,.055);  --sw:256px;
}

html,body{height:100%;overflow:hidden}
body{font-family:'Barlow',sans-serif;background:var(--ink);color:var(--ivory);display:flex;min-height:100vh}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.032'/%3E%3C/svg%3E")}

/* ── Sidebar ─────────────────────────────────────────────── */
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
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s;text-decoration:none;padding:0 14px;gap:8px;font-family:'Barlow',sans-serif;letter-spacing:.12em;text-transform:uppercase;font-size:10px}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-btn.gold{border-color:var(--gold);color:var(--gold)}
.tb-btn.gold:hover{background:var(--goldp)}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

.content{flex:1;overflow-y:auto;padding:28px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

/* Animations */
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .42s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}.d3{animation-delay:.15s}
.d4{animation-delay:.20s}.d5{animation-delay:.25s}.d6{animation-delay:.30s}

/* Alert */
.alert{padding:13px 18px;margin-bottom:22px;font-size:12px;letter-spacing:.03em;border-left:2px solid;display:flex;align-items:center;gap:10px}
.alert-success{background:rgba(77,189,138,.08);border-color:var(--green);color:var(--green)}
.alert-danger {background:rgba(208,104,120,.08);border-color:var(--rose);color:var(--rose)}

/* ── Hall Cards Grid ───────────────────────────────────────── */
.hall-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}

.hcard{
  background:var(--ink3);border:1px solid var(--bsub);
  display:flex;flex-direction:column;
  transition:border-color .25s;position:relative;overflow:hidden
}
.hcard:hover{border-color:var(--border)}

/* Image area */
.hcard-img{
  height:180px;overflow:hidden;position:relative;
  background:var(--ink4);flex-shrink:0
}
.hcard-img img{width:100%;height:100%;object-fit:cover;display:block;filter:brightness(.85);transition:filter .3s}
.hcard:hover .hcard-img img{filter:brightness(1)}
.hcard-img-placeholder{
  width:100%;height:100%;display:flex;align-items:center;justify-content:center;
  font-size:2.5rem;color:var(--ivory3);
  background:linear-gradient(135deg,var(--ink3),var(--ink4))
}
.hcard-img-placeholder i{opacity:.35}

/* Status badge overlay on image */
.hcard-status{
  position:absolute;top:14px;right:14px;
  display:inline-flex;align-items:center;gap:5px;
  padding:4px 10px;font-size:9px;letter-spacing:.1em;text-transform:uppercase;font-weight:500;
  backdrop-filter:blur(8px)
}
.hcard-status.available  {color:var(--green);background:rgba(0,0,0,.55);border:1px solid rgba(77,189,138,.35)}
.hcard-status.unavailable{color:var(--rose); background:rgba(0,0,0,.55);border:1px solid rgba(208,104,120,.35)}
.hcard-status .sdot{width:4px;height:4px;border-radius:50%}
.available  .sdot{background:var(--green)}
.unavailable .sdot{background:var(--rose)}

/* Body */
.hcard-body{padding:20px 20px 16px;flex:1;display:flex;flex-direction:column;gap:0}
.hcard-name{font-family:'Cormorant Garamond',serif;font-size:21px;font-weight:400;color:var(--ivory);line-height:1.15;margin-bottom:6px}
.hcard-desc{font-size:12px;color:var(--ivory3);line-height:1.6;font-weight:300;margin-bottom:16px}

/* Stat row */
.hcard-stats{display:flex;gap:0;margin-bottom:16px;border:1px solid var(--bsub)}
.hstat{flex:1;padding:10px 12px;text-align:center;border-right:1px solid var(--bsub)}
.hstat:last-child{border-right:none}
.hstat-val{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:300;color:var(--ivory);line-height:1}
.hstat-lbl{font-size:8.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--ivory3);margin-top:3px}

/* Amenity tags */
.hcard-amenities{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:16px}
.atag{
  font-size:9.5px;padding:3px 8px;
  background:rgba(255,255,255,.05);
  color:var(--ivory3);letter-spacing:.06em
}

/* Actions */
.hcard-actions{
  display:flex;gap:8px;padding-top:14px;
  border-top:1px solid var(--bsub);margin-top:auto
}
.abtn{
  background:none;border:1px solid var(--bsub);
  color:var(--ivory3);font-family:'Barlow',sans-serif;
  font-size:10px;letter-spacing:.07em;text-transform:uppercase;
  padding:7px 12px;cursor:pointer;transition:all .2s;
  text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap
}
.abtn i{font-size:9px}
.abtn:hover           {border-color:var(--gold);color:var(--gold)}
.abtn.toggle:hover    {border-color:var(--teal);color:var(--teal)}
.abtn.del:hover       {border-color:var(--rose);color:var(--rose)}
.abtn.stretch         {flex:1;justify-content:center}

/* Empty state */
.empty-state{
  grid-column:1/-1;padding:72px 24px;text-align:center;
  border:1px solid var(--bsub);background:var(--ink3)
}
.empty-state i{font-size:2rem;color:var(--ivory3);opacity:.35;margin-bottom:16px;display:block}
.empty-state p{font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--ivory3)}

/* ── Modal ─────────────────────────────────────────────────── */
.modal{
  display:none;position:fixed;inset:0;z-index:1000;
  background:rgba(0,0,0,.75);backdrop-filter:blur(4px);
  align-items:center;justify-content:center
}
.modal.open{display:flex}

.modal-box{
  background:var(--ink2);border:1px solid var(--border);
  width:100%;max-width:520px;max-height:90vh;
  display:flex;flex-direction:column;
  animation:up .3s ease both
}

.modal-hdr{
  display:flex;align-items:center;justify-content:space-between;
  padding:22px 28px;border-bottom:1px solid var(--bsub);flex-shrink:0
}
.modal-title{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:300;color:var(--ivory)}
.modal-close{
  background:none;border:none;color:var(--ivory3);font-size:18px;
  cursor:pointer;transition:color .2s;line-height:1
}
.modal-close:hover{color:var(--rose)}

.modal-body{padding:28px;overflow-y:auto;flex:1}
.modal-body::-webkit-scrollbar{width:3px}
.modal-body::-webkit-scrollbar-thumb{background:var(--border)}

/* Form elements */
.fg{margin-bottom:20px}
.fg label{display:block;font-size:9px;letter-spacing:.25em;text-transform:uppercase;color:var(--ivory3);margin-bottom:9px;font-weight:400}
.fg input,.fg textarea,.fg select{
  width:100%;background:var(--ink3);border:none;border-bottom:1px solid var(--bsub);
  color:var(--ivory);font-family:'Barlow',sans-serif;font-size:14px;font-weight:300;
  padding:9px 0;outline:none;transition:border-color .2s;resize:none
}
.fg input:focus,.fg textarea:focus,.fg select:focus{border-bottom-color:var(--gold)}
.fg input::placeholder,.fg textarea::placeholder{color:rgba(110,101,96,.5)}
.fg select option{background:var(--ink3)}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}

/* File input styling */
.file-label{
  display:flex;align-items:center;gap:10px;
  border:1px dashed var(--bsub);padding:14px 16px;
  cursor:pointer;transition:border-color .2s;color:var(--ivory3);font-size:12px
}
.file-label:hover{border-color:var(--gold);color:var(--ivory2)}
.file-label i{font-size:16px;color:var(--gold)}
input[type=file]{display:none}

/* Submit */
.btn-submit{
  width:100%;background:none;border:1px solid var(--gold);color:var(--gold);
  font-family:'Barlow',sans-serif;font-size:10.5px;letter-spacing:.28em;
  text-transform:uppercase;padding:14px;cursor:pointer;transition:all .3s;
  position:relative;overflow:hidden;margin-top:8px
}
.btn-submit::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.btn-submit:hover::before{transform:scaleX(1)}
.btn-submit:hover{color:var(--ink)}
.btn-submit span{position:relative;z-index:1}

/* Responsive */
@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.content{padding-left:20px;padding-right:20px}
  .hall-grid{grid-template-columns:1fr}
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
  <a href="halls.php"   class="sb-link active"><i class="fas fa-building-columns"></i> Halls
    <span class="sb-badge"><?= $cnt_all ?></span>
  </a>
  <a href="vendors.php" class="sb-link"><i class="fas fa-store"></i> Vendors</a>
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

<!-- ── MAIN ─────────────────────────────────────────────────── -->
<main class="main">

  <div class="topbar">
    <div class="tb-left">
      <h1>Event <em>Halls</em></h1>
      <div class="tb-sub"><?= $cnt_all ?> total &nbsp;·&nbsp; <?= $cnt_avail ?> available</div>
    </div>
    <div class="tb-right">
      <button class="tb-btn gold" onclick="openModal()">
        <i class="fas fa-plus" style="font-size:10px"></i> Add New Hall
      </button>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <div class="content">

    <?php if ($success): ?>
      <div class="alert alert-success ani d1"><i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger ani d1"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="hall-grid">
      <?php if ($halls->num_rows > 0):
        $i = 0;
        while ($hall = $halls->fetch_assoc()):
          $i++;
          $amenity_list = array_filter(array_map('trim', explode(',', $hall['amenities'])));
          $status_class = $hall['status'] === 'available' ? 'available' : 'unavailable';
      ?>
      <div class="hcard ani" style="animation-delay:<?= $i * 0.06 ?>s">

        <!-- Image -->
        <div class="hcard-img">
          <?php if (!empty($hall['image'])): ?>
            <img src="../assets/images/<?= htmlspecialchars($hall['image']) ?>" alt="<?= htmlspecialchars($hall['name']) ?>">
          <?php else: ?>
            <div class="hcard-img-placeholder"><i class="fas fa-building-columns"></i></div>
          <?php endif; ?>
          <span class="hcard-status <?= $status_class ?>">
            <span class="sdot"></span><?= ucfirst($hall['status']) ?>
          </span>
        </div>

        <!-- Body -->
        <div class="hcard-body">
          <div class="hcard-name"><?= htmlspecialchars($hall['name']) ?></div>
          <div class="hcard-desc"><?= htmlspecialchars(substr($hall['description'],0,90)) ?>…</div>

          <!-- Stats -->
          <div class="hcard-stats">
            <div class="hstat">
              <div class="hstat-val"><?= number_format($hall['capacity']) ?></div>
              <div class="hstat-lbl">Guests</div>
            </div>
            <div class="hstat">
              <div class="hstat-val"><?= format_currency($hall['price_per_day']) ?></div>
              <div class="hstat-lbl">Per Day</div>
            </div>
          </div>

          <!-- Location -->
          <div style="display:flex;align-items:center;gap:7px;font-size:11.5px;color:var(--ivory3);margin-bottom:14px">
            <i class="fas fa-location-dot" style="color:var(--gold);font-size:10px"></i>
            <?= htmlspecialchars($hall['location']) ?>
          </div>

          <!-- Amenities -->
          <?php if (!empty($amenity_list)): ?>
          <div class="hcard-amenities">
            <?php foreach(array_slice($amenity_list,0,5) as $a): ?>
              <span class="atag"><?= htmlspecialchars($a) ?></span>
            <?php endforeach; ?>
            <?php if(count($amenity_list) > 5): ?>
              <span class="atag">+<?= count($amenity_list)-5 ?> more</span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Actions -->
          <div class="hcard-actions">
            <a href="?toggle=<?= $hall['id'] ?>" class="abtn toggle stretch">
              <i class="fas fa-<?= $hall['status']==='available'?'ban':'check' ?>"></i>
              <?= $hall['status']==='available'?'Set Unavailable':'Set Available' ?>
            </a>
            <a href="?delete=<?= $hall['id'] ?>" class="abtn del"
               onclick="return confirm('Delete this hall permanently?')">
              <i class="fas fa-trash"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endwhile; else: ?>
        <div class="empty-state">
          <i class="fas fa-building-columns"></i>
          <p>No halls added yet. Click "Add New Hall" to get started.</p>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /content -->
</main>

<!-- ── ADD HALL MODAL ────────────────────────────────────────── -->
<div class="modal" id="addHallModal">
  <div class="modal-box">
    <div class="modal-hdr">
      <div class="modal-title">Add New Hall</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">

        <div class="fg">
          <label>Hall Name</label>
          <input type="text" name="name" placeholder="e.g. Grand Ballroom" required>
        </div>

        <div class="fg">
          <label>Description</label>
          <textarea name="description" rows="3" placeholder="Describe the hall…" required></textarea>
        </div>

        <div class="fg-row">
          <div class="fg">
            <label>Capacity (Guests)</label>
            <input type="number" name="capacity" min="1" placeholder="500" required>
          </div>
          <div class="fg">
            <label>Price Per Day ($)</label>
            <input type="number" name="price_per_day" step="0.01" min="0" placeholder="5000" required>
          </div>
        </div>

        <div class="fg">
          <label>Location</label>
          <input type="text" name="location" placeholder="123 Main Street, City" required>
        </div>

        <div class="fg">
          <label>Amenities <span style="letter-spacing:0;text-transform:none;font-size:9px">(comma separated)</span></label>
          <input type="text" name="amenities" placeholder="AC, Parking, Stage, Sound System">
        </div>

        <div class="fg">
          <label>Initial Status</label>
          <select name="status">
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
          </select>
        </div>

        <div class="fg">
          <label>Hall Image</label>
          <label class="file-label" for="hall_image">
            <i class="fas fa-cloud-arrow-up"></i>
            <span id="file-name-display">Click to upload an image…</span>
          </label>
          <input type="file" id="hall_image" name="image" accept="image/*"
                 onchange="document.getElementById('file-name-display').textContent=this.files[0]?.name||'Click to upload an image…'">
        </div>

        <button type="submit" name="add_hall" class="btn-submit">
          <span><i class="fas fa-plus" style="margin-right:8px;font-size:10px"></i>Add Hall</span>
        </button>

      </form>
    </div>
  </div>
</div>

<script>
function openModal()  { document.getElementById('addHallModal').classList.add('open') }
function closeModal() { document.getElementById('addHallModal').classList.remove('open') }
window.addEventListener('click', e => {
  const m = document.getElementById('addHallModal');
  if (e.target === m) closeModal();
});
</script>
</body>
</html>