<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

$success = '';
$error = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        $success = 'Booking status updated successfully!';
    } else {
        $error = 'Failed to update booking status.';
    }
}

// Get all bookings
$bookings = $conn->query("SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone, h.name as hall_name 
                          FROM bookings b 
                          JOIN users u ON b.user_id = u.id 
                          JOIN halls h ON b.hall_id = h.id 
                          ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-calendar-check"></i>
                Event Manager - Admin
            </a>
            <nav class="nav">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="halls.php"><i class="fas fa-building"></i> Halls</a>
                <a href="vendors.php"><i class="fas fa-store"></i> Vendors</a>
                <a href="bookings.php"><i class="fas fa-calendar-alt"></i> Bookings</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </div>
    
    <div class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Manage Bookings</h1>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Hall</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Guests</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): ?>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td>
                                        <strong><?php echo $booking['user_name']; ?></strong><br>
                                        <small><?php echo $booking['user_email']; ?></small>
                                    </td>
                                    <td><?php echo $booking['hall_name']; ?></td>
                                    <td>
                                        <strong><?php echo $booking['event_name']; ?></strong><br>
                                        <small><?php echo $booking['event_type']; ?></small>
                                    </td>
                                    <td><?php echo format_date($booking['event_date']); ?></td>
                                    <td><?php echo $booking['guests_count']; ?></td>
                                    <td><?php echo format_currency($booking['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $badge_class = '';
                                        switch ($booking['status']) {
                                            case 'confirmed':
                                                $badge_class = 'badge-success';
                                                break;
                                            case 'pending':
                                                $badge_class = 'badge-warning';
                                                break;
                                            case 'cancelled':
                                                $badge_class = 'badge-danger';
                                                break;
                                            default:
                                                $badge_class = 'badge-primary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] == 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" name="update_status" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
