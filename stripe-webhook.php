<?php
// stripe-webhook.php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/stripe-config.php';
require_once __DIR__ . '/mail-config.php';
require_once __DIR__ . '/mail-template.php';

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
$endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? ''; 

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(Exception $e) {
    http_response_code(400); exit();
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $bookingId = $session->metadata->booking_id;

    // Update DB
    $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?");
    $stmt->execute([$bookingId]);

    // Fetch Details
    $stmt = $pdo->prepare("SELECT b.*, r.name as rental_name, r.price as full_price FROM bookings b JOIN rentals r ON b.rental_id = r.id WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if ($booking) {
        $balanceDue = $booking['full_price'] - 100.00;
        $title = "Deposit Received!";
        
        $content = "
            <p>Hi " . e($booking['customer_name']) . ",</p>
            <p>Your deposit of <strong>$110.51</strong> has been received, securing your date for the <strong>" . e($booking['rental_name']) . "</strong>.</p>
            
            <div style='background: #fffbeb; padding: 25px; border-radius: 15px; margin: 25px 0; border: 1px solid #fcd34d;'>
                <p style='margin: 0; color: #92400e;'><strong>REMAINING BALANCE:</strong><br><span style='font-size: 24px;'>$" . number_format($balanceDue, 2) . "</span></p>
                <p style='margin: 10px 0 0 0; font-size: 12px; color: #b45309;'>Payable via Cash or Card upon delivery.</p>
            </div>

            <p>We'll see you on <strong>" . date('M d, Y', strtotime($booking['booking_date'])) . "</strong>!</p>
        ";

        $body = getBrandedTemplate($title, $content);
        sendBookingEmail($booking['customer_email'], "Deposit Confirmation - Fun 4 All MS", $body);
    }
}

http_response_code(200);