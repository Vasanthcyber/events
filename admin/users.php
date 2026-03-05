<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

// Get all users
$users = $conn->query("SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
                <h1>Manage Users</h1>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): 
                                $booking_count = get_booking_count($conn, $user['id']);
                            ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td><?php echo $user['name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['phone'] ?: 'N/A'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($user['created_at']); ?></td>
                                    <td><?php echo $booking_count; ?> bookings</td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
