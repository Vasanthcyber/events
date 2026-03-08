<?php
// require_once '../includes/config.php';
// require_once '../includes/functions.php';
// session_start();
// if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
//     header('Location: ../index.php'); exit();
// }

// ── Mock Data ──────────────────────────────────────────────────
$admin_name = "Vasanth";

<<<<<<< HEAD
$stats = [
    ['icon'=>'fas fa-calendar-check', 'label'=>'Total Bookings',  'value'=>'142',    'delta'=>'+12%', 'up'=>true,  'color'=>'gold'],
    ['icon'=>'fas fa-circle-check',   'label'=>'Confirmed',       'value'=>'89',     'delta'=>'+8%',  'up'=>true,  'color'=>'green'],
    ['icon'=>'fas fa-hourglass-half', 'label'=>'Pending Review',  'value'=>'31',     'delta'=>'-3%',  'up'=>false, 'color'=>'amber'],
    ['icon'=>'fas fa-coins',          'label'=>'Revenue (YTD)',   'value'=>'$284k',  'delta'=>'+24%', 'up'=>true,  'color'=>'gold'],
    ['icon'=>'fas fa-building-columns','label'=>'Active Halls',   'value'=>'4',      'delta'=>'0%',   'up'=>true,  'color'=>'teal'],
    ['icon'=>'fas fa-store',          'label'=>'Vendors',         'value'=>'12',     'delta'=>'+2',   'up'=>true,  'color'=>'rose'],
    ['icon'=>'fas fa-users',          'label'=>'Registered Users','value'=>'208',    'delta'=>'+18',  'up'=>true,  'color'=>'teal'],
    ['icon'=>'fas fa-ban',            'label'=>'Cancelled',       'value'=>'22',     'delta'=>'+1',   'up'=>false, 'color'=>'rose'],
];

$bookings = [
    ['id'=>'BK-0041','client'=>'Isabelle Hartmann','event'=>'Anniversary Gala',    'hall'=>'Grand Ballroom',    'date'=>'Mar 14','guests'=>220,'amount'=>5000, 'status'=>'confirmed'],
    ['id'=>'BK-0040','client'=>'Rohan Mehta',      'event'=>'Product Launch',      'hall'=>'Sunset Terrace',    'date'=>'Mar 18','guests'=>90, 'amount'=>4000, 'status'=>'pending'],
    ['id'=>'BK-0039','client'=>'Céline Dupont',    'event'=>'Wedding Reception',   'hall'=>'Royal Palace Hall', 'date'=>'Mar 22','guests'=>310,'amount'=>6000, 'status'=>'confirmed'],
    ['id'=>'BK-0038','client'=>'Marcus Webb',      'event'=>'Corporate Dinner',    'hall'=>'Garden Paradise',   'date'=>'Mar 25','guests'=>140,'amount'=>3500, 'status'=>'pending'],
    ['id'=>'BK-0037','client'=>'Amara Osei',       'event'=>'Birthday Celebration','hall'=>'Grand Ballroom',    'date'=>'Mar 28','guests'=>180,'amount'=>5000, 'status'=>'cancelled'],
    ['id'=>'BK-0036','client'=>'Lena Vogel',       'event'=>'Charity Gala',        'hall'=>'Royal Palace Hall', 'date'=>'Apr 2', 'guests'=>400,'amount'=>6000, 'status'=>'confirmed'],
];

$monthly = [18,24,19,31,28,35,42,38,47,51,43,56];
$months  = ['A','M','J','J','A','S','O','N','D','J','F','M'];
$max_m   = max($monthly);

$halls = [
    ['name'=>'Grand Ballroom',    'cap'=>500,'price'=>5000,'bookings'=>48,'status'=>'available'],
    ['name'=>'Garden Paradise',   'cap'=>300,'price'=>3500,'bookings'=>31,'status'=>'available'],
    ['name'=>'Royal Palace Hall', 'cap'=>400,'price'=>6000,'bookings'=>38,'status'=>'available'],
    ['name'=>'Sunset Terrace',    'cap'=>200,'price'=>4000,'bookings'=>25,'status'=>'unavailable'],
];

$vendors = [
    ['name'=>'Le Bon Traiteur',     'type'=>'catering',     'bookings'=>22,'status'=>'active'],
    ['name'=>'Bloom & Co.',         'type'=>'decoration',   'bookings'=>18,'status'=>'active'],
    ['name'=>'Lumière Studio',      'type'=>'photography',  'bookings'=>31,'status'=>'active'],
    ['name'=>'SoundWave Pro',       'type'=>'music',        'bookings'=>14,'status'=>'inactive'],
    ['name'=>'Elite Catering Co.',  'type'=>'catering',     'bookings'=>9, 'status'=>'active'],
];

$activity = [
    ['icon'=>'fas fa-circle-check','color'=>'green','msg'=>'Booking <strong>BK-0039</strong> confirmed by admin','time'=>'2 min ago'],
    ['icon'=>'fas fa-user-plus',   'color'=>'teal', 'msg'=>'New user <strong>Amara Osei</strong> registered',   'time'=>'14 min ago'],
    ['icon'=>'fas fa-store',       'color'=>'gold', 'msg'=>'Vendor <strong>Lumière Studio</strong> added',      'time'=>'1 hr ago'],
    ['icon'=>'fas fa-ban',         'color'=>'rose', 'msg'=>'Booking <strong>BK-0037</strong> cancelled',        'time'=>'3 hr ago'],
    ['icon'=>'fas fa-building-columns','color'=>'amber','msg'=>'Hall <strong>Sunset Terrace</strong> set unavailable','time'=>'5 hr ago'],
];
=======
// Get statistics
$total_users     = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'user'")->fetch_assoc()['count'];
$total_vendors   = $conn->query("SELECT COUNT(*) as count FROM vendors WHERE status = 'active'")->fetch_assoc()['count'];
$total_halls     = $conn->query("SELECT COUNT(*) as count FROM halls")->fetch_assoc()['count'];
$total_bookings  = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$pending_bookings= $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'];
$confirmed_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['count'];

// Get recent bookings
$recent_bookings = $conn->query(
    "SELECT b.*, u.name as user_name, h.name as hall_name
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     JOIN halls h ON b.hall_id = h.id
     ORDER BY b.created_at DESC LIMIT 6"
);

// Hour-based greeting
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
>>>>>>> 7c77f6d (Updated project files)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
<<<<<<< HEAD
/* ── Reset & Root ──────────────────────────────────────────── */
=======
/* ── Reset ─────────────────────────────────────────────────── */
>>>>>>> 7c77f6d (Updated project files)
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --ink:   #0b0908;
  --ink2:  #131009;
  --ink3:  #1a1610;
<<<<<<< HEAD
  --ink4:  #221d16;
=======
  --ink4:  #201c15;
>>>>>>> 7c77f6d (Updated project files)
  --ivory: #f2ece0;
  --ivory2:#b5ad9f;
  --ivory3:#6e6560;
  --gold:  #c9a84c;
  --goldl: #e8d08a;
<<<<<<< HEAD
  --goldp: rgba(201,168,76,.12);
=======
  --goldp: rgba(201,168,76,.11);
>>>>>>> 7c77f6d (Updated project files)
  --green: #4dbd8a;
  --amber: #e8a83a;
  --rose:  #d06878;
  --teal:  #4db8bd;
<<<<<<< HEAD
  --border:rgba(201,168,76,.14);
  --bsub:  rgba(255,255,255,.055);
  --sw:    264px;
  --radius:0px;
}

html,body{height:100%;overflow:hidden}
body{font-family:'Barlow',sans-serif;background:var(--ink);color:var(--ivory);display:flex;min-height:100vh}

/* Grain overlay */
body::after{content:'';position:fixed;inset:0;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.03'/%3E%3C/svg%3E");
  pointer-events:none;z-index:9999}
=======
  --border:rgba(201,168,76,.15);
  --bsub:  rgba(255,255,255,.055);
  --sw:    256px;
}

html,body{height:100%;overflow:hidden}
body{
  font-family:'Barlow',sans-serif;
  background:var(--ink);
  color:var(--ivory);
  display:flex;min-height:100vh
}

/* Grain */
body::after{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.032'/%3E%3C/svg%3E")
}
>>>>>>> 7c77f6d (Updated project files)

/* ── Sidebar ───────────────────────────────────────────────── */
.sidebar{
  width:var(--sw);flex-shrink:0;
  background:var(--ink2);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;
<<<<<<< HEAD
  padding:0 0 32px;
  position:relative;overflow:hidden
}
.sidebar::before{
  content:'';position:absolute;bottom:-100px;left:-60px;
  width:300px;height:300px;border-radius:50%;
=======
  padding:0 0 28px;
  position:relative;overflow:hidden
}
.sidebar::before{
  content:'';position:absolute;bottom:-80px;left:-60px;
  width:260px;height:260px;border-radius:50%;
>>>>>>> 7c77f6d (Updated project files)
  background:radial-gradient(circle,rgba(201,168,76,.07) 0%,transparent 70%);
  pointer-events:none
}

<<<<<<< HEAD
.sb-brand{
  padding:36px 28px 28px;
  border-bottom:1px solid var(--bsub);
  margin-bottom:8px
}
.sb-logo{display:flex;align-items:center;gap:12px}
.sb-diamond{
  width:34px;height:34px;border:1px solid var(--gold);
=======
.sb-brand{padding:32px 26px 26px;border-bottom:1px solid var(--bsub);margin-bottom:6px}
.sb-logo{display:flex;align-items:center;gap:11px}
.sb-diamond{
  width:32px;height:32px;border:1px solid var(--gold);
>>>>>>> 7c77f6d (Updated project files)
  display:flex;align-items:center;justify-content:center;
  color:var(--gold);font-size:12px;transform:rotate(45deg);flex-shrink:0
}
.sb-diamond i{transform:rotate(-45deg)}
.sb-name{
  font-family:'Cormorant Garamond',serif;
<<<<<<< HEAD
  font-size:21px;font-weight:400;letter-spacing:.1em;
  color:var(--ivory);text-transform:uppercase
}
.sb-sub{
  font-size:8.5px;letter-spacing:.3em;color:var(--gold);
  text-transform:uppercase;margin-top:5px;padding-left:46px;font-weight:300
}

.sb-section{
  font-size:8.5px;letter-spacing:.28em;text-transform:uppercase;
  color:var(--ivory3);padding:20px 28px 8px;font-weight:400
}
.sb-link{
  display:flex;align-items:center;gap:13px;
  padding:12px 28px;color:var(--ivory2);text-decoration:none;
  font-size:12.5px;font-weight:400;letter-spacing:.03em;
  transition:all .2s;position:relative;cursor:pointer;border:none;background:none;width:100%;text-align:left
}
.sb-link i{font-size:13px;width:15px;text-align:center;transition:color .2s}
.sb-link:hover{color:var(--ivory);background:rgba(201,168,76,.05)}
.sb-link.active{color:var(--ivory);background:rgba(201,168,76,.07)}
.sb-link.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:2px;background:var(--gold)}
.sb-link.active i{color:var(--gold)}
.sb-badge{
  margin-left:auto;background:var(--goldp);border:1px solid rgba(201,168,76,.4);
  color:var(--gold);font-size:9px;font-weight:500;padding:1px 6px
}

.sb-footer{
  margin-top:auto;padding:20px 28px 0;
  border-top:1px solid var(--bsub)
}
.sb-user{display:flex;align-items:center;gap:11px}
.sb-avatar{
  width:32px;height:32px;border:1px solid var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-family:'Cormorant Garamond',serif;font-size:14px;color:var(--gold)
}
.sb-uname{font-size:12.5px;font-weight:500;color:var(--ivory)}
.sb-urole{font-size:9px;letter-spacing:.15em;text-transform:uppercase;color:var(--ivory3);margin-top:2px}
=======
  font-size:20px;font-weight:400;letter-spacing:.1em;
  color:var(--ivory);text-transform:uppercase
}
.sb-sub{
  font-size:8px;letter-spacing:.3em;color:var(--gold);
  text-transform:uppercase;margin-top:5px;padding-left:43px;font-weight:300
}

.sb-section{
  font-size:8px;letter-spacing:.28em;text-transform:uppercase;
  color:var(--ivory3);padding:18px 26px 7px;font-weight:400
}
.sb-link{
  display:flex;align-items:center;gap:12px;
  padding:11px 26px;color:var(--ivory2);text-decoration:none;
  font-size:12.5px;font-weight:400;letter-spacing:.03em;
  transition:all .2s;position:relative
}
.sb-link i{font-size:13px;width:14px;text-align:center;transition:color .2s}
.sb-link:hover{color:var(--ivory);background:rgba(201,168,76,.05)}
.sb-link.active{color:var(--ivory);background:rgba(201,168,76,.08)}
.sb-link.active::before{
  content:'';position:absolute;left:0;top:0;bottom:0;
  width:2px;background:var(--gold)
}
.sb-link.active i{color:var(--gold)}
.sb-badge{
  margin-left:auto;background:var(--goldp);
  border:1px solid rgba(201,168,76,.38);color:var(--gold);
  font-size:9px;font-weight:500;padding:1px 6px
}

.sb-footer{
  margin-top:auto;padding:18px 26px 0;
  border-top:1px solid var(--bsub)
}
.sb-user{display:flex;align-items:center;gap:10px}
.sb-avatar{
  width:30px;height:30px;border:1px solid var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-family:'Cormorant Garamond',serif;font-size:13px;color:var(--gold)
}
.sb-uname{font-size:12.5px;font-weight:500;color:var(--ivory)}
.sb-urole{font-size:8.5px;letter-spacing:.14em;text-transform:uppercase;color:var(--ivory3);margin-top:2px}
>>>>>>> 7c77f6d (Updated project files)
.sb-logout{color:var(--ivory3);font-size:13px;margin-left:auto;transition:color .2s;text-decoration:none}
.sb-logout:hover{color:var(--rose)}

/* ── Main ──────────────────────────────────────────────────── */
<<<<<<< HEAD
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--ink)}

/* Topbar */
.topbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:32px 44px 0;flex-shrink:0
}
.tb-left h1{
  font-family:'Cormorant Garamond',serif;
  font-size:30px;font-weight:300;color:var(--ivory);line-height:1
}
.tb-left h1 em{font-style:italic;color:var(--goldl)}
.tb-date{font-size:10.5px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-right{display:flex;align-items:center;gap:12px}
.tb-btn{
  background:none;border:1px solid var(--bsub);color:var(--ivory2);
  width:36px;height:36px;display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:13px;transition:all .2s;position:relative
}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-dot{
  width:6px;height:6px;border-radius:50%;background:var(--gold);
  position:absolute;top:6px;right:6px
}
.tb-search{
  display:flex;align-items:center;gap:10px;
  background:var(--ink3);border:1px solid var(--bsub);
  padding:0 16px;height:36px;min-width:200px
}
.tb-search input{
  background:none;border:none;color:var(--ivory);font-family:'Barlow',sans-serif;
  font-size:12px;outline:none;flex:1
}
.tb-search input::placeholder{color:var(--ivory3)}
.tb-search i{color:var(--ivory3);font-size:12px}

/* Tabs */
.tabs{
  display:flex;gap:0;padding:24px 44px 0;
  border-bottom:1px solid var(--bsub);flex-shrink:0
}
.tab{
  background:none;border:none;color:var(--ivory3);
  font-family:'Barlow',sans-serif;font-size:10.5px;
  letter-spacing:.22em;text-transform:uppercase;font-weight:400;
  padding:0 0 16px;margin-right:32px;cursor:pointer;
  position:relative;transition:color .2s
}
.tab.active{color:var(--ivory)}
.tab.active::after{
  content:'';position:absolute;bottom:-1px;left:0;right:0;
  height:1px;background:var(--gold)
}

/* Content */
.content{flex:1;overflow-y:auto;padding:32px 44px 48px}
=======
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}

.topbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:28px 40px 0;flex-shrink:0
}
.tb-left h1{
  font-family:'Cormorant Garamond',serif;
  font-size:28px;font-weight:300;color:var(--ivory);line-height:1
}
.tb-left h1 em{font-style:italic;color:var(--goldl)}
.tb-date{font-size:10px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-btn{
  background:none;border:1px solid var(--bsub);color:var(--ivory2);
  width:34px;height:34px;display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:12px;transition:all .2s;position:relative;text-decoration:none
}
.tb-btn:hover{border-color:var(--gold);color:var(--gold)}
.tb-dot{
  width:5px;height:5px;border-radius:50%;background:var(--gold);
  position:absolute;top:6px;right:6px
}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}

.content{flex:1;overflow-y:auto;padding:28px 40px 48px}
>>>>>>> 7c77f6d (Updated project files)
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-track{background:transparent}
.content::-webkit-scrollbar-thumb{background:var(--border)}

<<<<<<< HEAD
/* Tab panels */
.tab-panel{display:none}
.tab-panel.active{display:block}

/* ── Animations ────────────────────────────────────────────── */
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .45s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}.d3{animation-delay:.15s}.d4{animation-delay:.20s}
.d5{animation-delay:.25s}.d6{animation-delay:.30s}.d7{animation-delay:.35s}.d8{animation-delay:.40s}

/* ── Stat Grid ─────────────────────────────────────────────── */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}

.stat-card{
  background:var(--ink3);border:1px solid var(--bsub);
  padding:24px 24px 20px;position:relative;overflow:hidden;cursor:default
}
.stat-card::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:1px;
  opacity:0;transition:opacity .3s
}
.stat-card:hover::after{opacity:1}

/* colour accents per card */
.stat-card[data-c="gold"]  .sc-icon{color:var(--gold); background:var(--goldp)}
.stat-card[data-c="green"] .sc-icon{color:var(--green);background:rgba(77,189,138,.1)}
.stat-card[data-c="amber"] .sc-icon{color:var(--amber);background:rgba(232,168,58,.1)}
.stat-card[data-c="teal"]  .sc-icon{color:var(--teal); background:rgba(77,184,189,.1)}
.stat-card[data-c="rose"]  .sc-icon{color:var(--rose); background:rgba(208,104,120,.1)}
.stat-card[data-c="gold"]  ::after{background:var(--gold)}
.stat-card[data-c="green"] ::after{background:var(--green)}
.stat-card[data-c="amber"] ::after{background:var(--amber)}
.stat-card[data-c="teal"]  ::after{background:var(--teal)}
.stat-card[data-c="rose"]  ::after{background:var(--rose)}

.sc-icon{
  width:34px;height:34px;display:flex;align-items:center;
  justify-content:center;font-size:13px;margin-bottom:18px
}
.sc-val{
  font-family:'Cormorant Garamond',serif;
  font-size:38px;font-weight:300;color:var(--ivory);line-height:1;margin-bottom:5px
}
.sc-label{font-size:9.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);font-weight:400}
.sc-delta{
  position:absolute;top:22px;right:20px;
  font-size:10.5px;font-weight:500
}
.up  {color:var(--green)}
.down{color:var(--rose)}

/* ── Two-col grid ──────────────────────────────────────────── */
.g2{display:grid;grid-template-columns:1fr 340px;gap:14px;margin-bottom:14px}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px}

/* ── Panel ─────────────────────────────────────────────────── */
.panel{background:var(--ink3);border:1px solid var(--bsub)}
.ph{
  display:flex;align-items:center;justify-content:space-between;
  padding:20px 24px;border-bottom:1px solid var(--bsub)
}
.pt{font-family:'Cormorant Garamond',serif;font-size:19px;font-weight:400;color:var(--ivory)}
.pa{font-size:9.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);
    text-decoration:none;font-weight:400;transition:opacity .2s;cursor:pointer;background:none;border:none}
.pa:hover{opacity:.65}

/* ── Revenue Chart ─────────────────────────────────────────── */
.chart-wrap{padding:24px 24px 20px}
.chart-meta{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px}
.chart-total{font-family:'Cormorant Garamond',serif;font-size:36px;font-weight:300;color:var(--ivory);line-height:1}
.chart-total-sub{font-size:9.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--ivory3);margin-top:4px}
.chart-legend{display:flex;gap:16px}
.cl-item{display:flex;align-items:center;gap:6px;font-size:10px;color:var(--ivory3)}
.cl-dot{width:6px;height:6px;border-radius:50%}

.bars{display:flex;align-items:flex-end;gap:6px;height:100px}
.bc{flex:1;display:flex;flex-direction:column;align-items:center;gap:7px}
.bar{
  width:100%;position:relative;
  background:rgba(255,255,255,.04);
  overflow:hidden
}
.bar-fill{
  position:absolute;bottom:0;left:0;right:0;
  background:linear-gradient(to top,var(--gold),var(--goldl));
  opacity:.65;transition:opacity .2s
}
.bar:hover .bar-fill{opacity:1}
@keyframes bg{from{height:0 !important}}
.bar-fill{animation:bg .9s cubic-bezier(.4,0,.2,1) both}
.bl{font-size:8.5px;letter-spacing:.07em;color:var(--ivory3);text-transform:uppercase}

/* ── Donut Chart ───────────────────────────────────────────── */
.donut-wrap{display:flex;flex-direction:column;align-items:center;padding:24px}
.donut-svg{width:120px;height:120px}
.donut-labels{display:flex;flex-direction:column;gap:10px;width:100%;padding:0 8px;margin-top:20px}
.dl{display:flex;align-items:center;gap:10px}
.dl-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dl-label{font-size:11px;color:var(--ivory2);flex:1}
.dl-val{font-family:'Cormorant Garamond',serif;font-size:17px;color:var(--ivory)}

/* ── Activity Feed ─────────────────────────────────────────── */
.activity-list{padding:4px 0}
.act{display:flex;align-items:flex-start;gap:14px;padding:15px 24px;border-bottom:1px solid var(--bsub);transition:background .15s}
.act:last-child{border-bottom:none}
.act:hover{background:rgba(201,168,76,.03)}
.act-icon{
  width:30px;height:30px;display:flex;align-items:center;justify-content:center;
  font-size:11px;flex-shrink:0;margin-top:1px
}
.act-icon.green{color:var(--green);background:rgba(77,189,138,.1)}
.act-icon.teal {color:var(--teal); background:rgba(77,184,189,.1)}
.act-icon.gold {color:var(--gold); background:var(--goldp)}
.act-icon.rose {color:var(--rose); background:rgba(208,104,120,.1)}
.act-icon.amber{color:var(--amber);background:rgba(232,168,58,.1)}
.act-msg{font-size:12px;color:var(--ivory2);line-height:1.5;font-weight:300}
.act-msg strong{color:var(--ivory);font-weight:500}
.act-time{font-size:10px;color:var(--ivory3);margin-top:3px;letter-spacing:.04em}

/* ── Table ─────────────────────────────────────────────────── */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid var(--bsub)}
th{
  padding:12px 24px;font-size:8.5px;letter-spacing:.24em;
  text-transform:uppercase;color:var(--ivory3);font-weight:400;text-align:left;white-space:nowrap
}
td{
  padding:14px 24px;font-size:12.5px;color:var(--ivory2);
  border-bottom:1px solid var(--bsub);vertical-align:middle
}
tr:last-child td{border-bottom:none}
tbody tr{transition:background .14s;cursor:default}
tbody tr:hover{background:rgba(201,168,76,.03)}

.td-id{font-size:10.5px;letter-spacing:.1em;color:var(--ivory3)}
.td-name{color:var(--ivory);font-weight:500}
.td-amt{font-family:'Cormorant Garamond',serif;font-size:17px;color:var(--ivory)}

.badge{
  display:inline-flex;align-items:center;gap:5px;
  padding:3px 9px;font-size:9.5px;letter-spacing:.09em;text-transform:uppercase;font-weight:500
}
.bdot{width:4px;height:4px;border-radius:50%}
.badge.confirmed{color:var(--green);background:rgba(77,189,138,.1)}
.badge.confirmed .bdot{background:var(--green)}
.badge.pending  {color:var(--amber);background:rgba(232,168,58,.1)}
.badge.pending   .bdot{background:var(--amber)}
.badge.cancelled{color:var(--rose); background:rgba(208,104,120,.1)}
.badge.cancelled .bdot{background:var(--rose)}
.badge.active   {color:var(--green);background:rgba(77,189,138,.1)}
.badge.active    .bdot{background:var(--green)}
.badge.inactive {color:var(--ivory3);background:rgba(255,255,255,.06)}
.badge.inactive  .bdot{background:var(--ivory3)}
.badge.available  {color:var(--teal);background:rgba(77,184,189,.1)}
.badge.available   .bdot{background:var(--teal)}
.badge.unavailable{color:var(--rose);background:rgba(208,104,120,.1)}
.badge.unavailable .bdot{background:var(--rose)}

.abtn{
  background:none;border:1px solid var(--bsub);
  color:var(--ivory3);font-size:10.5px;padding:5px 11px;cursor:pointer;
  transition:all .2s;font-family:'Barlow',sans-serif;letter-spacing:.05em;text-decoration:none;display:inline-block
}
.abtn:hover{border-color:var(--gold);color:var(--gold)}
.abtn.danger:hover{border-color:var(--rose);color:var(--rose)}

/* ── Hall cards ────────────────────────────────────────────── */
.hall-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.hall-card{
  background:var(--ink4);border:1px solid var(--bsub);
  padding:22px;position:relative;overflow:hidden;transition:border-color .2s
}
.hall-card:hover{border-color:var(--border)}
.hc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
.hc-name{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;color:var(--ivory)}
.hc-row{display:flex;gap:20px;margin-bottom:14px}
.hc-stat{display:flex;flex-direction:column;gap:3px}
.hc-stat-val{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:300;color:var(--ivory);line-height:1}
.hc-stat-lbl{font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:var(--ivory3)}
.hc-bar{height:3px;background:var(--bsub);margin-bottom:16px}
.hc-bar-fill{height:100%;background:linear-gradient(90deg,var(--gold),var(--goldl));opacity:.7}
.hc-actions{display:flex;gap:8px}

/* ── Vendor table specifics ────────────────────────────────── */
.svc-pill{
  display:inline-block;padding:2px 9px;font-size:9.5px;
  letter-spacing:.08em;text-transform:uppercase;
  background:rgba(255,255,255,.05);color:var(--ivory2)
}

/* ── Responsive ────────────────────────────────────────────── */
@media(max-width:1100px){.stat-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:900px){
  .sidebar{display:none}
  .g2,.g3{grid-template-columns:1fr}
=======
/* ── Animations ─────────────────────────────────────────────── */
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .45s ease both}
.d1{animation-delay:.04s}.d2{animation-delay:.09s}
.d3{animation-delay:.14s}.d4{animation-delay:.19s}
.d5{animation-delay:.24s}.d6{animation-delay:.29s}

/* ── Stat Cards ─────────────────────────────────────────────── */
.stat-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:22px}

.sc{
  background:var(--ink3);border:1px solid var(--bsub);
  padding:22px 20px 18px;position:relative;overflow:hidden;cursor:default;transition:border-color .25s
}
.sc:hover{border-color:var(--border)}
.sc::after{content:'';position:absolute;top:0;left:0;right:0;height:1px;opacity:0;transition:opacity .3s}
.sc:hover::after{opacity:1}

.sc[data-c="gold"]  .sc-icon{color:var(--gold); background:var(--goldp)}
.sc[data-c="green"] .sc-icon{color:var(--green);background:rgba(77,189,138,.1)}
.sc[data-c="amber"] .sc-icon{color:var(--amber);background:rgba(232,168,58,.1)}
.sc[data-c="teal"]  .sc-icon{color:var(--teal); background:rgba(77,184,189,.1)}
.sc[data-c="rose"]  .sc-icon{color:var(--rose); background:rgba(208,104,120,.1)}
.sc[data-c="gold"]::after  {background:var(--gold)}
.sc[data-c="green"]::after {background:var(--green)}
.sc[data-c="amber"]::after {background:var(--amber)}
.sc[data-c="teal"]::after  {background:var(--teal)}
.sc[data-c="rose"]::after  {background:var(--rose)}

.sc-icon{width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:12px;margin-bottom:16px}
.sc-val{font-family:'Cormorant Garamond',serif;font-size:36px;font-weight:300;color:var(--ivory);line-height:1;margin-bottom:5px}
.sc-label{font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);font-weight:400}

/* ── Two-col ─────────────────────────────────────────────────── */
.g2{display:grid;grid-template-columns:1fr 300px;gap:14px;margin-bottom:14px}

/* ── Panel ───────────────────────────────────────────────────── */
.panel{background:var(--ink3);border:1px solid var(--bsub)}
.ph{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--bsub)}
.pt{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory)}
.pa{font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);text-decoration:none;font-weight:400;transition:opacity .2s}
.pa:hover{opacity:.65}

/* ── Quick-access list ───────────────────────────────────────── */
.ml-row{
  display:flex;align-items:center;gap:14px;
  padding:14px 22px;border-bottom:1px solid var(--bsub);
  transition:background .15s;text-decoration:none
}
.ml-row:last-child{border-bottom:none}
.ml-row:hover{background:rgba(201,168,76,.04)}
.ml-icon{width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}
.ml-icon.gold {color:var(--gold); background:var(--goldp)}
.ml-icon.green{color:var(--green);background:rgba(77,189,138,.1)}
.ml-icon.amber{color:var(--amber);background:rgba(232,168,58,.1)}
.ml-icon.teal {color:var(--teal); background:rgba(77,184,189,.1)}
.ml-label{flex:1;font-size:12px;color:var(--ivory2)}
.ml-val{font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--ivory)}

/* ── Table ───────────────────────────────────────────────────── */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid var(--bsub)}
th{padding:11px 22px;font-size:8.5px;letter-spacing:.24em;text-transform:uppercase;color:var(--ivory3);font-weight:400;text-align:left;white-space:nowrap}
td{padding:13px 22px;font-size:12.5px;color:var(--ivory2);border-bottom:1px solid var(--bsub);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr{transition:background .14s}
tbody tr:hover{background:rgba(201,168,76,.03)}

.td-id{font-size:10.5px;letter-spacing:.08em;color:var(--ivory3)}
.td-name{color:var(--ivory);font-weight:500}
.td-amt{font-family:'Cormorant Garamond',serif;font-size:16px;color:var(--ivory)}

.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 8px;font-size:9px;letter-spacing:.08em;text-transform:uppercase;font-weight:500}
.bdot{width:4px;height:4px;border-radius:50%}
.badge-success{color:var(--green);background:rgba(77,189,138,.1)}
.badge-success .bdot{background:var(--green)}
.badge-warning{color:var(--amber);background:rgba(232,168,58,.1)}
.badge-warning .bdot{background:var(--amber)}
.badge-danger {color:var(--rose); background:rgba(208,104,120,.1)}
.badge-danger  .bdot{background:var(--rose)}
.badge-primary{color:var(--teal); background:rgba(77,184,189,.1)}
.badge-primary .bdot{background:var(--teal)}

.empty{padding:40px 22px;text-align:center;font-size:11px;color:var(--ivory3);letter-spacing:.12em;text-transform:uppercase}

/* Responsive */
@media(max-width:1200px){.stat-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:900px){
  .sidebar{display:none}
  .g2{grid-template-columns:1fr}
>>>>>>> 7c77f6d (Updated project files)
  .stat-grid{grid-template-columns:repeat(2,1fr)}
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
<<<<<<< HEAD
  <button class="sb-link active" onclick="switchTab('overview',this)"><i class="fas fa-chart-tree-map"></i> Dashboard</button>
  <button class="sb-link" onclick="switchTab('bookings',this)">
    <i class="fas fa-calendar-check"></i> Bookings
    <span class="sb-badge">31</span>
  </button>

  <span class="sb-section">Manage</span>
  <button class="sb-link" onclick="switchTab('halls',this)"><i class="fas fa-building-columns"></i> Halls</button>
  <button class="sb-link" onclick="switchTab('vendors',this)"><i class="fas fa-store"></i> Vendors</button>
  <button class="sb-link" onclick="switchTab('users',this)">
    <i class="fas fa-users"></i> Users
    <span class="sb-badge">208</span>
  </button>

  <span class="sb-section">System</span>
  <a class="sb-link" href="#"><i class="fas fa-gear"></i> Settings</a>
  <a class="sb-link" href="#"><i class="fas fa-chart-line"></i> Analytics</a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar">A</div>
      <div>
        <div class="sb-uname">Super Admin</div>
        <div class="sb-urole">Administrator</div>
      </div>
      <a class="sb-logout" href="logout.php" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
=======
  <a href="dashboard.php" class="sb-link active">
    <i class="fas fa-chart-tree-map"></i> Dashboard
  </a>
  <a href="bookings.php" class="sb-link">
    <i class="fas fa-calendar-check"></i> Bookings
    <?php if($pending_bookings > 0): ?>
      <span class="sb-badge"><?= $pending_bookings ?></span>
    <?php endif; ?>
  </a>

  <span class="sb-section">Manage</span>
  <a href="halls.php"   class="sb-link"><i class="fas fa-building-columns"></i> Halls</a>
  <a href="vendors.php" class="sb-link"><i class="fas fa-store"></i> Vendors</a>
  <a href="users.php"   class="sb-link">
    <i class="fas fa-users"></i> Users
    <span class="sb-badge"><?= $total_users ?></span>
  </a>

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
>>>>>>> 7c77f6d (Updated project files)
    </div>
  </div>
</aside>

<!-- ── MAIN ─────────────────────────────────────────────────── -->
<main class="main">

<<<<<<< HEAD
  <!-- Topbar -->
  <div class="topbar">
    <div class="tb-left">
      <h1>Good <?= (date('G')<12)?'morning':((date('G')<18)?'afternoon':'evening') ?>, <em><?= $admin_name ?></em>.</h1>
      <div class="tb-date"><?= date('l, F j, Y') ?></div>
    </div>
    <div class="tb-right">
      <div class="tb-search">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" placeholder="Search anything…">
      </div>
      <button class="tb-btn"><i class="fas fa-bell"></i><span class="tb-dot"></span></button>
      <button class="tb-btn"><i class="fas fa-sliders"></i></button>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab active" id="tab-overview"  onclick="switchTab('overview',this)">Overview</button>
    <button class="tab"        id="tab-bookings"  onclick="switchTab('bookings',this)">Bookings</button>
    <button class="tab"        id="tab-halls"     onclick="switchTab('halls',this)">Halls</button>
    <button class="tab"        id="tab-vendors"   onclick="switchTab('vendors',this)">Vendors</button>
    <button class="tab"        id="tab-users"     onclick="switchTab('users',this)">Users</button>
  </div>

  <!-- ── CONTENT ─────────────────────────────────────────── -->
  <div class="content">

    <!-- ════ OVERVIEW ════ -->
    <div class="tab-panel active" id="panel-overview">

      <!-- 8 stat cards -->
      <div class="stat-grid">
        <?php foreach($stats as $i=>$s): ?>
        <div class="stat-card ani d<?= $i+1 ?>" data-c="<?= $s['color'] ?>">
          <div class="sc-icon"><i class="<?= $s['icon'] ?>"></i></div>
          <div class="sc-val"><?= $s['value'] ?></div>
          <div class="sc-label"><?= $s['label'] ?></div>
          <div class="sc-delta <?= $s['up']?'up':'down' ?>">
            <i class="fas fa-arrow-<?= $s['up']?'up':'down' ?>" style="font-size:8px;margin-right:2px;"></i><?= $s['delta'] ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Revenue chart + Donut + Activity -->
      <div class="g2 ani d5">
        <!-- Revenue chart -->
        <div class="panel">
          <div class="ph">
            <span class="pt">Revenue Overview</span>
            <button class="pa">Annual View →</button>
          </div>
          <div class="chart-wrap">
            <div class="chart-meta">
              <div>
                <div class="chart-total">$284,500</div>
                <div class="chart-total-sub">Total this year</div>
              </div>
              <div class="chart-legend">
                <div class="cl-item"><div class="cl-dot" style="background:var(--gold)"></div>Revenue</div>
              </div>
            </div>
            <div class="bars">
              <?php foreach($monthly as $i=>$v):
                $pct = round($v/$max_m*100); ?>
              <div class="bc">
                <div class="bar" style="height:90px;" title="$<?= $v ?>k">
                  <div class="bar-fill" style="height:<?= $pct ?>%;animation-delay:<?= $i*0.05 ?>s"></div>
                </div>
                <div class="bl"><?= $months[$i] ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Right column: donut + activity -->
        <div style="display:flex;flex-direction:column;gap:14px">
          <!-- Booking status donut -->
          <div class="panel">
            <div class="ph">
              <span class="pt">Bookings</span>
            </div>
            <div class="donut-wrap">
              <svg class="donut-svg" viewBox="0 0 42 42">
                <!-- confirmed: 89/142 = 62.7% -->
                <circle cx="21" cy="21" r="15.9" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="5"/>
                <circle cx="21" cy="21" r="15.9" fill="none" stroke="#4dbd8a" stroke-width="5"
                  stroke-dasharray="62.7 37.3" stroke-dashoffset="25" style="transition:stroke-dasharray 1s ease"/>
                <circle cx="21" cy="21" r="15.9" fill="none" stroke="#e8a83a" stroke-width="5"
                  stroke-dasharray="21.8 78.2" stroke-dashoffset="-37.7"/>
                <circle cx="21" cy="21" r="15.9" fill="none" stroke="#d06878" stroke-width="5"
                  stroke-dasharray="15.5 84.5" stroke-dashoffset="-59.5"/>
                <text x="21" y="21.5" text-anchor="middle" dominant-baseline="middle"
                  fill="#f2ece0" font-size="6.5" font-family="Cormorant Garamond,serif" font-weight="300">142</text>
                <text x="21" y="27" text-anchor="middle" dominant-baseline="middle"
                  fill="#6e6560" font-size="3" font-family="Barlow,sans-serif" letter-spacing="0.05em">TOTAL</text>
              </svg>
              <div class="donut-labels">
                <div class="dl"><div class="dl-dot" style="background:var(--green)"></div><div class="dl-label">Confirmed</div><div class="dl-val">89</div></div>
                <div class="dl"><div class="dl-dot" style="background:var(--amber)"></div><div class="dl-label">Pending</div><div class="dl-val">31</div></div>
                <div class="dl"><div class="dl-dot" style="background:var(--rose)"></div><div class="dl-label">Cancelled</div><div class="dl-val">22</div></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent bookings + Activity feed -->
      <div class="g2 ani d6">
        <div class="panel">
          <div class="ph">
            <span class="pt">Recent Bookings</span>
            <button class="pa" onclick="switchTab('bookings',null)">View All →</button>
          </div>
          <div class="tbl-wrap">
            <table>
              <thead><tr>
                <th>ID</th><th>Client</th><th>Event</th><th>Date</th><th>Amount</th><th>Status</th><th></th>
              </tr></thead>
              <tbody>
              <?php foreach(array_slice($bookings,0,5) as $b): ?>
              <tr>
                <td><span class="td-id"><?= $b['id'] ?></span></td>
                <td><span class="td-name"><?= htmlspecialchars($b['client']) ?></span></td>
                <td><?= htmlspecialchars($b['event']) ?></td>
                <td><?= $b['date'] ?></td>
                <td><span class="td-amt">$<?= number_format($b['amount']) ?></span></td>
                <td><span class="badge <?= $b['status'] ?>"><span class="bdot"></span><?= ucfirst($b['status']) ?></span></td>
                <td><a href="#" class="abtn"><?= $b['status']==='pending'?'Review':'View' ?></a></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Activity -->
        <div class="panel">
          <div class="ph"><span class="pt">Activity</span></div>
          <div class="activity-list">
            <?php foreach($activity as $a): ?>
            <div class="act">
              <div class="act-icon <?= $a['color'] ?>"><i class="<?= $a['icon'] ?>"></i></div>
              <div>
                <div class="act-msg"><?= $a['msg'] ?></div>
                <div class="act-time"><?= $a['time'] ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div><!-- /overview -->

    <!-- ════ BOOKINGS ════ -->
    <div class="tab-panel" id="panel-bookings">
      <div class="panel ani d1">
        <div class="ph">
          <span class="pt">All Bookings</span>
          <div style="display:flex;gap:10px;align-items:center">
            <span style="font-size:10px;color:var(--ivory3);letter-spacing:.1em">Filter:</span>
            <button class="abtn" onclick="filterTable('bktbl','')">All</button>
            <button class="abtn" onclick="filterTable('bktbl','confirmed')">Confirmed</button>
            <button class="abtn" onclick="filterTable('bktbl','pending')">Pending</button>
            <button class="abtn" onclick="filterTable('bktbl','cancelled')">Cancelled</button>
          </div>
        </div>
        <div class="tbl-wrap">
          <table id="bktbl">
            <thead><tr>
              <th>ID</th><th>Client</th><th>Event</th><th>Hall</th><th>Date</th><th>Guests</th><th>Amount</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach($bookings as $b): ?>
            <tr data-status="<?= $b['status'] ?>">
              <td><span class="td-id"><?= $b['id'] ?></span></td>
              <td><span class="td-name"><?= htmlspecialchars($b['client']) ?></span></td>
              <td><?= htmlspecialchars($b['event']) ?></td>
              <td><?= htmlspecialchars($b['hall']) ?></td>
              <td><?= $b['date'] ?></td>
              <td><?= $b['guests'] ?></td>
              <td><span class="td-amt">$<?= number_format($b['amount']) ?></span></td>
              <td><span class="badge <?= $b['status'] ?>"><span class="bdot"></span><?= ucfirst($b['status']) ?></span></td>
              <td style="display:flex;gap:6px;">
                <?php if($b['status']==='pending'): ?>
                  <a href="#" class="abtn">Confirm</a>
                  <a href="#" class="abtn danger">Cancel</a>
                <?php else: ?>
                  <a href="#" class="abtn">Details</a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div><!-- /bookings -->

    <!-- ════ HALLS ════ -->
    <div class="tab-panel" id="panel-halls">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;color:var(--ivory)">Event Halls</div>
        <button class="abtn" style="border-color:var(--gold);color:var(--gold)"><i class="fas fa-plus" style="margin-right:7px;font-size:10px"></i>Add New Hall</button>
      </div>
      <div class="hall-grid ani d1">
        <?php foreach($halls as $h):
          $util = round($h['bookings']/60*100); ?>
        <div class="hall-card">
          <div class="hc-top">
            <div class="hc-name"><?= htmlspecialchars($h['name']) ?></div>
            <span class="badge <?= $h['status'] ?>"><span class="bdot"></span><?= ucfirst($h['status']) ?></span>
          </div>
          <div class="hc-row">
            <div class="hc-stat">
              <div class="hc-stat-val"><?= $h['cap'] ?></div>
              <div class="hc-stat-lbl">Capacity</div>
            </div>
            <div class="hc-stat">
              <div class="hc-stat-val">$<?= number_format($h['price']) ?></div>
              <div class="hc-stat-lbl">Per Day</div>
            </div>
            <div class="hc-stat">
              <div class="hc-stat-val"><?= $h['bookings'] ?></div>
              <div class="hc-stat-lbl">Bookings</div>
            </div>
          </div>
          <div style="font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:var(--ivory3);margin-bottom:6px">Utilisation <?= $util ?>%</div>
          <div class="hc-bar"><div class="hc-bar-fill" style="width:<?= $util ?>%"></div></div>
          <div class="hc-actions">
            <a href="#" class="abtn">Edit</a>
            <a href="#" class="abtn">View Bookings</a>
            <a href="#" class="abtn danger">Remove</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div><!-- /halls -->

    <!-- ════ VENDORS ════ -->
    <div class="tab-panel" id="panel-vendors">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;color:var(--ivory)">Vendors</div>
        <button class="abtn" style="border-color:var(--gold);color:var(--gold)"><i class="fas fa-plus" style="margin-right:7px;font-size:10px"></i>Add Vendor</button>
      </div>
      <div class="panel ani d1">
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Business Name</th><th>Service</th><th>Bookings</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach($vendors as $v): ?>
            <tr>
              <td><span class="td-name"><?= htmlspecialchars($v['name']) ?></span></td>
              <td><span class="svc-pill"><?= ucfirst($v['type']) ?></span></td>
              <td><?= $v['bookings'] ?></td>
              <td><span class="badge <?= $v['status'] ?>"><span class="bdot"></span><?= ucfirst($v['status']) ?></span></td>
              <td style="display:flex;gap:6px;">
                <a href="#" class="abtn">Edit</a>
                <a href="#" class="abtn"><?= $v['status']==='active'?'Deactivate':'Activate' ?></a>
                <a href="#" class="abtn danger">Remove</a>
              </td>
            </tr>
            <?php endforeach; ?>
=======
  <div class="topbar">
    <div class="tb-left">
      <h1><?= $greeting ?>, <em><?= htmlspecialchars(explode(' ',$_SESSION['user_name'])[0]) ?></em>.</h1>
      <div class="tb-date"><?= date('l, F j, Y') ?></div>
    </div>
    <div class="tb-right">
      <a href="bookings.php" class="tb-btn" title="Pending bookings">
        <i class="fas fa-bell"></i>
        <?php if($pending_bookings > 0): ?><span class="tb-dot"></span><?php endif; ?>
      </a>
      <a href="halls.php"    class="tb-btn" title="Halls"><i class="fas fa-building-columns"></i></a>
      <a href="../logout.php" class="tb-btn" title="Sign out"><i class="fas fa-arrow-right-from-bracket"></i></a>
    </div>
  </div>
  <div class="topbar-rule"></div>

  <div class="content">

    <!-- Stat cards -->
    <div class="stat-grid">
      <div class="sc ani d1" data-c="teal">
        <div class="sc-icon"><i class="fas fa-users"></i></div>
        <div class="sc-val"><?= $total_users ?></div>
        <div class="sc-label">Total Users</div>
      </div>
      <div class="sc ani d2" data-c="green">
        <div class="sc-icon"><i class="fas fa-store"></i></div>
        <div class="sc-val"><?= $total_vendors ?></div>
        <div class="sc-label">Active Vendors</div>
      </div>
      <div class="sc ani d3" data-c="gold">
        <div class="sc-icon"><i class="fas fa-building-columns"></i></div>
        <div class="sc-val"><?= $total_halls ?></div>
        <div class="sc-label">Total Halls</div>
      </div>
      <div class="sc ani d4" data-c="amber">
        <div class="sc-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="sc-val"><?= $total_bookings ?></div>
        <div class="sc-label">Total Bookings</div>
      </div>
      <div class="sc ani d5" data-c="rose">
        <div class="sc-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="sc-val"><?= $pending_bookings ?></div>
        <div class="sc-label">Pending</div>
      </div>
      <div class="sc ani d6" data-c="green">
        <div class="sc-icon"><i class="fas fa-circle-check"></i></div>
        <div class="sc-val"><?= $confirmed_count ?></div>
        <div class="sc-label">Confirmed</div>
      </div>
    </div>

    <!-- Table + Quick links -->
    <div class="g2 ani d5">

      <div class="panel">
        <div class="ph">
          <span class="pt">Recent Bookings</span>
          <a href="bookings.php" class="pa">View All →</a>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th><th>Customer</th><th>Hall</th>
                <th>Event</th><th>Date</th><th>Status</th><th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recent_bookings->num_rows > 0): ?>
                <?php while ($b = $recent_bookings->fetch_assoc()):
                  $bc = 'badge-primary';
                  if ($b['status'] === 'confirmed') $bc = 'badge-success';
                  elseif ($b['status'] === 'pending')   $bc = 'badge-warning';
                  elseif ($b['status'] === 'cancelled') $bc = 'badge-danger';
                ?>
                <tr>
                  <td><span class="td-id">#<?= $b['id'] ?></span></td>
                  <td><span class="td-name"><?= htmlspecialchars($b['user_name']) ?></span></td>
                  <td><?= htmlspecialchars($b['hall_name']) ?></td>
                  <td><?= htmlspecialchars($b['event_name']) ?></td>
                  <td><?= format_date($b['event_date']) ?></td>
                  <td>
                    <span class="badge <?= $bc ?>">
                      <span class="bdot"></span><?= ucfirst($b['status']) ?>
                    </span>
                  </td>
                  <td><span class="td-amt"><?= format_currency($b['total_amount']) ?></span></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7"><div class="empty">No bookings yet</div></td></tr>
              <?php endif; ?>
>>>>>>> 7c77f6d (Updated project files)
            </tbody>
          </table>
        </div>
      </div>
<<<<<<< HEAD
    </div><!-- /vendors -->

    <!-- ════ USERS ════ -->
    <div class="tab-panel" id="panel-users">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;color:var(--ivory)">Registered Users</div>
        <div style="font-size:10px;letter-spacing:.15em;text-transform:uppercase;color:var(--ivory3)">208 Total</div>
      </div>
      <div class="panel ani d1">
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php
            $sample_users = [
              ['id'=>1,'name'=>'Isabelle Hartmann','email'=>'isabelle@example.com','phone'=>'+1 555 0101','role'=>'user',  'status'=>'active',  'joined'=>'Jan 12'],
              ['id'=>2,'name'=>'Rohan Mehta',      'email'=>'rohan@example.com',  'phone'=>'+1 555 0182','role'=>'user',  'status'=>'active',  'joined'=>'Jan 20'],
              ['id'=>3,'name'=>'Céline Dupont',    'email'=>'celine@example.com', 'phone'=>'+33 6 1234','role'=>'vendor','status'=>'active',  'joined'=>'Feb 3'],
              ['id'=>4,'name'=>'Marcus Webb',      'email'=>'marcus@example.com', 'phone'=>'+1 555 0234','role'=>'user',  'status'=>'inactive','joined'=>'Feb 14'],
              ['id'=>5,'name'=>'Amara Osei',       'email'=>'amara@example.com',  'phone'=>'+44 7700','role'=>'user',  'status'=>'active',  'joined'=>'Mar 1'],
            ];
            foreach($sample_users as $u): ?>
            <tr>
              <td><span class="td-id"><?= $u['id'] ?></span></td>
              <td><span class="td-name"><?= htmlspecialchars($u['name']) ?></span></td>
              <td style="color:var(--ivory3);font-size:12px"><?= $u['email'] ?></td>
              <td><?= $u['phone'] ?></td>
              <td><span class="svc-pill"><?= ucfirst($u['role']) ?></span></td>
              <td><span class="badge <?= $u['status'] ?>"><span class="bdot"></span><?= ucfirst($u['status']) ?></span></td>
              <td style="color:var(--ivory3)"><?= $u['joined'] ?></td>
              <td style="display:flex;gap:6px;">
                <a href="#" class="abtn">View</a>
                <a href="#" class="abtn danger">Suspend</a>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div><!-- /users -->

  </div><!-- /content -->
</main>

<script>
function switchTab(name, triggerEl) {
  // hide all panels
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  // show target
  const panel = document.getElementById('panel-' + name);
  if (panel) { panel.classList.add('active'); }

  // deactivate all sidebar links + tabs
  document.querySelectorAll('.sb-link').forEach(l => l.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

  // activate matching top tab
  const topTab = document.getElementById('tab-' + name);
  if (topTab) topTab.classList.add('active');

  // activate triggering element (could be sidebar btn)
  if (triggerEl) triggerEl.classList.add('active');
}

function filterTable(tableId, status) {
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(r => {
    r.style.display = (!status || r.dataset.status === status) ? '' : 'none';
  });
}
</script>
=======

      <!-- Quick access -->
      <div class="panel">
        <div class="ph"><span class="pt">Quick Access</span></div>
        <a href="users.php"   class="ml-row">
          <div class="ml-icon teal"><i class="fas fa-users"></i></div>
          <div class="ml-label">Manage Users</div>
          <div class="ml-val"><?= $total_users ?></div>
        </a>
        <a href="vendors.php" class="ml-row">
          <div class="ml-icon green"><i class="fas fa-store"></i></div>
          <div class="ml-label">Manage Vendors</div>
          <div class="ml-val"><?= $total_vendors ?></div>
        </a>
        <a href="halls.php"   class="ml-row">
          <div class="ml-icon gold"><i class="fas fa-building-columns"></i></div>
          <div class="ml-label">Manage Halls</div>
          <div class="ml-val"><?= $total_halls ?></div>
        </a>
        <a href="bookings.php" class="ml-row">
          <div class="ml-icon amber"><i class="fas fa-hourglass-half"></i></div>
          <div class="ml-label">Pending Reviews</div>
          <div class="ml-val"><?= $pending_bookings ?></div>
        </a>
      </div>

    </div>
  </div><!-- /content -->
</main>

>>>>>>> 7c77f6d (Updated project files)
</body>
</html>