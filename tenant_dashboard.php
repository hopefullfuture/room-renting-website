<?php
session_start();
include 'includes/db_connect.php';

// Security: Only tenants allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

// 1. Fetch Tenant Stats for the Boxes
$total_query = mysqli_query($conn, "SELECT id FROM bookings WHERE tenant_id = '$tenant_id'");
$total_req = mysqli_num_rows($total_query);

$accepted_query = mysqli_query($conn, "SELECT id FROM bookings WHERE tenant_id = '$tenant_id' AND status = 'accepted'");
$accepted_req = mysqli_num_rows($accepted_query);

include 'header.php';
?>

<div class="main-container" style="padding: 40px; max-width: 1100px; margin: auto; min-height: 80vh;">
    <div class="dashboard-header" style="margin-bottom: 30px;">
        <h1>Tenant Dashboard</h1>
        <p>Welcome back, <strong><?php echo $_SESSION['full_name']; ?></strong></p>
    </div>

    <div style="display: flex; gap: 20px; margin-bottom: 40px;">
        <div style="background: #e7f3ff; padding: 25px; border-radius: 12px; flex: 1; border: 1px solid #b6d4fe; text-align: center;">
            <h3 style="margin: 0; color: #0d6efd;">Total Requested</h3>
            <p style="font-size: 2rem; font-weight: bold; margin: 10px 0;"><?php echo $total_req; ?></p>
        </div>
        <div style="background: #d1e7dd; padding: 25px; border-radius: 12px; flex: 1; border: 1px solid #badbcc; text-align: center;">
            <h3 style="margin: 0; color: #198754;">Accepted Rooms</h3>
            <p style="font-size: 2rem; font-weight: bold; margin: 10px 0;"><?php echo $accepted_req; ?></p>
        </div>
    </div>

    <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 20px;">Booking Notifications</h2>
        <table width="100%" border="0" style="border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; text-align: left; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 15px;">Room Name</th>
                    <th>Monthly Rent</th>
                    <th>Current Status</th>
                    <th>Landlord Contact</th> </tr>
            </thead>
            <tbody>
                <?php
              
                $q = "SELECT b.status, r.title, r.price, b.room_id 
                      FROM bookings b 
                      JOIN rooms r ON b.room_id = r.id 
                      WHERE b.tenant_id = '$tenant_id' 
                      ORDER BY b.id DESC";
                $res = mysqli_query($conn, $q);

                if(mysqli_num_rows($res) > 0){
                    while($row = mysqli_fetch_assoc($res)) {
                        $status = $row['status'];
                        $color = "#ffc107"; 
                        $bg = "#fff3cd";
                        $contact_info = "<span style='color:#999;'>---</span>"; 

                        if ($status == 'accepted') { 
                            $color = "#198754"; 
                            $bg = "#d1e7dd"; 
                            
                           
                            $rid = $row['room_id'];
                            $l_query = mysqli_query($conn, "SELECT u.phone, u.email FROM rooms r 
                                                            JOIN users u ON r.landlord_id = u.id 
                                                            WHERE r.id = '$rid'");
                            $landlord = mysqli_fetch_assoc($l_query);
                            
                            if($landlord && !empty($landlord['phone'])) {
                                $contact_info = "<div style='background:#f0fff4; padding:8px; border:1px dashed #198754; border-radius:5px;'>
                                                    <strong style='color:#198754;'>📞 " . $landlord['phone'] . "</strong><br>
                                                    <small style='color:#666;'>" . $landlord['email'] . "</small>
                                                 </div>";
                            } else {
                                $contact_info = "<span style='color:#d63384; font-size:0.8rem;'>Contact Info Pending</span>";
                            }
                        }
                        
                        if ($status == 'rejected') { 
                            $color = "#dc3545"; 
                            $bg = "#f8d7da"; 
                            $contact_info = "<span style='color:#777;'>N/A</span>";
                        }
                        
                        echo "<tr style='border-bottom: 1px solid #dee2e6;'>
                                <td style='padding: 15px; font-weight: 500;'>{$row['title']}</td>
                                <td style='padding: 15px;'>Rs. " . number_format($row['price']) . "</td>
                                <td style='padding: 15px;'>
                                    <span style='padding: 6px 15px; border-radius: 20px; background: $bg; color: $color; font-weight: bold; font-size: 0.85rem;'>
                                        ".strtoupper($status)."
                                    </span>
                                </td>
                                <td style='padding: 15px;'>$contact_info</td> </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='padding:30px; text-align:center;'>No bookings found. <a href='index.php'>Find a room</a></td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>