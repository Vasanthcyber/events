<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

// Get user bookings
$user_id = $_SESSION['user_id'];
$bookings = $conn->query("SELECT b.*, h.name as hall_name, h.location 
                          FROM bookings b 
                          JOIN halls h ON b.hall_id = h.id 
                          WHERE b.user_id = $user_id 
                          ORDER BY b.created_at DESC");

// Get booking statistics
$total_bookings = get_booking_count($conn, $user_id);
$confirmed_bookings = get_booking_count($conn, $user_id, 'confirmed');
$pending_bookings = get_booking_count($conn, $user_id, 'pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
    <body class="user-dashboard">
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
                <h1>Welcome, <?php echo $_SESSION['user_name']; ?>!</h1>
                <p>Manage your event bookings and explore our venues</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_bookings; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $confirmed_bookings; ?></h3>
                        <p>Confirmed</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $pending_bookings; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <h3 class="mb-3">Quick Actions</h3>
                    <div class="grid grid-2">
                        <a href="halls.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-building"></i> Browse Available Halls
                        </a>
                        <a href="vendors.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-store"></i> View Vendors
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="card mt-4">
                <div class="card-body">
                    <h3 class="mb-3">Recent Bookings</h3>
                    
                    <?php if ($bookings->num_rows > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Hall</th>
                                        <th>Event Name</th>
                                        <th>Event Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td><?php echo $booking['hall_name']; ?></td>
                                            <td><?php echo $booking['event_name']; ?></td>
                                            <td><?php echo format_date($booking['event_date']); ?></td>
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
                                            <td><?php echo format_currency($booking['total_amount']); ?></td>
                                            <td>
                                                <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary btn-sm">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                            <p style="color: var(--gray-500);">You haven't made any bookings yet.</p>
                            <a href="halls.php" class="btn btn-primary mt-2">Browse Halls</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
