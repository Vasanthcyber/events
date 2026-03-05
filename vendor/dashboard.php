<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('vendor');

$user_id = $_SESSION['user_id'];

// Get vendor information
$vendor_info = $conn->query("SELECT * FROM vendors WHERE user_id = $user_id")->fetch_assoc();

// Get vendor bookings
$vendor_bookings = $conn->query("SELECT bv.*, b.event_name, b.event_date, u.name as customer_name, u.phone as customer_phone 
                                 FROM booking_vendors bv 
                                 JOIN bookings b ON bv.booking_id = b.id 
                                 JOIN users u ON b.user_id = u.id 
                                 WHERE bv.vendor_id = " . ($vendor_info['id'] ?? 0) . "
                                 ORDER BY b.event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
    <body class="vendor-dashboard">
    <div class="header">
        <div class="container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-store"></i>
                Event Manager - Vendor
            </a>
            <nav class="nav">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </div>
    
    <div class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Vendor Dashboard</h1>
                <p>Welcome, <?php echo $_SESSION['user_name']; ?>!</p>
            </div>
            
            <?php if ($vendor_info): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h3>Your Business Profile</h3>
                        <div class="grid grid-2 mt-3">
                            <div>
                                <p><strong>Business Name:</strong> <?php echo $vendor_info['business_name']; ?></p>
                                <p><strong>Service Type:</strong> <span class="badge badge-primary"><?php echo ucfirst($vendor_info['service_type']); ?></span></p>
                                <p><strong>Contact:</strong> <?php echo $vendor_info['contact_number']; ?></p>
                                <p><strong>Email:</strong> <?php echo $vendor_info['email']; ?></p>
                            </div>
                            <div>
                                <p><strong>Price Range:</strong> <?php echo $vendor_info['price_range']; ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge <?php echo $vendor_info['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($vendor_info['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Description:</strong> <?php echo $vendor_info['description']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-body">
                        <h3>Booking Requests</h3>
                        <?php if ($vendor_bookings->num_rows > 0): ?>
                            <div class="table-container mt-3">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Event Name</th>
                                            <th>Customer</th>
                                            <th>Contact</th>
                                            <th>Event Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($booking = $vendor_bookings->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $booking['event_name']; ?></td>
                                                <td><?php echo $booking['customer_name']; ?></td>
                                                <td><?php echo $booking['customer_phone']; ?></td>
                                                <td><?php echo format_date($booking['event_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = '';
                                                    switch ($booking['status']) {
                                                        case 'confirmed':
                                                            $badge_class = 'badge-success';
                                                            break;
                                                        case 'requested':
                                                            $badge_class = 'badge-warning';
                                                            break;
                                                        case 'declined':
                                                            $badge_class = 'badge-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center mt-3" style="color: var(--gray-500);">No booking requests yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-4">
                    Your vendor profile is not yet set up. Please contact the administrator.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
