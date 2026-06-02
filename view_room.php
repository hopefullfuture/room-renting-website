<?php
session_start();
include 'includes/db_connect.php';
include 'header.php';

$room_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$res = mysqli_query($conn, "SELECT * FROM rooms WHERE id = '$room_id'");
$room = mysqli_fetch_assoc($res);

if (!$room) {
    echo "<div style='text-align:center; padding:100px;'><h3>Room not found.</h3><a href='index.php'>Go Back</a></div>";
    include 'footer.php';
    exit;
}

// --- RECOMMENDATION LOGIC START ---
// Get current room details for the algorithm
$current_price = $room['price'];
$location_full = $room['location'];
$parts = explode(',', $location_full);
$current_city = trim($parts[0]); // Extract "Kathmandu", "Pokhara", etc.

// SQL Algorithm: Same city, ± Rs. 2000 price range, excluding current room
$rec_sql = "SELECT * FROM rooms 
            WHERE location LIKE '%$current_city%' 
            AND id != '$room_id' 
            AND price BETWEEN ($current_price - 2000) AND ($current_price + 2000) 
            LIMIT 4";
$rec_res = mysqli_query($conn, $rec_sql);
$recommended_rooms = mysqli_fetch_all($rec_res, MYSQLI_ASSOC);
// --- RECOMMENDATION LOGIC END ---

// 1. CHECK IF THE ROOM IS ALREADY BOOKED (ACCEPTED)
$check_status = mysqli_query($conn, "SELECT * FROM bookings WHERE room_id = '$room_id' AND status = 'accepted'");
$is_booked = (mysqli_num_rows($check_status) > 0);

// 2. CHECK IF THIS SPECIFIC TENANT HAS ALREADY SENT A REQUEST
$has_sent_request = false;
if (isset($_SESSION['user_id'])) {
    $current_user = $_SESSION['user_id'];
    $check_req = mysqli_query($conn, "SELECT id FROM bookings WHERE room_id = '$room_id' AND tenant_id = '$current_user'");
    if (mysqli_num_rows($check_req) > 0) {
        $has_sent_request = true;
    }
}

// 3. HANDLE THE RENT BUTTON CLICK
if (isset($_POST['rent_now'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please login as a tenant to book!'); window.location='login.php';</script>";
    } else {
        $tenant_id = $_SESSION['user_id'];
        if ($is_booked) {
            echo "<script>alert('Sorry, this room was just rented by someone else!');</script>";
        } else {
            $insert_query = "INSERT INTO bookings (room_id, tenant_id, status) VALUES ('$room_id', '$tenant_id', 'pending')";
            if (mysqli_query($conn, $insert_query)) {
                echo "<script>alert('Rent request sent to landlord! Check your dashboard for updates.'); window.location='view_room.php?id=$room_id';</script>";
            }
        }
    }
}

// Visual Logic for Rules based on Price
$price = $room['price'];
if ($price >= 20000) {
    $desc = "A premium, luxury space located in a prime area. Features high-quality finishes and a spacious layout.";
    $pets = "No";
    $bachelors = "Family Only";
} elseif ($price >= 12000) {
    $desc = "A modern and comfortable flat offering great value. Located near local transport and markets.";
    $pets = "Negotiable";
    $bachelors = "Yes";
} else {
    $desc = "An affordable, cozy room ideal for students. Well-maintained and located in a friendly neighborhood.";
    $pets = "Yes";
    $bachelors = "Students Preferred";
}
?>

<div style="background: #f9fbfd; padding: 40px 0; min-height: 80vh;">
    <div style="max-width: 1200px; margin: auto; display: flex; flex-direction: column; gap: 30px; padding: 0 20px;">
        
        <!-- Main Content Row -->
        <div style="display: flex; gap: 30px;">
            <div style="flex: 2;">
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 25px;">
                    <h1 style="font-size: 2rem; color: #222; margin-bottom: 5px;"><?php echo $room['title']; ?></h1>
                    <p style="color: #666; font-size: 1rem; margin-bottom: 20px;">📍 <?php echo $room['location']; ?></p>
                    
                    <img src="images/<?php echo $room['image_path']; ?>" 
                         style="width: 100%; height: 450px; object-fit: cover; border-radius: 8px;" 
                         onerror="this.src='images/placeholder.webp'">

                    <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <h3 style="margin-bottom: 20px;">Facilities & Amenities</h3>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; color: #555;">
                            <p>🚿 24 Hr Water Supply</p>
                            <p>🚲 Bike Parking</p>
                            <p>📶 Free Wifi</p>
                            <p>☀️ Solar Water</p>
                        </div>
                    </div>

                    <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <h3 style="margin-bottom: 15px;">About this property</h3>
                        <p style="line-height: 1.6; color: #444; font-size: 1.05rem;"><?php echo $desc; ?></p>
                    </div>
                </div>
            </div>

            <!-- Booking Sidebar -->
            <div style="flex: 1;">
                <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); position: sticky; top: 100px; border: 1px solid #eee;">
                    <h2 style="color: #d9534f; margin-bottom: 5px;">Rs. <?php echo number_format($room['price']); ?><span style="font-size: 0.9rem; color: #777;">/month</span></h2>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                    
                    <div style="margin-bottom: 25px; font-size: 0.95rem; color: #555;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span>Electricity & Water</span>
                            <strong style="color: #222;">Included</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span>Pets Allowed</span>
                            <strong style="color: #222;"><?php echo $pets; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span>Bachelor Friendly</span>
                            <strong style="color: #222;"><?php echo $bachelors; ?></strong>
                        </div>
                    </div>

                    <div class="action-area">
                        <?php if ($is_booked): ?>
                            <button disabled style="width: 100%; background: #6c757d; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: not-allowed;">Already Rented</button>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <a href="login.php" style="display: block; text-align: center; background: #007bff; color: white; padding: 15px; border-radius: 8px; text-decoration: none; font-weight: bold;">Log In to Rent</a>
                        <?php elseif ($_SESSION['role'] !== 'tenant'): ?>
                            <p style="text-align: center; color: #856404; font-size: 0.85rem; background: #fff3cd; padding: 10px; border-radius: 5px;">Switch to Tenant account to book.</p>
                        <?php elseif ($has_sent_request): ?>
                            <button disabled style="width: 100%; background: #28a745; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: not-allowed;">Request Sent ✓</button>
                        <?php else: ?>
                            <form method="POST">
                                <button type="submit" name="rent_now" style="width: 100%; background: #d9534f; color: white; border: none; padding: 15px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer;">Rent This Room Now</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECOMMENDATION SECTION -->
        <div style="margin-top: 40px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h2 style="margin-bottom: 10px; color: #333;">Recommended for You</h2>
            <p style="color: #888; font-size: 0.9rem; margin-bottom: 25px;">Based on similar price and location in <?php echo $current_city; ?>.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
                <?php if(count($recommended_rooms) > 0): ?>
                    <?php foreach($recommended_rooms as $rec): ?>
                        <a href="view_room.php?id=<?php echo $rec['id']; ?>" style="text-decoration: none; color: inherit; transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="border: 1px solid #eee; border-radius: 10px; overflow: hidden; background: #fff; height: 100%;">
                                <img src="images/<?php echo $rec['image_path']; ?>" style="width: 100%; height: 150px; object-fit: cover;" onerror="this.src='images/placeholder.webp'">
                                <div style="padding: 15px;">
                                    <h4 style="margin: 0 0 8px 0; font-size: 1rem; color: #222;"><?php echo htmlspecialchars($rec['title']); ?></h4>
                                    <p style="color: #198754; font-weight: bold; margin: 0 0 5px 0;">Rs. <?php echo number_format($rec['price']); ?></p>
                                    <p style="font-size: 0.8rem; color: #777; margin: 0;">📍 <?php echo htmlspecialchars($rec['location']); ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #999;">
                        No similar recommendations found in this area.
                    </div>
                <?php endif; ?>
            </div>
        </div>
       

    </div>
</div>

<?php include 'footer.php'; ?>