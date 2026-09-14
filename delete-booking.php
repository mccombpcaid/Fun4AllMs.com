<?php
require_once 'bootstrap.php';

// Security: Only logged-in admins allowed
require_login();

// Verify this is a POST request with a valid CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
    }
}

// Redirect back to the referrer or calendar
header("Location: " . ($_SERVER['HTTP_REFERER'] ?: 'admin-calendar.php'));
exit;