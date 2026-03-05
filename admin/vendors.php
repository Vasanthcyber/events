<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

check_login('admin');

$success = '';
$error = '';

// Handle vendor status update
if (isset($_POST['update_status'])) {
    $vendor_id = intval($_POST['vendor_id']);
    $status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE vendors SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $vendor_id);
    
    if ($stmt->execute()) {
        $success = 'Vendor status updated successfully!';
    } else {
        $error = 'Failed to update vendor status.';
    }
}

// Handle vendor deletion
if (isset($_GET['delete'])) {
    $vendor_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $vendor_id);
    
    if ($stmt->execute()) {
        $success = 'Vendor deleted successfully!';
    } else {
        $error = 'Failed to delete vendor.';
    }
}

// Handle new vendor addition
if (isset($_POST['add_vendor'])) {
    $business_name = sanitize_input($_POST['business_name']);
    $service_type = sanitize_input($_POST['service_type']);
    $description = sanitize_input($_POST['description']);
    $contact_number = sanitize_input($_POST['contact_number']);
    $email = sanitize_input($_POST['email']);
    $price_range = sanitize_input($_POST['price_range']);
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = upload_image($_FILES['image']);
    }
    
    $stmt = $conn->prepare("INSERT INTO vendors (business_name, service_type, description, contact_number, email, price_range, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $business_name, $service_type, $description, $contact_number, $email, $price_range, $image);
    
    if ($stmt->execute()) {
        $success = 'Vendor added successfully!';
    } else {
        $error = 'Failed to add vendor.';
    }
}

// Get all vendors
$vendors = $conn->query("SELECT v.*, u.name as user_name FROM vendors v LEFT JOIN users u ON v.user_id = u.id ORDER BY v.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vendors - Admin</title>
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
                <h1>Manage Vendors</h1>
                <button class="btn btn-primary" onclick="document.getElementById('addVendorModal').style.display='block'">
                    <i class="fas fa-plus"></i> Add New Vendor
                </button>
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
                            <th>Business Name</th>
                            <th>Service Type</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Price Range</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($vendors->num_rows > 0): ?>
                            <?php while ($vendor = $vendors->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $vendor['id']; ?></td>
                                    <td><?php echo $vendor['business_name']; ?></td>
                                    <td><span class="badge badge-primary"><?php echo ucfirst($vendor['service_type']); ?></span></td>
                                    <td><?php echo $vendor['contact_number']; ?></td>
                                    <td><?php echo $vendor['email']; ?></td>
                                    <td><?php echo $vendor['price_range']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $vendor['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ucfirst($vendor['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $vendor['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                            <button type="submit" name="update_status" class="btn btn-sm <?php echo $vendor['status'] == 'active' ? 'btn-danger' : 'btn-success'; ?>">
                                                <?php echo $vendor['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                        <a href="?delete=<?php echo $vendor['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this vendor?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No vendors found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Vendor Modal -->
    <div id="addVendorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Vendor</h3>
                <span class="close" onclick="document.getElementById('addVendorModal').style.display='none'">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Business Name</label>
                    <input type="text" name="business_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Service Type</label>
                    <select name="service_type" class="form-control" required>
                        <option value="catering">Catering</option>
                        <option value="decoration">Decoration</option>
                        <option value="photography">Photography</option>
                        <option value="music">Music/DJ</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" name="contact_number" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Price Range</label>
                    <input type="text" name="price_range" class="form-control" placeholder="e.g., $500 - $2000">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <button type="submit" name="add_vendor" class="btn btn-primary w-100">
                    <i class="fas fa-plus"></i> Add Vendor
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addVendorModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
