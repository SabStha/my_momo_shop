<section x-data="customBulkBuilder()" class="bg-[#FFF7F2] rounded-xl p-4 space-y-6 text-[#530016] font-serif max-w-md mx-auto shadow-md">
  <!-- Step 1: Type -->
  <div>
    <div class="flex items-center gap-2 text-green-600 font-bold text-sm">
      <span class="rounded-full bg-green-100 w-5 h-5 flex items-center justify-center">1</span>
      TYPE OF ORDER
    </div>
    <div class="mt-2 grid grid-cols-2 gap-3">
      <button @click="orderType = 'cooked'" 
              :class="{'border-orange-400 bg-orange-100': orderType === 'cooked', 'border-orange-300 bg-orange-50': orderType !== 'cooked'}"
              class="border rounded-lg p-3 text-left shadow-sm hover:shadow-md transition-all">
        <div class="flex items-center gap-2">
          🔥 <span class="font-bold">Cooked</span>
        </div>
        <p class="text-xs text-gray-600 font-sans">Hot and ready to eat</p>
      </button>
      <button @click="orderType = 'frozen'" 
              :class="{'border-blue-400 bg-blue-100': orderType === 'frozen', 'border-blue-300 bg-blue-50': orderType !== 'frozen'}"
              class="border rounded-lg p-3 text-left shadow-sm hover:shadow-md transition-all">
        <div class="flex items-center gap-2">
          ❄️ <span class="font-bold">Frozen</span>
        </div>
        <p class="text-xs text-gray-600 font-sans">Ready for freezer</p>
      </button>
    </div>
    <!-- Date Picker -->
    <input type="datetime-local" x-model="deliveryDateTime" class="w-full mt-3 p-2 border rounded-md text-sm font-sans" />
  </div>

  <!-- Step 2: Momo Selection -->
  <div>
    <div class="flex items-center gap-2 text-yellow-600 font-bold text-sm">
      <span class="rounded-full bg-yellow-100 w-5 h-5 flex items-center justify-center">2</span>
      SELECT MOMO
    </div>
    <div class="mt-2 space-y-3">
      <!-- Dynamic Momo Selection -->
      @if(count($momoTypes) > 0)
        @foreach($momoTypes as $index => $momo)
        <div class="bg-white rounded-lg p-3 border shadow-sm">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-lg">🥟</span>
            <div>
              <p class="font-semibold text-sm">{{ $momo['name'] }}</p>
              <p class="text-xs text-gray-500 font-sans">Rs. {{ $momo['price'] }} per piece</p>
            </div>
          </div>
          <div class="flex items-center gap-1 mt-3 justify-center flex-shrink-0">
            <button @click="decreaseMomoQuantity('{{ $index }}')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
            <input type="number" x-model.number="momoQuantities['{{ $index }}']" min="0" class="w-16 p-1 border rounded text-center text-sm" />
            <button @click="increaseMomoQuantity('{{ $index }}')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
          </div>
        </div>
        @endforeach
      @else
        <div class="bg-white rounded-lg p-3 border shadow-sm text-center">
          <p class="text-gray-500 text-sm">No momo types available</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Step 3: Sides -->
  <div>
    <div class="flex items-center gap-2 text-pink-600 font-bold text-sm">
      <span class="rounded-full bg-pink-100 w-5 h-5 flex items-center justify-center">3</span>
      CHOOSE SIDES
    </div>
    <div class="grid grid-cols-2 gap-3 mt-2">
      @if(count($sideDishes) > 0)
        @foreach($sideDishes as $index => $side)
        <div class="p-2 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 min-w-0 flex-1 mr-4">
            <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
              🍽️
            </div>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-sm">{{ $side['name'] }}</p>
              <p class="text-xs text-gray-500 font-sans">Rs. {{ $side['price'] }}</p>
            </div>
          </div>
          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="decreaseQuantity('side_{{ $index }}')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
            <input type="number" x-model.number="quantities.side_{{ $index }}" min="0" class="w-12 p-1 border rounded text-center text-sm" />
            <button @click="increaseQuantity('side_{{ $index }}')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
          </div>
        </div>
        @endforeach
      @else
        <div class="col-span-2 p-4 border rounded-lg bg-white shadow-sm text-center">
          <p class="text-gray-500 text-sm">No side dishes available</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Step 4: Sauce Selection -->
  <div>
    <div class="flex items-center gap-2 text-red-600 font-bold text-sm mb-2">
      <span class="rounded-full bg-red-100 w-5 h-5 flex items-center justify-center">4</span>
      SAUCE SELECTION
    </div>
    <div class="grid grid-cols-3 gap-2 text-xs font-sans mb-3">
      <div @click="selectedSauce = 'mild'" :class="{'bg-green-100 border-green-300': selectedSauce === 'mild'}" class="border rounded p-2 text-center bg-green-50 border-green-200 cursor-pointer hover:bg-green-100 transition-colors">
        ✅ Mild <p class="text-gray-500">Perfect for all</p>
      </div>
      <div @click="selectedSauce = 'medium'" :class="{'bg-yellow-100 border-yellow-300': selectedSauce === 'medium'}" class="border rounded p-2 text-center bg-yellow-50 border-yellow-200 cursor-pointer hover:bg-yellow-100 transition-colors">
        ⚠️ Medium <p class="text-gray-500">Bit of a kick</p>
      </div>
      <div @click="selectedSauce = 'hot'" :class="{'bg-red-100 border-red-300': selectedSauce === 'hot'}" class="border rounded p-2 text-center bg-red-50 border-red-200 cursor-pointer hover:bg-red-100 transition-colors">
        🔥 Hot <p class="text-gray-500">For spice lovers</p>
      </div>
    </div>
    <!-- Sauce Quantity -->
          <div class="bg-white rounded-lg p-3 border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 min-w-0 flex-1 mr-4">
            <span class="text-lg flex-shrink-0">🌶️</span>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-sm" x-text="selectedSauce.charAt(0).toUpperCase() + selectedSauce.slice(1) + ' Sauce'"></p>
              <p class="text-xs text-gray-500 font-sans">Rs. 20 per pot</p>
            </div>
          </div>
          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="decreaseSauceQuantity()" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
            <input type="number" x-model.number="sauceQuantity" min="0" class="w-12 p-1 border rounded text-center text-sm" />
            <button @click="increaseSauceQuantity()" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
          </div>
        </div>
      </div>
  </div>

  <!-- Step 5: Drinks -->
  <div>
    <div class="flex items-center gap-2 text-blue-600 font-bold text-sm">
      <span class="rounded-full bg-blue-100 w-5 h-5 flex items-center justify-center">5</span>
      SELECT DRINKS
    </div>
    <div class="mt-2 space-y-3">
      <!-- Dynamic Drinks -->
      @if(count($drinks) > 0)
        <div class="bg-white rounded-lg p-3 border shadow-sm">
          <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">🥤 Drinks</h4>
          <div class="grid grid-cols-2 gap-2">
            @foreach($drinks as $index => $drink)
            <div class="flex items-center justify-between p-2 border rounded bg-blue-50">
              <div class="flex items-center gap-2">
                <span class="text-sm">🥤</span>
                <div class="min-w-0 flex-1">
                  <span class="text-xs font-sans">{{ $drink['name'] }}</span>
                  <p class="text-xs text-gray-500">Rs. {{ $drink['price'] }}</p>
                </div>
              </div>
              <div class="flex items-center gap-1 flex-shrink-0">
                <button @click="decreaseQuantity('drink_{{ $index }}')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
                <input type="number" x-model.number="quantities.drink_{{ $index }}" min="0" class="w-8 p-1 border rounded text-center text-xs" />
                <button @click="increaseQuantity('drink_{{ $index }}')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      @else
        <div class="bg-white rounded-lg p-3 border shadow-sm text-center">
          <p class="text-gray-500 text-sm">No drinks available</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Step 6: Desserts -->
  <div>
    <div class="flex items-center gap-2 text-purple-600 font-bold text-sm">
      <span class="rounded-full bg-purple-100 w-5 h-5 flex items-center justify-center">6</span>
      DESSERTS
    </div>
    <div class="grid grid-cols-1 gap-3 mt-2">
      @if(count($desserts) > 0)
        @foreach($desserts as $index => $dessert)
        <div class="p-3 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 min-w-0 flex-1 mr-4">
            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
              🍰
            </div>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-sm">{{ $dessert['name'] }}</p>
              <p class="text-xs text-gray-500 font-sans">Rs. {{ $dessert['price'] }}</p>
            </div>
          </div>
          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="decreaseQuantity('dessert_{{ $index }}')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
            <input type="number" x-model.number="quantities.dessert_{{ $index }}" min="0" class="w-12 p-1 border rounded text-center text-sm" />
            <button @click="increaseQuantity('dessert_{{ $index }}')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
          </div>
        </div>
        @endforeach
      @else
        <div class="p-4 border rounded-lg bg-white shadow-sm text-center">
          <p class="text-gray-500 text-sm">No desserts available</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Step 7: Delivery & Notes -->
  <div>
    <div class="flex items-center gap-2 text-indigo-600 font-bold text-sm mb-2">
      <span class="rounded-full bg-indigo-100 w-5 h-5 flex items-center justify-center">7</span>
      DELIVERY & NOTES
    </div>
    <div class="space-y-3">
      <input type="text" x-model="deliveryArea" placeholder="Delivery Area" class="w-full p-2 border rounded-md text-sm font-sans" />
      <textarea x-model="specialNotes" placeholder="Special instructions or notes..." class="w-full p-2 border rounded-md text-sm font-sans h-20 resize-none"></textarea>
    </div>
  </div>

  <!-- Order Summary -->
  <div class="bg-white rounded-lg p-4 border shadow-sm">
    <h3 class="font-bold text-sm mb-3">📋 ORDER SUMMARY</h3>
    <div class="space-y-2 text-sm">
      <!-- Dynamic Momo -->
      @if(count($momoTypes) > 0)
        @foreach($momoTypes as $index => $momo)
        <div class="flex justify-between" x-show="momoQuantities['{{ $index }}'] > 0">
          <span>{{ $momo['name'] }} (<span x-text="momoQuantities['{{ $index }}']"></span>)</span>
          <span>Rs. <span x-text="momoQuantities['{{ $index }}'] * {{ $momo['price'] }}"></span></span>
        </div>
        @endforeach
      @endif
      
      <!-- Dynamic Sides -->
      @if(count($sideDishes) > 0)
        @foreach($sideDishes as $index => $side)
        <div class="flex justify-between" x-show="quantities.side_{{ $index }} > 0">
          <span>{{ $side['name'] }} (<span x-text="quantities.side_{{ $index }}"></span>)</span>
          <span>Rs. <span x-text="quantities.side_{{ $index }} * {{ $side['price'] }}"></span></span>
        </div>
        @endforeach
      @endif
      
      <!-- Sauce -->
      <div class="flex justify-between" x-show="sauceQuantity > 0">
        <span x-text="selectedSauce.charAt(0).toUpperCase() + selectedSauce.slice(1) + ' Sauce (' + sauceQuantity + ')'"></span>
        <span>Rs. <span x-text="sauceQuantity * 20"></span></span>
      </div>
      
      <!-- Dynamic Drinks -->
      @if(count($drinks) > 0)
        @foreach($drinks as $index => $drink)
        <div class="flex justify-between" x-show="quantities.drink_{{ $index }} > 0">
          <span>{{ $drink['name'] }} (<span x-text="quantities.drink_{{ $index }}"></span>)</span>
          <span>Rs. <span x-text="quantities.drink_{{ $index }} * {{ $drink['price'] }}"></span></span>
        </div>
        @endforeach
      @endif
      
      <!-- Dynamic Desserts -->
      @if(count($desserts) > 0)
        @foreach($desserts as $index => $dessert)
        <div class="flex justify-between" x-show="quantities.dessert_{{ $index }} > 0">
          <span>{{ $dessert['name'] }} (<span x-text="quantities.dessert_{{ $index }}"></span>)</span>
          <span>Rs. <span x-text="quantities.dessert_{{ $index }} * {{ $dessert['price'] }}"></span></span>
        </div>
        @endforeach
      @endif
      
      <div class="border-t pt-2 mt-2">
        <div class="flex justify-between font-bold">
          <span>TOTAL</span>
          <span>Rs. <span x-text="totalPrice"></span></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="flex gap-3">
    <button @click="clearOrder()" class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-semibold text-sm hover:bg-gray-300 transition-colors">
      Clear Order
    </button>
    <button @click="addToCart()" :disabled="totalPrice === 0" :class="{'opacity-50 cursor-not-allowed': totalPrice === 0, 'hover:bg-[#3A0010]': totalPrice > 0}" class="flex-1 bg-[#530016] text-white py-2 px-4 rounded-lg font-semibold text-sm transition-colors">
      Add to Cart
    </button>
  </div>
</section>

<script>
function customBulkBuilder() {
    return {
        orderType: 'cooked',
        deliveryDateTime: '',
        momoQuantities: {},
        quantities: {
            // Sides, drinks, and desserts will be populated dynamically
        },
        selectedSauce: 'mild',
        sauceQuantity: 0,
        deliveryArea: '',
        specialNotes: '',
        
        get totalPrice() {
            let total = 0;
            
            // Momo - calculate dynamically
            @if(count($momoTypes) > 0)
                @foreach($momoTypes as $index => $momo)
                total += (this.momoQuantities['{{ $index }}'] || 0) * {{ $momo['price'] }};
                @endforeach
            @endif
            
            // Sides - calculate dynamically
            @if(count($sideDishes) > 0)
                @foreach($sideDishes as $index => $side)
                total += (this.quantities.side_{{ $index }} || 0) * {{ $side['price'] }};
                @endforeach
            @endif
            
            // Drinks - calculate dynamically
            @if(count($drinks) > 0)
                @foreach($drinks as $index => $drink)
                total += (this.quantities.drink_{{ $index }} || 0) * {{ $drink['price'] }};
                @endforeach
            @endif
            
            // Desserts - calculate dynamically
            @if(count($desserts) > 0)
                @foreach($desserts as $index => $dessert)
                total += (this.quantities.dessert_{{ $index }} || 0) * {{ $dessert['price'] }};
                @endforeach
            @endif
            
            // Sauce
            total += this.sauceQuantity * 20;
            
            return total;
        },
        
        increaseQuantity(type) {
            this.quantities[type]++;
        },
        
        decreaseQuantity(type) {
            if (this.quantities[type] > 0) {
                this.quantities[type]--;
            }
        },
        
        increaseMomoQuantity(index) {
            if (!this.momoQuantities[index]) {
                this.momoQuantities[index] = 0;
            }
            this.momoQuantities[index]++;
        },
        
        decreaseMomoQuantity(index) {
            if (!this.momoQuantities[index]) {
                this.momoQuantities[index] = 0;
            }
            if (this.momoQuantities[index] > 0) {
                this.momoQuantities[index]--;
            }
        },
        
        increaseSauceQuantity() {
            this.sauceQuantity++;
        },
        
        decreaseSauceQuantity() {
            if (this.sauceQuantity > 0) {
                this.sauceQuantity--;
            }
        },
        
        clearOrder() {
            // Clear momo quantities
            this.momoQuantities = {};
            
            // Clear side dish quantities
            @if(count($sideDishes) > 0)
                @foreach($sideDishes as $index => $side)
                this.quantities.side_{{ $index }} = 0;
                @endforeach
            @endif
            
            // Clear drink quantities
            @if(count($drinks) > 0)
                @foreach($drinks as $index => $drink)
                this.quantities.drink_{{ $index }} = 0;
                @endforeach
            @endif
            
            // Clear dessert quantities
            @if(count($desserts) > 0)
                @foreach($desserts as $index => $dessert)
                this.quantities.dessert_{{ $index }} = 0;
                @endforeach
            @endif
            
            this.selectedSauce = 'mild';
            this.sauceQuantity = 0;
            this.deliveryArea = '';
            this.specialNotes = '';
        },
        
        addToCart() {
            if (this.totalPrice === 0) return;
            
            // Validate required fields
            if (!this.deliveryArea.trim()) {
                alert('Please enter delivery area');
                return;
            }
            
            if (this.orderType === 'cooked' && !this.deliveryDateTime) {
                alert('Please select delivery date and time');
                return;
            }
            
            const orderData = {
                orderType: this.orderType,
                deliveryDateTime: this.deliveryDateTime,
                            selectedProtein: this.selectedProtein,
            momoQuantities: this.momoQuantities,
                quantities: this.quantities,
                selectedSauce: this.selectedSauce,
                sauceQuantity: this.sauceQuantity,
                deliveryArea: this.deliveryArea,
                specialNotes: this.specialNotes,
                totalPrice: this.totalPrice,
                itemCount: this.getItemCount(),
                orderSummary: this.getOrderSummary()
            };
            
            console.log('Adding to cart:', orderData);
            
            // Show success message with order details
            const message = `✅ Order Added to Cart!\n\n` +
                          `📦 ${this.getItemCount()} items\n` +
                          `💰 Total: Rs. ${this.totalPrice}\n` +
                          `📍 Delivery: ${this.deliveryArea}\n` +
                          `📅 Type: ${this.orderType === 'cooked' ? 'Hot & Ready' : 'Frozen'}`;
            
            alert(message);
            
            // Clear the form after successful addition
            this.clearOrder();
        },
        
        getItemCount() {
            let count = 0;
            
            // Count momo quantities
            Object.values(this.momoQuantities).forEach(qty => count += qty);
            
            // Count other quantities
            Object.values(this.quantities).forEach(qty => count += qty);
            count += this.sauceQuantity;
            return count;
        },
        
        getOrderSummary() {
            const items = [];
            
            // Add momo items
            Object.entries(this.momoQuantities).forEach(([index, qty]) => {
                if (qty > 0) {
                    const momoData = this.getMomoData(index);
                    if (momoData) {
                        items.push(`${momoData.name} (${qty})`);
                    }
                }
            });
            
            // Add side dish items
            Object.entries(this.quantities).forEach(([item, qty]) => {
                if (qty > 0 && item.startsWith('side_')) {
                    const sideData = this.getSideData(item);
                    if (sideData) {
                        items.push(`${sideData.name} (${qty})`);
                    }
                } else if (qty > 0 && item.startsWith('drink_')) {
                    const drinkData = this.getDrinkData(item);
                    if (drinkData) {
                        items.push(`${drinkData.name} (${qty})`);
                    }
                } else if (qty > 0 && item.startsWith('dessert_')) {
                    const dessertData = this.getDessertData(item);
                    if (dessertData) {
                        items.push(`${dessertData.name} (${qty})`);
                    }
                }
            });
            
            if (this.sauceQuantity > 0) {
                items.push(`${this.selectedSauce.charAt(0).toUpperCase() + this.selectedSauce.slice(1)} Sauce (${this.sauceQuantity})`);
            }
            
            return items.join(', ');
        },
        
        getMomoData(index) {
            const momoTypes = @json($momoTypes);
            return momoTypes[index] || null;
        },
        
        getSideData(itemKey) {
            const sideDishes = @json($sideDishes);
            const index = itemKey.replace('side_', '');
            return sideDishes[index] || null;
        },
        
        getDrinkData(itemKey) {
            const drinks = @json($drinks);
            const index = itemKey.replace('drink_', '');
            return drinks[index] || null;
        },
        
        getDessertData(itemKey) {
            const desserts = @json($desserts);
            const index = itemKey.replace('dessert_', '');
            return desserts[index] || null;
        },
        
        getItemDisplayName(itemKey) {
            const names = {
                coffee: 'Coffee',
                milkTea: 'Milk Tea',
                blackTea: 'Black Tea',
                masalaTea: 'Masala Tea',
                coke: 'Coke',
                fanta: 'Fanta',
                sprite: 'Sprite',
                boba: 'Boba Drinks',
                brownie: 'Brownie with Ice Cream',
                waffle: 'Waffle with Ice Cream',
                iceCream: 'Ice Cream'
            };
            return names[itemKey] || itemKey;
        }
    }
}
</script> 