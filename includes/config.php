
Copy

<?php
// Session MUST start first — before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_management');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Reconnect if dropped
if (!$conn->ping()) {
    $conn->close();
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
}

// Auto-create invitations table if missing
$sql = "CREATE TABLE IF NOT EXISTS invitations (
  id INT NOT NULL AUTO_INCREMENT,
  booking_id INT NOT NULL,
  user_id INT NOT NULL,
  token VARCHAR(64) NOT NULL,
  event_time VARCHAR(20) DEFAULT NULL,
  meeting_agenda TEXT DEFAULT NULL,
  speaker_name VARCHAR(200) DEFAULT NULL,
  speaker_title VARCHAR(200) DEFAULT NULL,
  dress_code VARCHAR(100) DEFAULT NULL,
  rsvp_deadline VARCHAR(20) DEFAULT NULL,
  extra_note TEXT DEFAULT NULL,
  recipient_emails TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_token (token),
  UNIQUE KEY uq_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($sql);
?>