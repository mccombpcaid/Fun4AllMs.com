<?php
require_once 'bootstrap.php';
// admin-bookings.php (Top section)
// --- SECURE MANUAL CONFIRMATION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_id'])) {
    verify_csrf(); 
    
    $id = (int)$_POST['confirm_id'];
    $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?");
    $stmt->execute([$id]);
    
    // Optional: Add email confirmation logic here later if needed
    header("Location: admin-bookings.php?msg=Confirmed");
    exit;
}
// Security: Only logged-in admins allowed
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: admin-login.php");
    exit;
}

$pageTitle = "Manage All Bookings | Admin";
include 'header.php';

// Fetch all bookings, newest first, including rental names
$stmt = $pdo->query("
    SELECT b.*, r.name as rental_name 
    FROM bookings b 
    LEFT JOIN rentals r ON b.rental_id = r.id 
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();
?>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
        <h1 class="text-4xl font-black text-slate-900 uppercase italic tracking-tighter">Manage All Bookings</h1>
        <a href="admin-calendar.php" class="text-blue-600 font-black uppercase text-xs tracking-widest hover:underline">← Back to Calendar</a>
    </div>

    <!-- The Scrollable Wrapper (Option 1) -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px] md:min-w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Date / Status</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Rental</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Total</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($bookings as $b): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-6 whitespace-nowrap">
                                <div class="font-black text-slate-900 uppercase italic text-sm">
                                    <?= date('M d, Y', strtotime($b['booking_date'])) ?>
                                </div>
                                <span class="inline-block mt-1 px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter 
                                    <?= $b['payment_status'] === 'paid' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600' ?>">
                                    <?= $b['payment_status'] ?>
                                </span>
                            </td>
                            <td class="p-6">
                                <div class="font-bold text-slate-800 text-sm whitespace-nowrap"><?= htmlspecialchars($b['customer_name']) ?></div>
                                <div class="text-[10px] text-slate-400 font-medium"><?= htmlspecialchars($b['customer_phone']) ?></div>
                            </td>
                            <td class="p-6">
                                <div class="text-xs font-bold text-slate-600 uppercase tracking-wide whitespace-nowrap">
                                    <?= htmlspecialchars($b['rental_name'] ?? 'Unknown Item') ?>
                                </div>
                            </td>
                            <td class="p-6 text-right font-black text-slate-900 text-sm whitespace-nowrap">
                                $<?= number_format($b['total_price'], 2) ?>
                            </td>
                            <td class="p-6 text-center whitespace-nowrap space-x-2">
    <!-- SECURE MARK AS PAID BUTTON -->
    <?php if ($b['payment_status'] !== 'paid'): ?>
        <form method="POST" class="inline">
            <?= csrf_input() ?>
            <input type="hidden" name="confirm_id" value="<?= $b['id'] ?>">
            <button type="submit" 
                    onclick="return confirm('Mark this booking as Paid?')"
                    class="bg-green-600 text-white px-5 py-2 rounded-full font-black uppercase text-[10px] tracking-widest shadow-md hover:bg-green-700 transition italic inline-block">
            Paid
            </button>
        </form>
    <?php endif; ?>

    <!-- RED DELETE BUTTON -->
    <form method="POST" action="delete-booking.php" class="inline">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= $b['id'] ?>">
        <button type="submit" 
                onclick="return confirm('PERMANENTLY DELETE THIS BOOKING?')"
                class="bg-red-500 text-white px-5 py-2 rounded-full font-black uppercase text-[10px] tracking-widest shadow-md hover:bg-red-600 transition italic inline-block">
        Delete
        </button>
    </form>
</td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="5" class="p-20 text-center text-slate-300 font-black uppercase tracking-widest">
                                No bookings found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>