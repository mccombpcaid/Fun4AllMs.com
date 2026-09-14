<?php
require_once 'bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['product_id'];
    
    // Initialize cart if empty
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add item ID to cart (prevent duplicates)
    if (!in_array($id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $id;
    }
    
    // Redirect straight to the cart
    redirect('cart.php');
}