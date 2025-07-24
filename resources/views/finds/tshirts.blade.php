<div class="bg-[#F4E9E1] min-h-screen">
    <div class="pt-4 sm:pt-[50px] px-4 pb-20 space-y-8 sm:space-y-16 max-w-5xl mx-auto">

        @foreach($merchandise['tshirts'] as $index => $item)
            @if($index % 2 == 0)
                <!-- LEFT ALIGNED ITEMS -->
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="w-full sm:w-1/2 text-center sm:text-right sm:pr-4">
                        <h2 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold font-serif mb-2">{{ $item->name }}</h2>
                        <p class="text-sm sm:text-base md:text-lg text-gray-700 leading-snug mb-3">
                            {{ $item->description }}
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center sm:justify-end items-center gap-2 sm:gap-3">
                            <span class="text-lg sm:text-xl font-bold {{ $item->purchasable ? 'text-[#6E0D25]' : 'text-gray-500' }}">{{ $item->formatted_price }}</span>
                            @if($item->purchasable)
                                <button class="w-full sm:w-auto bg-[#6E0D25] text-white px-4 py-3 sm:px-4 sm:py-2 rounded-lg hover:bg-[#8B1A3A] transition text-sm sm:text-base min-h-[44px]">
                                    Add to Cart
                                </button>
                            @else
                                <span class="w-full sm:w-auto bg-gray-300 text-gray-600 px-4 py-3 sm:px-4 sm:py-2 rounded-lg text-sm text-center">
                                    {{ $item->status === 'coming_soon' ? 'Coming Soon' : 'Display Only' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="w-full sm:w-1/2 flex justify-center sm:justify-start">
                        <img src="{{ $item->image_url }}"
                             alt="{{ $item->name }}"
                             class="w-48 sm:w-36 md:w-48 lg:w-56 xl:w-64 rounded-xl shadow-md {{ $item->purchasable ? 'hover:scale-105 transition duration-300' : 'opacity-75' }}">
                    </div>
                </div>
            @else
                <!-- RIGHT ALIGNED ITEMS -->
                <div class="flex flex-col-reverse sm:flex-row-reverse items-center gap-4 sm:gap-6">
                    <div class="w-full sm:w-1/2 text-center sm:text-left sm:pl-4">
                        <h2 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold font-serif mb-2">{{ $item->name }}</h2>
                        <p class="text-sm sm:text-base md:text-lg text-gray-700 leading-snug mb-3">
                            {{ $item->description }}
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center sm:justify-start items-center gap-2 sm:gap-3">
                            <span class="text-lg sm:text-xl font-bold {{ $item->purchasable ? 'text-[#6E0D25]' : 'text-gray-500' }}">{{ $item->formatted_price }}</span>
                            @if($item->purchasable)
                                <button class="w-full sm:w-auto bg-[#6E0D25] text-white px-4 py-3 sm:px-4 sm:py-2 rounded-lg hover:bg-[#8B1A3A] transition text-sm sm:text-base min-h-[44px]">
                                    Add to Cart
                                </button>
                            @else
                                <span class="w-full sm:w-auto bg-gray-300 text-gray-600 px-4 py-3 sm:px-4 sm:py-2 rounded-lg text-sm text-center">
                                    {{ $item->status === 'coming_soon' ? 'Coming Soon' : 'Display Only' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="w-full sm:w-1/2 flex justify-center sm:justify-end">
                        <img src="{{ $item->image_url }}"
                             alt="{{ $item->name }}"
                             class="w-48 sm:w-36 md:w-48 lg:w-56 xl:w-64 rounded-xl shadow-md {{ $item->purchasable ? 'hover:scale-105 transition duration-300' : 'opacity-75' }}">
                    </div>
                </div>
            @endif
        @endforeach

    </div>
</div> 