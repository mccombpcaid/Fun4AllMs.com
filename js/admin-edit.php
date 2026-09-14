<?php 
require 'auth.php'; 
require 'db.php'; 

$id = $_GET['id'] ?? die("ID missing");

// Handle Update
if (isset($_POST['update_item'])) {
    $stmt = $pdo->prepare("UPDATE rentals SET name=?, price=?, quantity=?, description=?, category_id=?, image_url=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['price'], $_POST['quantity'], $_POST['description'], $_POST['category_id'], $_POST['image_url'], $id]);
    header("Location: admin-inventory.php?msg=Updated Successfully");
    exit;
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 md:p-12">
    <div class="max-w-2xl mx-auto bg-white p-8 md:p-12 rounded-[3rem] shadow-xl">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-black uppercase">Edit Rental</h1>
            <a href="admin-inventory.php" class="text-gray-400 font-bold hover:text-gray-600">Cancel</a>
        </div>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Item Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" class="w-full p-3 border rounded-xl" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                    <select name="category_id" class="w-full p-3 border rounded-xl">
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $item['category_id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Price ($)</label>
            <input type="number" name="price" value="<?= (int)$item['price'] ?>" class="w-full p-3 border rounded-xl" required>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity</label>
            <input type="number" name="quantity" value="<?= (int)($item['quantity'] ?? 1) ?>" min="1" class="w-full p-3 border rounded-xl" required>
        </div>
    </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Image Path</label>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($item['image_url']) ?>" class="w-full p-3 border rounded-xl" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                <textarea name="description" rows="6" class="w-full p-3 border rounded-xl"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" name="update_item" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-blue-700 transition">SAVE CHANGES</button>
        </form>
    </div>
</body>
</html>