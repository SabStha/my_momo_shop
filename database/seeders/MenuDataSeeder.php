<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Str;

class MenuDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting Menu Data Seeder...');

        // Get or create main branch
        $mainBranch = Branch::where('is_main', true)->first();
        if (!$mainBranch) {
            $mainBranch = Branch::first();
        }

        // Update existing food products with correct categories
        $this->updateFoodProducts();
        
        // Update existing drink products with correct categories
        $this->updateDrinkProducts();
        
        // Update existing dessert products
        $this->updateDessertProducts();
        
        // Update existing combo products
        $this->updateComboProducts();
        
        // Update existing products with menu details
        $this->updateExistingProductsWithMenuDetails();
        
        // Add missing products to ensure all categories have data
        $this->addMissingProducts();

        $this->command->info('Menu Data Seeder completed successfully!');
    }

    private function updateFoodProducts()
    {
        $this->command->info('Updating food products...');
        
        // Update existing food products with correct categories
        $foodUpdates = [
            'Classic Pork Momos' => 'main',
            'Spicy Chicken Momos' => 'chicken',
            'Veg Momos' => 'buff',
            'Cheese Corn Momos' => 'buff',
            'Paneer Momos' => 'buff',
            'Chilli Garlic Momos' => 'main',
            'Fried Chicken Momos' => 'chicken',
            'Steamed Chicken Momos' => 'chicken',
            'Tandoori Momos' => 'main',
        ];

        foreach ($foodUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'foods')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function updateDrinkProducts()
    {
        $this->command->info('Updating drink products...');
        
        // Update existing drink products with correct categories
        $drinkUpdates = [
            'Iced Coffee' => 'cold',
            'Mango Lassi' => 'cold',
            'Lemon Iced Tea' => 'cold',
            'Hot Chocolate' => 'hot',
            'Coconut Water' => 'cold',
            'Masala Chai' => 'hot',
            'Cold Brew' => 'cold',
            'Matcha Latte' => 'hot',
            'Strawberry Smoothie' => 'cold',
            'Mint Cooler' => 'cold',
        ];

        foreach ($drinkUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'drinks')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function updateDessertProducts()
    {
        $this->command->info('Updating dessert products...');
        
        // Update existing dessert products
        $dessertUpdates = [
            'Chocolate Cake' => 'desserts',
            'Mango Cheesecake' => 'desserts',
            'Gulab Jamun' => 'desserts',
            'Brownie Sundae' => 'desserts',
            'Rice Pudding' => 'desserts',
        ];

        foreach ($dessertUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'desserts')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function updateComboProducts()
    {
        $this->command->info('Updating combo products...');
        
        // Update existing combo products
        $comboUpdates = [
            'Momo Combo Plate' => 'combos',
            'Family Combo Feast' => 'combos',
            'Spicy Duo Combo' => 'combos',
        ];

        foreach ($comboUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'combos')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function updateExistingProductsWithMenuDetails()
    {
        $this->command->info('Updating existing products with menu details...');
        
        // Menu details for existing products
        $menuDetails = [
            // Food items
            'Classic Pork Momos' => [
                'ingredients' => 'Wheat flour, ground pork, onions, garlic, ginger, spices, oil, salt, water',
                'allergens' => 'Contains: Gluten',
                'calories' => '350-400',
                'preparation_time' => '18-22 minutes',
                'spice_level' => 'Medium',
                'is_vegetarian' => false,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '6 pieces'
            ],
            'Spicy Chicken Momos' => [
                'ingredients' => 'Wheat flour, ground chicken, onions, garlic, ginger, spices, oil, salt, water',
                'allergens' => 'Contains: Gluten',
                'calories' => '320-370',
                'preparation_time' => '18-22 minutes',
                'spice_level' => 'Hot',
                'is_vegetarian' => false,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '6 pieces'
            ],
            'Veg Momos' => [
                'ingredients' => 'Wheat flour, mixed vegetables (carrot, cabbage, onion), spices, oil, salt, water',
                'allergens' => 'Contains: Gluten',
                'calories' => '250-300',
                'preparation_time' => '15-20 minutes',
                'spice_level' => 'Medium',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '6 pieces'
            ],
            'Cheese Corn Momos' => [
                'ingredients' => 'Wheat flour, cheese, sweet corn, onions, spices, oil, salt, water',
                'allergens' => 'Contains: Gluten, Dairy',
                'calories' => '300-350',
                'preparation_time' => '16-20 minutes',
                'spice_level' => 'Mild',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '6 pieces'
            ],
            'Paneer Momos' => [
                'ingredients' => 'Wheat flour, paneer, onions, garlic, ginger, spices, oil, salt, water',
                'allergens' => 'Contains: Gluten, Dairy',
                'calories' => '280-330',
                'preparation_time' => '16-20 minutes',
                'spice_level' => 'Medium',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '6 pieces'
            ],
            // Drink items
            'Iced Coffee' => [
                'ingredients' => 'Fresh coffee beans, milk, ice, sugar',
                'allergens' => 'Contains: Dairy',
                'calories' => '120-150',
                'preparation_time' => '5-8 minutes',
                'spice_level' => 'None',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => true,
                'serving_size' => '1 cup'
            ],
            'Mango Lassi' => [
                'ingredients' => 'Fresh mango, yogurt, milk, sugar, cardamom',
                'allergens' => 'Contains: Dairy',
                'calories' => '180-220',
                'preparation_time' => '3-5 minutes',
                'spice_level' => 'None',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => true,
                'serving_size' => '1 glass'
            ],
            'Hot Chocolate' => [
                'ingredients' => 'Premium cocoa powder, milk, sugar, vanilla extract',
                'allergens' => 'Contains: Dairy',
                'calories' => '200-250',
                'preparation_time' => '4-6 minutes',
                'spice_level' => 'None',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => true,
                'serving_size' => '1 cup'
            ],
            'Masala Chai' => [
                'ingredients' => 'Black tea leaves, milk, sugar, ginger, cardamom, cinnamon, cloves',
                'allergens' => 'Contains: Dairy',
                'calories' => '80-120',
                'preparation_time' => '6-8 minutes',
                'spice_level' => 'Medium',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => true,
                'serving_size' => '1 cup'
            ],
            // Dessert items
            'Chocolate Cake' => [
                'ingredients' => 'Flour, cocoa powder, eggs, butter, sugar, vanilla extract, baking powder',
                'allergens' => 'Contains: Gluten, Dairy, Eggs',
                'calories' => '350-400',
                'preparation_time' => '30-35 minutes',
                'spice_level' => 'None',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '1 slice'
            ],
            'Mango Cheesecake' => [
                'ingredients' => 'Cream cheese, mango puree, graham crackers, butter, sugar, eggs',
                'allergens' => 'Contains: Gluten, Dairy, Eggs',
                'calories' => '320-380',
                'preparation_time' => '45-50 minutes',
                'spice_level' => 'None',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '1 slice'
            ],
            'Gulab Jamun' => [
                'ingredients' => 'Milk powder, flour, ghee, sugar, cardamom, rose water, saffron',
                'allergens' => 'Contains: Gluten, Dairy',
                'calories' => '280-320',
                'preparation_time' => '25-30 minutes',
                'spice_level' => 'None',
                'is_vegetarian' => true,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '4 pieces'
            ],
            // Combo items
            'Momo Combo Plate' => [
                'ingredients' => 'Multiple items including momos, drinks, and sides',
                'allergens' => 'Contains: Gluten, Soy, Dairy',
                'calories' => '400-600',
                'preparation_time' => '20-25 minutes',
                'spice_level' => 'Medium',
                'is_vegetarian' => false,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '1 plate'
            ],
            'Family Combo Feast' => [
                'ingredients' => 'Variety of momos, sides, and beverages for the whole family',
                'allergens' => 'Contains: Gluten, Soy, Dairy',
                'calories' => '800-1000',
                'preparation_time' => '30-35 minutes',
                'spice_level' => 'Medium',
                'is_vegetarian' => false,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '4-6 servings'
            ],
            'Spicy Duo Combo' => [
                'ingredients' => 'Two spicy momo varieties with complementary sides',
                'allergens' => 'Contains: Gluten, Soy',
                'calories' => '450-550',
                'preparation_time' => '22-27 minutes',
                'spice_level' => 'Hot',
                'is_vegetarian' => false,
                'is_vegan' => false,
                'is_gluten_free' => false,
                'serving_size' => '2 servings'
            ],
        ];

        foreach ($menuDetails as $productName => $details) {
            $product = Product::where('name', $productName)->first();
            if ($product) {
                $product->update($details);
                $this->command->info("Updated menu details for: {$productName}");
            }
        }
    }

    private function addMissingProducts()
    {
        $this->command->info('Adding missing products...');
        
        // Add missing food products for each category with detailed information
        $missingFoods = [
            'buff' => [
                [
                    'name' => 'Mixed Vegetable Momos', 
                    'image' => 'veg-momos.jpg', 
                    'price' => 6.00,
                    'ingredients' => 'Wheat flour, mixed vegetables (carrot, cabbage, onion), spices, oil, salt, water',
                    'allergens' => 'Contains: Gluten, Soy',
                    'calories' => '280-320',
                    'preparation_time' => '15-20 minutes',
                    'spice_level' => 'Medium',
                    'is_vegetarian' => true,
                    'is_vegan' => false,
                    'is_gluten_free' => false,
                    'serving_size' => '6 pieces'
                ],
                [
                    'name' => 'Mushroom Momos', 
                    'image' => 'veg-momos.jpg', 
                    'price' => 6.50,
                    'ingredients' => 'Wheat flour, fresh mushrooms, garlic, ginger, spices, oil, salt, water',
                    'allergens' => 'Contains: Gluten',
                    'calories' => '250-290',
                    'preparation_time' => '15-20 minutes',
                    'spice_level' => 'Medium',
                    'is_vegetarian' => true,
                    'is_vegan' => false,
                    'is_gluten_free' => false,
                    'serving_size' => '6 pieces'
                ],
            ],
            'chicken' => [
                [
                    'name' => 'Spicy Chicken Wings', 
                    'image' => 'fried-chicken-momos.jpg', 
                    'price' => 8.00,
                    'ingredients' => 'Chicken wings, spices, garlic, ginger, soy sauce, oil, salt',
                    'allergens' => 'Contains: Soy',
                    'calories' => '350-400',
                    'preparation_time' => '20-25 minutes',
                    'spice_level' => 'Hot',
                    'is_vegetarian' => false,
                    'is_vegan' => false,
                    'is_gluten_free' => false,
                    'serving_size' => '6 pieces'
                ],
                [
                    'name' => 'Grilled Chicken Momos', 
                    'image' => 'steamed-chicken-momos.jpg', 
                    'price' => 7.50,
                    'ingredients' => 'Wheat flour, grilled chicken, onions, spices, oil, salt, water',
                    'allergens' => 'Contains: Gluten',
                    'calories' => '320-370',
                    'preparation_time' => '18-22 minutes',
                    'spice_level' => 'Medium',
                    'is_vegetarian' => false,
                    'is_vegan' => false,
                    'is_gluten_free' => false,
                    'serving_size' => '6 pieces'
                ],
            ],
            'main' => [
                [
                    'name' => 'Beef Momos', 
                    'image' => 'classic-pork-momos.jpg', 
                    'price' => 7.00,
                    'ingredients' => 'Wheat flour, ground beef, onions, garlic, spices, oil, salt, water',
                    'allergens' => 'Contains: Gluten',
                    'calories' => '380-420',
                    'preparation_time' => '20-25 minutes',
                    'spice_level' => 'Medium',
                    'is_vegetarian' => false,
                    'is_vegan' => false,
                    'is_gluten_free' => false,
                    'serving_size' => '6 pieces'
                ],
                [
                    'name' => 'Lamb Momos', 
                    'image' => 'classic-pork-momos.jpg', 
                    'price' => 7.50,
                    'ingredients' => 'Wheat flour, ground lamb, onions, garlic, spices, oil, salt, water',
                    'allergens' => 'Contains: Gluten',
                    'calories' => '400-450',
                    'preparation_time' => '22-27 minutes',
                    'spice_level' => 'Medium',
                    'is_vegetarian' => false,
                    'is_vegan' => false,
                    'is_gluten_free' => false,
                    'serving_size' => '6 pieces'
                ],
            ],
            'side' => [
                [
                    'name' => 'French Fries', 
                    'image' => 'image.png', 
                    'price' => 3.00,
                    'ingredients' => 'Potatoes, oil, salt, spices',
                    'allergens' => 'None',
                    'calories' => '320-380',
                    'preparation_time' => '8-12 minutes',
                    'spice_level' => 'Mild',
                    'is_vegetarian' => true,
                    'is_vegan' => true,
                    'is_gluten_free' => true,
                    'serving_size' => '1 portion'
                ],
                [
                    'name' => 'Onion Rings', 
                    'image' => 'image.png', 
                    'price' => 3.50,
                    'ingredients' => 'Onions, flour, eggs, breadcrumbs, oil, salt, spices',
                    'allergens' => 'Contains: Gluten, Eggs',
                    'calories' => '280-320',
                    'preparation_time' => '10-15 minutes',
                    'spice_level' => 'Mild',
                    'is_vegetarian' => true,
                    'is_vegan' => false,
                    'is_gluten_free' => false,
                    'serving_size' => '8 pieces'
                ],
                [
                    'name' => 'Chicken Wings', 
                    'image' => 'fried-chicken-momos.jpg', 
                    'price' => 5.00,
                    'ingredients' => 'Chicken wings, spices, garlic, ginger, oil, salt',
                    'allergens' => 'None',
                    'calories' => '300-350',
                    'preparation_time' => '15-20 minutes',
                    'spice_level' => 'Medium',
                    'is_vegetarian' => false,
                    'is_vegan' => false,
                    'is_gluten_free' => true,
                    'serving_size' => '6 pieces'
                ],
            ],
        ];

        foreach ($missingFoods as $category => $products) {
            foreach ($products as $product) {
                $this->createProductIfNotExists(
                    $product['name'], 
                    $product['description'] ?? 'Delicious ' . strtolower($product['name']), 
                    $product['price'], 
                    $product['image'], 
                    'foods', 
                    $category,
                    $product
                );
            }
        }

        // Add missing drink products for each category
        $missingDrinks = [
            'hot' => [
                ['name' => 'Green Tea', 'image' => 'masala-chai.jpg', 'price' => 2.50],
                ['name' => 'Coffee', 'image' => 'hot-chocolate.jpg', 'price' => 3.00],
            ],
            'cold' => [
                ['name' => 'Orange Juice', 'image' => 'mango-lassi.jpg', 'price' => 3.50],
                ['name' => 'Apple Juice', 'image' => 'mango-lassi.jpg', 'price' => 3.00],
            ],
            'boba' => [
                ['name' => 'Boba Milk Tea', 'image' => 'matcha-latte.jpg', 'price' => 4.50],
                ['name' => 'Taro Bubble Tea', 'image' => 'matcha-latte.jpg', 'price' => 4.00],
                ['name' => 'Brown Sugar Boba', 'image' => 'matcha-latte.jpg', 'price' => 4.50],
            ],
        ];

        foreach ($missingDrinks as $category => $products) {
            foreach ($products as $product) {
                $this->createProductIfNotExists($product['name'], $product['description'] ?? 'Refreshing ' . strtolower($product['name']), $product['price'], $product['image'], 'drinks', $category);
            }
        }

        // Add missing dessert products
        $missingDesserts = [
            ['name' => 'Ice Cream Sundae', 'image' => 'custom-icecream.jpg', 'price' => 4.50],
            ['name' => 'Waffles with Ice Cream', 'image' => 'waffles-icecream.jpg', 'price' => 5.00],
            ['name' => 'Chocolate Brownie', 'image' => 'browine-sundae.jpg', 'price' => 3.50],
        ];

        foreach ($missingDesserts as $product) {
            $this->createProductIfNotExists($product['name'], $product['description'] ?? 'Sweet and delicious ' . strtolower($product['name']), $product['price'], $product['image'], 'desserts', 'desserts');
        }

        // Add missing combo products
        $missingCombos = [
            ['name' => 'Student Combo', 'image' => 'student-set.jpg', 'price' => 12.99],
            ['name' => 'Office Worker Combo', 'image' => 'office-worker-set.jpg', 'price' => 15.99],
            ['name' => 'Party Combo', 'image' => 'party-set.jpg', 'price' => 29.99],
            ['name' => 'Group Combo', 'image' => 'group-combo.jpg', 'price' => 19.99],
            ['name' => 'Family Set', 'image' => 'family-set.jpg', 'price' => 24.99],
        ];

        foreach ($missingCombos as $product) {
            $this->createProductIfNotExists($product['name'], $product['description'] ?? 'Perfect combo meal with great value', $product['price'], $product['image'], 'combos', 'combos');
        }
    }

    private function createProductIfNotExists($name, $description, $price, $image, $tag, $category, $additionalData = [])
    {
        $existingProduct = Product::where('name', $name)->first();
        
        if (!$existingProduct) {
            Product::create([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => rand(20, 100),
                'is_active' => true,
                'cost_price' => $price * 0.5,
                'is_featured' => rand(0, 1),
                'image' => 'products/' . $tag . '/' . $image,
                'unit' => $this->getUnit($tag),
                'category' => $category,
                'tag' => $tag,
                'points' => $price,
                'tax_rate' => 5.00,
                'discount_rate' => rand(0, 1) ? 0.50 : 0.00,
                'code' => Str::upper(substr($name, 0, 3)) . '-' . Str::random(6),
                // New menu detail fields
                'ingredients' => $additionalData['ingredients'] ?? 'Fresh ingredients prepared daily',
                'allergens' => $additionalData['allergens'] ?? 'No allergens',
                'calories' => $additionalData['calories'] ?? 'N/A',
                'preparation_time' => $additionalData['preparation_time'] ?? '10-15 minutes',
                'spice_level' => $additionalData['spice_level'] ?? 'Medium',
                'is_vegetarian' => $additionalData['is_vegetarian'] ?? false,
                'is_vegan' => $additionalData['is_vegan'] ?? false,
                'is_gluten_free' => $additionalData['is_gluten_free'] ?? false,
                'nutritional_info' => $additionalData['nutritional_info'] ?? 'Nutritional information available upon request',
                'serving_size' => $additionalData['serving_size'] ?? '1 serving',
            ]);
            $this->command->info("Created product: {$name}");
        } else {
            // Update existing product with new fields if they don't exist
            $updateData = [];
            if (empty($existingProduct->ingredients)) {
                $updateData['ingredients'] = $additionalData['ingredients'] ?? 'Fresh ingredients prepared daily';
            }
            if (empty($existingProduct->allergens)) {
                $updateData['allergens'] = $additionalData['allergens'] ?? 'No allergens';
            }
            if (empty($existingProduct->calories)) {
                $updateData['calories'] = $additionalData['calories'] ?? 'N/A';
            }
            
            if (!empty($updateData)) {
                $existingProduct->update($updateData);
                $this->command->info("Updated existing product: {$name} with new menu details");
            } else {
                $this->command->info("Product already exists: {$name} (skipping)");
            }
        }
    }

    private function getUnit($tag)
    {
        return match ($tag) {
            'foods' => 'plate',
            'drinks' => 'cup',
            'desserts' => 'piece',
            'combos' => 'set',
            default => 'item',
        };
    }
} 