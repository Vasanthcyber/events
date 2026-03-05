<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'user'")->fetch_assoc()['count'];
$total_vendors = $conn->query("SELECT COUNT(*) as count FROM vendors WHERE status = 'active'")->fetch_assoc()['count'];
$total_halls = $conn->query("SELECT COUNT(*) as count FROM halls")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'];

// Get recent bookings
$recent_bookings = $conn->query("SELECT b.*, u.name as user_name, h.name as hall_name 
                                  FROM bookings b 
                                  JOIN users u ON b.user_id = u.id 
                                  JOIN halls h ON b.hall_id = h.id 
                                  ORDER BY b.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
    <body class="admin-dashboard">
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
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?php echo $_SESSION['user_name']; ?>!</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_vendors; ?></h3>
                        <p>Active Vendors</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_halls; ?></h3>
                        <p>Total Halls</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_bookings; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-between align-center mb-3">
                        <h3>Recent Bookings</h3>
                        <a href="bookings.php" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Hall</th>
                                    <th>Event Name</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_bookings->num_rows > 0): ?>
                                    <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td><?php echo $booking['user_name']; ?></td>
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
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No bookings found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
