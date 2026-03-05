<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

// Get all available halls
$halls = $conn->query("SELECT * FROM halls WHERE status = 'available' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Halls - Event Management</title>
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
                <h1>Browse Event Halls</h1>
                <p>Find the perfect venue for your special event</p>
            </div>
            
            <div class="grid grid-3 mt-4">
                <?php while ($hall = $halls->fetch_assoc()): ?>
                    <div class="card">
                        <?php if ($hall['image']): ?>
                            <img src="../assets/images/<?php echo $hall['image']; ?>" alt="<?php echo $hall['name']; ?>" class="card-img">
                        <?php else: ?>
                            <div style="height: 250px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $hall['name']; ?></h3>
                            <p class="card-text"><?php echo $hall['description']; ?></p>
                            
                            <div style="margin: 1.5rem 0; padding: 1rem; background: var(--gray-100); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><i class="fas fa-users"></i> Capacity:</span>
                                    <strong><?php echo $hall['capacity']; ?> guests</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><i class="fas fa-map-marker-alt"></i> Location:</span>
                                    <strong><?php echo $hall['location']; ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span><i class="fas fa-dollar-sign"></i> Price:</span>
                                    <strong style="color: var(--primary-color); font-size: 1.25rem;"><?php echo format_currency($hall['price_per_day']); ?>/day</strong>
                                </div>
                            </div>
                            
                            <?php if ($hall['amenities']): ?>
                                <div style="margin-bottom: 1rem;">
                                    <strong>Amenities:</strong>
                                    <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                        <?php 
                                        $amenities = explode(',', $hall['amenities']);
                                        foreach ($amenities as $amenity): 
                                        ?>
                                            <span class="badge badge-primary"><?php echo trim($amenity); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <a href="book_hall.php?hall_id=<?php echo $hall['id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-calendar-check"></i> Book This Hall
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>
