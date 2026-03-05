<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

$user_id = $_SESSION['user_id'];

// Get user's bookings
$bookings = $conn->query("SELECT b.*, h.name as hall_name, h.location, h.image 
                          FROM bookings b 
                          JOIN halls h ON b.hall_id = h.id 
                          WHERE b.user_id = $user_id 
                          ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-calendar-check"></i>
                Event Manager
            </a>
            <nav class="nav">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="halls.php"><i class="fas fa-building"></i> Browse Halls</a>
                <a href="vendors.php"><i class="fas fa-store"></i> Vendors</a>
                <a href="bookings.php"><i class="fas fa-calendar-alt"></i> My Bookings</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </div>
    
    <div class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>My Bookings</h1>
                <a href="halls.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Booking
                </a>
            </div>
            
            <?php if ($bookings->num_rows > 0): ?>
                <div class="grid grid-2 mt-4">
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-between align-center mb-3">
                                    <h3 style="margin: 0;"><?php echo $booking['event_name']; ?></h3>
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
                                        case 'completed':
                                            $badge_class = 'badge-primary';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                
                                <div style="padding: 1rem; background: var(--gray-100); border-radius: 8px; margin-bottom: 1rem;">
                                    <p style="margin-bottom: 0.5rem;">
                                        <i class="fas fa-building"></i> 
                                        <strong>Venue:</strong> <?php echo $booking['hall_name']; ?>
                                    </p>
                                    <p style="margin-bottom: 0.5rem;">
                                        <i class="fas fa-calendar"></i> 
                                        <strong>Date:</strong> <?php echo format_date($booking['event_date']); ?>
                                    </p>
                                    <p style="margin-bottom: 0.5rem;">
                                        <i class="fas fa-tag"></i> 
                                        <strong>Type:</strong> <?php echo $booking['event_type']; ?>
                                    </p>
                                    <p style="margin-bottom: 0.5rem;">
                                        <i class="fas fa-users"></i> 
                                        <strong>Guests:</strong> <?php echo $booking['guests_count']; ?>
                                    </p>
                                    <p style="margin-bottom: 0.5rem;">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <strong>Location:</strong> <?php echo $booking['location']; ?>
                                    </p>
                                    <p style="margin-bottom: 0;">
                                        <i class="fas fa-dollar-sign"></i> 
                                        <strong>Amount:</strong> 
                                        <span style="color: var(--primary-color); font-size: 1.25rem; font-weight: 700;">
                                            <?php echo format_currency($booking['total_amount']); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <?php if ($booking['special_requirements']): ?>
                                    <div style="margin-bottom: 1rem;">
                                        <strong>Special Requirements:</strong>
                                        <p style="color: var(--gray-600); margin-top: 0.25rem;"><?php echo $booking['special_requirements']; ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="border-top: 1px solid var(--gray-300); padding-top: 1rem; margin-top: 1rem;">
                                    <small style="color: var(--gray-500);">
                                        <i class="fas fa-clock"></i> Booked on <?php echo format_date($booking['created_at']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card mt-4">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                        <h3>No Bookings Yet</h3>
                        <p style="color: var(--gray-500); margin-bottom: 1.5rem;">You haven't made any bookings yet. Start exploring our amazing venues!</p>
                        <a href="halls.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-building"></i> Browse Halls
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
