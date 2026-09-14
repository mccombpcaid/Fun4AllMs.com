<?php
require_once 'bootstrap.php';
require_login();

$uploadDir = 'images/rentals/';
$msg = $_GET['msg'] ?? '';

// 1. LIVE FEATURE TOGGLE HANDLER (New)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_featured'])) {
    verify_csrf();
    $id = (int)$_POST['id'];
    $current = (int)$_POST['current_status'];
    $new_status = ($current === 1) ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE rentals SET is_featured = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    
    redirect('admin-inventory.php?msg=Featured Status Updated');
}

// 2. CONSOLIDATED ADD LOGIC (Updated with Featured Switch)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    verify_csrf();

    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $image_url = ''; 
    $status = 'active';

    if (!empty($_FILES['rental_image']['tmp_name'])) {
        $file = $_FILES['rental_image'];
        
        if ($file['size'] > 2 * 1024 * 1024) {
             redirect('admin-inventory.php?msg=Error: File too large');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowed)) {
             redirect('admin-inventory.php?msg=Error: Invalid file type');
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(8)) . '.' . $extension;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
            $image_url = $uploadDir . $safeName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO rentals (name, price, image_url, status, is_featured) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $price, $image_url, $status, $is_featured]);

    redirect('admin-inventory.php?msg=Item Added Successfully');
}

// 3. FETCH DATA
$activeRentals = $pdo->query("SELECT * FROM rentals WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$retiredRentals = $pdo->query("SELECT * FROM rentals WHERE status = 'retired' ORDER BY name ASC")->fetchAll();

$pageTitle = "Inventory Management | Admin";
include 'header.php';
?>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
        <h1 class="text-4xl font-black text-slate-900 uppercase italic tracking-tighter">Inventory Manager</h1>
        <div class="flex gap-4">
             <?php if ($msg): ?>
                <span class="bg-blue-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase shadow-lg italic animate-bounce"><?= e($msg) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- QUICK ADD FORM -->
    <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl mb-12">
        <h2 class="text-2xl font-black text-slate-900 uppercase italic mb-6">Add New Equipment</h2>
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
            <?= csrf_input() ?>
            <div>
                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Item Name</label>
                <input type="text" name="name" required placeholder="e.g. Blue Crush Slide" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50 text-sm">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Daily Price</label>
                <input type="number" name="price" step="0.01" required placeholder="250.00" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50 text-sm">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Item Image</label>
                <input type="file" name="rental_image" accept="image/*" required class="w-full p-3 rounded-2xl bg-slate-50 border border-slate-200 font-bold text-[10px] text-slate-500">
            </div>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-2">Feature Home</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-400"></div>
                </label>
            </div>
            <button type="submit" name="add_item" class="bg-blue-600 text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-100 uppercase italic tracking-widest text-xs">
                Upload Item
            </button>
        </form>
    </div>

    <!-- INVENTORY TABLE -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Preview</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Item Name</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Featured Toggle</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Daily Rate</th>
                        <th class="p-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($activeRentals as $r): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-6">
                                <img src="<?= e($r['image_url']) ?>" alt="" class="w-16 h-12 object-cover rounded-xl shadow-sm border border-slate-100">
                            </td>
                            <td class="p-6">
                                <div class="font-black text-slate-900 uppercase italic text-sm"><?= e($r['name']) ?></div>
                                <div class="text-[8px] font-black text-green-500 uppercase tracking-widest">Active</div>
                            </td>
                            <td class="p-6">
                                <!-- CLICKABLE FEATURE TOGGLE -->
                                <form method="POST" class="inline">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= (int)$r['is_featured'] ?>">
                                    <button type="submit" name="toggle_featured" class="group relative inline-flex items-center cursor-pointer outline-none">
                                        <div class="w-10 h-5 rounded-full transition-colors border border-slate-200 
                                            <?= $r['is_featured'] ? 'bg-yellow-400 border-yellow-500' : 'bg-slate-100' ?>"></div>
                                        <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow-sm transform transition-transform
                                            <?= $r['is_featured'] ? 'translate-x-5' : 'translate-x-0' ?>"></div>
                                        <span class="ml-3 text-[9px] font-black uppercase tracking-tighter 
                                            <?= $r['is_featured'] ? 'text-yellow-700 italic' : 'text-slate-300' ?>">
                                            <?= $r['is_featured'] ? '★ Featured' : 'Standard' ?>
                                        </span>
                                    </button>
                                </form>
                            </td>
                            <td class="p-6 font-black text-slate-900 text-sm">$<?= number_format($r['price'], 2) ?></td>
                            <td class="p-6 text-center whitespace-nowrap space-x-2">
                                <a href="edit-item.php?id=<?= $r['id']?>" class="bg-slate-800 text-white px-5 py-2 rounded-full font-black uppercase text-[10px] tracking-widest hover:bg-slate-900 transition italic inline-block">Edit</a>
                                <form method="POST" action="retire-rental.php" class="inline">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button type="submit" onclick="return confirm('Retire this item?')" class="bg-red-500 text-white px-5 py-2 rounded-full font-black uppercase text-[10px] tracking-widest shadow-md hover:bg-red-600 transition italic inline-block">Retire</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>