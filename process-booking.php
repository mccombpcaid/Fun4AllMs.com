<?php

require_once 'bootstrap.php';

/**
 * Helper to calculate delivery fee based on Google Maps Distance Matrix
 */
function getVerifiedMileageFee($destAddress) {
    if (empty($destAddress)) return 0.00;
    
    $origin = urlencode("1411 Vermont Ave, McComb, MS");
    $dest = urlencode($destAddress);
    $apiKey = $_ENV['GOOGLE_MAPS_KEY']; 
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origin&destinations=$dest&units=imperial&key=$apiKey";
    
    $response = @file_get_contents($url);
    $data = json_decode($response, true);
    
    if (($data['status'] ?? '') === 'OK') {
        $element = $data['rows'][0]['elements'][0];
        if ($element['status'] === 'OK') {
            $miles = $element['distance']['value'] * 0.000621371;
            if ($miles <= 10) return 25.00;
            elseif ($miles <= 50) return ceil(($miles * 4) / 5) * 5;
            else return -1.00; 
        }
    }
    return 0.00;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['cart'])) {
        die("Your cart is empty.");
    }

    $date = $_POST['booking_date'];
    $cart = $_SESSION['cart'];

    // 1. AVAILABILITY CHECK WITH 10-MINUTE HOLD
    foreach ($cart as $rentalId) {
        $rentalId = (int)$rentalId;

        $stmt = $pdo->prepare("SELECT name, quantity FROM rentals WHERE id = ?");
        $stmt->execute([$rentalId]);
        $itemData = $stmt->fetch();

        if (!$itemData) continue;

        $totalUnits = (int)$itemData['quantity'];
        $itemName = $itemData['name'];

        // Count existing confirmed bookings OR pending bookings created in the last 10 mins
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM bookings 
            WHERE rental_id = ? 
            AND booking_date = ? 
            AND (
                status IN ('confirmed', 'paid') 
                OR (status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE))
            )
        ");
        $stmt->execute([$rentalId, $date]);
        $unitsBooked = (int)$stmt->fetchColumn();

        if ($unitsBooked >= $totalUnits) {
            header("Location: cart.php?error=unavailable&date=" . urlencode($date) . "&item=" . urlencode($itemName));
            exit;
        }
    }

    // 2. INPUT SANITIZATION
    $name      = filter_var($_POST['customer_name'], FILTER_SANITIZE_SPECIAL_CHARS);
    $email     = filter_var($_POST['customer_email'], FILTER_SANITIZE_EMAIL);
    $phone     = filter_var($_POST['customer_phone'], FILTER_SANITIZE_SPECIAL_CHARS);
    $address   = filter_var($_POST['customer_address'], FILTER_SANITIZE_SPECIAL_CHARS);

    // 3. RENTAL COST CALCULATION
    $ids = implode(',', array_map('intval', $cart));
    $stmt = $pdo->query("SELECT price FROM rentals WHERE id IN ($ids)");
    $items = $stmt->fetchAll();
    
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += (float)$item['price'];
    }
    
    // 4. DELIVERY FEE CALCULATION
    $deliveryFee = getVerifiedMileageFee($address);
    if ($deliveryFee < 0) die("Location too far for automated booking.");

    // 5. DEPOSIT CALCULATION (Matches cart.php)
    $unitCount = count($cart);
    $depositBase = ($unitCount * 100) + $deliveryFee;
    
    $tax = round($depositBase * 0.07, 2);
    $stripeFee = round(($depositBase * 0.029) + 0.30, 2);
    
    $depositTotal = $depositBase + $tax + $stripeFee;

    try {
        // 6. RECORD BOOKING AS PENDING
        // Ensure the ID exists to prevent foreign key errors
        if (!isset($cart[0])) die("Error: No item in cart.");
        $primaryRentalId = (int)$cart[0];

        $sql = "INSERT INTO bookings (
            rental_id, 
            customer_name, 
            customer_email, 
            customer_phone, 
            customer_address, 
            booking_date, 
            delivery_fee, 
            total_price, 
            status, 
            payment_status, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$primaryRentalId, $name, $email, $phone, $address, $date, $deliveryFee, $depositTotal]);
        
        $bookingId = $pdo->lastInsertId();
        
        $_SESSION['last_booking_unit_count'] = $unitCount;
        $_SESSION['cart'] = [];
        
        header("Location: checkout.php?id=" . $bookingId);
        exit;
    } catch (PDOException $e) {
        die("DATABASE ERROR: " . $e->getMessage());
    }
}