<?php
require_once 'bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'retired' WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
    }
}
redirect('admin-inventory.php');