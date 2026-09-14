<?php
require_once 'bootstrap.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

$sessionId = $_GET['session_id'] ?? null;
$bookingId = $_GET['booking_id'] ?? null;

try {
    $session = \Stripe\Checkout\Session::retrieve($sessionId);
    $amountCharged = $session->amount_total / 100;

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    $pdo->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?")->execute([$bookingId]);
} catch (Exception $e) {
    die("Error.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirmed | Fun 4 All MS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 py-20">
    <div class="max-w-xl mx-auto bg-white p-12 rounded-[3rem] shadow-xl text-center border border-slate-100">
        <h1 class="text-4xl font-black text-slate-900 mb-6 uppercase italic">DEPOSIT PAID!</h1>
        <p class="text-slate-600 mb-8 font-bold">Confirmed for <?= e($booking['booking_date']) ?></p>
        
        <div class="bg-slate-50 rounded-3xl p-8 mb-8 text-left space-y-4">
            <div class="flex justify-between border-b pb-4">
                <span class="text-xs font-black uppercase text-slate-400">Paid Today</span>
                <span class="font-black text-green-600">$<?= number_format($amountCharged, 2) ?></span>
            </div>
            <div class="flex justify-between pt-2">
                <span class="text-xs font-black uppercase text-slate-400">Balance Due</span>
                <span class="text-2xl font-black text-slate-900">$<?= number_format($booking['total_price'] - $amountCharged, 2) ?></span>
            </div>
        </div>
        <a href="index.php" class="inline-block bg-blue-600 text-white font-black px-12 py-4 rounded-2xl shadow-xl">Back to Gallery</a>
    </div>
</body>
</html>