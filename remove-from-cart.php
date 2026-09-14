<?php
require_once 'bootstrap.php';

// 1. Get the ID from the URL link
$idToRemove = (isset($_GET['id'])) ? (int)$_GET['id'] : 0;

if ($idToRemove > 0 && !empty($_SESSION['cart'])) {
    
    // 2. Find the position of the item in the cart array
    $key = array_search($idToRemove, $_SESSION['cart']);

    // 3. If found, remove ONLY that item
    if ($key !== false) {
        unset($_SESSION['cart'][$key]);
        
        // 4. IMPORTANT: "Re-index" the array so there are no empty gaps
        // This prevents the cart from looking broken to other pages
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

// 5. Send the user back to the cart page
redirect('cart.php');