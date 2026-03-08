<?php
require_once '../includes/config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('<p style="font-family:sans-serif;text-align:center;padding:60px;color:#999">Invalid invitation link.</p>');
}

$token = $_GET['token'];
$stmt = $conn->prepare("
    SELECT i.*, b.event_name, b.event_date, b.event_type, b.guests_count,
           h.name AS hall_name, h.location, h.capacity, h.image,
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

if (!$inv) {
    die('<p style="font-family:sans-serif;text-align:center;padding:60px;color:#999">Invitation not found or link has expired.</p>');
}

$event_date_fmt = date('l, d F Y', strtotime($inv['event_date']));
$time_fmt       = $inv['event_time'] ? date('h:i A', strtotime($inv['event_time'])) : null;
$rsvp_fmt       = $inv['rsvp_deadline'] ? date('d F Y', strtotime($inv['rsvp_deadline'])) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invitation — <?= htmlspecialchars($inv['event_name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#0b0908;--ink2:#0f0d0a;--ink3:#171310;--ink4:#1e1a13;
  --ivory:#f2ece0;--ivory2:#b5ad9f;--ivory3:#6e6560;
  --gold:#c9a84c;--goldl:#e8d08a;--goldp:rgba(201,168,76,.11);
  --green:#4dbd8a;--rose:#d06878;
  --border:rgba(201,168,76,.2);--bsub:rgba(255,255,255,.06);
}
html{background:var(--ink);min-height:100%}
body{
  font-family:'Barlow',sans-serif;background:var(--ink);color:var(--ivory);
  min-height:100vh;display:flex;flex-direction:column;align-items:center;
  padding:40px 20px 60px;
  background-image:
    radial-gradient(ellipse 80% 50% at 50% -10%,rgba(201,168,76,.08) 0%,transparent 60%),
    radial-gradient(ellipse 60% 60% at 80% 100%,rgba(201,168,76,.05) 0%,transparent 50%);
}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:100;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.03'/%3E%3C/svg%3E")}

/* ── Card ── */
.card-wrap{width:100%;max-width:560px;animation:rise .7s ease both}
@keyframes rise{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

.inv-card{
  background:var(--ink2);
  border:1px solid var(--border);
  position:relative;
  overflow:hidden;
  padding:48px 44px;
}
.inv-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent 0%,var(--gold) 50%,transparent 100%)}
.inv-card::after {content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent 0%,var(--gold) 50%,transparent 100%)}
/* Side lines */
.inv-card-sl{position:absolute;top:0;bottom:0;width:3px}
.inv-card-sl.left {left:0; background:linear-gradient(180deg,transparent,var(--gold),transparent)}
.inv-card-sl.right{right:0;background:linear-gradient(180deg,transparent,var(--gold),transparent)}

/* Corner decorations */
.inv-corner{position:absolute;width:22px;height:22px;border:1px solid rgba(201,168,76,.3);transform:rotate(45deg)}
.c-tl{top:16px;left:16px}
.c-tr{top:16px;right:16px}
.c-bl{bottom:16px;left:16px}
.c-br{bottom:16px;right:16px}

/* Organisation header */
.inv-org{display:flex;flex-direction:column;align-items:center;margin-bottom:24px}
.inv-gem{width:48px;height:48px;border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:16px;transform:rotate(45deg);margin-bottom:12px}
.inv-gem i{transform:rotate(-45deg)}
.inv-org-name{font-family:'Cormorant Garamond',serif;font-size:18px;letter-spacing:.2em;text-transform:uppercase;color:var(--ivory)}
.inv-org-tag{font-size:8.5px;letter-spacing:.32em;text-transform:uppercase;color:var(--gold);margin-top:4px}

.rule{width:48px;height:1px;background:var(--gold);margin:0 auto 24px;opacity:.55}

/* Headline */
.inv-eyebrow{text-align:center;font-size:9px;letter-spacing:.3em;text-transform:uppercase;color:var(--ivory3);margin-bottom:8px}
.inv-headline{font-family:'Cormorant Garamond',serif;font-size:34px;font-weight:300;text-align:center;color:var(--ivory);line-height:1.12;margin-bottom:6px}
.inv-headline em{font-style:italic;color:var(--goldl)}
.inv-event-type{text-align:center;font-size:9.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);margin-bottom:28px}

/* Detail rows */
.inv-details{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--bsub);border:1px solid var(--bsub);margin-bottom:22px}
.inv-det{background:var(--ink3);padding:14px 18px}
.inv-det.full{grid-column:1/-1}
.inv-det-label{font-size:8px;letter-spacing:.24em;text-transform:uppercase;color:var(--gold);margin-bottom:5px;display:flex;align-items:center;gap:6px}
.inv-det-val{font-size:13.5px;color:var(--ivory);font-weight:400;line-height:1.35}
.inv-det-val.serif{font-family:'Cormorant Garamond',serif;font-size:17px}

/* Agenda */
.inv-section{background:var(--ink4);border:1px solid var(--bsub);padding:16px 20px;margin-bottom:16px}
.inv-section-label{font-size:8px;letter-spacing:.26em;text-transform:uppercase;color:var(--gold);margin-bottom:10px;display:flex;align-items:center;gap:7px}
.inv-section-label i{font-size:11px}
.inv-section-text{font-size:13px;color:var(--ivory2);line-height:1.8;font-weight:300}

/* Speaker */
.inv-speaker{display:flex;align-items:center;gap:16px;background:var(--ink4);border:1px solid var(--bsub);padding:14px 20px;margin-bottom:16px}
.inv-speaker-icon{width:40px;height:40px;border:1px solid rgba(201,168,76,.35);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:14px;flex-shrink:0}
.inv-speaker-name{font-size:15px;color:var(--ivory);font-weight:500;font-family:'Cormorant Garamond',serif}
.inv-speaker-title{font-size:11px;color:var(--ivory3);margin-top:3px;letter-spacing:.06em}

/* Footer note */
.inv-note{text-align:center;padding:20px 0 8px;border-top:1px solid var(--bsub);margin-top:8px}
.inv-note-text{font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:300;font-style:italic;color:var(--ivory2);line-height:1.7}

/* RSVP */
.inv-rsvp{text-align:center;margin:16px 0}
.inv-rsvp-label{font-size:8.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);margin-bottom:5px}
.inv-rsvp-date{font-family:'Cormorant Garamond',serif;font-size:20px;color:var(--goldl);font-weight:300}

/* Hosted by */
.inv-host{text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid var(--bsub)}
.inv-host-label{font-size:8.5px;letter-spacing:.2em;text-transform:uppercase;color:var(--ivory3);margin-bottom:3px}
.inv-host-name{font-family:'Cormorant Garamond',serif;font-size:18px;color:var(--ivory);font-weight:400}

/* Action bar */
.action-bar{display:flex;gap:10px;margin-top:20px;width:100%;max-width:560px}
.action-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--border);background:none;color:var(--ivory2);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.18em;text-transform:uppercase;padding:12px;cursor:pointer;text-decoration:none;transition:all .22s}
.action-btn:hover{border-color:var(--gold);color:var(--gold)}
.action-btn i{font-size:12px}

@media(max-width:500px){
  .inv-card{padding:32px 22px}
  .inv-headline{font-size:26px}
  .inv-details{grid-template-columns:1fr}
  .action-bar{flex-direction:column}
}

/* ── Action Bar ── */
.action-bar{
  position:fixed;bottom:0;left:0;right:0;
  background:var(--ink2);
  border-top:1px solid var(--border);
  z-index:100;
  padding:14px 0;
}
.action-bar-inner{
  max-width:680px;margin:0 auto;
  display:flex;align-items:center;justify-content:space-between;
  gap:20px;padding:0 24px;
}
.action-bar-left{flex:1;min-width:0}
.action-bar-label{font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);margin-bottom:8px}
.action-link-row{display:flex;gap:8px;align-items:center}
.action-link-input{
  flex:1;min-width:0;
  background:rgba(0,0,0,.3);
  border:1px solid var(--border);
  color:var(--goldl);
  font-family:'Barlow',sans-serif;
  font-size:11px;padding:8px 12px;
  outline:none;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.action-copy-btn{
  background:none;border:1px solid var(--gold);
  color:var(--gold);font-family:'Barlow',sans-serif;
  font-size:9px;letter-spacing:.16em;text-transform:uppercase;
  padding:8px 14px;cursor:pointer;white-space:nowrap;
  transition:all .25s;flex-shrink:0;
}
.action-copy-btn:hover{background:var(--gold);color:var(--ink)}
.action-bar-right{display:flex;gap:8px;flex-shrink:0}
.act-btn{
  display:flex;flex-direction:column;align-items:center;gap:4px;
  background:none;border:1px solid var(--bsub);
  color:var(--ivory2);
  font-family:'Barlow',sans-serif;font-size:8.5px;
  letter-spacing:.12em;text-transform:uppercase;
  padding:8px 14px;cursor:pointer;text-decoration:none;
  transition:all .22s;min-width:60px;
}
.act-btn i{font-size:14px}
.act-btn:hover{border-color:var(--gold);color:var(--gold)}
.act-btn-gold{border-color:rgba(201,168,76,.4);color:var(--goldl)}
.act-btn-gold:hover{background:var(--gold);color:var(--ink)}

/* Toast */
.toast{
  position:fixed;bottom:110px;left:50%;transform:translateX(-50%) translateY(20px);
  background:var(--ink3);border:1px solid rgba(77,189,138,.4);
  color:var(--green);font-family:'Barlow',sans-serif;
  font-size:12px;letter-spacing:.06em;
  padding:12px 24px;z-index:200;
  opacity:0;transition:all .3s;pointer-events:none;
  display:flex;align-items:center;gap:8px;
}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

/* Push card above action bar */
.card-wrap{padding-bottom:110px}

@media(max-width:640px){
  .action-bar-inner{flex-direction:column;gap:12px}
  .action-bar-left{width:100%}
  .action-bar-right{width:100%;justify-content:center}
}

@media print{
  body{background:white;padding:0}
  body::after,.action-bar{display:none}
  .inv-card{border:none;box-shadow:none;padding:20px}
  :root{--ink:#fff;--ink2:#fff;--ink3:#f8f8f8;--ink4:#f0f0f0;--ivory:#111;--ivory2:#333;--ivory3:#666;--gold:#8a6a1f}
}
</style>
</head>
<body>

<div class="card-wrap">
  <div class="inv-card">
    <div class="inv-card-sl left"></div>
    <div class="inv-card-sl right"></div>
    <div class="inv-corner c-tl"></div>
    <div class="inv-corner c-tr"></div>
    <div class="inv-corner c-bl"></div>
    <div class="inv-corner c-br"></div>

    <!-- Org header -->
    <div class="inv-org">
      <div class="inv-gem"><i class="fas fa-gem"></i></div>
      <div class="inv-org-name">Eventique</div>
      <div class="inv-org-tag">Curated Event Experiences</div>
    </div>

    <div class="rule"></div>

    <div class="inv-eyebrow">You are cordially invited to</div>
    <div class="inv-headline"><?= htmlspecialchars($inv['event_name']) ?></div>
    <?php if ($inv['event_type']): ?>
    <div class="inv-event-type"><?= htmlspecialchars($inv['event_type']) ?></div>
    <?php endif; ?>

    <!-- Details grid -->
    <div class="inv-details">
      <div class="inv-det">
        <div class="inv-det-label"><i class="fas fa-calendar"></i> Date</div>
        <div class="inv-det-val serif"><?= $event_date_fmt ?></div>
      </div>
      <?php if ($time_fmt): ?>
      <div class="inv-det">
        <div class="inv-det-label"><i class="fas fa-clock"></i> Time</div>
        <div class="inv-det-val serif"><?= $time_fmt ?></div>
      </div>
      <?php endif; ?>
      <div class="inv-det">
        <div class="inv-det-label"><i class="fas fa-building-columns"></i> Venue</div>
        <div class="inv-det-val"><?= htmlspecialchars($inv['hall_name']) ?></div>
      </div>
      <div class="inv-det">
        <div class="inv-det-label"><i class="fas fa-location-dot"></i> Location</div>
        <div class="inv-det-val"><?= htmlspecialchars($inv['location']) ?></div>
      </div>
      <?php if (!empty($inv['dress_code'])): ?>
      <div class="inv-det full">
        <div class="inv-det-label"><i class="fas fa-shirt"></i> Dress Code</div>
        <div class="inv-det-val"><?= htmlspecialchars($inv['dress_code']) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Agenda -->
    <?php if (!empty($inv['meeting_agenda'])): ?>
    <div class="inv-section">
      <div class="inv-section-label"><i class="fas fa-list-check"></i> Agenda</div>
      <div class="inv-section-text"><?= nl2br(htmlspecialchars($inv['meeting_agenda'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Speaker -->
    <?php if (!empty($inv['speaker_name'])): ?>
    <div class="inv-speaker">
      <div class="inv-speaker-icon"><i class="fas fa-microphone-lines"></i></div>
      <div>
        <div class="inv-speaker-name"><?= htmlspecialchars($inv['speaker_name']) ?></div>
        <?php if (!empty($inv['speaker_title'])): ?>
        <div class="inv-speaker-title"><?= htmlspecialchars($inv['speaker_title']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Note -->
    <?php if (!empty($inv['extra_note'])): ?>
    <div class="inv-note">
      <div class="inv-note-text"><?= nl2br(htmlspecialchars($inv['extra_note'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- RSVP -->
    <?php if ($rsvp_fmt): ?>
    <div class="inv-rsvp">
      <div class="inv-rsvp-label">Please RSVP by</div>
      <div class="inv-rsvp-date"><?= $rsvp_fmt ?></div>
    </div>
    <?php endif; ?>

    <!-- Hosted by -->
    <div class="inv-host">
      <div class="inv-host-label">Hosted by</div>
      <div class="inv-host-name"><?= htmlspecialchars($inv['host_name']) ?></div>
    </div>

  </div><!-- /inv-card -->
</div><!-- /card-wrap -->

<!-- Action Bar -->
<div class="action-bar">
  <div class="action-bar-inner">

    <div class="action-bar-left">
      <div class="action-bar-label"><i class="fas fa-link" style="margin-right:6px;color:var(--gold)"></i>Shareable Invitation Link</div>
      <div class="action-link-row">
        <input class="action-link-input" id="inv-url" type="text" value="<?= htmlspecialchars((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" readonly>
        <button class="action-copy-btn" id="copy-btn" onclick="copyLink()">
          <i class="fas fa-copy"></i> Copy
        </button>
      </div>
    </div>

    <div class="action-bar-right">
      <button class="act-btn" onclick="window.print()">
        <i class="fas fa-print"></i>
        <span>Print / PDF</span>
      </button>
      <button class="act-btn" onclick="copyLink()">
        <i class="fas fa-share-nodes"></i>
        <span>Share Link</span>
      </button>
      <a class="act-btn" href="mailto:?subject=<?= urlencode("You're invited: ".$inv['event_name']) ?>&body=<?= urlencode("You are cordially invited to ".$inv['event_name']." on ".$event_date_fmt.($time_fmt ? " at ".$time_fmt : "")." at ".$inv['hall_name'].", ".$inv['location'].". View your invitation: ".((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])) ?>">
        <i class="fas fa-envelope"></i>
        <span>Email</span>
      </a>
      <button class="act-btn act-btn-gold" onclick="shareNative()">
        <i class="fas fa-arrow-up-from-bracket"></i>
        <span>Share</span>
      </button>
    </div>

  </div>
</div>

<!-- Toast notification -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i> Link copied to clipboard!
</div>

<script>
function copyLink() {
  const url = document.getElementById('inv-url').value;
  navigator.clipboard.writeText(url).then(() => {
    // Update copy button
    const btn = document.getElementById('copy-btn');
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.style.background = 'var(--green)';
    btn.style.borderColor = 'var(--green)';
    btn.style.color = 'var(--ink)';
    setTimeout(() => {
      btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
      btn.style.background = '';
      btn.style.borderColor = '';
      btn.style.color = '';
    }, 2500);
    // Show toast
    showToast();
  }).catch(() => {
    // Fallback for older browsers
    const inp = document.getElementById('inv-url');
    inp.select();
    document.execCommand('copy');
    showToast();
  });
}

function showToast() {
  const t = document.getElementById('toast');
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

function shareNative() {
  if (navigator.share) {
    navigator.share({
      title: '<?= addslashes("You're invited: ".$inv['event_name']) ?>',
      text:  '<?= addslashes("You are cordially invited to ".$inv['event_name']." on ".$event_date_fmt) ?>',
      url:   window.location.href
    });
  } else {
    copyLink();
  }
}
</script>
</body>
</html>
