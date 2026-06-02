<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if we are inside a subfolder (landlord or admin)
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'landlord' || $current_dir == 'admin') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $path; ?>style.css">
    <script src="js/validation.js" defer></script>
</head>
<body>

<nav class="navbar">
    <!-- Clickable Logo -->
    <div class="logo">
        <a href="<?php echo $path; ?>index.php">RoomRent</a>
    </div>
    
    <ul class="nav-links">
        <li><a href="<?php echo $path; ?>index.php">Home</a></li>
        <li><a href="<?php echo $path; ?>full_list.php">Full List</a></li>
        <li><a href="<?php echo $path; ?>about.php">About Us</a></li>
        
        <?php if(isset($_SESSION['role'])): ?>
            <?php 
                $dash_link = 'tenant_dashboard.php';
                if ($_SESSION['role'] == 'landlord') {
                    $dash_link = 'landlord/index.php';
                } elseif ($_SESSION['role'] == 'admin') {
                    $dash_link = 'admin/dashboard.php';
                }
            ?>
            
            <li><a href="<?php echo $path . $dash_link; ?>" class="signin-btn" style="background: #198754;">Dashboard</a></li>
            
            <li class="user-name" style="color: #ffc107; font-weight: bold; margin-left: 10px;">
                <?php 
                if(isset($_SESSION['full_name']) && !empty($_SESSION['full_name'])) {
                    $name_parts = explode(' ', $_SESSION['full_name']);
                    echo "Hi, " . $name_parts[0]; 
                } else {
                    echo "Hi, User";
                }
                ?>
            </li>

            <li><a href="<?php echo $path; ?>logout.php" class="signin-btn" style="background: #dc3545;">Logout</a></li>        
            
        <?php else: ?>
            <li><a href="<?php echo $path; ?>login.php" class="signin-btn">Sign In</a></li>
        <?php endif; ?>
    </ul>
</nav>