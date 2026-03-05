# Event Management System

A professional event management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system features separate dashboards for administrators, vendors, and users with comprehensive event hall booking and vendor management capabilities.

## Features

### Admin Features
- **Dashboard** with statistics and analytics
- **Hall Management** - Add, edit, and delete event halls
- **Vendor Management** - Add, activate, or deactivate vendors
- **Booking Management** - View and manage all bookings, confirm or cancel
- **User Management** - View all registered users

### User Features
- **Browse Halls** - View available event halls with details
- **Book Halls** - Reserve halls for events with date selection
- **Browse Vendors** - View all active vendors by service type
- **My Bookings** - Track all bookings and their status
- **Dashboard** - Overview of booking statistics

### Vendor Features
- **Profile Management** - View business profile
- **Booking Requests** - View customer booking requests

## Technologies Used
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6.4.0
- **Design**: Custom CSS with modern gradient styles

## Installation Instructions

### Prerequisites
- XAMPP, WAMP, or LAMP stack installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Setup Files
1. Copy the entire `event_management_system` folder to your web server directory:
   - For XAMPP: `C:/xampp/htdocs/`
   - For WAMP: `C:/wamp/www/`
   - For LAMP: `/var/www/html/`

### Step 2: Create Database
1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Click on "New" to create a new database
3. Name it `event_management`
4. Click on the database, then go to the "Import" tab
5. Click "Choose File" and select `database.sql` from the project folder
6. Click "Go" to import the database structure and sample data

Alternatively, you can run the SQL file directly:
```sql
-- Open MySQL command line or phpMyAdmin SQL tab
-- Copy and paste the contents of database.sql
-- Execute the queries
```

### Step 3: Configure Database Connection
1. Open `includes/config.php`
2. Update the database credentials if needed:
```php
define('DB_HOST', 'localhost');     // Usually localhost
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password (empty by default)
define('DB_NAME', 'event_management');
```

### Step 4: Create Upload Directory
1. Make sure the `assets/images/` directory exists and has write permissions
2. On Linux/Mac, run:
```bash
chmod 755 assets/images/
```

### Step 5: Access the System
1. Open your web browser
2. Navigate to: `http://localhost/event_management_system/`

## Default Login Credentials

### Administrator
- **Email**: admin@eventmanager.com
- **Password**: admin123

### Test User
Register a new user account through the registration form.

## Usage Guide

### For Administrators

1. **Login** with admin credentials
2. **Manage Halls**:
   - Click "Halls" in navigation
   - Click "Add New Hall" button
   - Fill in hall details (name, capacity, location, price, amenities)
   - Upload hall image (optional)
   - Submit to add hall

3. **Manage Vendors**:
   - Click "Vendors" in navigation
   - Click "Add New Vendor" button
   - Fill in vendor details
   - Choose service type (Catering, Decoration, Photography, Music, Other)
   - Submit to add vendor
   - Use Activate/Deactivate buttons to control vendor visibility

4. **Manage Bookings**:
   - Click "Bookings" in navigation
   - View all customer bookings
   - Confirm or cancel pending bookings
   - View booking details

5. **Manage Users**:
   - Click "Users" in navigation
   - View all registered users
   - See booking statistics for each user

### For Users

1. **Register/Login**:
   - Create new account or login
   - Fill in required information

2. **Browse Halls**:
   - Click "Browse Halls" to view all available halls
   - See hall details, capacity, pricing, amenities
   - Click "Book This Hall" to make a reservation

3. **Book a Hall**:
   - Select hall
   - Fill in event details (name, date, type, guest count)
   - Add special requirements (optional)
   - Submit booking
   - Wait for admin confirmation

4. **View Vendors**:
   - Click "Vendors" to browse service providers
   - Vendors are organized by service type
   - View contact information and price ranges
   - Call or email vendors directly

5. **Track Bookings**:
   - Click "My Bookings" to see all your reservations
   - View booking status (Pending, Confirmed, Cancelled)
   - See event details and amounts

### For Vendors

1. **Login** with vendor credentials
2. **View Profile** - See your business information
3. **Manage Requests** - View booking requests from customers

## Directory Structure

```
event_management_system/
├── admin/
│   ├── dashboard.php      # Admin dashboard
│   ├── halls.php          # Hall management
│   ├── vendors.php        # Vendor management
│   ├── bookings.php       # Booking management
│   └── users.php          # User management
├── user/
│   ├── dashboard.php      # User dashboard
│   ├── halls.php          # Browse halls
│   ├── book_hall.php      # Booking form
│   ├── vendors.php        # Browse vendors
│   └── bookings.php       # View user bookings
├── vendor/
│   └── dashboard.php      # Vendor dashboard
├── includes/
│   ├── config.php         # Database configuration
│   └── functions.php      # Common functions
├── css/
│   └── style.css          # Main stylesheet
├── assets/
│   └── images/            # Upload directory for images
├── index.php              # Login/Register page
├── logout.php             # Logout functionality
└── database.sql           # Database structure and sample data
```

## Database Schema

- **users** - User accounts (admin, vendor, user)
- **halls** - Event hall information
- **vendors** - Vendor/service provider details
- **bookings** - Hall booking records
- **booking_vendors** - Relationship between bookings and vendors

## Security Features

- Password hashing using PHP password_hash()
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars()
- Session-based authentication
- Role-based access control

## Customization

### Changing Colors
Edit `css/style.css` and modify the CSS variables:
```css
:root {
    --primary-color: #6366f1;
    --primary-dark: #4f46e5;
    --secondary-color: #ec4899;
    /* ... other colors ... */
}
```

### Adding More Features
- Extend the database schema in `database.sql`
- Add new PHP files for additional pages
- Update navigation menus in respective dashboards

## Troubleshooting

### Database Connection Error
- Check database credentials in `includes/config.php`
- Ensure MySQL service is running
- Verify database `event_management` exists

### Images Not Uploading
- Check `assets/images/` directory permissions
- Ensure PHP file upload is enabled
- Check `upload_max_filesize` in php.ini

### Session Issues
- Ensure cookies are enabled in browser
- Check session configuration in PHP

## Browser Support
- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## License
This project is open-source and available for educational purposes.

## Support
For issues or questions, please check:
1. Database connection settings
2. File permissions
3. PHP error logs
4. Browser console for JavaScript errors

## Credits
Developed with modern web technologies and best practices for event management.
