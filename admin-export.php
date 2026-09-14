<?php
require_once 'bootstrap.php';
require_login();

// Set headers to force download of a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Fun4All_Taxes_' . date('Y-m-d') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Set the column headers for your tax spreadsheet
fputcsv($output, ['Booking ID', 'Date', 'Customer Name', 'Email', 'Rental Item', 'Amount Paid', 'Status']);

// Fetch only PAID bookings for tax reporting
$query = "SELECT b.id, b.booking_date, b.customer_name, b.customer_email, r.name as rental_name, r.price, b.payment_status 
          FROM bookings b 
          JOIN rentals r ON b.rental_id = r.id 
          WHERE b.payment_status = 'paid' 
          ORDER BY b.booking_date ASC";

$stmt = $pdo->query($query);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;