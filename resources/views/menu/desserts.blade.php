<!-- DESSERT SECTION WITH BACKGROUND -->
<div id="desserts" class="relative bg-cover bg-center px-4 pb-20"
     style="background-image: url('{{ asset('storage/products/bg/2.png') }}');">
    
    <!-- Overlay for readability -->
    <div class="absolute inset-0 bg-white bg-opacity-80 z-0"></div>

    <!-- Actual content -->
    <div class="relative z-10">
        <h2 class="text-2xl font-bold font-serif mb-6 text-center">DESSERTS</h2>

        <!-- Dessert 1: Brownie with Ice Cream -->
        <div class="grid grid-cols-2 items-center gap-4 mb-10">
            <div data-aos="fade-left">
                <img src="{{ asset('storage/products/desserts/brownie-icecream.jpg') }}"
                     class="rounded-xl shadow-lg w-full max-w-[240px] mx-auto hover:scale-105 transition duration-500"
                     alt="Brownie with Ice Cream">
            </div>
            <div data-aos="fade-right" class="p-2">
                <h3 class="text-xl font-semibold font-serif mb-2">Brownie with Ice Cream</h3>
                <p class="text-base font-light">Warm fudgy brownie served with a scoop of vanilla ice cream.</p>
            </div>
        </div>

        <!-- Dessert 2: Waffles with Ice Cream -->
        <div class="grid grid-cols-2 items-center gap-4 mb-10">
            <div data-aos="fade-left">
                <img src="{{ asset('storage/products/desserts/waffles-icecream.jpg') }}"
                     class="rounded-xl shadow-lg w-full max-w-[240px] mx-auto hover:scale-105 transition duration-500"
                     alt="Waffles with Ice Cream">
            </div>
            <div data-aos="fade-right" class="p-2">
                <h3 class="text-xl font-semibold font-serif mb-2">Waffles with Ice Cream</h3>
                <p class="text-base font-light">Golden waffles topped with your choice of ice cream and syrup.</p>
            </div>
        </div>

        <!-- Dessert 3: Custom Ice Cream -->
        <div class="grid grid-cols-2 items-center gap-4 mb-10">
            <div data-aos="fade-left">
                <img src="{{ asset('storage/products/desserts/custom-icecream.jpg') }}"
                     class="rounded-xl shadow-lg w-full max-w-[240px] mx-auto hover:scale-105 transition duration-500"
                     alt="Custom Ice Cream Bowl">
            </div>
            <div data-aos="fade-right" class="p-2">
                <h3 class="text-xl font-semibold font-serif mb-2">Custom Ice Cream Bowl</h3>
                <p class="text-base font-light">
                    Pick your toppings: Strawberry, Chocolate Chips, Oreo Crumbs, and more.
                </p>
            </div>
        </div>
    </div>
</div>
