<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

$success = '';
$error = '';

// Handle hall addition
if (isset($_POST['add_hall'])) {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $capacity = intval($_POST['capacity']);
    $location = sanitize_input($_POST['location']);
    $price = floatval($_POST['price_per_day']);
    $amenities = sanitize_input($_POST['amenities']);
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = upload_image($_FILES['image']);
    }
    
    $stmt = $conn->prepare("INSERT INTO halls (name, description, capacity, location, price_per_day, amenities, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $name, $description, $capacity, $location, $price, $amenities, $image);
    
    if ($stmt->execute()) {
        $success = 'Hall added successfully!';
    } else {
        $error = 'Failed to add hall.';
    }
}

// Handle hall deletion
if (isset($_GET['delete'])) {
    $hall_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM halls WHERE id = ?");
    $stmt->bind_param("i", $hall_id);
    
    if ($stmt->execute()) {
        $success = 'Hall deleted successfully!';
    } else {
        $error = 'Failed to delete hall.';
    }
}

// Get all halls
$halls = $conn->query("SELECT * FROM halls ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Halls - Admin</title>
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
                <h1>Manage Halls</h1>
                <button class="btn btn-primary" onclick="document.getElementById('addHallModal').style.display='block'">
                    <i class="fas fa-plus"></i> Add New Hall
                </button>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
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
                            <p class="card-text"><?php echo substr($hall['description'], 0, 100) . '...'; ?></p>
                            <div style="margin: 1rem 0;">
                                <p><i class="fas fa-users"></i> Capacity: <strong><?php echo $hall['capacity']; ?> guests</strong></p>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo $hall['location']; ?></p>
                                <p><i class="fas fa-dollar-sign"></i> <strong><?php echo format_currency($hall['price_per_day']); ?></strong>/day</p>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="?delete=<?php echo $hall['id']; ?>" class="btn btn-danger btn-sm w-100" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Hall Modal -->
    <div id="addHallModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Hall</h3>
                <span class="close" onclick="document.getElementById('addHallModal').style.display='none'">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Hall Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Capacity (Number of Guests)</label>
                    <input type="number" name="capacity" class="form-control" min="1" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Price Per Day ($)</label>
                    <input type="number" name="price_per_day" class="form-control" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amenities (comma separated)</label>
                    <input type="text" name="amenities" class="form-control" placeholder="e.g., AC, Parking, Stage, Sound System">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hall Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <button type="submit" name="add_hall" class="btn btn-primary w-100">
                    <i class="fas fa-plus"></i> Add Hall
                </button>
            </form>
        </div>
    </div>
    
    <script>
        window.onclick = function(event) {
            const modal = document.getElementById('addHallModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
