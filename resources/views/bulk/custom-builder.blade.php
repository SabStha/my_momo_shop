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
      <!-- Protein Type Selection -->
      <div class="bg-white rounded-lg p-3 border shadow-sm">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-lg">🥟</span>
          <div>
            <p class="font-semibold text-sm">AmakoSteamed Momo</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 25 per piece</p>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-2">
          <button @click="selectedProtein = 'buff'" 
                  :class="{'bg-red-100 border-red-300': selectedProtein === 'buff'}"
                  class="border rounded p-2 text-center text-xs font-sans hover:bg-gray-50 transition-colors">
            🐂 Buff
          </button>
          <button @click="selectedProtein = 'chicken'" 
                  :class="{'bg-orange-100 border-orange-300': selectedProtein === 'chicken'}"
                  class="border rounded p-2 text-center text-xs font-sans hover:bg-gray-50 transition-colors">
            🐔 Chicken
          </button>
          <button @click="selectedProtein = 'veg'" 
                  :class="{'bg-green-100 border-green-300': selectedProtein === 'veg'}"
                  class="border rounded p-2 text-center text-xs font-sans hover:bg-gray-50 transition-colors">
            🥬 Veg
          </button>
        </div>
        <div class="flex items-center gap-1 mt-3 justify-center flex-shrink-0">
          <button @click="decreaseMomoQuantity()" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="momoQuantities[selectedProtein]" min="0" class="w-16 p-1 border rounded text-center text-sm" />
          <button @click="increaseMomoQuantity()" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Step 3: Sides -->
  <div>
    <div class="flex items-center gap-2 text-pink-600 font-bold text-sm">
      <span class="rounded-full bg-pink-100 w-5 h-5 flex items-center justify-center">3</span>
      CHOOSE SIDES
    </div>
    <div class="grid grid-cols-2 gap-3 mt-2">
      <div class="p-2 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 min-w-0 flex-1 mr-4">
          <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
            🥓
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">Chicken Sausage</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 40</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('chickenSausage')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.chickenSausage" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('chickenSausage')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
      
      <div class="p-2 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 min-w-0 flex-1 mr-4">
          <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
            🥓
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">Buff Sausage</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 35</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('buffSausage')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.buffSausage" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('buffSausage')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
      
      <div class="p-2 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 min-w-0 flex-1 mr-4">
          <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
            🍟
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">French Fries</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 60</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('fries')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.fries" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('fries')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
      
      <div class="p-2 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 min-w-0 flex-1 mr-4">
          <div class="w-6 h-6 bg-brown-100 rounded-full flex items-center justify-center flex-shrink-0">
            🍄
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">Fried Mushroom</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 60</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('mushroom')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.mushroom" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('mushroom')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
      
      <div class="p-2 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden col-span-2">
        <div class="flex items-center gap-2 min-w-0 flex-1 mr-4">
          <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
            🍗
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">Karaage</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 80</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('karaage')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.karaage" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('karaage')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
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
      <!-- Hot Drinks -->
      <div class="bg-white rounded-lg p-3 border shadow-sm">
        <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">☕ Hot Drinks</h4>
        <div class="grid grid-cols-2 gap-2">
          <div class="flex items-center justify-between p-2 border rounded bg-gray-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">☕</span>
              <span class="text-xs font-sans">Coffee</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('coffee')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.coffee" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('coffee')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
          <div class="flex items-center justify-between p-2 border rounded bg-gray-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">🫖</span>
              <span class="text-xs font-sans">Milk Tea</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('milkTea')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.milkTea" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('milkTea')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
          <div class="flex items-center justify-between p-2 border rounded bg-gray-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">🍵</span>
              <span class="text-xs font-sans">Black Tea</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('blackTea')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.blackTea" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('blackTea')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
          <div class="flex items-center justify-between p-2 border rounded bg-gray-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">🌶️</span>
              <span class="text-xs font-sans">Masala Tea</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('masalaTea')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.masalaTea" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('masalaTea')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Cold Drinks -->
      <div class="bg-white rounded-lg p-3 border shadow-sm">
        <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">🥤 Cold Drinks</h4>
        <div class="grid grid-cols-2 gap-2">
          <div class="flex items-center justify-between p-2 border rounded bg-blue-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">🥤</span>
              <span class="text-xs font-sans">Coke</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('coke')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.coke" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('coke')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
          <div class="flex items-center justify-between p-2 border rounded bg-blue-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">🥤</span>
              <span class="text-xs font-sans">Fanta</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('fanta')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.fanta" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('fanta')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
          <div class="flex items-center justify-between p-2 border rounded bg-blue-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">🥤</span>
              <span class="text-xs font-sans">Sprite</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('sprite')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.sprite" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('sprite')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
          <div class="flex items-center justify-between p-2 border rounded bg-blue-50">
            <div class="flex items-center gap-2">
              <span class="text-sm">🧋</span>
              <span class="text-xs font-sans">Boba Drinks</span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="decreaseQuantity('boba')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">-</button>
              <input type="number" x-model.number="quantities.boba" min="0" class="w-8 p-1 border rounded text-center text-xs" />
              <button @click="increaseQuantity('boba')" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-xs">+</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Step 6: Desserts -->
  <div>
    <div class="flex items-center gap-2 text-purple-600 font-bold text-sm">
      <span class="rounded-full bg-purple-100 w-5 h-5 flex items-center justify-center">6</span>
      DESSERTS
    </div>
    <div class="grid grid-cols-1 gap-3 mt-2">
      <div class="p-3 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 min-w-0 flex-1 mr-4">
          <div class="w-8 h-8 bg-brown-100 rounded-full flex items-center justify-center flex-shrink-0">
            🍰
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">Brownie with Ice Cream</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 169</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('brownie')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.brownie" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('brownie')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
      
      <div class="p-3 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 min-w-0 flex-1 mr-4">
          <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
            🧇
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">Waffle with Ice Cream</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 169</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('waffle')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.waffle" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('waffle')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
      
      <div class="p-3 border rounded-lg flex items-center justify-between bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 min-w-0 flex-1 mr-4">
          <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
            🍦
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm">Ice Cream (Fruit/Chocolate/Oreo)</p>
            <p class="text-xs text-gray-500 font-sans">Rs. 169</p>
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <button @click="decreaseQuantity('iceCream')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
          <input type="number" x-model.number="quantities.iceCream" min="0" class="w-12 p-1 border rounded text-center text-sm" />
          <button @click="increaseQuantity('iceCream')" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
        </div>
      </div>
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
      <!-- Momo -->
      <div class="flex justify-between" x-show="momoQuantities.buff > 0">
        <span>Buff Momo (<span x-text="momoQuantities.buff"></span>)</span>
        <span>Rs. <span x-text="momoQuantities.buff * 25"></span></span>
      </div>
      <div class="flex justify-between" x-show="momoQuantities.chicken > 0">
        <span>Chicken Momo (<span x-text="momoQuantities.chicken"></span>)</span>
        <span>Rs. <span x-text="momoQuantities.chicken * 25"></span></span>
      </div>
      <div class="flex justify-between" x-show="momoQuantities.veg > 0">
        <span>Veg Momo (<span x-text="momoQuantities.veg"></span>)</span>
        <span>Rs. <span x-text="momoQuantities.veg * 25"></span></span>
      </div>
      
      <!-- Sides -->
      <div class="flex justify-between" x-show="quantities.chickenSausage > 0">
        <span>Chicken Sausage (<span x-text="quantities.chickenSausage"></span>)</span>
        <span>Rs. <span x-text="quantities.chickenSausage * 40"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.buffSausage > 0">
        <span>Buff Sausage (<span x-text="quantities.buffSausage"></span>)</span>
        <span>Rs. <span x-text="quantities.buffSausage * 35"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.fries > 0">
        <span>French Fries (<span x-text="quantities.fries"></span>)</span>
        <span>Rs. <span x-text="quantities.fries * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.mushroom > 0">
        <span>Fried Mushroom (<span x-text="quantities.mushroom"></span>)</span>
        <span>Rs. <span x-text="quantities.mushroom * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.karaage > 0">
        <span>Karaage (<span x-text="quantities.karaage"></span>)</span>
        <span>Rs. <span x-text="quantities.karaage * 80"></span></span>
      </div>
      
      <!-- Sauce -->
      <div class="flex justify-between" x-show="sauceQuantity > 0">
        <span x-text="selectedSauce.charAt(0).toUpperCase() + selectedSauce.slice(1) + ' Sauce (' + sauceQuantity + ')'"></span>
        <span>Rs. <span x-text="sauceQuantity * 20"></span></span>
      </div>
      
      <!-- Drinks -->
      <div class="flex justify-between" x-show="quantities.coffee > 0">
        <span>Coffee (<span x-text="quantities.coffee"></span>)</span>
        <span>Rs. <span x-text="quantities.coffee * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.milkTea > 0">
        <span>Milk Tea (<span x-text="quantities.milkTea"></span>)</span>
        <span>Rs. <span x-text="quantities.milkTea * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.blackTea > 0">
        <span>Black Tea (<span x-text="quantities.blackTea"></span>)</span>
        <span>Rs. <span x-text="quantities.blackTea * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.masalaTea > 0">
        <span>Masala Tea (<span x-text="quantities.masalaTea"></span>)</span>
        <span>Rs. <span x-text="quantities.masalaTea * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.coke > 0">
        <span>Coke (<span x-text="quantities.coke"></span>)</span>
        <span>Rs. <span x-text="quantities.coke * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.fanta > 0">
        <span>Fanta (<span x-text="quantities.fanta"></span>)</span>
        <span>Rs. <span x-text="quantities.fanta * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.sprite > 0">
        <span>Sprite (<span x-text="quantities.sprite"></span>)</span>
        <span>Rs. <span x-text="quantities.sprite * 60"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.boba > 0">
        <span>Boba Drinks (<span x-text="quantities.boba"></span>)</span>
        <span>Rs. <span x-text="quantities.boba * 60"></span></span>
      </div>
      
      <!-- Desserts -->
      <div class="flex justify-between" x-show="quantities.brownie > 0">
        <span>Brownie with Ice Cream (<span x-text="quantities.brownie"></span>)</span>
        <span>Rs. <span x-text="quantities.brownie * 169"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.waffle > 0">
        <span>Waffle with Ice Cream (<span x-text="quantities.waffle"></span>)</span>
        <span>Rs. <span x-text="quantities.waffle * 169"></span></span>
      </div>
      <div class="flex justify-between" x-show="quantities.iceCream > 0">
        <span>Ice Cream (<span x-text="quantities.iceCream"></span>)</span>
        <span>Rs. <span x-text="quantities.iceCream * 169"></span></span>
      </div>
      
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
        selectedProtein: 'buff',
        momoQuantities: {
            buff: 0,
            chicken: 0,
            veg: 0
        },
        quantities: {
            // Sides
            chickenSausage: 0,
            buffSausage: 0,
            fries: 0,
            mushroom: 0,
            karaage: 0,
            // Hot Drinks
            coffee: 0,
            milkTea: 0,
            blackTea: 0,
            masalaTea: 0,
            // Cold Drinks
            coke: 0,
            fanta: 0,
            sprite: 0,
            boba: 0,
            // Desserts
            brownie: 0,
            waffle: 0,
            iceCream: 0
        },
        selectedSauce: 'mild',
        sauceQuantity: 0,
        deliveryArea: '',
        specialNotes: '',
        
        get totalPrice() {
            let total = 0;
            
            // Momo
            total += this.momoQuantities.buff * 25;
            total += this.momoQuantities.chicken * 25;
            total += this.momoQuantities.veg * 25;
            
            // Sides
            total += this.quantities.chickenSausage * 40;
            total += this.quantities.buffSausage * 35;
            total += this.quantities.fries * 60;
            total += this.quantities.mushroom * 60;
            total += this.quantities.karaage * 80;
            
            // Sauce
            total += this.sauceQuantity * 20;
            
            // Hot Drinks
            total += this.quantities.coffee * 60;
            total += this.quantities.milkTea * 60;
            total += this.quantities.blackTea * 60;
            total += this.quantities.masalaTea * 60;
            
            // Cold Drinks
            total += this.quantities.coke * 60;
            total += this.quantities.fanta * 60;
            total += this.quantities.sprite * 60;
            total += this.quantities.boba * 60;
            
            // Desserts
            total += this.quantities.brownie * 169;
            total += this.quantities.waffle * 169;
            total += this.quantities.iceCream * 169;
            
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
        
        increaseMomoQuantity() {
            this.momoQuantities[this.selectedProtein]++;
        },
        
        decreaseMomoQuantity() {
            if (this.momoQuantities[this.selectedProtein] > 0) {
                this.momoQuantities[this.selectedProtein]--;
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
            this.selectedProtein = 'buff';
            this.momoQuantities = {
                buff: 0,
                chicken: 0,
                veg: 0
            };
            this.quantities = {
                chickenSausage: 0,
                buffSausage: 0,
                fries: 0,
                mushroom: 0,
                karaage: 0,
                coffee: 0,
                milkTea: 0,
                blackTea: 0,
                masalaTea: 0,
                coke: 0,
                fanta: 0,
                sprite: 0,
                boba: 0,
                brownie: 0,
                waffle: 0,
                iceCream: 0
            };
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
            let count = this.momoQuantities.buff + this.momoQuantities.chicken + this.momoQuantities.veg;
            Object.values(this.quantities).forEach(qty => count += qty);
            count += this.sauceQuantity;
            return count;
        },
        
        getOrderSummary() {
            const items = [];
            if (this.momoQuantities.buff > 0) {
                items.push(`Buff Momo (${this.momoQuantities.buff})`);
            }
            if (this.momoQuantities.chicken > 0) {
                items.push(`Chicken Momo (${this.momoQuantities.chicken})`);
            }
            if (this.momoQuantities.veg > 0) {
                items.push(`Veg Momo (${this.momoQuantities.veg})`);
            }
            
            Object.entries(this.quantities).forEach(([item, qty]) => {
                if (qty > 0) {
                    const itemName = this.getItemDisplayName(item);
                    items.push(`${itemName} (${qty})`);
                }
            });
            
            if (this.sauceQuantity > 0) {
                items.push(`${this.selectedSauce.charAt(0).toUpperCase() + this.selectedSauce.slice(1)} Sauce (${this.sauceQuantity})`);
            }
            
            return items.join(', ');
        },
        
        getItemDisplayName(itemKey) {
            const names = {
                chickenSausage: 'Chicken Sausage',
                buffSausage: 'Buff Sausage',
                fries: 'French Fries',
                mushroom: 'Fried Mushroom',
                karaage: 'Karaage',
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