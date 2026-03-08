<?php
// Start session FIRST before anything else
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';
$inv_link = '';

// Get booking id from URL
if (!isset($_GET['booking_id'])) { redirect('bookings.php'); }
$booking_id = intval($_GET['booking_id']);

// Fetch booking — must belong to this user & be confirmed
$stmt = $conn->prepare("
    SELECT b.*, h.name AS hall_name, h.location, h.capacity, h.image
    FROM bookings b
    JOIN halls h ON b.hall_id = h.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) { redirect('bookings.php'); }

// Handle invitation save/send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_invitation'])) {
    $event_time     = sanitize_input($_POST['event_time']);
    $meeting_agenda = sanitize_input($_POST['meeting_agenda']);
    $speaker_name   = sanitize_input($_POST['speaker_name']);
    $speaker_title  = sanitize_input($_POST['speaker_title']);
    $dress_code     = sanitize_input($_POST['dress_code']);
    $rsvp_deadline  = sanitize_input($_POST['rsvp_deadline']);
    $extra_note     = sanitize_input($_POST['extra_note']);
    $recipient_emails = sanitize_input($_POST['recipient_emails'] ?? '');

    // Check if invitation already exists for this booking
    $chk = $conn->prepare("SELECT id FROM invitations WHERE booking_id = ?");
    $chk->bind_param("i", $booking_id);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();

    if ($existing) {
        // Update existing invitation
        $custom_location = sanitize_input($_POST['custom_location'] ?? '');
        $custom_address  = sanitize_input($_POST['custom_address']  ?? '');
        $upd = $conn->prepare("UPDATE invitations SET event_time=?, meeting_agenda=?, speaker_name=?, speaker_title=?, dress_code=?, rsvp_deadline=?, extra_note=?, recipient_emails=?, custom_location=?, custom_address=? WHERE booking_id=?");
        $upd->bind_param("ssssssssssi", $event_time, $meeting_agenda, $speaker_name, $speaker_title, $dress_code, $rsvp_deadline, $extra_note, $recipient_emails, $custom_location, $custom_address, $booking_id);
        $upd->execute();
        $inv_id = $existing['id'];
    } else {
        // Insert new invitation
        $custom_location = sanitize_input($_POST['custom_location'] ?? '');
        $custom_address  = sanitize_input($_POST['custom_address']  ?? '');
        $token = bin2hex(random_bytes(16));
        $ins = $conn->prepare("INSERT INTO invitations (booking_id, user_id, token, event_time, meeting_agenda, speaker_name, speaker_title, dress_code, rsvp_deadline, extra_note, recipient_emails, custom_location, custom_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $ins->bind_param("iisssssssssss", $booking_id, $user_id, $token, $event_time, $meeting_agenda, $speaker_name, $speaker_title, $dress_code, $rsvp_deadline, $extra_note, $recipient_emails, $custom_location, $custom_address);
        $ins->execute();
        $inv_id = $conn->insert_id;
    }

    // Fetch the token for the link
    $tk_row = $conn->prepare("SELECT token FROM invitations WHERE id = ?");
    $tk_row->bind_param("i", $inv_id);
    $tk_row->execute();
    $token_val = $tk_row->get_result()->fetch_assoc()['token'];

    $inv_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
              . dirname($_SERVER['REQUEST_URI']) . '/view_invitation.php?token=' . $token_val;
    // Send emails if requested
    $email_sent = 0;
    $email_error = '';
    if (isset($_POST['send_emails']) && !empty($recipient_emails)) {
        $emails = array_map('trim', explode(',', $recipient_emails));
        $venue   = !empty($_POST['custom_location']) ? $_POST['custom_location'] : $booking['hall_name'];
        $address = !empty($_POST['custom_address'])  ? $_POST['custom_address']  : $booking['location'];
        $time_display = '';
        if (!empty($event_time)) {
            $tp = explode(':', $event_time);
            $h = intval($tp[0]); $m = $tp[1] ?? '00';
            $ap = $h >= 12 ? 'PM' : 'AM';
            $h12 = $h % 12 ?: 12;
            $time_display = str_pad($h12,2,'0',STR_PAD_LEFT).':'.$m.' '.$ap;
        }
        $date_display = date('l, d F Y', strtotime($booking['event_date']));
        $subject = "You're Invited: " . $booking['event_name'];
        $body = buildEmailHTML($booking['event_name'], $booking['event_type'] ?? '', $date_display, $time_display, $venue, $address, $inv_link, $_SESSION['user_name'], $extra_note, $dress_code);
        $headers  = "MIME-Version: 1.0
";
        $headers .= "Content-Type: text/html; charset=UTF-8
";
        $headers .= "From: Eventique <no-reply@eventique.com>
";
        $headers .= "Reply-To: " . $_SESSION['user_email'] . "
";
        foreach ($emails as $em) {
            if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                if (mail($em, $subject, $body, $headers)) $email_sent++;
                else $email_error = 'Some emails could not be sent. Check your server mail config.';
            }
        }
        if ($email_sent > 0)
            $success = "Invitation saved & emailed to $email_sent guest(s)!";
        else if ($email_error)
            $success = 'Invitation saved! ' . $email_error;
        else
            $success = 'Invitation saved! No valid emails found to send.';
    } else {
        $success = 'Invitation saved! Share the link below with your guests.';
    }
}

function buildEmailHTML($event_name, $event_type, $date, $time, $venue, $address, $link, $host, $note, $dress) {
    $time_row = $time ? "<tr><td style='padding:6px 12px;font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:#9e9080'>Time</td><td style='padding:6px 12px;font-size:13px;color:#1a1610;font-weight:500'>$time</td></tr>" : '';
    $dress_row = $dress ? "<tr><td style='padding:6px 12px;font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:#9e9080'>Dress Code</td><td style='padding:6px 12px;font-size:13px;color:#1a1610;font-weight:500'>$dress</td></tr>" : '';
    $note_html = $note ? "<p style='font-family:Georgia,serif;font-style:italic;font-size:13px;color:#6e6050;text-align:center;margin:20px 0;line-height:1.7'>".nl2br(htmlspecialchars($note))."</p>" : '';
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f0ebe0;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0ebe0;padding:40px 20px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #d4c9a8;max-width:600px">

  <!-- Gold top bar -->
  <tr><td style="height:4px;background:linear-gradient(90deg,transparent,#c9a84c,transparent)"></td></tr>

  <!-- Header -->
  <tr><td style="padding:36px 40px 20px;text-align:center;background:#0f0d0a">
    <div style="display:inline-block;width:40px;height:40px;border:1.5px solid #c9a84c;transform:rotate(45deg);margin-bottom:12px"></div>
    <div style="font-family:Georgia,serif;font-size:20px;letter-spacing:6px;text-transform:uppercase;color:#f2ece0">EVENTIQUE</div>
    <div style="font-size:8px;letter-spacing:4px;text-transform:uppercase;color:#c9a84c;margin-top:4px">Curated Event Experiences</div>
  </td></tr>

  <!-- Invite text -->
  <tr><td style="padding:32px 40px 8px;text-align:center">
    <div style="font-size:9px;letter-spacing:4px;text-transform:uppercase;color:#9e9080;margin-bottom:10px">You are cordially invited to</div>
    <div style="font-family:Georgia,serif;font-size:34px;font-weight:300;color:#1a1610;line-height:1.1;margin-bottom:6px">'.htmlspecialchars($event_name).'</div>
    <div style="font-size:9px;letter-spacing:3px;text-transform:uppercase;color:#9e9080">'.htmlspecialchars($event_type).'</div>
  </td></tr>

  <!-- Divider -->
  <tr><td style="padding:16px 40px"><div style="height:1px;background:linear-gradient(90deg,transparent,#c9a84c,transparent)"></div></td></tr>

  <!-- Details table -->
  <tr><td style="padding:0 40px 20px">
    <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8dfc8;background:#faf6ee">
      <tr><td style="padding:6px 12px;font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:#9e9080">Date</td><td style="padding:6px 12px;font-size:13px;color:#1a1610;font-weight:500">'.htmlspecialchars($date).'</td></tr>
      '.$time_row.'
      <tr><td style="padding:6px 12px;font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:#9e9080">Venue</td><td style="padding:6px 12px;font-size:13px;color:#1a1610;font-weight:500">'.htmlspecialchars($venue).'</td></tr>
      <tr><td style="padding:6px 12px;font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:#9e9080">Location</td><td style="padding:6px 12px;font-size:13px;color:#1a1610;font-weight:500">'.htmlspecialchars($address).'</td></tr>
      '.$dress_row.'
    </table>
  </td></tr>

  <!-- Note -->
  <tr><td style="padding:0 40px 20px">'.$note_html.'</td></tr>

  <!-- CTA Button -->
  <tr><td style="padding:0 40px 28px;text-align:center">
    <a href="'.htmlspecialchars($link).'" style="display:inline-block;background:#c9a84c;color:#0b0908;font-size:11px;letter-spacing:3px;text-transform:uppercase;padding:14px 32px;text-decoration:none;font-weight:600">View Full Invitation →</a>
  </td></tr>

  <!-- Hosted by -->
  <tr><td style="padding:16px 40px;text-align:center;border-top:1px solid #e8dfc8">
    <div style="font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#9e9080">Hosted by</div>
    <div style="font-family:Georgia,serif;font-size:15px;color:#1a1610;margin-top:4px">'.htmlspecialchars($host).'</div>
  </td></tr>

  <!-- Gold bottom bar -->
  <tr><td style="height:4px;background:linear-gradient(90deg,transparent,#c9a84c,transparent)"></td></tr>

</table>
</td></tr>
</table>
</body></html>';
}

// Add custom_location / custom_address columns if missing (safe migration)
$cols = $conn->query("SHOW COLUMNS FROM invitations LIKE 'custom_location'")->num_rows;
if ($cols === 0) $conn->query("ALTER TABLE invitations ADD COLUMN custom_location VARCHAR(255) DEFAULT NULL");
$cols2 = $conn->query("SHOW COLUMNS FROM invitations LIKE 'custom_address'")->num_rows;
if ($cols2 === 0) $conn->query("ALTER TABLE invitations ADD COLUMN custom_address VARCHAR(255) DEFAULT NULL");

// Load existing invitation if any
$inv = null;
$load = $conn->prepare("SELECT * FROM invitations WHERE booking_id = ?");
$load->bind_param("i", $booking_id);
$load->execute();
$inv = $load->get_result()->fetch_assoc();
if ($inv && !$inv_link) {
    $inv_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
              . dirname($_SERVER['REQUEST_URI']) . '/view_invitation.php?token=' . $inv['token'];
}

$pending = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$user_id AND status='pending'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventique — Send Invitation</title>
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
  --green:#4dbd8a;--amber:#e8a83a;--rose:#d06878;
  --border:rgba(201,168,76,.15);--bsub:rgba(255,255,255,.055);--sw:256px;
}
html,body{height:100%;overflow:hidden}
body{font-family:'Barlow',sans-serif;background:var(--ink);color:var(--ivory);display:flex;min-height:100vh}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.032'/%3E%3C/svg%3E")}

/* ── Sidebar ── */
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

/* ── Main ── */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:28px 40px 0;flex-shrink:0}
.tb-left h1{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;color:var(--ivory);line-height:1}
.tb-left h1 em{font-style:italic;color:var(--goldl)}
.tb-sub{font-size:10px;letter-spacing:.14em;color:var(--ivory3);margin-top:5px;text-transform:uppercase}
.tb-back{display:flex;align-items:center;gap:8px;border:1px solid var(--bsub);color:var(--ivory2);font-family:'Barlow',sans-serif;font-size:10px;letter-spacing:.14em;text-transform:uppercase;padding:0 14px;height:34px;text-decoration:none;transition:all .2s}
.tb-back:hover{border-color:var(--gold);color:var(--gold)}
.topbar-rule{height:1px;background:var(--bsub);margin:20px 40px 0}
.content{flex:1;overflow-y:auto;padding:28px 40px 48px}
.content::-webkit-scrollbar{width:3px}
.content::-webkit-scrollbar-thumb{background:var(--border)}

/* ── Alerts ── */
.alert{padding:14px 20px;margin-bottom:24px;font-size:12.5px;border-left:2px solid;display:flex;align-items:flex-start;gap:12px}
.alert-success{background:rgba(77,189,138,.08);border-color:var(--green);color:var(--green)}
.alert-danger{background:rgba(208,104,120,.08);border-color:var(--rose);color:var(--rose)}
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ani{animation:up .4s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.1s}.d3{animation-delay:.15s}

/* ── Two-col layout ── */
.inv-layout{display:grid;grid-template-columns:1fr 420px;gap:24px;align-items:start}

/* ── Form Panel ── */
.form-panel{background:var(--ink3);border:1px solid var(--bsub)}
.panel-hdr{padding:20px 24px;border-bottom:1px solid var(--bsub);display:flex;align-items:center;gap:12px}
.panel-hdr-icon{width:32px;height:32px;background:var(--goldp);border:1px solid rgba(201,168,76,.25);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:13px;flex-shrink:0}
.panel-hdr-title{font-family:'Cormorant Garamond',serif;font-size:19px;font-weight:400;color:var(--ivory)}
.panel-hdr-sub{font-size:9.5px;letter-spacing:.1em;text-transform:uppercase;color:var(--ivory3);margin-top:3px}
.form-body{padding:22px 24px 24px}
.fg{margin-bottom:20px}
.fg label{display:block;font-size:9px;letter-spacing:.25em;text-transform:uppercase;color:var(--ivory3);margin-bottom:9px}
.fg input,.fg select,.fg textarea{width:100%;background:none;border:none;border-bottom:1px solid var(--bsub);color:var(--ivory);font-family:'Barlow',sans-serif;font-size:13.5px;font-weight:300;padding:9px 0;outline:none;transition:border-color .2s;resize:none}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-bottom-color:var(--gold)}
.fg input::placeholder,.fg textarea::placeholder{color:rgba(110,101,96,.4)}
.fg select option{background:var(--ink3)}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fg-hint{font-size:10px;color:var(--ivory3);margin-top:5px}
.sec-divider{font-size:8.5px;letter-spacing:.28em;text-transform:uppercase;color:var(--gold);padding:6px 0 14px;border-bottom:1px solid var(--bsub);margin-bottom:20px;display:flex;align-items:center;gap:10px}
.sec-divider::after{content:'';flex:1;height:1px;background:var(--bsub)}
.submit-btn{width:100%;background:none;border:1px solid var(--gold);color:var(--gold);font-family:'Barlow',sans-serif;font-size:10.5px;letter-spacing:.28em;text-transform:uppercase;padding:15px;cursor:pointer;transition:all .3s;position:relative;overflow:hidden;margin-top:4px}
.submit-btn::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.submit-btn:hover::before{transform:scaleX(1)}
.submit-btn:hover{color:var(--ink)}
.submit-btn span{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:10px}

/* ── Share Link Box ── */
.share-box{background:var(--goldp);border:1px solid rgba(201,168,76,.3);padding:16px 18px;margin-bottom:20px}
.share-box-label{font-size:9px;letter-spacing:.24em;text-transform:uppercase;color:var(--goldl);margin-bottom:10px}
.share-row{display:flex;gap:8px;align-items:center}
.share-input{flex:1;background:rgba(0,0,0,.25);border:1px solid var(--border);color:var(--goldl);font-family:'Barlow',sans-serif;font-size:11.5px;padding:9px 12px;outline:none;font-weight:300;letter-spacing:.02em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.share-copy-btn{background:none;border:1px solid var(--gold);color:var(--gold);font-family:'Barlow',sans-serif;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:9px 14px;cursor:pointer;white-space:nowrap;transition:all .25s;flex-shrink:0}
.share-copy-btn:hover{background:var(--gold);color:var(--ink)}
.share-actions{display:flex;gap:8px;margin-top:10px}
.share-action-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:7px;border:1px solid var(--border);background:none;color:var(--ivory2);font-family:'Barlow',sans-serif;font-size:9.5px;letter-spacing:.14em;text-transform:uppercase;padding:9px;cursor:pointer;text-decoration:none;transition:all .22s}
.share-action-btn:hover{border-color:var(--gold);color:var(--gold)}
.share-action-btn i{font-size:11px}

/* ── Preview Panel ── */
.preview-panel{position:sticky;top:0}
.preview-label{font-size:9px;letter-spacing:.28em;text-transform:uppercase;color:var(--ivory3);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.preview-label::after{content:'LIVE PREVIEW';font-size:7.5px;letter-spacing:.18em;background:var(--goldp);border:1px solid var(--border);color:var(--gold);padding:2px 7px}

/* ── Invitation Card ── */
.inv-card{
  background:var(--ink2);
  border:1px solid var(--border);
  position:relative;
  overflow:hidden;
  padding:36px 32px;
}
.inv-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,transparent,var(--gold),transparent);
}
.inv-card::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,transparent,var(--gold),transparent);
}
/* decorative corner diamonds */
.inv-corner{position:absolute;width:18px;height:18px;border:1px solid rgba(201,168,76,.25);transform:rotate(45deg)}
.inv-corner.tl{top:12px;left:12px}
.inv-corner.tr{top:12px;right:12px}
.inv-corner.bl{bottom:12px;left:12px}
.inv-corner.br{bottom:12px;right:12px}
.inv-org-logo{display:flex;flex-direction:column;align-items:center;margin-bottom:20px}
.inv-gem{width:40px;height:40px;border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:14px;transform:rotate(45deg);margin-bottom:10px}
.inv-gem i{transform:rotate(-45deg)}
.inv-org-name{font-family:'Cormorant Garamond',serif;font-size:16px;letter-spacing:.18em;text-transform:uppercase;color:var(--ivory)}
.inv-org-tag{font-size:8px;letter-spacing:.3em;text-transform:uppercase;color:var(--gold);margin-top:3px}
.inv-divider{width:40px;height:1px;background:var(--gold);margin:16px auto;opacity:.5}
.inv-eyebrow{text-align:center;font-size:8.5px;letter-spacing:.3em;text-transform:uppercase;color:var(--ivory3);margin-bottom:6px}
.inv-headline{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;text-align:center;color:var(--ivory);line-height:1.15;margin-bottom:4px}
.inv-headline em{font-style:italic;color:var(--goldl)}
.inv-event-type{text-align:center;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);margin-bottom:20px}
.inv-details-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--bsub);border:1px solid var(--bsub);margin-bottom:18px}
.inv-det{background:var(--ink3);padding:12px 14px}
.inv-det-label{font-size:7.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--gold);margin-bottom:4px}
.inv-det-val{font-size:12.5px;color:var(--ivory);font-weight:400;line-height:1.3}
.inv-det-val.serif{font-family:'Cormorant Garamond',serif;font-size:15px}
.inv-agenda-block{background:var(--ink4);border:1px solid var(--bsub);padding:14px 16px;margin-bottom:14px}
.inv-agenda-lbl{font-size:7.5px;letter-spacing:.24em;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.inv-agenda-txt{font-size:12px;color:var(--ivory2);line-height:1.7;font-weight:300}
.inv-speaker{display:flex;align-items:center;gap:12px;background:var(--ink4);border:1px solid var(--bsub);padding:12px 16px;margin-bottom:14px}
.inv-speaker-icon{width:34px;height:34px;border:1px solid rgba(201,168,76,.3);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:12px;flex-shrink:0}
.inv-speaker-name{font-size:13px;color:var(--ivory);font-weight:500}
.inv-speaker-title{font-size:10px;color:var(--ivory3);margin-top:2px;letter-spacing:.06em}
.inv-footer-note{text-align:center;font-size:11px;color:var(--ivory3);font-style:italic;font-family:'Cormorant Garamond',serif;font-weight:300;line-height:1.6;padding:14px 0 4px;border-top:1px solid var(--bsub)}
.inv-rsvp{text-align:center;margin-top:10px}
.inv-rsvp-label{font-size:8px;letter-spacing:.22em;text-transform:uppercase;color:var(--ivory3);margin-bottom:4px}
.inv-rsvp-date{font-family:'Cormorant Garamond',serif;font-size:15px;color:var(--goldl)}
.inv-hosted{text-align:center;margin-top:16px;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:var(--ivory3)}
.inv-hosted span{color:var(--ivory2)}


/* ── Calendar Picker ── */
.cal-input-wrap{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--bsub);padding:9px 0;cursor:pointer;transition:border-color .2s}
.cal-input-wrap:hover{border-bottom-color:var(--gold)}
#cal-display{font-family:'Barlow',sans-serif;font-size:13.5px;font-weight:300;color:var(--ivory)}
#cal-display.placeholder{color:rgba(110,101,96,.4)}
.cal-icon{color:var(--ivory3);font-size:13px;transition:color .2s}
.cal-input-wrap:hover .cal-icon{color:var(--gold)}
.cal-popup{position:absolute;top:calc(100% + 6px);right:0;width:260px;background:var(--ink3);border:1px solid rgba(201,168,76,.3);z-index:999;padding:14px;box-shadow:0 8px 32px rgba(0,0,0,.5)}
.cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.cal-nav-btn{background:none;border:1px solid var(--bsub);color:var(--ivory2);width:26px;height:26px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:10px;transition:all .2s}
.cal-nav-btn:hover{border-color:var(--gold);color:var(--gold)}
.cal-month-label{font-family:'Cormorant Garamond',serif;font-size:15px;color:var(--ivory);letter-spacing:.08em}
.cal-dow{display:grid;grid-template-columns:repeat(7,1fr);margin-bottom:6px}
.cal-dow span{text-align:center;font-size:8.5px;letter-spacing:.1em;color:var(--ivory3);padding:3px 0;text-transform:uppercase}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px}
.cal-day{background:none;border:none;color:var(--ivory2);font-family:'Barlow',sans-serif;font-size:12px;padding:6px 0;cursor:pointer;text-align:center;transition:all .18s;border-radius:0}
.cal-day:hover:not(:disabled){background:var(--goldp);color:var(--gold)}
.cal-day.today{color:var(--goldl);font-weight:600}
.cal-day.selected{background:var(--gold);color:var(--ink);font-weight:600}
.cal-day.other-month{color:rgba(110,101,96,.3)}
.cal-day:disabled{opacity:.25;cursor:not-allowed}
.cal-clear{width:100%;margin-top:10px;background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'Barlow',sans-serif;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:7px;cursor:pointer;transition:all .2s}
.cal-clear:hover{border-color:var(--rose);color:var(--rose)}


/* ── Time Picker ── */
.time-popup{width:200px !important}
.time-picker-wrap{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 0 14px}
.time-col{display:flex;flex-direction:column;align-items:center;gap:6px}
.time-arrow{background:none;border:1px solid var(--bsub);color:var(--ivory2);width:32px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:10px;transition:all .2s}
.time-arrow:hover{border-color:var(--gold);color:var(--gold)}
.time-val{font-family:'Cormorant Garamond',serif;font-size:28px;color:var(--ivory);min-width:44px;text-align:center;line-height:1}
.time-sep{font-family:'Cormorant Garamond',serif;font-size:28px;color:var(--gold);padding-bottom:10px}
.ampm-col .time-val{font-size:16px;min-width:36px;color:var(--goldl)}


/* ── Email toggle ── */
.toggle-wrap{position:relative;flex-shrink:0}
.toggle-track{width:36px;height:20px;background:var(--bsub);border:1px solid var(--border);cursor:pointer;position:relative;transition:background .25s}
.toggle-track.on{background:rgba(77,189,138,.25);border-color:var(--green)}
.toggle-thumb{position:absolute;top:3px;left:3px;width:12px;height:12px;background:var(--ivory3);transition:all .25s}
.toggle-track.on .toggle-thumb{left:19px;background:var(--green)}
.toggle-label{font-size:11px;color:var(--ivory2);letter-spacing:.04em}

/* ── Responsive ── */
@media(max-width:1100px){.inv-layout{grid-template-columns:1fr}}
@media(max-width:900px){
  .sidebar{display:none}
  .topbar,.topbar-rule,.content{padding-left:20px;padding-right:20px}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-diamond"><i class="fas fa-gem"></i></div>
      <span class="sb-name">Eventique</span>
    </div>
    <div class="sb-sub">My Account</div>
  </div>
  <span class="sb-section">Navigation</span>
  <a href="dashboard.php"  class="sb-link"><i class="fas fa-chart-tree-map"></i> Dashboard</a>
  <a href="halls.php"      class="sb-link"><i class="fas fa-building-columns"></i> Browse Halls</a>
  <a href="vendors.php"    class="sb-link"><i class="fas fa-store"></i> Vendors</a>
  <a href="bookings.php"   class="sb-link active">
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

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <div class="tb-left">
      <h1>Send <em>Invitation</em></h1>
      <div class="tb-sub"><?= htmlspecialchars($booking['event_name']) ?> &nbsp;·&nbsp; <?= date('d M Y', strtotime($booking['event_date'])) ?></div>
    </div>
    <a href="bookings.php" class="tb-back"><i class="fas fa-arrow-left"></i> My Bookings</a>
  </div>
  <div class="topbar-rule"></div>

  <div class="content">

    <?php if ($success): ?>
      <div class="alert alert-success ani"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger ani"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($inv_link || $inv): ?>
    <div class="share-box ani">
      <div class="share-box-label"><i class="fas fa-link" style="margin-right:6px"></i>Shareable Invitation Link</div>
      <div class="share-row">
        <input class="share-input" id="inv-link-input" type="text" value="<?= htmlspecialchars($inv_link ?: (
          (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/view_invitation.php?token='.($inv['token']??'')
        )) ?>" readonly>
        <button class="share-copy-btn" onclick="copyLink()"><i class="fas fa-copy"></i> Copy</button>
      </div>
      <div class="share-actions">
        <a href="view_invitation.php?token=<?= $inv['token'] ?? '' ?>" target="_blank" class="share-action-btn"><i class="fas fa-eye"></i> Preview</a>
        <a href="print_invitation.php?token=<?= $inv['token'] ?? '' ?>" target="_blank" class="share-action-btn"><i class="fas fa-print"></i> Print / PDF</a>
        <a href="mailto:?subject=You're Invited — <?= urlencode($booking['event_name']) ?>&body=<?= urlencode('You are invited to '.$booking['event_name'].'. View your invitation: '.$inv_link) ?>" class="share-action-btn"><i class="fas fa-envelope"></i> Email</a>
      </div>
    </div>
    <?php endif; ?>

    <div class="inv-layout">

      <!-- FORM -->
      <div class="form-panel ani d1">
        <div class="panel-hdr">
          <div class="panel-hdr-icon"><i class="fas fa-envelope-open-text"></i></div>
          <div>
            <div class="panel-hdr-title">Invitation Details</div>
            <div class="panel-hdr-sub">These will appear on the invitation card</div>
          </div>
        </div>
        <div class="form-body">
          <form method="POST" action="" id="inv-form">

            <div class="sec-divider">Event Information</div>

            <div class="fg-row">
              <div class="fg" style="position:relative">
                <label>Event Time</label>
                <div class="cal-input-wrap" onclick="toggleTime()" id="time-trigger">
                  <span id="time-display"><?php
                    if (!empty($inv['event_time'])) {
                      $tp = explode(':', $inv['event_time']);
                      $h = intval($tp[0]); $m = $tp[1] ?? '00';
                      $ap = $h >= 12 ? 'PM' : 'AM';
                      $h12 = $h % 12 ?: 12;
                      echo str_pad($h12,2,'0',STR_PAD_LEFT).':'.$m.' '.$ap;
                    } else { echo 'Select time'; }
                  ?></span>
                  <i class="fas fa-clock cal-icon"></i>
                </div>
                <input type="hidden" name="event_time" id="f-time" value="<?= htmlspecialchars($inv['event_time'] ?? '') ?>">
                <!-- Time Picker Popup -->
                <div class="cal-popup time-popup" id="time-popup" style="display:none">
                  <div class="time-picker-wrap">
                    <div class="time-col">
                      <button type="button" class="time-arrow" onclick="changeHour(1)"><i class="fas fa-chevron-up"></i></button>
                      <div class="time-val" id="tp-hour">12</div>
                      <button type="button" class="time-arrow" onclick="changeHour(-1)"><i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="time-sep">:</div>
                    <div class="time-col">
                      <button type="button" class="time-arrow" onclick="changeMin(5)"><i class="fas fa-chevron-up"></i></button>
                      <div class="time-val" id="tp-min">00</div>
                      <button type="button" class="time-arrow" onclick="changeMin(-5)"><i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="time-col ampm-col">
                      <button type="button" class="time-arrow" onclick="toggleAMPM()"><i class="fas fa-chevron-up"></i></button>
                      <div class="time-val" id="tp-ampm">AM</div>
                      <button type="button" class="time-arrow" onclick="toggleAMPM()"><i class="fas fa-chevron-down"></i></button>
                    </div>
                  </div>
                  <button type="button" class="cal-clear" onclick="confirmTime()">
                    <i class="fas fa-check" style="margin-right:6px"></i>Set Time
                  </button>
                </div>
              </div>
              <div class="fg" style="position:relative">
                <label>RSVP Deadline</label>
                <div class="cal-input-wrap" onclick="toggleCal()" id="cal-trigger">
                  <span id="cal-display"><?= $inv && $inv['rsvp_deadline'] ? date('d M Y', strtotime($inv['rsvp_deadline'])) : 'Select date' ?></span>
                  <i class="fas fa-calendar-days cal-icon"></i>
                </div>
                <input type="hidden" name="rsvp_deadline" id="f-rsvp" value="<?= htmlspecialchars($inv['rsvp_deadline'] ?? '') ?>">
                <!-- Calendar Popup -->
                <div class="cal-popup" id="cal-popup" style="display:none">
                  <div class="cal-nav">
                    <button type="button" class="cal-nav-btn" onclick="calMove(-1)"><i class="fas fa-chevron-left"></i></button>
                    <span class="cal-month-label" id="cal-month-label"></span>
                    <button type="button" class="cal-nav-btn" onclick="calMove(1)"><i class="fas fa-chevron-right"></i></button>
                  </div>
                  <div class="cal-dow">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                  </div>
                  <div class="cal-grid" id="cal-grid"></div>
                </div>
              </div>
            </div>

            <div class="fg-row">
              <div class="fg">
                <label>Venue Name <span style="opacity:.6;letter-spacing:0;text-transform:none;font-size:9px">(optional override)</span></label>
                <input type="text" name="custom_location" id="f-location"
                  placeholder="<?= htmlspecialchars($booking['hall_name']) ?>"
                  value="<?= htmlspecialchars($inv['custom_location'] ?? '') ?>"
                  oninput="syncPreview()">
                <div class="fg-hint"><i class="fas fa-circle-info" style="margin-right:4px"></i>Default: <?= htmlspecialchars($booking['hall_name'].', '.$booking['location']) ?></div>
              </div>
              <div class="fg">
                <label>Full Address <span style="opacity:.6;letter-spacing:0;text-transform:none;font-size:9px">(optional override)</span></label>
                <input type="text" name="custom_address" id="f-address"
                  placeholder="<?= htmlspecialchars($booking['location']) ?>"
                  value="<?= htmlspecialchars($inv['custom_address'] ?? '') ?>"
                  oninput="syncPreview()">
              </div>
            </div>

            <div class="fg">
              <label>Meeting / Event Agenda</label>
              <textarea name="meeting_agenda" id="f-agenda" rows="3"
                placeholder="Describe the agenda, purpose, or programme of the event…"
                oninput="syncPreview()"><?= htmlspecialchars($inv['meeting_agenda'] ?? '') ?></textarea>
            </div>

            <div class="fg">
              <label>Dress Code <span style="opacity:.6;letter-spacing:0;text-transform:none;font-size:9px">(optional)</span></label>
              <select name="dress_code" id="f-dress" onchange="syncPreview()">
                <option value="">None specified</option>
                <?php foreach(['Formal','Semi-Formal','Business Casual','Smart Casual','Casual','Traditional','Black Tie','White Tie'] as $dc): ?>
                <option value="<?= $dc ?>" <?= ($inv['dress_code']??'')===$dc?'selected':'' ?>><?= $dc ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="sec-divider">Speaker / Guest of Honour</div>

            <div class="fg-row">
              <div class="fg">
                <label>Speaker / Guest Name</label>
                <input type="text" name="speaker_name" id="f-speaker"
                  placeholder="e.g. Dr. Arun Kumar"
                  value="<?= htmlspecialchars($inv['speaker_name'] ?? '') ?>"
                  oninput="syncPreview()">
              </div>
              <div class="fg">
                <label>Title / Designation</label>
                <input type="text" name="speaker_title" id="f-speaker-title"
                  placeholder="e.g. Chief Executive Officer"
                  value="<?= htmlspecialchars($inv['speaker_title'] ?? '') ?>"
                  oninput="syncPreview()">
              </div>
            </div>

            <div class="sec-divider">Additional Details</div>

            <div class="fg">
              <label>Special Note to Guests</label>
              <textarea name="extra_note" id="f-note" rows="2"
                placeholder="e.g. All are requested to attend the meeting."
                oninput="syncPreview()"><?= htmlspecialchars($inv['extra_note'] ?? '') ?></textarea>
            </div>

            <div class="fg">
              <label>Recipient Emails <span style="opacity:.6;letter-spacing:0;text-transform:none;font-size:9px">(optional, comma separated)</span></label>
              <input type="text" name="recipient_emails" id="f-emails"
                placeholder="alice@example.com, bob@example.com"
                value="<?= htmlspecialchars($inv['recipient_emails'] ?? '') ?>">
              <div class="fg-hint"><i class="fas fa-circle-info" style="margin-right:4px"></i>Separate multiple emails with commas.</div>
              <label class="email-send-toggle" style="margin-top:12px;display:flex;align-items:center;gap:10px;cursor:pointer">
                <div class="toggle-wrap">
                  <input type="checkbox" name="send_emails" id="send_emails" value="1" style="display:none" <?= (isset($_POST['send_emails'])) ? 'checked' : '' ?>>
                  <div class="toggle-track" id="toggle-track"><div class="toggle-thumb"></div></div>
                </div>
                <span class="toggle-label">Send invitation email to recipients when saving</span>
              </label>
            </div>

            <button type="submit" name="save_invitation" class="submit-btn">
              <span><i class="fas fa-paper-plane"></i>
                <?= $inv ? 'Update Invitation' : 'Generate Invitation' ?>
              </span>
            </button>

          </form>
        </div>
      </div>

      <!-- LIVE PREVIEW -->
      <div class="preview-panel ani d2">
        <div class="preview-label">Invitation Card</div>

        <div class="inv-card" id="inv-card">
          <!-- decorative corners -->
          <div class="inv-corner tl"></div>
          <div class="inv-corner tr"></div>
          <div class="inv-corner bl"></div>
          <div class="inv-corner br"></div>

          <!-- Header / Org -->
          <div class="inv-org-logo">
            <div class="inv-gem"><i class="fas fa-gem"></i></div>
            <div class="inv-org-name">Eventique</div>
            <div class="inv-org-tag">Curated Event Experiences</div>
          </div>

          <div class="inv-divider"></div>

          <div class="inv-eyebrow">You are cordially invited to</div>
          <div class="inv-headline" id="p-event-name">
            <?= htmlspecialchars($booking['event_name']) ?>
          </div>
          <div class="inv-event-type" id="p-event-type"><?= htmlspecialchars($booking['event_type']) ?></div>

          <!-- Details grid -->
          <div class="inv-details-grid">
            <div class="inv-det">
              <div class="inv-det-label"><i class="fas fa-calendar" style="margin-right:4px"></i>Date</div>
              <div class="inv-det-val serif"><?= date('d M Y', strtotime($booking['event_date'])) ?></div>
            </div>
            <div class="inv-det">
              <div class="inv-det-label"><i class="fas fa-clock" style="margin-right:4px"></i>Time</div>
              <div class="inv-det-val serif" id="p-time"><?= $inv ? date('h:i A', strtotime($inv['event_time'])) : '—' ?></div>
            </div>
            <div class="inv-det">
              <div class="inv-det-label"><i class="fas fa-building-columns" style="margin-right:4px"></i>Venue</div>
              <div class="inv-det-val" id="p-hall"><?= htmlspecialchars($booking['hall_name']) ?></div>
            </div>
            <div class="inv-det">
              <div class="inv-det-label"><i class="fas fa-location-dot" style="margin-right:4px"></i>Location</div>
              <div class="inv-det-val" id="p-location"><?= htmlspecialchars($booking['location']) ?></div>
            </div>
            <?php if(!empty($inv['dress_code'])): ?>
            <div class="inv-det" style="grid-column:1/-1" id="p-dress-row">
              <div class="inv-det-label"><i class="fas fa-shirt" style="margin-right:4px"></i>Dress Code</div>
              <div class="inv-det-val" id="p-dress"><?= htmlspecialchars($inv['dress_code']) ?></div>
            </div>
            <?php else: ?>
            <div class="inv-det" style="grid-column:1/-1;display:none" id="p-dress-row">
              <div class="inv-det-label"><i class="fas fa-shirt" style="margin-right:4px"></i>Dress Code</div>
              <div class="inv-det-val" id="p-dress"></div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Agenda -->
          <div class="inv-agenda-block" id="p-agenda-block" style="<?= empty($inv['meeting_agenda']) ? 'display:none' : '' ?>">
            <div class="inv-agenda-lbl"><i class="fas fa-list-check" style="margin-right:5px"></i>Agenda</div>
            <div class="inv-agenda-txt" id="p-agenda"><?= nl2br(htmlspecialchars($inv['meeting_agenda'] ?? '')) ?></div>
          </div>

          <!-- Speaker -->
          <div class="inv-speaker" id="p-speaker-block" style="<?= empty($inv['speaker_name']) ? 'display:none' : '' ?>">
            <div class="inv-speaker-icon"><i class="fas fa-microphone-lines"></i></div>
            <div>
              <div class="inv-speaker-name" id="p-speaker"><?= htmlspecialchars($inv['speaker_name'] ?? '') ?></div>
              <div class="inv-speaker-title" id="p-speaker-title"><?= htmlspecialchars($inv['speaker_title'] ?? '') ?></div>
            </div>
          </div>

          <!-- Footer note -->
          <div class="inv-footer-note" id="p-note"><?= nl2br(htmlspecialchars($inv['extra_note'] ?? 'All are requested to attend the event.')) ?></div>

          <!-- RSVP -->
          <div class="inv-rsvp" id="p-rsvp-block" style="<?= empty($inv['rsvp_deadline']) ? 'display:none' : '' ?>">
            <div class="inv-rsvp-label">Please RSVP by</div>
            <div class="inv-rsvp-date" id="p-rsvp"><?= $inv && $inv['rsvp_deadline'] ? date('d M Y', strtotime($inv['rsvp_deadline'])) : '' ?></div>
          </div>

          <!-- Hosted by -->
          <div class="inv-hosted">Hosted by &nbsp;<span><?= htmlspecialchars($_SESSION['user_name']) ?></span></div>
        </div>
      </div>

    </div>
  </div>
</main>

<script>
// ── Live preview sync ─────────────────────────────────────
function syncPreview() {
  const timeVal    = document.getElementById('f-time').value;
  const agendaVal  = document.getElementById('f-agenda').value;
  const speakerVal = document.getElementById('f-speaker').value;
  const stitleVal  = document.getElementById('f-speaker-title').value;
  const noteVal    = document.getElementById('f-note').value;
  const rsvpVal    = document.getElementById('f-rsvp').value;
  const dressVal   = document.getElementById('f-dress').value;
  const locVal     = document.getElementById('f-location').value;
  const addrVal    = document.getElementById('f-address').value;

  // Location / Venue
  document.getElementById('p-hall').textContent     = locVal.trim()  || <?= json_encode($booking['hall_name']) ?>;
  document.getElementById('p-location').textContent = addrVal.trim() || <?= json_encode($booking['location']) ?>;

  // Time
  if (timeVal) {
    const [h,m] = timeVal.split(':');
    const hr = parseInt(h), ampm = hr >= 12 ? 'PM' : 'AM';
    const h12 = hr % 12 || 12;
    document.getElementById('p-time').textContent = h12 + ':' + m + ' ' + ampm;
  } else {
    document.getElementById('p-time').textContent = '—';
  }

  // Agenda
  const agendaBlock = document.getElementById('p-agenda-block');
  if (agendaVal.trim()) {
    document.getElementById('p-agenda').innerHTML = agendaVal.replace(/\n/g,'<br>');
    agendaBlock.style.display = 'block';
  } else {
    agendaBlock.style.display = 'none';
  }

  // Speaker
  const speakerBlock = document.getElementById('p-speaker-block');
  if (speakerVal.trim()) {
    document.getElementById('p-speaker').textContent = speakerVal;
    document.getElementById('p-speaker-title').textContent = stitleVal;
    speakerBlock.style.display = 'flex';
  } else {
    speakerBlock.style.display = 'none';
  }

  // Note
  document.getElementById('p-note').innerHTML = noteVal.trim()
    ? noteVal.replace(/\n/g,'<br>')
    : 'All are requested to attend the event.';

  // RSVP
  const rsvpBlock = document.getElementById('p-rsvp-block');
  if (rsvpVal) {
    const d = new Date(rsvpVal + 'T00:00:00');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    document.getElementById('p-rsvp').textContent =
      String(d.getDate()).padStart(2,'0') + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    rsvpBlock.style.display = 'block';
  } else {
    rsvpBlock.style.display = 'none';
  }

  // Dress code
  const dressRow = document.getElementById('p-dress-row');
  if (dressVal) {
    document.getElementById('p-dress').textContent = dressVal;
    dressRow.style.display = 'block';
  } else {
    dressRow.style.display = 'none';
  }
}

// Copy link
function copyLink() {
  const inp = document.getElementById('inv-link-input');
  if (!inp) return;
  inp.select();
  navigator.clipboard.writeText(inp.value).then(() => {
    const btn = document.querySelector('.share-copy-btn');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.style.background = 'var(--green)';
    btn.style.color = 'var(--ink)';
    btn.style.borderColor = 'var(--green)';
    setTimeout(() => {
      btn.innerHTML = orig;
      btn.style.background = '';
      btn.style.color = '';
      btn.style.borderColor = '';
    }, 2000);
  });
}



// ── Time Picker ─────────────────────────────────────────────
let tpHour = 12, tpMin = 0, tpAMPM = 'AM';

function toggleTime() {
  const popup = document.getElementById('time-popup');
  document.getElementById('cal-popup').style.display = 'none'; // close calendar if open
  if (popup.style.display === 'none') {
    // Init from existing value
    const val = document.getElementById('f-time').value;
    if (val) {
      const parts = val.split(':');
      let h = parseInt(parts[0]), m = parseInt(parts[1]);
      tpAMPM = h >= 12 ? 'PM' : 'AM';
      tpHour = h % 12 || 12;
      tpMin = m;
    }
    renderTime();
    popup.style.display = 'block';
  } else {
    popup.style.display = 'none';
  }
}

function renderTime() {
  document.getElementById('tp-hour').textContent  = String(tpHour).padStart(2,'0');
  document.getElementById('tp-min').textContent   = String(tpMin).padStart(2,'0');
  document.getElementById('tp-ampm').textContent  = tpAMPM;
}

function changeHour(dir) {
  tpHour += dir;
  if (tpHour > 12) tpHour = 1;
  if (tpHour < 1)  tpHour = 12;
  renderTime();
}

function changeMin(dir) {
  tpMin += dir;
  if (tpMin >= 60) tpMin = 0;
  if (tpMin < 0)   tpMin = 55;
  renderTime();
}

function toggleAMPM() {
  tpAMPM = tpAMPM === 'AM' ? 'PM' : 'AM';
  renderTime();
}

function confirmTime() {
  // Convert to 24h for hidden input
  let h24 = tpHour;
  if (tpAMPM === 'AM' && tpHour === 12) h24 = 0;
  if (tpAMPM === 'PM' && tpHour !== 12) h24 = tpHour + 12;
  const val24 = String(h24).padStart(2,'0') + ':' + String(tpMin).padStart(2,'0');
  document.getElementById('f-time').value = val24;
  // Display
  document.getElementById('time-display').textContent =
    String(tpHour).padStart(2,'0') + ':' + String(tpMin).padStart(2,'0') + ' ' + tpAMPM;
  document.getElementById('time-display').classList.remove('placeholder');
  document.getElementById('time-popup').style.display = 'none';
  syncPreview();
}

// ── Calendar Picker ──────────────────────────────────────────
let calDate = new Date();
const maxDate = new Date('<?= $booking["event_date"] ?>');

function toggleCal() {
  const popup = document.getElementById('cal-popup');
  if (popup.style.display === 'none') {
    // Init to selected date or today
    const sel = document.getElementById('f-rsvp').value;
    if (sel) calDate = new Date(sel + 'T00:00:00');
    else calDate = new Date();
    renderCal();
    popup.style.display = 'block';
  } else {
    popup.style.display = 'none';
  }
}

function calMove(dir) {
  calDate.setMonth(calDate.getMonth() + dir);
  renderCal();
}

function renderCal() {
  const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  document.getElementById('cal-month-label').textContent = months[calDate.getMonth()] + ' ' + calDate.getFullYear();

  const grid = document.getElementById('cal-grid');
  grid.innerHTML = '';

  const year = calDate.getFullYear();
  const month = calDate.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const today = new Date(); today.setHours(0,0,0,0);
  const selectedVal = document.getElementById('f-rsvp').value;

  // Blanks before first day
  for (let i = 0; i < firstDay; i++) {
    const blank = document.createElement('button');
    blank.type = 'button';
    blank.className = 'cal-day other-month';
    blank.disabled = true;
    grid.appendChild(blank);
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'cal-day';
    btn.textContent = d;

    const thisDate = new Date(year, month, d);
    thisDate.setHours(0,0,0,0);

    // Past dates disabled
    if (thisDate < today) { btn.disabled = true; btn.classList.add('other-month'); }
    // Beyond event date disabled
    if (thisDate > maxDate) { btn.disabled = true; btn.classList.add('other-month'); }
    // Today highlight
    if (thisDate.getTime() === today.getTime()) btn.classList.add('today');
    // Selected highlight
    const yyyy = year + '-' + String(month+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
    if (yyyy === selectedVal) btn.classList.add('selected');

    btn.onclick = () => selectCalDay(year, month, d);
    grid.appendChild(btn);
  }
}

function selectCalDay(year, month, day) {
  const yyyy = year + '-' + String(month+1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
  document.getElementById('f-rsvp').value = yyyy;

  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const disp = String(day).padStart(2,'0') + ' ' + months[month] + ' ' + year;
  const dispEl = document.getElementById('cal-display');
  dispEl.textContent = disp;
  dispEl.classList.remove('placeholder');

  document.getElementById('cal-popup').style.display = 'none';
  syncPreview();
}

// Close calendar when clicking outside
document.addEventListener('click', function(e) {
  const calPopup  = document.getElementById('cal-popup');
  const calTrig   = document.getElementById('cal-trigger');
  const timePopup = document.getElementById('time-popup');
  const timeTrig  = document.getElementById('time-trigger');
  if (calPopup  && !calPopup.contains(e.target)  && !calTrig.contains(e.target))  calPopup.style.display  = 'none';
  if (timePopup && !timePopup.contains(e.target) && !timeTrig.contains(e.target)) timePopup.style.display = 'none';
});

// Init display placeholder style
(function(){
  const v = document.getElementById('f-rsvp').value;
  const d = document.getElementById('cal-display');
  if (!v) d.classList.add('placeholder');
  const tv = document.getElementById('f-time').value;
  const td = document.getElementById('time-display');
  if (!tv) td.classList.add('placeholder');
})();


// Email send toggle
const sendEmailsCb = document.getElementById('send_emails');
const toggleTrack  = document.getElementById('toggle-track');
if (sendEmailsCb && toggleTrack) {
  if (sendEmailsCb.checked) toggleTrack.classList.add('on');
  toggleTrack.addEventListener('click', () => {
    sendEmailsCb.checked = !sendEmailsCb.checked;
    toggleTrack.classList.toggle('on', sendEmailsCb.checked);
  });
}

// Run once on load to sync any pre-filled values
syncPreview();
</script>
</body>
</html>
