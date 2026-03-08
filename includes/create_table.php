<?php
$conn = new mysqli('localhost', 'root', '', 'event_management');
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("<h2 style='color:red;font-family:sans-serif;padding:30px'>DB Error: " . $conn->connect_error . "</h2>");
}

// Drop if broken, recreate clean
$conn->query("DROP TABLE IF EXISTS invitations");

$sql = "CREATE TABLE invitations (
  id               INT           NOT NULL AUTO_INCREMENT,
  booking_id       INT           NOT NULL,
  user_id          INT           NOT NULL,
  token            VARCHAR(64)   NOT NULL,
  event_time       VARCHAR(20)   DEFAULT NULL,
  meeting_agenda   TEXT          DEFAULT NULL,
  speaker_name     VARCHAR(200)  DEFAULT NULL,
  speaker_title    VARCHAR(200)  DEFAULT NULL,
  dress_code       VARCHAR(100)  DEFAULT NULL,
  rsvp_deadline    VARCHAR(20)   DEFAULT NULL,
  extra_note       TEXT          DEFAULT NULL,
  recipient_emails TEXT          DEFAULT NULL,
  created_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_token (token),
  UNIQUE KEY uq_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "
    <div style='font-family:sans-serif;padding:40px;background:#0b0908;color:#f2ece0;min-height:100vh'>
        <h2 style='color:#4dbd8a'>✅ invitations table created successfully!</h2>
        <p style='color:#c9a84c;margin-top:20px'>Now go back and click the Invitation button — it will work.</p>
        <a href='index.php' style='display:inline-block;margin-top:20px;border:1px solid #c9a84c;color:#c9a84c;padding:12px 28px;text-decoration:none'>→ Go to Login</a>
        <p style='color:#d06878;margin-top:30px;font-size:13px'>⚠ DELETE this file from your server now!</p>
    </div>";
} else {
    echo "<h2 style='color:red;font-family:sans-serif;padding:30px'>❌ Error: " . $conn->error . "</h2>";
}
?>
