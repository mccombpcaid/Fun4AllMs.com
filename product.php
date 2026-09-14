<?php
require_once 'bootstrap.php';

// 1. Get the Product ID from the URL (e.g. product.php?id=8)
$productId = filter_var($_GET['id'] ?? 1, FILTER_SANITIZE_NUMBER_INT);

try {
    // 2. UNIFIED: Pulling from 'rentals' table which the Admin manages
    $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        die("Error: Product #$productId not found in the Rental Inventory.");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'header.php'; 
?>

    <main class="max-w-6xl mx-auto px-6 py-12">
        <div class="grid lg:grid-cols-2 gap-16 items-start">
            
            <!-- Product Display -->
            <div class="space-y-8">
                <div class="aspect-video bg-slate-200 rounded-[2.5rem] overflow-hidden border-4 border-white shadow-2xl relative">
                    <?php if(!empty($product['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-slate-400 font-black italic uppercase text-xs">No Photo Available</div>
                    <?php endif; ?>
                </div>

                <div class="space-y-4">
                    <h1 class="text-5xl font-black text-slate-900 tracking-tight"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="text-xl text-slate-500 leading-relaxed"><?php echo htmlspecialchars($product['description']); ?></p>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm inline-flex items-center space-x-6">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase block mb-1 tracking-widest">Deposit Due Now</span>
                        <span class="text-4xl font-black text-blue-600 tracking-tighter">$100.00 <span class="text-sm text-slate-400 tracking-normal">+ fees & taxes</span></span>
                        <div class="mt-2 pt-2 border-t border-slate-50">
                            <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest italic">Full Price: $<?= number_format($product['price'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="pt-10">
                <form action="add-to-cart.php" method="POST">
                    <?php echo csrf_input(); ?> 
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                    <button type="submit" class="w-full bg-blue-600 text-white font-black py-6 rounded-[2.5rem] hover:bg-blue-700 transition shadow-2xl shadow-blue-200 text-xl uppercase italic tracking-[0.2em]">
                        Add to Rental Cart;
                    </button>
                </form>
                <p class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest mt-6 italic">
                    *One delivery fee applied in cart
                </p>
            </div>
        </div>
    </main>

<?php include 'footer.php'; ?>