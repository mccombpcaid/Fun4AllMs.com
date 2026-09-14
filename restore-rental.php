<?php
require_once 'bootstrap.php';
if (isset($_GET['id']) && isset($_SESSION['admin_user_id'])) {
    $stmt = $pdo->prepare("UPDATE rentals SET status = 'active' WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: admin-inventory.php");
exit;