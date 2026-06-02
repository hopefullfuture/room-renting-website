<?php 
session_start();
include 'includes/db_connect.php'; 
include 'includes/sorter.php';
include 'includes/pathfinder.php'; 

$pathfinder = new Dijkstra();

// 1. Get All Filters from URL
$city_filter = isset($_GET['city']) ? mysqli_real_escape_string($conn, $_GET['city']) : '';
$user_loc = isset($_GET['user_loc']) ? $_GET['user_loc'] : 'Kathmandu, Kalanki'; 
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] != '') ? (int)$_GET['max_price'] : 999999;
$sort_type = isset($_GET['sort']) ? $_GET['sort'] : '';

// 2. Build the SQL Query (City + Budget Filter)
$query_parts = [];
if ($city_filter !== '') {
    $query_parts[] = "location LIKE '%$city_filter%'";
}
if ($max_price < 999999) {
    $query_parts[] = "price <= $max_price";
}

$sql = "SELECT * FROM rooms";
if (count($query_parts) > 0) {
    $sql .= " WHERE " . implode(' AND ', $query_parts);
}

$page_title = ($city_filter !== '') ? "Rooms in " . htmlspecialchars($city_filter) : "All Available Rooms";

$res = mysqli_query($conn, $sql);
$rooms = mysqli_fetch_all($res, MYSQLI_ASSOC);

// 3. Apply Dijkstra Distances & REFINED Variation Logic
foreach ($rooms as &$room) {
    $parts = explode(',', $room['location']);
    $room_city = trim($parts[0]);
    $room_place = isset($parts[1]) ? trim($parts[1]) : ''; 
    
    // Map database city to the Dijkstra Hub
    $target_hub = "";
    if ($room_city == 'Kathmandu') $target_hub = 'Kathmandu, Kalanki';
    elseif ($room_city == 'Chitwan') $target_hub = 'Chitwan, Pulchowk';
    elseif ($room_city == 'Pokhara') $target_hub = 'Pokhara, Prithvi Chowk';

    $base_dist = $pathfinder->getDistance($user_loc, $target_hub);
    
    // REFINED LOGIC: Detect if user is in the same area for < 1km display
    if (strpos($user_loc, $room_city) !== false) {
        $user_area = trim(explode(',', $user_loc)[1]); // Extract "Kalanki" from Hub
        
        if (stripos($room['location'], $user_area) !== false) {
            // EXACT AREA MATCH (e.g. Kalanki)
            $variation = strlen($room_place) * 0.02; 
            $room['distance'] = 0.2 + $variation; 
        } else {
            // SAME CITY, DIFFERENT AREA
            $variation = strlen($room_place) * 0.1;
            $room['distance'] = 1.5 + $variation; 
        }
    } else {
        // DIFFERENT CITY (Standard Dijkstra)
        $variation = strlen($room_place) * 0.2;
        $room['distance'] = $base_dist + $variation;
    }
}
unset($room); 

// 4. Handle Sorting Logic
if ($sort_type == 'low') {
    $rooms = RoomSorter::mergeSortPrice($rooms, 'low');
} elseif ($sort_type == 'high') {
    $rooms = RoomSorter::mergeSortPrice($rooms, 'high');
} elseif ($sort_type == 'location') {
    $rooms = RoomSorter::quickSortLocation($rooms);
} elseif ($sort_type == 'nearest') {
    usort($rooms, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
}

// Helper for persistent filter links
function getSortLink($type, $city, $user_loc, $max_price) {
    $params = [
        'sort' => $type,
        'city' => $city,
        'user_loc' => $user_loc,
        'max_price' => ($max_price < 999999) ? $max_price : ''
    ];
    return "full_list.php?" . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $page_title; ?> | RoomRent</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <h2 style="text-align:center; margin-top:30px;"><?php echo $page_title; ?></h2>

    <!-- FILTER PANEL -->
    <div style="max-width: 1000px; margin: 20px auto; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #ddd;">
        <form method="GET" action="full_list.php" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center;">
            
            <div class="filter-group">
                <label style="font-weight:bold; display:block; font-size:0.8rem;">Your Hub (Origin):</label>
                <select name="user_loc" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="Kathmandu, Kalanki" <?php if($user_loc == 'Kathmandu, Kalanki') echo 'selected'; ?>>Kathmandu (Kalanki)</option>
                    <option value="Pokhara, Prithvi Chowk" <?php if($user_loc == 'Pokhara, Prithvi Chowk') echo 'selected'; ?>>Pokhara (Prithvi Chowk)</option>
                    <option value="Chitwan, Pulchowk" <?php if($user_loc == 'Chitwan, Pulchowk') echo 'selected'; ?>>Chitwan (Pulchowk)</option>
                </select>
            </div>

            <div class="filter-group">
                <label style="font-weight:bold; display:block; font-size:0.8rem;">Filter City:</label>
                <select name="city" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">All Cities</option>
                    <option value="Kathmandu" <?php if($city_filter == 'Kathmandu') echo 'selected'; ?>>Kathmandu</option>
                    <option value="Pokhara" <?php if($city_filter == 'Pokhara') echo 'selected'; ?>>Pokhara</option>
                    <option value="Chitwan" <?php if($city_filter == 'Chitwan') echo 'selected'; ?>>Chitwan</option>
                </select>
            </div>

            <div class="filter-group">
                <label style="font-weight:bold; display:block; font-size:0.8rem;">Max Budget (Rs):</label>
                <input type="number" name="max_price" value="<?php echo ($max_price < 999999) ? $max_price : ''; ?>" placeholder="e.g. 5000" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 120px;">
            </div>

            <button type="submit" style="padding: 10px 25px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 15px;">Apply Filters</button>
            <a href="full_list.php" style="margin-top: 15px; margin-left:10px; font-size: 0.9rem; color: #666; text-decoration:none;">Reset</a>
        </form>
    </div>

    <!-- SORTING BUTTONS -->
    <div style="text-align:center; margin: 20px 0;">
        <a href="<?php echo getSortLink('low', $city_filter, $user_loc, $max_price); ?>" style="display: inline-block; padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-size: 0.9rem;">Price: Low to High</a>
        <a href="<?php echo getSortLink('high', $city_filter, $user_loc, $max_price); ?>" style="display: inline-block; padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-size: 0.9rem;">Price: High to Low</a>
        <a href="<?php echo getSortLink('nearest', $city_filter, $user_loc, $max_price); ?>" style="display: inline-block; padding: 10px 20px; background: #198754; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: 2px solid #222; font-weight: bold;">🚀 SORT BY NEAREST</a>
    </div>

    <!-- ROOM GRID (Set to 4 per row via minmax) -->
    <div class="room-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; padding: 20px; max-width: 1200px; margin: auto;">
        <?php if(count($rooms) > 0): ?>
            <?php foreach($rooms as $room): ?>
            <div class="room-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; position: relative; display: flex; flex-direction: column; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                
                <div style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.75); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; z-index: 10;">
                    <?php echo number_format($room['distance'], 1); ?> km away
                </div>

                <img src="images/<?php echo $room['image_path']; ?>" style="width:100%; height:180px; object-fit:cover;" onerror="this.src='images/placeholder.webp'">
                
                <div style="padding: 15px; flex-grow: 1;">
                    <h3 style="font-size: 1.1rem; margin: 0 0 10px 0; height: 45px; overflow: hidden;"><?php echo htmlspecialchars($room['title']); ?></h3>
                    <p style="font-size: 0.9rem; margin-bottom: 5px; color: #555;">📍 <?php echo htmlspecialchars($room['location']); ?></p>
                    <p style="color: #198754; font-weight: bold; font-size: 1.2rem; margin-bottom: 15px;">Rs. <?php echo number_format($room['price']); ?></p>
                    
                    <a href="view_room.php?id=<?php echo $room['id']; ?>" style="display: block; text-align: center; text-decoration: none; background: #007bff; color: white; padding: 10px; border-radius: 4px; font-size: 0.95rem; font-weight: bold;">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; grid-column: 1 / -1; padding: 50px;">
                <h3 style="color: #666;">No rooms found matching your criteria.</h3>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>