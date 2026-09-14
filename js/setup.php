<?php
require 'db.php';

// The password you want to use
$password = 'password123'; 

// Create the secure hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Clear the old user and insert the fresh one
    $pdo->exec("TRUNCATE TABLE admin_users");
    
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hashed_password]);

    echo "✅ Admin user created successfully!<br>";
    echo "Username: <b>admin</b><br>";
    echo "Password: <b>password123</b><br><br>";
    echo "<a href='login.php'>Go to Login</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>