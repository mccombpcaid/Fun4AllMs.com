<?php 
require_once 'bootstrap.php'; 

// 1. Get the Category ID from the URL
$cat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Fetch the Category Name for the header
$stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
$stmt->execute([$cat_id]);
$category = $stmt->fetch();

// 3. Redirect home if the category doesn't exist
if (!$category) {
    redirect('index.php');
}

// 4. Set the dynamic page title and include header
$pageTitle = $category['name'] . " | Fun 4 All MS";
include 'header.php'; 
?>

    <!-- Header Section -->
    <header class="bg-gradient-to-br from-blue-600 to-blue-800 py-20 text-white text-center">
        <div class="container mx-auto px-4">
            <h1 class="text-5xl md:text-7xl font-black uppercase tracking-tight mb-2">
                <?= e($category['name']); ?>
            </h1>
            <div class="h-2 w-24 bg-yellow-400 mx-auto rounded-full"></div>
            <p class="mt-6 text-blue-100 text-lg uppercase tracking-widest font-bold">Premium Rental Collection</p>
        </div>
    </header>

    <!-- Breadcrumbs -->
    <div class="container mx-auto px-4 py-6">
        <nav class="text-xs font-bold uppercase tracking-widest text-gray-400">
            <a href="index.php" class="hover:text-blue-600">Home</a> 
            <span class="mx-2">/</span> 
            <a href="rentals.php" class="hover:text-blue-600">All Rentals</a>
            <span class="mx-2">/</span> 
            <span class="text-gray-800"><?= e($category['name']); ?></span>
        </nav>
    </div>

    <!-- Product Grid -->
    <section class="pb-24 container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <?php
            // 5. Fetch all ACTIVE rentals for THIS category
            $stmt = $pdo->prepare("SELECT * FROM rentals WHERE category_id = ? AND status = 'active' ORDER BY id DESC");
            $stmt->execute([$cat_id]);
            $results = $stmt->fetchAll();

            if (empty($results)) {
                echo '<div class="col-span-full text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-gray-200">
                        <p class="text-gray-400 font-bold uppercase tracking-widest">No items found in this category yet.</p>
                      </div>';
            }

            foreach ($results as $row): ?>
                <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden group border border-gray-100 flex flex-col transition-transform hover:-translate-y-2">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?= e($row['image_url']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute top-4 right-4 bg-yellow-400 text-blue-900 font-black px-4 py-1 rounded-full shadow-md">
                            $<?= number_format($row['price'], 0); ?>
                        </div>
                    </div>
                    <div class="p-8 text-center flex-grow">
                        <h4 class="text-2xl font-black mb-6 text-gray-800 uppercase tracking-tight"><?= e($row['name']); ?></h4>
                        <a href="product.php?id=<?= $row['id']; ?>" class="inline-block w-full py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-yellow-400 hover:text-blue-900 transition-all shadow-lg uppercase tracking-widest">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php include 'footer.php'; ?>