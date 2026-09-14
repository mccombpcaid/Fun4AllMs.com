<?php
require_once 'bootstrap.php';
$pageTitle = "Your Rental Cart | Fun 4 All MS";
include 'header.php';

// 1. Fetch items currently in the cart
$cartItems = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id IN ($placeholders)");
    $stmt->execute($_SESSION['cart']);
    $cartItems = $stmt->fetchAll();
    
    foreach ($cartItems as $item) {
        $subtotal += $item['price'];
    }
}

$unitCount = count($cartItems);
$depositAmount = $unitCount * 100;
?>

<style>
    /* Google Maps Autocomplete Dropdown styling */
    .pac-container {
        z-index: 2000 !important;
        border-radius: 1.5rem;
        margin-top: 5px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        font-family: inherit;        
    }
    
    /* Prevent browser from overlaying its own suggestions */
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0px 1000px #f8fafc inset !important;
        transition: background-color 5000s ease-in-out 0s;
    }

    /* Layout reset: Remove sticky behavior that caused sliding issues */
    .checkout-card-fixed {
        position: relative !important;
        top: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }
</style>

<main class="max-w-6xl mx-auto px-6 py-12">

    <!-- AVAILABILITY ALERT -->
    <!-- UPDATED: AVAILABILITY ALERT -->
    <?php if(isset($_GET['error']) && $_GET['error'] === 'unavailable'): ?>
        <div class="bg-red-600 text-white p-6 rounded-[2rem] mb-8 shadow-xl shadow-red-200 text-center border-4 border-red-500">
            <h4 class="text-xl font-black uppercase italic tracking-tighter">Fully Booked!</h4>
            <p class="text-[10px] font-bold uppercase tracking-widest mt-1">
                Our "<?= htmlspecialchars($_GET['item'] ?? 'Inventory') ?>" is already reserved for <?= htmlspecialchars($_GET['date']) ?>.
            </p>
            <p class="text-[9px] font-black uppercase tracking-[0.2em] mt-3 opacity-75 text-white/80">
                WE HAVE <?= htmlspecialchars($_GET['qty'] ?? '0') ?> UNITS LEFT. PLEASE TRY ANOTHER DATE OR UNIT.
            </p>
        </div>
    <?php endif; ?>

    <h1 class="text-5xl font-black text-slate-900 uppercase italic mb-10 tracking-tighter text-center md:text-left">Your Cart</h1>

    <?php if (empty($cartItems)): ?>
        <div class="bg-white p-20 rounded-[3rem] text-center border border-slate-200 shadow-sm">
            <p class="text-slate-400 font-black uppercase tracking-widest mb-8">Your cart is empty</p>
            <a href="index.php" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-xs inline-block">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="grid lg:grid-cols-3 gap-12">
            
            <!-- Items List -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($cartItems as $item): ?>
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-6">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-20 h-20 object-cover rounded-2xl shadow-sm border border-slate-50">
                            <div>
                                <h4 class="text-xl font-black text-slate-800 uppercase italic leading-tight"><?= htmlspecialchars($item['name']) ?></h4>
                                <p class="text-blue-600 font-black text-sm">$<?= number_format($item['price'], 2) ?></p>
                            </div>
                        </div>
                        <a href="remove-from-cart.php?id=<?= $item['id'] ?>" class="text-red-400 hover:text-red-600 font-black uppercase text-[10px] tracking-widest">Remove</a>
                    </div>
                <?php endforeach; ?>
                <a href="index.php" class="inline-block pt-4 text-blue-600 font-black uppercase text-xs tracking-widest hover:underline">+ Add more items</a>
            </div>

            <!-- Checkout Box: Fixed Layout -->
            <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-2xl checkout-card-fixed">
                
            <?php echo csrf_input(); ?> 
                    <h3 class="text-2xl font-black text-slate-900 uppercase italic mb-4">Checkout</h3>
                    
                    <div class="space-y-4">
                        <input type="text" name="customer_name" required placeholder="Full Name" autocomplete="name" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50">
                        <input type="email" name="customer_email" required placeholder="Email Address" autocomplete="email" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50">
                        <input type="text" name="customer_phone" required placeholder="Phone Number" autocomplete="tel" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50">
                        <input type="date" name="booking_date" required class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50">
                        
                        <!-- Nuclear Option Address Field -->
                        <div class="relative">
                            <input type="password" id="cust_address" name="customer_address" required 
                                   placeholder="Delivery Address..." 
                                   readonly
                                   onfocus="this.removeAttribute('readonly'); this.type='text';"
                                   autocomplete="new-password" 
                                   class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50">
                            
                            <div id="addr-check" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div id="delivery-status" class="p-5 rounded-2xl bg-blue-50 border border-blue-100 text-[10px] font-black text-blue-600 hidden justify-between items-center shadow-inner tracking-widest uppercase italic"></div>

                    <div class="pt-6 border-t border-slate-100 space-y-2 mb-4">
                        <div class="flex justify-between text-slate-400 font-bold uppercase text-[9px] tracking-widest">
                            <span>Total Rental Value</span>
                            <span>$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                            <span>Deposit (<?= $unitCount ?> Units x $100)</span>
                            <span>$<?= number_format($depositAmount, 2) ?></span>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-xl space-y-2 mt-4 border border-slate-100">
                            <div class="flex justify-between text-slate-500 font-bold uppercase text-[9px] tracking-widest">
                                <span>Sales Tax (7%)</span>
                                <span id="tax-display">$---</span>
                            </div>
                            <div class="flex justify-between text-slate-500 font-bold uppercase text-[9px] tracking-widest">
                                <span>Processing Fee (2.9% + 30¢)</span>
                                <span id="fee-display">$---</span>
                            </div>
                        </div>

                        <div class="flex justify-between text-2xl font-black text-slate-900 italic uppercase pt-4">
                            <span>Due Today</span>
                            <span>$<span id="total-display">---</span></span>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" disabled class="w-full bg-slate-200 text-slate-400 font-black py-6 rounded-[2rem] transition-all shadow-xl uppercase italic tracking-widest text-lg cursor-not-allowed">
                        Verify Address to Book
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= $_ENV['GOOGLE_MAPS_KEY']; ?>&libraries=places"></script>
<script>
    const addressInput = document.getElementById('cust_address');
    const statusDiv = document.getElementById('delivery-status');
    const totalDisplay = document.getElementById('total-display');
    const taxDisplay = document.getElementById('tax-display');
    const feeDisplay = document.getElementById('fee-display');
    const submitBtn = document.getElementById('submitBtn');
    const addrCheck = document.getElementById('addr-check');
    
    const unitCount = <?= (int)$unitCount; ?>;
    const warehouse = "1411 Vermont Ave, McComb, MS"; 

    let deliveryVerified = false;

    // Nuclear Option: Force clear and handle readonly
    window.addEventListener('load', () => {
        addressInput.value = '';
        setTimeout(() => { addressInput.removeAttribute('readonly'); }, 150);
    });

    const autocomplete = new google.maps.places.Autocomplete(addressInput, { 
        componentRestrictions: { country: "us" },
        fields: ["formatted_address"]
    });

    autocomplete.addListener("place_changed", () => {
        const place = autocomplete.getPlace();
        if (place.formatted_address) {
            calculateDeposit(place.formatted_address);
        } else {
            resetValidation();
        }
    });

    addressInput.addEventListener('input', () => { if(deliveryVerified) resetValidation(); });

    function resetValidation() {
        deliveryVerified = false;
        submitBtn.disabled = true;
        submitBtn.innerText = "Verify Address to Book";
        submitBtn.className = "w-full bg-slate-200 text-slate-400 font-black py-6 rounded-[2rem] transition-all shadow-xl uppercase italic tracking-widest text-lg cursor-not-allowed";
        addrCheck.classList.replace('text-green-500', 'text-slate-300');
        statusDiv.classList.add('hidden');
        taxDisplay.innerText = "$---";
        feeDisplay.innerText = "$---";
        totalDisplay.innerText = "---";
    }

    function calculateDeposit(destination) {
        statusDiv.classList.remove('hidden');
        statusDiv.classList.add('flex');
        statusDiv.innerHTML = '<span class="animate-pulse">Calculating Delivery...</span>';

        const service = new google.maps.DistanceMatrixService();
        service.getDistanceMatrix({
            origins: [warehouse],
            destinations: [destination],
            travelMode: 'DRIVING',
            unitSystem: google.maps.UnitSystem.IMPERIAL,
        }, (response, status) => {
            if (status === 'OK') {
                const el = response.rows[0].elements[0];
                if (el.status === 'OK') {
                    const miles = el.distance.value * 0.000621371;
                    
                    if (miles > 50) {
                        resetValidation();
                        statusDiv.innerHTML = '<span class="text-red-500">Address outside delivery zone</span>';
                        submitBtn.innerText = "Manual Quote Required";
                        return;
                    }

                    let deliveryFee = (miles <= 10) ? 25.00 : Math.ceil((miles * 4) / 5) * 5;
                    const depositBase = (unitCount * 100) + deliveryFee;
                    
                    const tax = depositBase * 0.07;
                    const stripeFee = (depositBase * 0.029) + 0.30;
                    const dueToday = depositBase + tax + stripeFee;

                    deliveryVerified = true;
                    addrCheck.classList.replace('text-slate-300', 'text-green-500');
                    statusDiv.innerHTML = `<span>📍 ${el.distance.text}</span> <span class="bg-white px-3 py-1 rounded-full text-green-600">+$${deliveryFee.toFixed(2)} DELIVERY</span>`;
                    
                    taxDisplay.innerText = `$${tax.toFixed(2)}`;
                    feeDisplay.innerText = `$${stripeFee.toFixed(2)}`;
                    totalDisplay.innerText = dueToday.toFixed(2);

                    submitBtn.disabled = false;
                    submitBtn.innerText = "Pay Deposit & Book";
                    submitBtn.className = "w-full bg-blue-600 text-white font-black py-6 rounded-[2rem] hover:bg-blue-700 transition shadow-xl shadow-blue-100 text-lg uppercase italic tracking-widest cursor-pointer";
                }
            }
        });
    }
</script>

<?php include 'footer.php'; ?>