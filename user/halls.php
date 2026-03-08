<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

// Get all available halls
$halls     = $conn->query("SELECT * FROM halls WHERE status = 'available' ORDER BY created_at DESC");
$cnt_halls = $conn->query("SELECT COUNT(*) c FROM halls WHERE status='available'")->fetch_assoc()['c'];

// Pending bookings for badge
$pending = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id={$_SESSION['user_id']} AND status='pending'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Browse Halls</title>
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
.tb-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s;text-decoration:none;position:relative}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-dot{width:5px;height:5px;border-radius:50%;background:var(--gold);position:absolute;top:6px;right:6px}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

/* Filter bar */
.filter-bar{display:flex;align-items:center;gap:8px;padding:20px 40px 0;flex-shrink:0}
.search-wrap{display:flex;align-items:center;gap:10px;background:var(--ink3);border:1px solid var(--bsub);padding:0 14px;height:34px;flex:1;max-width:280px}
.search-wrap i{color:var(--ivory3);font-size:12px;flex-shrink:0}
.search-wrap input{background:none;border:none;color:var(--ivory);font-family:'Barlow',sans-serif;font-size:12px;outline:none;flex:1}
.search-wrap input::placeholder{color:var(--ivory3)}
.sort-select{background:var(--ink3);border:1px solid var(--bsub);color:var(--ivory2);font-family:'Barlow',sans-serif;font-size:11px;padding:7px 12px;outline:none;cursor:pointer;height:34px;letter-spacing:.06em}
.sort-select:focus{border-color:var(--gold)}
.sort-select option{background:var(--ink3)}
.filter-count{margin-left:auto;font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3)}
.filter-count span{color:var(--ivory);font-weight:500}

.content{flex:1;overflow-y:auto;padding:20px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

/* Animations */
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .44s ease both}

/* ── Hall Cards ────────────────────────────────────────────── */
.hall-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:18px}

.hcard{background:var(--ink3);border:1px solid var(--bsub);display:flex;flex-direction:column;transition:border-color .25s;position:relative;overflow:hidden}
.hcard:hover{border-color:var(--border)}

/* Hover gold top strip */
.hcard::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--gold),var(--goldl));opacity:0;transition:opacity .3s;z-index:1}
.hcard:hover::before{opacity:1}

/* Image */
.hcard-img{height:200px;overflow:hidden;position:relative;background:var(--ink4);flex-shrink:0}
.hcard-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.8);transition:filter .35s,transform .35s}
.hcard:hover .hcard-img img{filter:brightness(.95);transform:scale(1.03)}
.hcard-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--ink3),var(--ink4))}
.hcard-placeholder i{font-size:2.8rem;color:var(--ivory3);opacity:.3}

/* Price overlay on image */
.price-overlay{
  position:absolute;bottom:14px;right:14px;
  background:rgba(0,0,0,.72);backdrop-filter:blur(8px);
  border:1px solid var(--border);
  padding:6px 12px;
  font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:300;
  color:var(--goldl);line-height:1
}
.price-overlay span{display:block;font-family:'Barlow',sans-serif;font-size:8px;letter-spacing:.2em;text-transform:uppercase;color:var(--ivory3);margin-top:2px;text-align:right}

/* Body */
.hcard-body{padding:20px 20px 16px;flex:1;display:flex;flex-direction:column}
.hcard-name{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:400;color:var(--ivory);line-height:1.15;margin-bottom:7px}
.hcard-desc{font-size:12px;color:var(--ivory3);line-height:1.65;font-weight:300;margin-bottom:16px;flex:1}

/* Stats row */
.hcard-stats{display:flex;border:1px solid var(--bsub);margin-bottom:14px}
.hstat{flex:1;padding:10px 12px;text-align:center;border-right:1px solid var(--bsub)}
.hstat:last-child{border-right:none}
.hstat-val{font-family:'Cormorant Garamond',serif;font-size:19px;font-weight:300;color:var(--ivory);line-height:1}
.hstat-lbl{font-size:8px;letter-spacing:.18em;text-transform:uppercase;color:var(--ivory3);margin-top:3px}

/* Location */
.hcard-loc{display:flex;align-items:center;gap:7px;font-size:11.5px;color:var(--ivory3);margin-bottom:14px}
.hcard-loc i{font-size:10px;color:var(--gold);flex-shrink:0}

/* Amenity chips */
.hcard-amenities{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:16px}
.atag{font-size:9.5px;padding:3px 9px;background:rgba(255,255,255,.05);color:var(--ivory3);letter-spacing:.06em}

/* Book button */
.book-btn{
  width:100%;background:none;border:1px solid var(--gold);
  color:var(--gold);font-family:'Barlow',sans-serif;
  font-size:10.5px;letter-spacing:.28em;text-transform:uppercase;
  padding:13px;cursor:pointer;transition:all .3s;
  position:relative;overflow:hidden;text-decoration:none;
  display:flex;align-items:center;justify-content:center;gap:10px
}
.book-btn::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.book-btn:hover::before{transform:scaleX(1)}
.book-btn:hover{color:var(--ink)}
.book-btn span,.book-btn i{position:relative;z-index:1}

/* Empty state */
.empty{padding:72px 24px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:14px}
.empty-icon{width:64px;height:64px;border:1px solid var(--bsub);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--ivory3);opacity:.45;margin-bottom:4px}
.empty h3{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:300;color:var(--ivory2)}
.empty p{font-size:12px;color:var(--ivory3);font-weight:300}

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
      <h1>Browse <em>Event Halls</em></h1>
      <div class="tb-sub">Find the perfect venue for your occasion</div>
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
    <div class="search-wrap">
      <i class="fas fa-magnifying-glass"></i>
      <input type="text" id="search-input" placeholder="Search halls…" oninput="filterHalls()">
    </div>
    <select class="sort-select" id="sort-select" onchange="sortHalls()">
      <option value="">Sort: Default</option>
      <option value="price-asc">Price: Low → High</option>
      <option value="price-desc">Price: High → Low</option>
      <option value="cap-desc">Capacity: Largest</option>
      <option value="name-asc">Name: A → Z</option>
    </select>
    <div class="filter-count">Showing <span id="visible-count"><?= $cnt_halls ?></span> of <?= $cnt_halls ?> venues</div>
  </div>

  <div class="content">
    <div class="hall-grid" id="hall-grid">
      <?php if ($halls->num_rows > 0):
        $i = 0;
        while ($hall = $halls->fetch_assoc()):
          $i++;
          $amenity_list = array_filter(array_map('trim', explode(',', $hall['amenities'] ?? '')));
      ?>
      <div class="hcard ani"
           style="animation-delay:<?= $i * 0.065 ?>s"
           data-name="<?= strtolower(htmlspecialchars($hall['name'])) ?>"
           data-price="<?= $hall['price_per_day'] ?>"
           data-cap="<?= $hall['capacity'] ?>">

        <!-- Image -->
        <div class="hcard-img">
          <?php if (!empty($hall['image'])): ?>
            <img src="../assets/images/<?= htmlspecialchars($hall['image']) ?>" alt="<?= htmlspecialchars($hall['name']) ?>">
          <?php else: ?>
            <div class="hcard-placeholder"><i class="fas fa-building-columns"></i></div>
          <?php endif; ?>
          <div class="price-overlay">
            <?= format_currency($hall['price_per_day']) ?>
            <span>per day</span>
          </div>
        </div>

        <!-- Body -->
        <div class="hcard-body">
          <div class="hcard-name"><?= htmlspecialchars($hall['name']) ?></div>
          <div class="hcard-desc"><?= htmlspecialchars(substr($hall['description'],0,110)) ?>…</div>

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
          <div class="hcard-loc">
            <i class="fas fa-location-dot"></i>
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

          <!-- Book button -->
          <a href="book_hall.php?hall_id=<?= $hall['id'] ?>" class="book-btn">
            <i class="fas fa-calendar-check"></i>
            <span>Book This Hall</span>
          </a>
        </div>
      </div>
      <?php endwhile;
      else: ?>
        <div class="empty" style="grid-column:1/-1">
          <div class="empty-icon"><i class="fas fa-building-columns"></i></div>
          <h3>No halls available</h3>
          <p>Check back soon — new venues are added regularly.</p>
        </div>
      <?php endif; ?>
    </div>
  </div><!-- /content -->
</main>

<script>
const allCards = () => Array.from(document.querySelectorAll('#hall-grid .hcard'));

function filterHalls() {
  const q = document.getElementById('search-input').value.toLowerCase().trim();
  let count = 0;
  allCards().forEach(c => {
    const show = !q || c.dataset.name.includes(q);
    c.style.display = show ? '' : 'none';
    if (show) count++;
  });
  document.getElementById('visible-count').textContent = count;
}

function sortHalls() {
  const val   = document.getElementById('sort-select').value;
  const grid  = document.getElementById('hall-grid');
  const cards = allCards();

  cards.sort((a, b) => {
    if (val === 'price-asc')  return +a.dataset.price - +b.dataset.price;
    if (val === 'price-desc') return +b.dataset.price - +a.dataset.price;
    if (val === 'cap-desc')   return +b.dataset.cap   - +a.dataset.cap;
    if (val === 'name-asc')   return a.dataset.name.localeCompare(b.dataset.name);
    return 0;
  });
  cards.forEach(c => grid.appendChild(c));
}
</script>
</body>
</html>
