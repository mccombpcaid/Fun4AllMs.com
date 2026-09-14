<?php
require_once 'bootstrap.php';
$pageTitle = "Fun 4 All MS | Inflatable Rentals McComb, MS";
include 'header.php';
?>

<!-- Hero Section -->
<section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden bg-slate-900">
    <div class="absolute inset-0 z-0">
        <img src="assets/hero-bg.jpg" class="w-full h-full object-cover opacity-40 grayscale-[20%]" alt="Fun 4 All MS Inflatables">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-slate-900/60 to-slate-900"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10 text-center">
        <div class="inline-block mb-6 px-6 py-2 bg-blue-600/20 backdrop-blur-xl border border-blue-500/30 rounded-full">
            <span class="text-blue-400 font-black uppercase tracking-[0.3em] text-[10px]">Serving McComb & Surrounding Areas</span>
        </div>
        <h1 class="text-6xl md:text-8xl font-black text-white uppercase italic tracking-tighter leading-none mb-8">
            BIG FUN. <br>
            <span class="text-blue-500">SMALL PRICES.</span>
        </h1>
        <p class="text-slate-300 max-w-2xl mx-auto font-bold text-lg mb-12 uppercase tracking-wide">
            Mississippi's premier destination for high-end bouncy houses, water slides, and obstacle courses.
        </p>
        <div class="flex flex-col md:flex-row items-center justify-center gap-6">
            <a href="#inventory" class="w-full md:w-auto bg-blue-600 text-white font-black px-12 py-6 rounded-[2.5rem] shadow-2xl shadow-blue-500/20 hover:bg-blue-700 transition-all uppercase italic tracking-widest">
                Browse Inventory
            </a>
            <a href="tel:6018100119" class="w-full md:w-auto bg-white/10 backdrop-blur-md text-white border border-white/10 font-black px-12 py-6 rounded-[2.5rem] hover:bg-white/20 transition-all uppercase italic tracking-widest">
                601-810-0119
            </a>
        </div>
    </div>
</section>

<!-- Trust Bar -->
<div class="bg-white border-b border-slate-100 py-10">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="text-center">
                <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-2">Safety First</p>
                <p class="text-slate-900 font-black italic uppercase">Insured & Inspected</p>
            </div>
            <div class="text-center border-l border-slate-100">
                <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-2">Delivery</p>
                <p class="text-slate-900 font-black italic uppercase">McComb & Beyond</p>
            </div>
            <div class="text-center border-l border-slate-100">
                <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-2">Cleanliness</p>
                <p class="text-slate-900 font-black italic uppercase">Sanitized Weekly</p>
            </div>
            <div class="text-center border-l border-slate-100">
                <p class="text-slate-400 font-black uppercase text-[10px] tracking-widest mb-2">Payment</p>
                <p class="text-slate-900 font-black italic uppercase">Secure Checkout</p>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Section -->
<section id="inventory" class="py-32 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-end mb-20">
            <div class="max-w-xl">
                <span class="text-blue-600 font-black uppercase tracking-widest text-[10px]">Ready to Rent</span>
                <h2 class="text-5xl font-black text-slate-900 uppercase italic tracking-tighter mt-4 leading-none">
                    Choose Your <span class="text-blue-600">Adventure</span>
                </h2>
            </div>
            <div class="mt-8 md:mt-0 text-right">
                <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest italic">All prices include full day rentals</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php
            // Pulling ALL rentals, newest first. 
            // To show ONLY featured items, change the line below to:
            // $stmt = $pdo->query("SELECT * FROM rentals WHERE is_featured = 1 ORDER BY id DESC");
            $stmt = $pdo->query("SELECT * FROM rentals WHERE is_featured = 1 AND status = 'active' ORDER BY id DESC");
            
            if ($stmt->rowCount() > 0):
                while ($row = $stmt->fetch()):
            ?>
                <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/50 overflow-hidden group border border-slate-100 flex flex-col transition-all duration-500 hover:-translate-y-4 hover:shadow-2xl">
                    <div class="relative h-80 overflow-hidden">
                        <img src="<?= htmlspecialchars($row['image_url']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="absolute top-8 left-8">
                            <div class="bg-white/90 backdrop-blur-xl px-6 py-2 rounded-2xl shadow-xl border border-white/20">
                                <span class="text-blue-600 font-black text-xl italic">$<?= number_format($row['price'], 0) ?></span>
                                <span class="text-[8px] font-black uppercase text-slate-400 tracking-widest block -mt-1">Per Day</span>
                            </div>
                        </div>

                        <?php if(isset($row['is_featured']) && $row['is_featured']): ?>
                        <div class="absolute top-8 right-8">
                            <span class="bg-blue-600 text-white px-4 py-2 rounded-xl text-[8px] font-black uppercase tracking-widest shadow-xl italic">Featured Fun</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-10 flex-grow flex flex-col items-center text-center">
                        <h4 class="text-3xl font-black text-slate-900 uppercase italic tracking-tighter mb-4 leading-tight leading-none">
                            <?= htmlspecialchars($row['name']) ?>
                        </h4>
                        <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mb-8 line-clamp-2">
                            <?= htmlspecialchars($row['description']) ?>
                        </p>
                        
                        <div class="mt-auto w-full">
                            <a href="product.php?id=<?= $row['id'] ?>" class="inline-block w-full py-5 bg-slate-900 text-white rounded-[2rem] font-black hover:bg-blue-600 transition-all shadow-xl uppercase italic tracking-widest text-xs">
                                Check Availability &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="col-span-full py-20 text-center">
                    <p class="text-slate-300 font-black uppercase tracking-[0.5em] text-xs italic">Updating Inventory...</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-32 bg-white text-center">
    <div class="container mx-auto px-6">
        <h2 class="text-5xl md:text-7xl font-black text-slate-900 uppercase italic tracking-tighter mb-8 leading-none">
            WANT SOMETHING <br> <span class="text-blue-600 underline decoration-blue-100">BIGGER?</span>
        </h2>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs mb-12">
            Multi-unit packages and custom events are our specialty.
        </p>
        <a href="tel:6018100119" class="inline-block bg-blue-600 text-white font-black px-16 py-7 rounded-[3rem] shadow-2xl shadow-blue-500/30 hover:bg-blue-700 transition-all uppercase italic tracking-widest text-lg">
            Call for Quote
        </a>
    </div>
</section>

<?php include 'footer.php'; ?>