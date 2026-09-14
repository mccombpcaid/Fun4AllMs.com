<?php
require_once 'bootstrap.php';

// Security: Only logged-in admins allowed
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: admin-login.php");
    exit;
}

$uploadDir = 'images/rentals/';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Fetch Item Details
$stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: admin-inventory.php?msg=Item Not Found");
    exit;
}

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    // Keep your CSRF and Validation Logic
    if(function_exists('verify_csrf')) { verify_csrf(); }

    $image_url = trim($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // --- YOUR SECURE UPLOAD HANDLER ---
    if (!empty($_FILES['image_file']['tmp_name'])) {
        $file = $_FILES['image_file'];

        if ($file['size'] > 2 * 1024 * 1024) {
             header("Location: edit-item.php?id=$id&msg=Error: File too large");
             exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowed)) {
             header("Location: edit-item.php?id=$id&msg=Error: Invalid file type");
             exit;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(8)) . '.' . $extension;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
            if (!empty($item['image_url']) && strpos($item['image_url'], $uploadDir) === 0) {
                @unlink($item['image_url']);
            }
            $image_url = $uploadDir . $safeName;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE rentals
        SET name = ?, price = ?, quantity = ?, description = ?, category_id = ?, image_url = ?, is_featured = ?
        WHERE id = ?
    ");

    $stmt->execute([
        trim($_POST['name']),
        (float)$_POST['price'],
        (int)$_POST['quantity'],
        trim($_POST['description']),
        (int)$_POST['category_id'],
        $image_url,
        $is_featured,
        $id
    ]);

    header("Location: admin-inventory.php?msg=Updated Successfully");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$pageTitle = "Edit " . $item['name'] . " | Admin";
include 'header.php';
?>

<main class="max-w-4xl mx-auto px-6 py-12">
    <div class="flex justify-between items-center mb-10">
        <div>
            <a href="admin-inventory.php" class="text-blue-600 font-black uppercase text-[10px] tracking-widest hover:underline">← Back to Inventory</a>
            <h1 class="text-4xl font-black text-slate-900 uppercase italic tracking-tighter mt-2">Edit Rental</h1>
        </div>
        <?php if(isset($_GET['msg'])): ?>
            <div class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest animate-pulse border border-red-100">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl space-y-8">
        <?php if(function_exists('csrf_input')) { echo csrf_input(); } ?>

        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Item Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50 text-sm">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Category</label>
                <select name="category_id" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50 text-sm appearance-none">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $c['id'] == $item['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Price, Qty, Featured -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Daily Price ($)</label>
                <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50 text-sm">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Total Units in Stock</label>
                <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="0" required class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50 text-sm">
            </div>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest ml-2">Featured Item</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" <?= !empty($item['is_featured']) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        <!-- Image Management -->
        <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-200">
            <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-4 ml-2">Item Media</label>
            <div class="flex flex-col md:flex-row gap-8 items-center">
                <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-32 h-32 object-cover rounded-[1.5rem] shadow-lg border-4 border-white">
                <?php endif; ?>
                <div class="flex-1 w-full space-y-4">
                    <div class="bg-white p-4 rounded-xl border border-slate-200">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2">Upload New Photo (JPG/PNG/WEBP)</p>
                        <input type="file" name="image_file" class="w-full text-xs text-slate-500 font-bold">
                    </div>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>" class="w-full p-4 rounded-xl border border-slate-200 text-[10px] font-bold bg-white outline-none focus:ring-2 focus:ring-blue-100" placeholder="...or use a specific URL">
                </div>
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest block mb-2 ml-2">Public Description</label>
            <textarea name="description" rows="5" class="w-full p-6 rounded-[2rem] bg-slate-50 border border-slate-200 font-bold outline-none focus:ring-4 focus:ring-blue-50 text-sm italic shadow-inner" placeholder="Enter details for the customers..."><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" name="update_item" value="1" class="w-full bg-slate-900 text-white font-black py-5 rounded-2xl hover:bg-black transition shadow-xl shadow-slate-200 uppercase italic tracking-[0.2em] text-sm">
            Save Changes to Database
        </button>
    </form>
</main>

<?php include 'footer.php'; ?>