<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

// Get all active vendors
$vendors = $conn->query("SELECT * FROM vendors WHERE status = 'active' ORDER BY service_type, business_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Vendors - Event Management</title>
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
                <h1>Event Service Vendors</h1>
                <p>Find the perfect vendors for your event</p>
            </div>
            
            <?php
            // Group vendors by service type
            $service_types = ['catering' => 'Catering Services', 
                            'decoration' => 'Decoration Services', 
                            'photography' => 'Photography Services', 
                            'music' => 'Music & DJ Services', 
                            'other' => 'Other Services'];
            
            foreach ($service_types as $type => $label):
                $type_vendors = $conn->query("SELECT * FROM vendors WHERE status = 'active' AND service_type = '$type' ORDER BY business_name");
                
                if ($type_vendors->num_rows > 0):
            ?>
                <div class="mt-4">
                    <h2 style="margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 3px solid var(--primary-color);">
                        <i class="fas fa-<?php 
                            echo $type == 'catering' ? 'utensils' : 
                                ($type == 'decoration' ? 'paint-brush' : 
                                ($type == 'photography' ? 'camera' : 
                                ($type == 'music' ? 'music' : 'concierge-bell'))); 
                        ?>"></i> 
                        <?php echo $label; ?>
                    </h2>
                    
                    <div class="grid grid-3">
                        <?php while ($vendor = $type_vendors->fetch_assoc()): ?>
                            <div class="card">
                                <?php if ($vendor['image']): ?>
                                    <img src="../assets/images/<?php echo $vendor['image']; ?>" alt="<?php echo $vendor['business_name']; ?>" class="card-img">
                                <?php else: ?>
                                    <div style="height: 200px; background: linear-gradient(135deg, #ec4899, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                        <i class="fas fa-store"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo $vendor['business_name']; ?></h3>
                                    <p class="card-text"><?php echo $vendor['description'] ?: 'Professional service provider for your events'; ?></p>
                                    
                                    <div style="margin: 1rem 0; padding: 1rem; background: var(--gray-100); border-radius: 8px;">
                                        <p style="margin-bottom: 0.5rem;">
                                            <i class="fas fa-phone"></i> 
                                            <strong>Contact:</strong> 
                                            <a href="tel:<?php echo $vendor['contact_number']; ?>" style="color: var(--primary-color);">
                                                <?php echo $vendor['contact_number']; ?>
                                            </a>
                                        </p>
                                        <p style="margin-bottom: 0.5rem;">
                                            <i class="fas fa-envelope"></i> 
                                            <strong>Email:</strong> 
                                            <a href="mailto:<?php echo $vendor['email']; ?>" style="color: var(--primary-color);">
                                                <?php echo $vendor['email']; ?>
                                            </a>
                                        </p>
                                        <?php if ($vendor['price_range']): ?>
                                            <p style="margin-bottom: 0;">
                                                <i class="fas fa-dollar-sign"></i> 
                                                <strong>Price Range:</strong> 
                                                <?php echo $vendor['price_range']; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex gap-1">
                                        <a href="tel:<?php echo $vendor['contact_number']; ?>" class="btn btn-primary btn-sm" style="flex: 1;">
                                            <i class="fas fa-phone"></i> Call
                                        </a>
                                        <a href="mailto:<?php echo $vendor['email']; ?>" class="btn btn-secondary btn-sm" style="flex: 1;">
                                            <i class="fas fa-envelope"></i> Email
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
            
            <?php if ($vendors->num_rows == 0): ?>
                <div class="card mt-4">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-store-slash" style="font-size: 4rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                        <h3>No Vendors Available</h3>
                        <p style="color: var(--gray-500);">Check back later for available service vendors.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
