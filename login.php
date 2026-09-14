<?php
require_once __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    redirect('admin-inventory.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        if (!empty($username) && !empty($password)) {
            $result = login_user($pdo, $username, $password);
            
            if ($result === true) {
                $goto = $_SESSION['redirect_after_login'] ?? 'admin-inventory.php';
                unset($_SESSION['redirect_after_login']);
                redirect($goto);
            } elseif ($result === 'locked') {
                $error = 'Too many failed attempts. Access locked for 15 minutes.';
            } else {
                $error = 'Invalid username or password.';
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'A database error occurred.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Fun 4 All MS</title>
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-[3rem] shadow-2xl p-12">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-blue-900 uppercase italic">Fun 4 All MS</h1>
            <p class="text-[10px] font-black text-gray-400 mt-2 uppercase tracking-widest">Restricted Admin Access</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 text-xs font-black rounded-2xl text-center uppercase">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <?= csrf_input() ?>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Username</label>
                <input type="text" name="username" required autofocus class="w-full p-4 rounded-2xl bg-gray-50 border border-gray-100 font-bold outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Password</label>
                <input type="password" name="password" required class="w-full p-4 rounded-2xl bg-gray-50 border border-gray-100 font-bold outline-none">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[2rem] hover:bg-blue-700 transition shadow-xl uppercase italic mt-4">
                Verify Identity &rarr;
            </button>
        </form>
    </div>
</body>
</html>