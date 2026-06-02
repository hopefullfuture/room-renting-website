<?php
session_start();
include 'includes/db_connect.php';
include 'header.php';

// --- 1. HANDLE LOGIN LOGIC ---
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 

    
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    if (mysqli_num_rows($res) == 1) {
        $user = mysqli_fetch_assoc($res);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($user['role'] == 'landlord') {
            header("Location: landlord/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid Email or Password');</script>";
    }
}

// --- 2. HANDLE SIGN UP LOGIC ---
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']); 
    $role = $_POST['role'];

    // PHP VALIDATION: Check Name (A-Z only)
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        echo "<script>alert('Name must only contain letters!'); window.history.back();</script>";
        exit;
    }

    // PHP VALIDATION: Check Phone (Exactly 10 digits)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        echo "<script>alert('Phone number must be exactly 10 digits!'); window.history.back();</script>";
        exit;
    }

    // PHP VALIDATION: Check Password length (6+ characters)
    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long!'); window.history.back();</script>";
        exit;
    }

    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check_email) > 0) {
        echo "<script>alert('Email already registered!');</script>";
    } else {
        $sql = "INSERT INTO users (full_name, email, phone, password, role) 
                VALUES ('$name', '$email', '$phone', '$password', '$role')";
        
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Registration Successful! Please Login.');</script>";
        }
    }
}
?>

<div class="auth-container" style="display: flex; justify-content: space-around; padding: 50px; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
    
    <div class="auth-box" style="flex: 1; min-width: 320px; max-width: 450px; padding: 30px; border: 1px solid #ddd; border-radius: 10px; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 20px; border-bottom: 2px solid #007bff; display: inline-block;">Create Account</h2>
        <form method="POST">
            <label style="display:block; font-weight:bold; margin-top:10px;">Full Name</label>
            <input type="text" name="full_name" required pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" style="width:100%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px;">
            
            <label style="display:block; font-weight:bold;">Email Address</label>
            <input type="email" name="email" required style="width:100%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px;">
            
            <label style="display:block; font-weight:bold;">Password (Min 6 characters)</label>
            <input type="password" name="password" minlength="6" required style="width:100%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px;">

            <label style="display:block; font-weight:bold;">Phone Number (10 digits)</label>
            <input type="text" name="phone" required maxlength="10" pattern="\d{10}" title="Must be exactly 10 digits" placeholder="e.g. 9841234567" style="width:100%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px;">
            
            <label style="display:block; font-weight:bold;">I want to be a:</label>
            <select name="role" style="width:100%; padding:10px; margin:5px 0 20px 0; border:1px solid #ccc; border-radius:5px;">
                <option value="tenant">Tenant (I want to rent)</option>
                <option value="landlord">Landlord (I want to post rooms)</option>
            </select>
            
            <button type="submit" name="register" style="width:100%; background: #007bff; color:white; border:none; padding:12px; font-weight:bold; border-radius:5px; cursor:pointer;">Sign Up</button>
        </form>
    </div>

    <div class="auth-box" style="flex: 1; min-width: 320px; max-width: 450px; padding: 30px; border: 1px solid #ddd; border-radius: 10px; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 20px; border-bottom: 2px solid #198754; display: inline-block;">Login</h2>
        <form method="POST">
            <label style="display:block; font-weight:bold; margin-top:10px;">Email Address</label>
            <input type="email" name="email" required style="width:100%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px;">
            
            <label style="display:block; font-weight:bold;">Password</label>
            <input type="password" name="password" required style="width:100%; padding:10px; margin:5px 0 20px 0; border:1px solid #ccc; border-radius:5px;">
            
            <button type="submit" name="login" style="width:100%; background: #198754; color:white; border:none; padding:12px; font-weight:bold; border-radius:5px; cursor:pointer;">Login</button>
        </form>
    </div>

</div>

<?php include 'footer.php'; ?>