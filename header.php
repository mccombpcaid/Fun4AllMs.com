<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamic Title: Use $pageTitle if set, otherwise default -->
    <title><?php echo $pageTitle ?? 'Fun 4 All MS | Party & Inflatable Rentals'; ?></title>
   <link href="style.css" rel="stylesheet">
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .float-animation { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-md shadow-sm p-2 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center px-4">
            
            <!-- Logo Home Link -->
            <a href="index.php" class="flex items-center space-x-2 hover:opacity-80 transition">
                <img src="images/fun4all-Logo-e1727989737699-768x747.webp" alt="Logo" class="h-12 w-auto">
                <span class="text-xl font-black text-blue-700 hidden lg:block uppercase tracking-tighter">Fun 4 All MS</span>
            </a>
            
            <div class="hidden md:flex space-x-6 font-bold text-gray-600 uppercase text-xs tracking-widest">
                <a href="index.php" class="hover:text-blue-600 transition">Home</a>
                <a href="rentals.php" class="hover:text-blue-600 transition">Rentals</a>
                <a href="faq.php" class="hover:text-blue-600 transition">FAQ</a>
                
                <!-- ADMIN DASHBOARD: Only shows if logged in -->
                <?php if(isset($_SESSION['admin_user_id'])): ?>
                    <div class="flex items-center space-x-4 ml-4 border-l pl-4 border-gray-200">
                        <a href="admin-calendar.php" class="text-blue-600 font-black text-[10px] uppercase hover:underline">⚙️ Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center space-x-4">
                <a href="tel:6010000000" class="bg-red-500 text-white px-5 py-2 rounded-full font-bold shadow-md hover:bg-red-600 transition text-sm">
                    (601) 810-0119
                </a>
            </div>
        </div>
    </nav>