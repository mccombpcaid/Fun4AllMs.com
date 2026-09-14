<?php
require_once __DIR__ . '/bootstrap.php';

if (!is_logged_in()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            b.id,
            b.customer_name,
            b.booking_date,
            b.status,
            r.name AS rental_name
        FROM bookings b
        JOIN rentals r ON b.rental_id = r.id
    ");

    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $color = '#3B82F6'; // Blue (Pending)
        if ($row['status'] === 'confirmed') $color = '#10B981'; // Green
        if ($row['status'] === 'paid') $color = '#8B5CF6'; // Purple

        $events[] = [
            'id' => (int)$row['id'],
            'title' => e($row['customer_name'] . ' - ' . $row['rental_name']),
            'start' => $row['booking_date'],
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'rental_name' => e($row['rental_name']),
                'status' => e($row['status'])
            ]
        ];
    }

    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}