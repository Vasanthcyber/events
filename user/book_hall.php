<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('user');

$error = '';
$success = '';

// Get hall ID
if (!isset($_GET['hall_id'])) {
    redirect('halls.php');
}

$hall_id = intval($_GET['hall_id']);

// Get hall details
$stmt = $conn->prepare("SELECT * FROM halls WHERE id = ? AND status = 'available'");
$stmt->bind_param("i", $hall_id);
$stmt->execute();
$hall = $stmt->get_result()->fetch_assoc();

if (!$hall) {
    redirect('halls.php');
}

// Handle booking submission
if (isset($_POST['book_hall'])) {
    $user_id = $_SESSION['user_id'];
    $event_name = sanitize_input($_POST['event_name']);
    $event_date = sanitize_input($_POST['event_date']);
    $event_type = sanitize_input($_POST['event_type']);
    $guests_count = intval($_POST['guests_count']);
    $special_requirements = sanitize_input($_POST['special_requirements']);
    $total_amount = $hall['price_per_day'];
    
    // Check if date is not in the past
    if (strtotime($event_date) < strtotime(date('Y-m-d'))) {
        $error = 'Event date cannot be in the past!';
    } else {
        // Check if hall is already booked on this date
        $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE hall_id = ? AND event_date = ? AND status != 'cancelled'");
        $check_stmt->bind_param("is", $hall_id, $event_date);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        
        if ($existing->num_rows > 0) {
            $error = 'This hall is already booked for the selected date!';
        } else {
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, hall_id, event_name, event_date, event_type, guests_count, special_requirements, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssisd", $user_id, $hall_id, $event_name, $event_date, $event_type, $guests_count, $special_requirements, $total_amount);
            
            if ($stmt->execute()) {
                $success = 'Booking submitted successfully! Please wait for confirmation.';
            } else {
                $error = 'Failed to submit booking. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Hall - Event Management</title>
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
                <h1>Book: <?php echo $hall['name']; ?></h1>
                <a href="halls.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Halls
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <a href="bookings.php" class="btn btn-primary mt-2">View My Bookings</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="grid grid-2 mt-4">
                <!-- Hall Details -->
                <div class="card">
                    <?php if ($hall['image']): ?>
                        <img src="../assets/images/<?php echo $hall['image']; ?>" alt="<?php echo $hall['name']; ?>" class="card-img">
                    <?php else: ?>
                        <div style="height: 300px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">
                            <i class="fas fa-building"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h3><?php echo $hall['name']; ?></h3>
                        <p><?php echo $hall['description']; ?></p>
                        
                        <div style="margin: 1.5rem 0; padding: 1rem; background: var(--gray-100); border-radius: 8px;">
                            <h4 style="margin-bottom: 1rem;">Hall Details</h4>
                            <p><i class="fas fa-users"></i> <strong>Capacity:</strong> <?php echo $hall['capacity']; ?> guests</p>
                            <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo $hall['location']; ?></p>
                            <p><i class="fas fa-dollar-sign"></i> <strong>Price:</strong> <?php echo format_currency($hall['price_per_day']); ?>/day</p>
                        </div>
                        
                        <?php if ($hall['amenities']): ?>
                            <div>
                                <h4>Amenities</h4>
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
                    </div>
                </div>
                
                <!-- Booking Form -->
                <div class="card">
                    <div class="card-body">
                        <h3>Booking Information</h3>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">Event Name</label>
                                <input type="text" name="event_name" class="form-control" required placeholder="e.g., Wedding Reception">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Event Type</label>
                                <select name="event_type" class="form-control" required>
                                    <option value="">Select Event Type</option>
                                    <option value="Wedding">Wedding</option>
                                    <option value="Birthday Party">Birthday Party</option>
                                    <option value="Corporate Event">Corporate Event</option>
                                    <option value="Conference">Conference</option>
                                    <option value="Anniversary">Anniversary</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Expected Number of Guests</label>
                                <input type="number" name="guests_count" class="form-control" min="1" max="<?php echo $hall['capacity']; ?>" required>
                                <small style="color: var(--gray-500);">Maximum capacity: <?php echo $hall['capacity']; ?> guests</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Special Requirements (Optional)</label>
                                <textarea name="special_requirements" class="form-control" rows="3" placeholder="Any special requirements or requests..."></textarea>
                            </div>
                            
                            <div style="padding: 1.5rem; background: var(--gray-100); border-radius: 8px; margin-bottom: 1.5rem;">
                                <h4 style="margin-bottom: 1rem;">Booking Summary</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Hall Rental (1 day)</span>
                                    <strong><?php echo format_currency($hall['price_per_day']); ?></strong>
                                </div>
                                <hr style="margin: 1rem 0; border: none; border-top: 2px solid var(--gray-300);">
                                <div style="display: flex; justify-content: space-between; font-size: 1.25rem;">
                                    <strong>Total Amount</strong>
                                    <strong style="color: var(--primary-color);"><?php echo format_currency($hall['price_per_day']); ?></strong>
                                </div>
                            </div>
                            
                            <button type="submit" name="book_hall" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-calendar-check"></i> Confirm Booking
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
