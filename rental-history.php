<?php
require_once 'bootstrap.php';

// Security: Only logged-in admins allowed
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: admin-login.php");
    exit;
}

$rentalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch rental details
$stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ?");
$stmt->execute([$rentalId]);
$rental = $stmt->fetch();

if (!$rental) {
    die("Rental item not found.");
}

// Fetch all bookings for this specific item, newest first
$stmt = $pdo->prepare("
    SELECT * FROM bookings 
    WHERE rental_id = ? 
    ORDER BY booking_date DESC
");
$stmt->execute([$rentalId]);
$bookings = $stmt->fetchAll();

// Calculate lifetime revenue
$totalRevenue = 0;
foreach ($bookings as $b) {
    if ($b['payment_status'] === 'paid') {
        $totalRevenue += $b['total_price'];
    }
}

$pageTitle = "Revenue History: " . $rental['name'] . " | Admin";
include 'header.php';
?>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
        <div>
            <div class="flex items-center gap-4 mb-2">
                <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter <?= $rental['status'] === 'active' ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-500' ?>">
                    <?= $rental['status'] ?>
                </span>
                <a href="admin-inventory.php" class="text-blue-600 font-black uppercase text-[10px] tracking-widest hover:underline">← Back to Inventory</a>
            </div>
            <h1 class="text-5xl font-black text-slate-900 uppercase italic tracking-tighter leading-none">
                <?= htmlspecialchars($rental['name']) ?>
            </h1>
            <p class="text-slate-400 font-bold uppercase text-xs tracking-widest mt-2">Lifetime Performance Report</p>
        </div>

        <!-- REVENUE CARD -->
        <div class="bg-slate-900 text-white p-8 rounded-[2.5rem] shadow-2xl flex flex-col items-end transform hover:scale-105 transition-transform border-b-8 border-blue-600">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400 mb-2">Total Paid Revenue</span>
            <div class="text-4xl font-black italic tracking-tighter">\$<?= number_format($totalRevenue, 2) ?></div>
        </div>
    </div>

    <!-- BOOKING HISTORY TABLE -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50">
            <h2 class="text-sm font-black uppercase tracking-widest text-slate-500 italic">Rental Log (<?= count($bookings) ?> Bookings)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-100">
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($bookings as $b): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-6 whitespace-nowrap">
                                <div class="font-black text-slate-900 uppercase italic text-sm"><?= date('M d, Y', strtotime($b['booking_date'])) ?></div>
                            </td>
                            <td class="p-6">
                                <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($b['customer_name']) ?></div>
                                <div class="text-[10px] text-slate-400 font-medium"><?= htmlspecialchars($b['customer_phone']) ?></div>
                            </td>
                            <td class="p-6">
                                <span class="inline-block px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter <?= $b['payment_status'] === 'paid' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600' ?>">
                                    <?= $b['payment_status'] ?>
                                </span>
                            </td>
                            <td class="p-6 text-right font-black text-slate-900 text-sm whitespace-nowrap">
                                \$<?= number_format($b['total_price'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>