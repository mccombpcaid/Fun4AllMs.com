<?php 
require_once 'bootstrap.php'; 
$pageTitle = "Full Inventory | Fun 4 All MS";
include 'header.php'; 
?>

    <!-- Header -->
    <header class="bg-blue-600 py-16 text-white text-center">
        <h1 class="text-4xl md:text-6xl font-black uppercase tracking-tight">Full Inventory</h1>
        <p class="mt-4 text-blue-100 text-lg">Browse our complete collection of party fun!</p>
    </header>

    <!-- Full Inventory Grid -->
    <section class="py-16 container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <?php
            // Pull EVERY active rental, joined with category name
            $stmt = $pdo->query("
                SELECT r.*, c.name as cat_name 
                FROM rentals r 
                JOIN categories c ON r.category_id = c.id 
                WHERE r.status = 'active' 
                ORDER BY cat_name ASC
            ");
            
            while ($row = $stmt->fetch()): ?>
                <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden group border border-gray-100 flex flex-col transition-transform hover:-translate-y-2">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?= e($row['image_url']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase text-blue-600 shadow-sm">
                            <?= e($row['cat_name']); ?>
                        </div>
                    </div>
                    <div class="p-8 text-center flex-grow">
                        <h4 class="text-2xl font-black mb-2 text-gray-800 uppercase tracking-tight"><?= e($row['name']); ?></h4>
                        <p class="text-red-500 font-bold mb-6">$<?= number_format($row['price'], 0); ?> / Day</p>
                        <a href="product.php?id=<?= $row['id']; ?>" class="inline-block w-full py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-yellow-400 hover:text-blue-900 transition-all shadow-lg uppercase tracking-widest">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

<?php include 'footer.php'; ?>