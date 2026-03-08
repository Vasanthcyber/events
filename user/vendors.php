<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

$pending = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id={$_SESSION['user_id']} AND status='pending'")->fetch_assoc()['c'];
$cnt_all = $conn->query("SELECT COUNT(*) c FROM vendors WHERE status='active'")->fetch_assoc()['c'];

$svc_meta = [
  'catering'    => ['label'=>'Catering',    'icon'=>'fa-utensils',              'color'=>'amber'],
  'decoration'  => ['label'=>'Decoration',  'icon'=>'fa-wand-magic-sparkles',   'color'=>'rose'],
  'photography' => ['label'=>'Photography', 'icon'=>'fa-camera',                'color'=>'teal'],
  'music'       => ['label'=>'Music & DJ',  'icon'=>'fa-music',                 'color'=>'gold'],
  'other'       => ['label'=>'Other',       'icon'=>'fa-star',                  'color'=>'ivory'],
];

// Pre-fetch vendors grouped
$grouped = [];
foreach ($svc_meta as $type => $_) {
    $res = $conn->query("SELECT * FROM vendors WHERE status='active' AND service_type='$type' ORDER BY business_name");
    if ($res->num_rows > 0) $grouped[$type] = $res;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Vendors</title>
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
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);width:34px;height:34px;display:flex;align-items:center;justify-content:center;font-size:12px;transition:all .2s;text-decoration:none;position:relative}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-dot{width:5px;height:5px;border-radius:50%;background:var(--gold);position:absolute;top:6px;right:6px}
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
.ani{animation:up .44s ease both}

/* ── Section headings ─────────────────────────────────────── */
.svc-section{margin-bottom:32px}
.svc-heading{
  display:flex;align-items:center;gap:12px;
  margin-bottom:16px;padding-bottom:12px;
  border-bottom:1px solid var(--bsub)
}
.svc-heading-icon{
  width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0
}
.svc-heading-icon.amber{color:var(--amber);background:rgba(232,168,58,.1)}
.svc-heading-icon.rose {color:var(--rose); background:rgba(208,104,120,.1)}
.svc-heading-icon.teal {color:var(--teal); background:rgba(77,184,189,.1)}
.svc-heading-icon.gold {color:var(--gold); background:var(--goldp)}
.svc-heading-icon.ivory{color:var(--ivory3);background:rgba(255,255,255,.05)}
.svc-heading-title{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;color:var(--ivory)}
.svc-heading-count{font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3);margin-left:auto}

/* ── Vendor Cards ─────────────────────────────────────────── */
.vendor-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}

.vcard{
  background:var(--ink3);border:1px solid var(--bsub);
  display:flex;flex-direction:column;transition:border-color .25s;
  position:relative;overflow:hidden
}
.vcard:hover{border-color:var(--border)}
.vcard::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;opacity:0;transition:opacity .3s}
.vcard:hover::before{opacity:1}
.vcard[data-svc="catering"]::before    {background:var(--amber)}
.vcard[data-svc="decoration"]::before  {background:var(--rose)}
.vcard[data-svc="photography"]::before {background:var(--teal)}
.vcard[data-svc="music"]::before       {background:var(--gold)}
.vcard[data-svc="other"]::before       {background:var(--ivory3)}

/* Image */
.vcard-img{height:150px;overflow:hidden;background:var(--ink4);position:relative;flex-shrink:0}
.vcard-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.82);transition:filter .3s}
.vcard:hover .vcard-img img{filter:brightness(.96)}
.vcard-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem}
.vcard-placeholder.amber{color:var(--amber);background:rgba(232,168,58,.05)}
.vcard-placeholder.rose {color:var(--rose); background:rgba(208,104,120,.05)}
.vcard-placeholder.teal {color:var(--teal); background:rgba(77,184,189,.05)}
.vcard-placeholder.gold {color:var(--gold); background:var(--goldp)}
.vcard-placeholder.ivory{color:var(--ivory3);background:rgba(255,255,255,.03)}

/* Body */
.vcard-body{padding:16px 16px 14px;flex:1;display:flex;flex-direction:column}

.svc-chip{display:inline-flex;align-items:center;gap:6px;padding:3px 9px;font-size:9px;letter-spacing:.1em;text-transform:uppercase;font-weight:500;margin-bottom:9px;align-self:flex-start}
.svc-chip.amber{color:var(--amber);background:rgba(232,168,58,.1)}
.svc-chip.rose {color:var(--rose); background:rgba(208,104,120,.1)}
.svc-chip.teal {color:var(--teal); background:rgba(77,184,189,.1)}
.svc-chip.gold {color:var(--gold); background:var(--goldp)}
.svc-chip.ivory{color:var(--ivory3);background:rgba(255,255,255,.05)}

.vcard-name{font-family:'Cormorant Garamond',serif;font-size:19px;font-weight:400;color:var(--ivory);line-height:1.2;margin-bottom:7px}
.vcard-desc{font-size:11.5px;color:var(--ivory3);line-height:1.65;font-weight:300;margin-bottom:14px;flex:1}

/* Contact block */
.vcard-contact{background:var(--ink4);border:1px solid var(--bsub);padding:10px 12px;margin-bottom:14px;display:flex;flex-direction:column;gap:6px}
.crow{display:flex;align-items:center;gap:8px;font-size:11.5px}
.crow i{font-size:10px;color:var(--ivory3);width:12px;text-align:center;flex-shrink:0}
.crow a{color:var(--ivory2);text-decoration:none;transition:color .18s;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.crow a:hover{color:var(--gold)}
.crow .price{font-family:'Cormorant Garamond',serif;font-size:15px;color:var(--gold);font-weight:300}

/* Action buttons */
.vcard-actions{display:flex;gap:8px}
.abtn{flex:1;display:flex;align-items:center;justify-content:center;gap:6px;background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.08em;text-transform:uppercase;padding:8px 10px;text-decoration:none;transition:all .2s;cursor:pointer}
.abtn i{font-size:10px}
.abtn.call:hover {border-color:var(--teal);color:var(--teal)}
.abtn.email:hover{border-color:var(--gold);color:var(--gold)}

/* ── Empty state ──────────────────────────────────────────── */
.empty{padding:72px 24px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:14px;background:var(--ink3);border:1px solid var(--bsub)}
.empty-icon{width:64px;height:64px;border:1px solid var(--bsub);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--ivory3);opacity:.38}
.empty h3{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:300;color:var(--ivory2)}
.empty p{font-size:12px;color:var(--ivory3);font-weight:300}

@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.filter-bar,.content{padding-left:20px;padding-right:20px}
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
    <div class="sb-sub">My Account</div>
  </div>
  <span class="sb-section">Navigation</span>
  <a href="dashboard.php" class="sb-link"><i class="fas fa-chart-tree-map"></i> Dashboard</a>
  <a href="halls.php"     class="sb-link"><i class="fas fa-building-columns"></i> Browse Halls</a>
  <a href="vendors.php"   class="sb-link active"><i class="fas fa-store"></i> Vendors</a>
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

<!-- ── MAIN ────────────────────────────────────────────────── -->
<main class="main">

  <div class="topbar">
    <div class="tb-left">
      <h1>Event <em>Vendors</em></h1>
      <div class="tb-sub"><?= $cnt_all ?> active service provider<?= $cnt_all != 1 ? 's' : '' ?></div>
    </div>
    <div class="tb-right">
      <a href="bookings.php" class="tb-btn" title="My bookings">
        <i class="fas fa-calendar-check"></i>
        <?php if($pending > 0): ?><span class="tb-dot"></span><?php endif; ?>
      </a>
      <a href="../logout.php" class="tb-btn" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <!-- Filter bar -->
  <div class="filter-bar">
    <button class="fpill active" onclick="filterVendors('all',this)">All <span class="fpill-count"><?= $cnt_all ?></span></button>
    <?php foreach($svc_meta as $type => $m):
      $c = $conn->query("SELECT COUNT(*) c FROM vendors WHERE status='active' AND service_type='$type'")->fetch_assoc()['c'];
      if ($c < 1) continue;
    ?>
    <button class="fpill" onclick="filterVendors('<?= $type ?>',this)">
      <i class="fas <?= $m['icon'] ?>" style="font-size:9px"></i>
      <?= $m['label'] ?> <span class="fpill-count"><?= $c ?></span>
    </button>
    <?php endforeach; ?>
    <div class="search-wrap">
      <i class="fas fa-magnifying-glass"></i>
      <input type="text" placeholder="Search vendors…" oninput="searchVendors(this.value)">
    </div>
  </div>

  <div class="content">
    <?php if ($cnt_all > 0): ?>

      <?php foreach ($grouped as $type => $result):
        $m   = $svc_meta[$type];
        $cnt = $result->num_rows;
        // re-fetch (result already consumed)
        $result = $conn->query("SELECT * FROM vendors WHERE status='active' AND service_type='$type' ORDER BY business_name");
      ?>
      <div class="svc-section" data-section="<?= $type ?>">
        <div class="svc-heading ani">
          <div class="svc-heading-icon <?= $m['color'] ?>"><i class="fas <?= $m['icon'] ?>"></i></div>
          <div class="svc-heading-title"><?= $m['label'] ?></div>
          <div class="svc-heading-count"><?= $cnt ?> vendor<?= $cnt != 1 ? 's' : '' ?></div>
        </div>

        <div class="vendor-grid">
          <?php $i=0; while ($v = $result->fetch_assoc()):
            $i++;
            $color = $m['color'];
          ?>
          <div class="vcard ani"
               style="animation-delay:<?= $i * 0.06 ?>s"
               data-svc="<?= $type ?>"
               data-name="<?= strtolower(htmlspecialchars($v['business_name'])) ?>">

            <!-- Image -->
            <div class="vcard-img">
              <?php if (!empty($v['image'])): ?>
                <img src="../assets/images/<?= htmlspecialchars($v['image']) ?>" alt="<?= htmlspecialchars($v['business_name']) ?>">
              <?php else: ?>
                <div class="vcard-placeholder <?= $color ?>"><i class="fas <?= $m['icon'] ?>"></i></div>
              <?php endif; ?>
            </div>

            <div class="vcard-body">
              <span class="svc-chip <?= $color ?>">
                <i class="fas <?= $m['icon'] ?>"></i><?= $m['label'] ?>
              </span>
              <div class="vcard-name"><?= htmlspecialchars($v['business_name']) ?></div>
              <div class="vcard-desc"><?= htmlspecialchars($v['description'] ?: 'Professional service provider for your events.') ?></div>

              <div class="vcard-contact">
                <div class="crow">
                  <i class="fas fa-phone"></i>
                  <a href="tel:<?= htmlspecialchars($v['contact_number']) ?>"><?= htmlspecialchars($v['contact_number']) ?></a>
                </div>
                <div class="crow">
                  <i class="fas fa-envelope"></i>
                  <a href="mailto:<?= htmlspecialchars($v['email']) ?>"><?= htmlspecialchars($v['email']) ?></a>
                </div>
                <?php if (!empty($v['price_range'])): ?>
                <div class="crow">
                  <i class="fas fa-tag"></i>
                  <span class="price"><?= htmlspecialchars($v['price_range']) ?></span>
                </div>
                <?php endif; ?>
              </div>

              <div class="vcard-actions">
                <a href="tel:<?= htmlspecialchars($v['contact_number']) ?>" class="abtn call">
                  <i class="fas fa-phone"></i> Call
                </a>
                <a href="mailto:<?= htmlspecialchars($v['email']) ?>" class="abtn email">
                  <i class="fas fa-envelope"></i> Email
                </a>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
      <?php endforeach; ?>

    <?php else: ?>
      <div class="empty ani">
        <div class="empty-icon"><i class="fas fa-store-slash"></i></div>
        <h3>No vendors available</h3>
        <p>Check back soon — service providers are added regularly.</p>
      </div>
    <?php endif; ?>
  </div>
</main>

<script>
function filterVendors(type, btn) {
  document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');

  document.querySelectorAll('.svc-section').forEach(sec => {
    if (type === 'all' || sec.dataset.section === type) {
      sec.style.display = '';
    } else {
      sec.style.display = 'none';
    }
  });

  // within visible sections, show all cards
  document.querySelectorAll('.vcard').forEach(c => c.style.display = '');
}

function searchVendors(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));

  document.querySelectorAll('.svc-section').forEach(sec => {
    let anyVisible = false;
    sec.querySelectorAll('.vcard').forEach(c => {
      const match = !q || c.dataset.name.includes(q);
      c.style.display = match ? '' : 'none';
      if (match) anyVisible = true;
    });
    sec.style.display = anyVisible ? '' : 'none';
  });
}
</script>
</body>
</html>