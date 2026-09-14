<?php 
require_once 'bootstrap.php';
require_login(); 

// --- STATS LOGIC ---

// 1. Total Revenue (Current Month) - Summing rental prices for paid bookings
$revenueStmt = $pdo->query("SELECT SUM(r.price) FROM bookings b JOIN rentals r ON b.rental_id = r.id WHERE b.payment_status = 'paid' AND MONTH(b.booking_date) = MONTH(CURRENT_DATE())");
$monthlyRevenue = $revenueStmt->fetchColumn() ?: 0;

// 2. Pending Requests Count
$pendingCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();

// 3. Upcoming Deliveries (Next 48 Hours)
$upcomingStmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 2 DAY) AND status = 'confirmed'");
$upcomingDeliveries = $upcomingStmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Fun 4 All MS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <style>
        #calendar {
            min-height: 500px;
            background: white;
            padding: 15px;
            border-radius: 2rem;
        }
        /* Style adjustments for FullCalendar buttons */
        .fc-button-primary { background-color: #1e40af !important; border: none !important; text-transform: uppercase; font-size: 11px !important; font-weight: bold; }
        .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 900; text-transform: uppercase; color: #1e3a8a; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="flex flex-col md:flex-row min-h-screen">
        
<?php include 'admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8">
            
            <!-- 1. QUICK STATS BAR -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8">
                <!-- Revenue Card -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border-l-8 border-green-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Monthly Revenue</p>
                    <h3 class="text-3xl font-black text-gray-800">$<?= number_format($monthlyRevenue, 0) ?></h3>
                </div>
                <!-- Pending Card -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border-l-8 border-orange-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pending Requests</p>
                    <h3 class="text-3xl font-black text-gray-800"><?= $pendingCount ?></h3>
                </div>
                <!-- Delivery Card -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border-l-8 border-blue-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Deliveries (48h)</p>
                    <h3 class="text-3xl font-black text-gray-800"><?= $upcomingDeliveries ?></h3>
                </div>
            </div>

            <!-- 2. CALENDAR VIEW -->
            <div class="bg-white shadow-xl rounded-[2.5rem] overflow-hidden border border-gray-100">
                <div id="calendar"></div>
            </div>

        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
            height: 'auto', // Important for mobile sizing
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth,timeGridWeek'
            },
            events: 'api-bookings.php',
            eventClick: function(info) {
                alert('Event: ' + info.event.title);
            },
            windowResize: function(view) {
                if (window.innerWidth < 768) {
                    calendar.changeView('listMonth');
                } else {
                    calendar.changeView('dayGridMonth');
                }
            }
        });
        calendar.render();
    });
    </script>
</body>
</html>