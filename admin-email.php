<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    verify_csrf();
    
    require_once 'mail-config.php';
    
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $recipient_type = $_POST['recipient_type'];
    
    // 1. Determine who we are emailing
    if ($recipient_type === 'all') {
        $stmt = $pdo->query("SELECT DISTINCT customer_email, customer_name FROM bookings");
    } elseif ($recipient_type === 'paid') {
        $stmt = $pdo->query("SELECT DISTINCT customer_email, customer_name FROM bookings WHERE payment_status = 'paid'");
    } else {
        $stmt = $pdo->prepare("SELECT customer_email, customer_name FROM bookings WHERE id = ?");
        $stmt->execute([(int)$recipient_type]);
    }
    
    $recipients = $stmt->fetchAll();
    
    if (empty($recipients)) {
        $error = "No recipients found for this selection.";
    } else {
        $success_count = 0;
        foreach ($recipients as $r) {
            // Branded HTML Wrapper
            $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 20px; overflow: hidden;'>
                    <div style='background: #1e3a8a; padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0; text-transform: uppercase;'>Fun 4 All MS</h1>
                    </div>
                    <div style='padding: 30px; color: #333;'>
                        <p>Hi " . e($r['customer_name']) . ",</p>
                        " . nl2br(e($message)) . "
                    </div>
                    <div style='background: #f9fafb; padding: 20px; text-align: center; font-size: 10px; color: #999;'>
                        &copy; " . date('Y') . " Fun 4 All MS - Mississippi Inflatable Rentals
                    </div>
                </div>
            ";
            
            if (sendBookingEmail($r['customer_email'], $subject, $body)) {
                $success_count++;
            }
        }
        $msg = "Successfully sent $success_count emails.";
    }
}

// Get all bookings for the dropdown
$all_bookings = $pdo->query("SELECT id, customer_name, customer_email, booking_date FROM bookings ORDER BY id DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Hub | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex flex-col md:flex-row min-h-screen">

   <?php include 'admin-sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8 max-w-4xl mx-auto w-full">
        <h2 class="text-3xl font-black text-gray-800 uppercase mb-8">Email Communication Hub</h2>

        <?php if ($msg): ?>
            <div class="bg-green-500 text-white p-4 rounded-2xl mb-6 font-bold shadow-lg"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-4 rounded-2xl mb-6 font-bold shadow-lg"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-[3rem] shadow-xl border border-gray-100">
            <form method="POST" class="space-y-6">
                <?= csrf_input() ?>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Send To:</label>
                    <select name="recipient_type" class="w-full p-4 bg-gray-50 border-none rounded-2xl font-bold text-gray-700">
                        <optgroup label="Bulk Groups">
                            <option value="all">All Customers (Newsletter)</option>
                            <option value="paid">All Paid Customers</option>
                        </optgroup>
                        <optgroup label="Recent Bookings">
                            <?php foreach($all_bookings as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= e($b['customer_name']) ?> (<?= date('M d', strtotime($b['booking_date'])) ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Subject Line:</label>
                    <input type="text" name="subject" placeholder="Important Update Regarding Your Rental" class="w-full p-4 bg-gray-50 border-none rounded-2xl font-bold" required>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Message Body:</label>
                    <textarea name="message" rows="8" placeholder="Type your message here..." class="w-full p-4 bg-gray-50 border-none rounded-2xl" required></textarea>
                </div>

                <button type="submit" name="send_email" class="w-full bg-blue-600 text-white font-black py-5 rounded-2xl shadow-xl hover:bg-blue-700 transition uppercase tracking-widest">
                    🚀 Launch Email Blast
                </button>
            </form>
        </div>
    </main>
</body>
</html>