<?php 
    $pageTitle = "Frequently Asked Questions | Fun 4 All MS";
    include 'header.php'; 
?>

<!-- Header -->
<header class="bg-gradient-to-r from-blue-600 to-blue-800 py-20 text-white text-center">
    <div class="container mx-auto px-4">
        <h1 class="text-5xl md:text-7xl font-black uppercase tracking-tight mb-4">Got Questions?</h1>
        <p class="text-xl text-blue-100 max-w-2xl mx-auto">We want your party to be stress-free. Here are the answers to our most common questions.</p>
    </div>
</header>

<!-- FAQ Content -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <div class="grid grid-cols-1 gap-8">
            
            <!-- Question 1 -->
            <div class="bg-white p-8 md:p-12 rounded-[3rem] shadow-sm border border-gray-100">
                <h3 class="text-2xl font-black text-blue-700 uppercase mb-4 flex items-center">
                    <span class="mr-4 text-3xl">⛈️</span> What is your Rain Policy?
                </h3>
                <p class="text-gray-600 leading-relaxed text-lg">
                    Safety is our #1 priority. If there is a 50% or higher chance of rain or winds exceeding 15mph on your event date, we reserve the right to cancel the rental for safety reasons. In the event of a weather-related cancellation **before delivery**, we will issue you a full rain check valid for one year.
                </p>
            </div>

            <!-- Question 2 -->
            <div class="bg-white p-8 md:p-12 rounded-[3rem] shadow-sm border border-gray-100">
                <h3 class="text-2xl font-black text-blue-700 uppercase mb-4 flex items-center">
                    <span class="mr-4 text-3xl">🔌</span> What kind of power is needed?
                </h3>
                <p class="text-gray-600 leading-relaxed text-lg">
                    Our blowers require a standard 110v outlet. We provide a heavy-duty 50ft extension cord. The outlet should be on its own dedicated circuit (not shared with refrigerators or other heavy appliances) to prevent breakers from tripping.
                </p>
            </div>

            <!-- Question 3 -->
            <div class="bg-white p-8 md:p-12 rounded-[3rem] shadow-sm border border-gray-100">
                <h3 class="text-2xl font-black text-blue-700 uppercase mb-4 flex items-center">
                    <span class="mr-4 text-3xl">🧼</span> Are the units clean?
                </h3>
                <p class="text-gray-600 leading-relaxed text-lg">
                    Absolutely! We vacuum, sanitize with child-safe cleaners, and inspect every unit after every single use. We pride ourselves on having the cleanest equipment in the state of Mississippi.
                </p>
            </div>

            <!-- Question 4 -->
            <div class="bg-white p-8 md:p-12 rounded-[3rem] shadow-sm border border-gray-100">
                <h3 class="text-2xl font-black text-blue-700 uppercase mb-4 flex items-center">
                    <span class="mr-4 text-3xl">📏</span> How much space do I need?
                </h3>
                <p class="text-gray-600 leading-relaxed text-lg">
                    You should have a flat area that is at least 5 feet larger than the unit dimensions on all sides. Ensure there are no low-hanging tree branches or power lines above the setup area.
                </p>
            </div>

        </div>

        <!-- Contact Call to Action -->
        <div class="mt-20 text-center bg-blue-900 text-white p-12 rounded-[4rem] shadow-2xl">
            <h2 class="text-3xl font-black uppercase mb-4">Still have questions?</h2>
            <p class="mb-8 opacity-80">We are happy to help you plan your perfect event.</p>
            <a href="tel:6018100119" class="inline-block bg-yellow-400 text-blue-900 px-10 py-4 rounded-2xl font-black text-xl hover:scale-105 transition transform">CALL (601) 810-0119</a>
        </div>

    </div>
</section>

<?php include 'footer.php'; ?>