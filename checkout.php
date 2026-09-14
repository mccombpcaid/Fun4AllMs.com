<?php
require_once 'bootstrap.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

$bookingId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
if (!$bookingId) die("Invalid Booking.");

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) die("Booking not found.");

// Deposit Calculation
$unitCount = $_SESSION['last_booking_unit_count'] ?? 1;
$deliveryFee = (float)$booking['delivery_fee'];
$taxRate = 0.07;

$depositBase = ($unitCount * 100) + $deliveryFee;
$depositWithTax = $depositBase * (1 + $taxRate);

// Stripe Gross-Up
$stripeTotal = ($depositWithTax + 0.30) / (1 - 0.029);
$finalStripeAmount = round($stripeTotal, 2);

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Rental Deposit (' . $unitCount . ' Units)',
                    'description' => 'Remaining balance due on delivery.',
                ],
                'unit_amount' => (int)($finalStripeAmount * 100), 
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/Web-design/fun4allms/success.php?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $bookingId,
        'cancel_url'  => 'http://localhost/Web-design/fun4allms/cart.php',
        'customer_email' => $booking['customer_email'],
        'metadata' => ['booking_id' => $bookingId]
    ]);

    header("Location: " . $session->url);
    exit;
} catch (Exception $e) {
    die("Payment Error: " . $e->getMessage());
}