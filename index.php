<?php
session_start();
include 'includes/db_connect.php';
include 'header.php';

// UNIFIED CARD FUNCTION
function renderRentalCard($room) {
    $formatted_price = number_format($room['price']);
    echo "
    <div class='room-card' style='background: white; border-radius: 12px; overflow: hidden; border: 1px solid #eee; transition: 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column;'>
        <img src='images/{$room['image_path']}' style='width: 100%; height: 180px; object-fit: cover;' onerror=\"this.src='images/placeholder.webp'\">
        <div style='padding: 20px; flex-grow: 1;'>
            <h4 style='margin: 0 0 10px 0; font-size: 1rem; color: #333; height: 40px; overflow: hidden;'>{$room['title']}</h4>
            <p style='margin: 5px 0; color: #666; font-size: 0.85rem;'>📍 {$room['location']}</p>
            <p style='color: #198754; font-weight: bold; font-size: 1.1rem; margin: 12px 0;'>Rs. $formatted_price</p>
            <a href='view_room.php?id={$room['id']}' class='view-btn'>View Details</a>
        </div>
    </div>";
}
?>

<!-- Clean Hero Section  -->
<div class="hero">
    <div class="hero-text-container">
        <h1>Find a place where you can be yourself.</h1>
        <p>Verified room listings across Nepal's major cities.</p>
    </div>
</div>

<div class="main-content" style="padding: 60px 5%; background: #fff; max-width: 1400px; margin: auto;">
    <?php
    $cities = [
        ['name' => 'Kathmandu', 'color' => '#0d6efd'],
        ['name' => 'Pokhara', 'color' => '#198754'],
        ['name' => 'Chitwan', 'color' => '#ffc107']
    ];

    foreach ($cities as $city) {
        $query = mysqli_query($conn, "SELECT * FROM rooms WHERE location LIKE '%{$city['name']}%' LIMIT 5");
        
        if (mysqli_num_rows($query) > 0) {
            echo "
            <div style='margin-bottom: 60px;'>
                <h2 style='font-size: 1.8rem; margin-bottom: 25px; border-left: 6px solid {$city['color']}; padding-left: 15px; color: #222;'>
                    Rooms in {$city['name']}
                </h2>
                <div style='display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px;'>";
                
                while($room = mysqli_fetch_assoc($query)) { 
                    renderRentalCard($room); 
                }
                
            echo "</div>
                <div style='margin-top: 20px; text-align: right;'>
                    <a href='full_list.php?city=" . urlencode($city['name']) . "' style='color: #0d6efd; text-decoration: none; font-weight: bold;'>
                        See More {$city['name']} Listings &rarr;
                    </a>
                </div>
            </div>";
        }
    }
    ?>
</div>

<?php include 'footer.php'; ?>