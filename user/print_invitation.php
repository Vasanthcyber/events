<?php
require_once '../includes/config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('<p style="font-family:sans-serif;text-align:center;padding:60px">Invalid link.</p>');
}

$token = $_GET['token'];
$stmt = $conn->prepare("
    SELECT i.*, b.event_name, b.event_date, b.event_type,
           h.name AS hall_name, h.location,
           u.name AS host_name
    FROM invitations i
    JOIN bookings b ON i.booking_id = b.id
    JOIN halls h    ON b.hall_id = h.id
    JOIN users u    ON i.user_id = u.id
    WHERE i.token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
if (!$inv) die('<p style="font-family:sans-serif;text-align:center;padding:60px">Not found.</p>');

$venue    = !empty($inv['custom_location']) ? $inv['custom_location'] : $inv['hall_name'];
$address  = !empty($inv['custom_address'])  ? $inv['custom_address']  : $inv['location'];
$date_fmt = date('l, d F Y', strtotime($inv['event_date']));
$time_fmt = !empty($inv['event_time']) ? date('h:i A', strtotime($inv['event_time'])) : null;
$rsvp_fmt = !empty($inv['rsvp_deadline']) ? date('d F Y', strtotime($inv['rsvp_deadline'])) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invitation — <?= htmlspecialchars($inv['event_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Barlow',sans-serif;background:#f5f0e8;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:40px 20px 80px}

/* Screen action bar */
.screen-bar{
  position:fixed;bottom:0;left:0;right:0;
  background:#1a1610;border-top:1px solid rgba(201,168,76,.25);
  display:flex;align-items:center;justify-content:center;gap:12px;
  padding:16px 24px;z-index:100;
}
.s-btn{
  display:flex;align-items:center;gap:8px;
  background:none;border:1px solid rgba(201,168,76,.4);
  color:#e8d08a;font-family:'Barlow',sans-serif;
  font-size:10px;letter-spacing:.2em;text-transform:uppercase;
  padding:11px 22px;cursor:pointer;text-decoration:none;
  transition:all .25s;
}
.s-btn:hover{background:rgba(201,168,76,.12);border-color:#c9a84c}
.s-btn-primary{background:#c9a84c;border-color:#c9a84c;color:#0b0908}
.s-btn-primary:hover{background:#e8d08a;border-color:#e8d08a}
.s-btn i{font-size:13px}

/* Invitation card */
.inv-page{
  width:100%;max-width:600px;
  background:#fff;
  border:1px solid #d4c9a8;
  position:relative;
  padding:56px 52px;
  box-shadow:0 4px 40px rgba(0,0,0,.12);
}
.corner{position:absolute;width:20px;height:20px;border:1px solid rgba(180,150,80,.4);transform:rotate(45deg)}
.corner.tl{top:14px;left:14px} .corner.tr{top:14px;right:14px}
.corner.bl{bottom:14px;left:14px} .corner.br{bottom:14px;right:14px}
.top-line{position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#c9a84c,transparent)}
.bot-line{position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#c9a84c,transparent)}

.org-wrap{text-align:center;margin-bottom:28px}
.org-gem{width:44px;height:44px;border:1.5px solid #c9a84c;display:inline-flex;align-items:center;justify-content:center;transform:rotate(45deg);margin-bottom:12px}
.org-gem-inner{transform:rotate(-45deg);font-size:16px;color:#c9a84c}
.org-name{font-family:'Cormorant Garamond',serif;font-size:18px;letter-spacing:.2em;text-transform:uppercase;color:#1a1610}
.org-tag{font-size:8px;letter-spacing:.32em;text-transform:uppercase;color:#c9a84c;margin-top:4px}
.divider{width:48px;height:1px;background:#c9a84c;margin:20px auto;opacity:.6}

.eyebrow{text-align:center;font-size:8.5px;letter-spacing:.32em;text-transform:uppercase;color:#9e9080;margin-bottom:8px}
.headline{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:300;text-align:center;color:#1a1610;line-height:1.1;margin-bottom:4px}
.sub-headline{text-align:center;font-size:9px;letter-spacing:.28em;text-transform:uppercase;color:#9e9080;margin-bottom:24px}

.det-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.det-box{background:#faf6ee;border:1px solid #e8dfc8;padding:12px 14px}
.det-label{font-size:7.5px;letter-spacing:.22em;text-transform:uppercase;color:#c9a84c;margin-bottom:4px}
.det-val{font-size:12.5px;color:#1a1610;font-weight:400}

.agenda-box{background:#faf6ee;border:1px solid #e8dfc8;padding:14px 16px;margin-bottom:14px}
.section-label{font-size:7.5px;letter-spacing:.24em;text-transform:uppercase;color:#c9a84c;margin-bottom:8px}
.section-text{font-size:12px;color:#4a4035;line-height:1.8;font-weight:300}

.speaker-box{display:flex;align-items:center;gap:14px;background:#faf6ee;border:1px solid #e8dfc8;padding:12px 16px;margin-bottom:14px}
.speaker-icon{width:36px;height:36px;border:1px solid rgba(201,168,76,.4);display:flex;align-items:center;justify-content:center;font-size:13px;color:#c9a84c;flex-shrink:0}
.speaker-name{font-size:13px;color:#1a1610;font-weight:500}
.speaker-title{font-size:10px;color:#9e9080;margin-top:2px;letter-spacing:.06em}

.note{text-align:center;font-family:'Cormorant Garamond',serif;font-size:13px;font-style:italic;color:#6e6050;line-height:1.7;padding:14px 0;border-top:1px solid #e8dfc8;margin-top:4px}
.rsvp-box{text-align:center;margin-top:14px}
.rsvp-label{font-size:8px;letter-spacing:.22em;text-transform:uppercase;color:#9e9080;margin-bottom:4px}
.rsvp-date{font-family:'Cormorant Garamond',serif;font-size:16px;color:#c9a84c}

.host-box{text-align:center;margin-top:18px;padding-top:14px;border-top:1px solid #e8dfc8}
.host-label{font-size:8px;letter-spacing:.2em;text-transform:uppercase;color:#9e9080;margin-bottom:4px}
.host-name{font-family:'Cormorant Garamond',serif;font-size:15px;color:#1a1610;font-weight:400;letter-spacing:.06em}

@media print{
  body{background:#fff;padding:0}
  .screen-bar{display:none}
  .inv-page{box-shadow:none;border:none;max-width:100%;padding:40px}
  @page{margin:10mm}
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="inv-page">
  <div class="top-line"></div>
  <div class="bot-line"></div>
  <div class="corner tl"></div><div class="corner tr"></div>
  <div class="corner bl"></div><div class="corner br"></div>

  <div class="org-wrap">
    <div class="org-gem"><div class="org-gem-inner"><i class="fas fa-gem"></i></div></div>
    <div class="org-name">Eventique</div>
    <div class="org-tag">Curated Event Experiences</div>
  </div>

  <div class="divider"></div>
  <div class="eyebrow">You are cordially invited to</div>
  <div class="headline"><?= htmlspecialchars($inv['event_name']) ?></div>
  <div class="sub-headline"><?= htmlspecialchars($inv['event_type'] ?? 'Special Event') ?></div>
  <div class="divider"></div>

  <div class="det-grid">
    <div class="det-box">
      <div class="det-label"><i class="fas fa-calendar" style="margin-right:4px"></i>Date</div>
      <div class="det-val"><?= $date_fmt ?></div>
    </div>
    <div class="det-box">
      <div class="det-label"><i class="fas fa-clock" style="margin-right:4px"></i>Time</div>
      <div class="det-val"><?= $time_fmt ?? '—' ?></div>
    </div>
    <div class="det-box">
      <div class="det-label"><i class="fas fa-building-columns" style="margin-right:4px"></i>Venue</div>
      <div class="det-val"><?= htmlspecialchars($venue) ?></div>
    </div>
    <div class="det-box">
      <div class="det-label"><i class="fas fa-location-dot" style="margin-right:4px"></i>Location</div>
      <div class="det-val"><?= htmlspecialchars($address) ?></div>
    </div>
    <?php if (!empty($inv['dress_code'])): ?>
    <div class="det-box" style="grid-column:1/-1">
      <div class="det-label"><i class="fas fa-shirt" style="margin-right:4px"></i>Dress Code</div>
      <div class="det-val"><?= htmlspecialchars($inv['dress_code']) ?></div>
    </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($inv['meeting_agenda'])): ?>
  <div class="agenda-box">
    <div class="section-label"><i class="fas fa-list-check" style="margin-right:4px"></i>Agenda</div>
    <div class="section-text"><?= nl2br(htmlspecialchars($inv['meeting_agenda'])) ?></div>
  </div>
  <?php endif; ?>

  <?php if (!empty($inv['speaker_name'])): ?>
  <div class="speaker-box">
    <div class="speaker-icon"><i class="fas fa-microphone-lines"></i></div>
    <div>
      <div class="speaker-name"><?= htmlspecialchars($inv['speaker_name']) ?></div>
      <?php if (!empty($inv['speaker_title'])): ?>
      <div class="speaker-title"><?= htmlspecialchars($inv['speaker_title']) ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="note"><?= !empty($inv['extra_note']) ? nl2br(htmlspecialchars($inv['extra_note'])) : 'All are requested to attend the event.' ?></div>

  <?php if ($rsvp_fmt): ?>
  <div class="rsvp-box">
    <div class="rsvp-label">Please RSVP by</div>
    <div class="rsvp-date"><?= $rsvp_fmt ?></div>
  </div>
  <?php endif; ?>

  <div class="host-box">
    <div class="host-label">Hosted by</div>
    <div class="host-name"><?= htmlspecialchars($inv['host_name']) ?></div>
  </div>
</div>

<!-- Screen action bar (hidden on print) -->
<div class="screen-bar">
  <a class="s-btn" href="view_invitation.php?token=<?= urlencode($token) ?>">
    <i class="fas fa-arrow-left"></i> Back
  </a>
  <button class="s-btn s-btn-primary" onclick="window.print()">
    <i class="fas fa-print"></i> Print / Save as PDF
  </button>
  <button class="s-btn" onclick="copyLink()">
    <i class="fas fa-copy"></i> Copy Link
  </button>
</div>

<script>
function copyLink() {
  const url = '<?= addslashes((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].str_replace('print_invitation','view_invitation',$_SERVER['REQUEST_URI'])) ?>';
  navigator.clipboard.writeText(url).then(() => {
    const btn = event.target.closest('button');
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i> Copy Link', 2000);
  });
}
// Auto-trigger print if ?print=1 in URL
if (new URLSearchParams(window.location.search).get('print') === '1') {
  window.onload = () => setTimeout(() => window.print(), 400);
}
</script>
</body>
</html>
