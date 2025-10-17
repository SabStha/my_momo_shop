<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    /** Order we’ll probe images in */
    private array $exts = ['jpg','jpeg','png','webp'];

    /** Find first existing image path on the public disk */
    private function imagePath(string $cat, string $basename): ?string
    {
        foreach ($this->exts as $e) {
            $p = "products/{$cat}/{$basename}.{$e}";
            if (Storage::disk('public')->exists($p)) return $p;
        }
        $slug = Str::slug($basename);
        foreach ($this->exts as $e) {
            $p = "products/{$cat}/{$slug}.{$e}";
            if (Storage::disk('public')->exists($p)) return $p;
        }
        // Return placeholder instead of null
        return 'logo/momokologo.png'; // Using existing logo as placeholder
    }

    private function cents($rs): int
    {
        return (int) round(((float)$rs) * 100);
    }

    public function run(): void
    {
        // Which short-description column do you have?
        $shortCol = null;
        foreach (['short_description','description_short','summary','subtitle','excerpt','desc_short'] as $c) {
            if (Schema::hasColumn('products', $c)) { $shortCol = $c; break; }
        }

        $hasPriceCents = Schema::hasColumn('products','price_cents');
        $hasCurrency   = Schema::hasColumn('products','currency');
        $hasTaxRate    = Schema::hasColumn('products','tax_rate');

        // Optional categories bootstrap (safe if table/slug doesn't exist)
        if (Schema::hasTable('categories')) {
            $hasSlug = Schema::hasColumn('categories','slug');
            $cats = [
                ['name'=>'Momo','slug'=>'momo'],
                ['name'=>'Sides','slug'=>'sides'],
                ['name'=>'Hot Drinks','slug'=>'hot-drinks'],
                ['name'=>'Cold Drinks','slug'=>'cold-drinks'],
                ['name'=>'Boba','slug'=>'boba'],
                ['name'=>'Desserts','slug'=>'desserts'],
                ['name'=>'Combos','slug'=>'combos'],
            ];
            foreach ($cats as $c) {
                if ($hasSlug) {
                    DB::table('categories')->updateOrInsert(
                        ['slug'=>$c['slug']],
                        ['name'=>$c['name'],'status'=>'active']
                    );
                } else {
                    DB::table('categories')->updateOrInsert(
                        ['name'=>$c['name']],
                        ['status'=>'active']
                    );
                }
            }
        }

        $now = now();
        $TAX = 13.00; // NPR VAT (exclusive)

        // Helper: upsert into products
        $upsert = function(array $p) use ($now, $TAX, $shortCol, $hasPriceCents, $hasCurrency, $hasTaxRate) {
            $code = $p['code'] ?? Str::slug($p['name']);

            $payload = [
                'name'        => $p['name'],
                'code'        => $code,
                'description' => $p['description'] ?? ($p['short'] ?? null),
                'price'       => $p['price'],                 // decimal price
                'category'    => $p['category'],
                'tag'         => $p['tag'] ?? $p['category'],
                'unit'        => $p['unit'] ?? 'plate',
                'is_featured' => $p['is_featured'] ?? false,
                'is_menu_highlight' => $p['is_menu_highlight'] ?? false,
                'is_active'   => true,
                'updated_at'  => $now,
                'created_at'  => $now,
            ];

            // Add nutritional information if provided
            if (!empty($p['ingredients'])) {
                $payload['ingredients'] = is_array($p['ingredients']) ? implode(', ', $p['ingredients']) : $p['ingredients'];
            }
            if (!empty($p['allergens'])) {
                $payload['allergens'] = is_array($p['allergens']) ? implode(', ', $p['allergens']) : $p['allergens'];
            }
            if (!empty($p['calories'])) {
                $payload['calories'] = $p['calories'];
            }
            if (!empty($p['serving_size'])) {
                $payload['serving_size'] = $p['serving_size'];
            }
            if (!empty($p['spice_level'])) {
                $payload['spice_level'] = $p['spice_level'];
            }
            if (!empty($p['prep_time'])) {
                $payload['preparation_time'] = $p['prep_time'];
            }
            if (isset($p['is_vegetarian'])) {
                $payload['is_vegetarian'] = $p['is_vegetarian'];
            }
            if (isset($p['is_vegan'])) {
                $payload['is_vegan'] = $p['is_vegan'];
            }
            if (isset($p['is_gluten_free'])) {
                $payload['is_gluten_free'] = $p['is_gluten_free'];
            }

            if ($shortCol) {
                $payload[$shortCol] = Str::limit($p['short'] ?? ($p['description'] ?? ''), 140);
            }
            if (!empty($p['image'])) {
                $payload['image'] = $p['image']; // storage-relative path, e.g. products/momo/xxx.png
            }
            if ($hasCurrency) {
                $payload['currency'] = 'NPR';
            }
            if ($hasTaxRate) {
                $payload['tax_rate'] = $p['tax_rate'] ?? $TAX;
            }
            if ($hasPriceCents) {
                $payload['price_cents'] = (int) round(((float)$p['price']) * 100);
            }

            DB::table('products')->updateOrInsert(['code'=>$code], $payload);
        };

        // === DATA ===
        // Supply image *basenames* (no extension) because we’ll probe the copied files.
        // You already copied images into storage/app/public/products/<cat>/...
        // MOMO
        $upsert([
            'name'=>'Amako Steamed Momo (Buff)', 'code'=>'amako-steamed-momo-buff',
            'short'=>'Signature steamed buff momos—juicy filling, handmade wrappers, served with house chutney.',
            'price'=>199, 'category'=>'momo', 'tag'=>'buff',
            'image'=>$this->imagePath('momo','amako-special-buff-momo'),
            'ingredients'=>'wheat flour wrapper, minced buffalo, onion, garlic, ginger, scallion, coriander, salt, pepper, soy sauce, oil; house tomato-sesame chutney',
            'allergens'=>'Gluten (wheat), Soy, Sesame (in chutney)',
            'calories'=>'520 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'12-15 minutes',
            'is_vegetarian'=>false,
            'is_vegan'=>false,
            
            'is_featured'=>true, // Featured product
        ]);

        $upsert([
            'name'=>'Amako Steamed Momo (Chicken)', 'code'=>'amako-steamed-momo-chicken',
            'short'=>'Classic steamed chicken momos with balanced spice and a clean, savory finish.',
            'price'=>229, 'category'=>'momo', 'tag'=>'chicken',
            'image'=>$this->imagePath('momo','amako-special-chicken-momo'),
            'ingredients'=>'wheat flour wrapper, minced chicken, onion, garlic, ginger, scallion, coriander, salt, pepper, soy sauce, oil; house tomato-sesame chutney',
            'allergens'=>'Gluten (wheat), Soy, Sesame (in chutney)',
            'calories'=>'440 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'12-15 minutes',
            'is_vegetarian'=>false,
            'is_vegan'=>false,
            
            'is_featured'=>true, // Featured product
        ]);

        $upsert([
            'name'=>'Amako Steamed Momo (Veg)', 'code'=>'amako-steamed-momo-veg',
            'short'=>'Vegetarian steamed momos—fresh veggies and herbs inside delicate wrappers.',
            'price'=>179, 'category'=>'momo', 'tag'=>'veg',
            'image'=>$this->imagePath('momo','amako-special-veg-momo'),
            'ingredients'=>'wheat flour wrapper, cabbage, carrot, onion, garlic, ginger, scallion, coriander, salt, pepper, soy sauce, oil; house tomato-sesame chutney',
            'allergens'=>'Gluten (wheat), Soy, Sesame (in chutney)',
            'calories'=>'340 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'12-15 minutes',
            'is_vegetarian'=>true,
            'is_vegan'=>false,
            'is_featured'=>true, // Featured product
        ]);

        // Fried Momos
        $upsert([
            'name'=>'Fried Momo (Buff)', 'code'=>'fried-momo-buff',
            'short'=>'Crispy fried buff momos with smoky notes and house dip.',
            'price'=>219, 'category'=>'momo', 'tag'=>'buff',
            'image'=>$this->imagePath('momo','fried-momo'),
            'ingredients'=>'buff steamed momos (above), vegetable oil for frying; house dip',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'680 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'6-8 minutes',
            'is_vegetarian'=>false,
            'is_featured'=>true, // Featured product
        ]);
        $upsert([
            'name'=>'Fried Momo (Chicken)', 'code'=>'fried-momo-chicken',
            'short'=>'Golden fried chicken momos; crunchy outside, tender inside.',
            'price'=>239, 'category'=>'momo', 'tag'=>'chicken',
            'image'=>$this->imagePath('momo','fried-momo'),
            'ingredients'=>'chicken steamed momos, vegetable oil for frying; house dip',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'620 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'6-8 minutes',
            'is_vegetarian'=>false,
        ]);
        $upsert([
            'name'=>'Fried Momo (Veg)', 'code'=>'fried-momo-veg',
            'short'=>'Crispy veg momos—light, crunchy, and satisfying.',
            'price'=>199, 'category'=>'momo', 'tag'=>'veg',
            'image'=>$this->imagePath('momo','fried-momo'),
            'ingredients'=>'veg steamed momos, vegetable oil for frying; house dip',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'500 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'6-8 minutes',
            'is_vegetarian'=>true,
        ]);

        // Kothey Momos
        $upsert([
            'name'=>'Kothey Momo (Buff)', 'code'=>'kothey-momo-buff',
            'short'=>'Pan-fried buff momos: seared bottoms, soft tops, best of both worlds.',
            'price'=>219, 'category'=>'momo', 'tag'=>'buff',
            'image'=>$this->imagePath('momo','kothey-momo'),
            'ingredients'=>'buff steamed momos, oil for pan-sear; house dip',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'600 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'8-10 minutes',
            'is_vegetarian'=>false,
        ]);
        $upsert([
            'name'=>'Kothey Momo (Chicken)', 'code'=>'kothey-momo-chicken',
            'short'=>'Kothey chicken momos with a caramelized sear for extra depth.',
            'price'=>240, 'category'=>'momo', 'tag'=>'chicken',
            'image'=>$this->imagePath('momo','kothey-momo'),
            'ingredients'=>'chicken steamed momos, oil for pan-sear; house dip',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'520 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'8-10 minutes',
            'is_vegetarian'=>false,
            'is_featured'=>true, // Featured product
        ]);
        $upsert([
            'name'=>'Kothey Momo (Veg)', 'code'=>'kothey-momo-veg',
            'short'=>'Vegetable kothey momos—charred edges, soft centers.',
            'price'=>198, 'category'=>'momo', 'tag'=>'veg',
            'image'=>$this->imagePath('momo','kothey-momo'),
            'ingredients'=>'veg steamed momos, oil for pan-sear; house dip',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'420 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Mild',
            'prep_time'=>'8-10 minutes',
            'is_vegetarian'=>true,
        ]);

        // C-Momos
        $upsert([
            'name'=>'C-Momo (Buff)', 'code'=>'c-momo-buff',
            'short'=>'Buff C-Momo tossed in spicy-tangy sauce; Nepali street favorite.',
            'price'=>219, 'category'=>'momo', 'tag'=>'buff',
            'image'=>$this->imagePath('momo','c-momo'),
            'ingredients'=>'buff steamed momos, chili-garlic tomato sauce (tomato, chili, garlic, onion, soy sauce, vinegar, sugar, oil)',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'660 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Hot',
            'prep_time'=>'7-9 minutes',
            'is_vegetarian'=>false,
        ]);
        $upsert([
            'name'=>'C-Momo (Chicken)', 'code'=>'c-momo-chicken',
            'short'=>'Chicken C-Momo in bold, garlicky chili sauce.',
            'price'=>241, 'category'=>'momo', 'tag'=>'chicken',
            'image'=>$this->imagePath('momo','c-momo'),
            'ingredients'=>'chicken steamed momos, chili-garlic tomato sauce (tomato, chili, garlic, onion, soy sauce, vinegar, sugar, oil)',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'580 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Hot',
            'prep_time'=>'7-9 minutes',
            'is_vegetarian'=>false,
        ]);
        $upsert([
            'name'=>'C-Momo (Veg)', 'code'=>'c-momo-veg',
            'short'=>'Veg C-Momo—zesty, saucy, and addictive heat.',
            'price'=>197, 'category'=>'momo', 'tag'=>'veg',
            'image'=>$this->imagePath('momo','c-momo'),
            'ingredients'=>'veg steamed momos, chili-garlic tomato sauce (tomato, chili, garlic, onion, soy sauce, vinegar, sugar, oil)',
            'allergens'=>'Gluten (wheat), Soy, Sesame',
            'calories'=>'460 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Hot',
            'prep_time'=>'7-9 minutes',
            'is_vegetarian'=>true,
        ]);

        // Sadeko Momos
        $upsert([
            'name'=>'Sadeko Momo (Buff)', 'code'=>'sadeko-momo-buff',
            'short'=>'Buff sadeko momos—mustard oil, timur zing, fresh herbs.',
            'price'=>219, 'category'=>'momo', 'tag'=>'buff',
            'image'=>$this->imagePath('momo','sadeko-momo'),
            'ingredients'=>'buff steamed momos, mustard oil, onion, tomato, green chili, coriander, timur (Sichuan pepper), lemon juice, roasted spices, salt',
            'allergens'=>'Gluten (wheat), Mustard, Soy',
            'calories'=>'640 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Hot',
            'prep_time'=>'5-7 minutes',
            'is_vegetarian'=>false,
        ]);
        $upsert([
            'name'=>'Sadeko Momo (Chicken)', 'code'=>'sadeko-momo-chicken',
            'short'=>'Chicken sadeko momos with citrusy heat and crunchy onions.',
            'price'=>242, 'category'=>'momo', 'tag'=>'chicken',
            'image'=>$this->imagePath('momo','sadeko-momo'),
            'ingredients'=>'chicken steamed momos, mustard oil, onion, tomato, green chili, coriander, timur, lemon juice, roasted spices, salt',
            'allergens'=>'Gluten (wheat), Mustard, Soy',
            'calories'=>'560 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Hot',
            'prep_time'=>'5-7 minutes',
            'is_vegetarian'=>false,
        ]);
        $upsert([
            'name'=>'Sadeko Momo (Veg)', 'code'=>'sadeko-momo-veg',
            'short'=>'Veg sadeko—bright, herby, and chili-forward.',
            'price'=>196, 'category'=>'momo', 'tag'=>'veg',
            'image'=>$this->imagePath('momo','sadeko-momo'),
            'ingredients'=>'veg steamed momos, mustard oil, onion, tomato, green chili, coriander, timur, lemon juice, roasted spices, salt',
            'allergens'=>'Gluten (wheat), Mustard, Soy',
            'calories'=>'440 kcal',
            'serving_size'=>'10 pieces',
            'spice_level'=>'Hot',
            'prep_time'=>'5-7 minutes',
            'is_vegetarian'=>true,
        ]);

        // SIDES
        $upsert(['name'=>'Chicken Sausage','short'=>'Grilled chicken sausages; quick, savory side.','price'=>99,'category'=>'sides','tag'=>'others','image'=>$this->imagePath('sides','chicken-sausage'),
            'ingredients'=>'chicken, salt, spices, starch/binder, antioxidant, casing',
            'allergens'=>'Soy (common binder), Gluten (possible), Sulfites (possible)',
            'calories'=>'220 kcal','serving_size'=>'2 pieces (~100 g)','spice_level'=>'Mild','prep_time'=>'6-8 minutes','is_vegetarian'=>false]);
        $upsert(['name'=>'Buff Sausage','short'=>'Juicy buff sausages with a peppery snap.','price'=>99,'category'=>'sides','tag'=>'others','image'=>$this->imagePath('sides','buff-sausage'),
            'ingredients'=>'buffalo meat, salt, spices, starch/binder, antioxidant, casing',
            'allergens'=>'Soy (common binder), Gluten (possible), Sulfites (possible)',
            'calories'=>'240 kcal','serving_size'=>'2 pieces (~100 g)','spice_level'=>'Mild','prep_time'=>'6-8 minutes','is_vegetarian'=>false]);
        $upsert(['name'=>'French Fries','short'=>'Crispy shoestring fries—salted and snackable.','price'=>99,'category'=>'sides','tag'=>'others','image'=>$this->imagePath('sides','french-fries'),
            'ingredients'=>'potatoes, vegetable oil, salt',
            'allergens'=>'None (shared fryer may contain gluten traces)',
            'calories'=>'360 kcal','serving_size'=>'~120 g','spice_level'=>'None','prep_time'=>'5-6 minutes','is_vegetarian'=>true,'is_vegan'=>true]);
        $upsert(['name'=>'Fried Mushroom','short'=>'Button mushrooms, lightly battered and fried.','price'=>139,'category'=>'sides','tag'=>'others','image'=>$this->imagePath('sides','fried-mushroom'),
            'ingredients'=>'button mushrooms, batter (wheat flour, cornflour, spices), oil, salt',
            'allergens'=>'Gluten (wheat)',
            'calories'=>'300 kcal','serving_size'=>'~150 g','spice_level'=>'Mild','prep_time'=>'5-7 minutes','is_vegetarian'=>true]);
        $upsert(['name'=>'Karaage (3 pcs)','short'=>'Japanese-style fried chicken bites—juicy and crunchy.','price'=>99,'category'=>'sides','tag'=>'others','image'=>$this->imagePath('sides','karaage'),
            'ingredients'=>'chicken thigh, soy sauce, garlic, ginger, starch/flour, oil',
            'allergens'=>'Soy, Gluten',
            'calories'=>'330 kcal','serving_size'=>'3 pieces','spice_level'=>'Mild','prep_time'=>'6-8 minutes','is_vegetarian'=>false]);
        $upsert(['name'=>'Globe (Chicken Leg, whole)','short'=>'Whole chicken leg—crispy skin, tender meat.','price'=>349,'category'=>'sides','tag'=>'others','image'=>$this->imagePath('sides','globe-chicken-leg'),
            'ingredients'=>'whole chicken leg, salt, pepper, spice rub, oil (roasted or fried)',
            'allergens'=>'Soy, Gluten (if soy/wheat marinade used); otherwise None',
            'calories'=>'520 kcal','serving_size'=>'1 whole leg','spice_level'=>'Mild','prep_time'=>'15-20 minutes','is_vegetarian'=>false]);

        // HOT DRINKS
        $upsert(['name'=>'Coffee','short'=>'Hot brewed coffee—balanced and smooth.','price'=>69,'category'=>'hot-drinks','tag'=>'hot','image'=>$this->imagePath('hot-drinks','coffee'),
            'ingredients'=>'coffee, hot water','allergens'=>'None','calories'=>'5 kcal','serving_size'=>'~200 ml','spice_level'=>'None','prep_time'=>'2-3 minutes','is_vegan'=>true]);
        $upsert(['name'=>'Milk Tea','short'=>'Creamy milk tea with gentle sweetness.','price'=>69,'category'=>'hot-drinks','tag'=>'hot','image'=>$this->imagePath('hot-drinks','milk-tea'),
            'ingredients'=>'black tea, milk, sugar','allergens'=>'Dairy','calories'=>'180 kcal','serving_size'=>'~250 ml','spice_level'=>'None','prep_time'=>'3-4 minutes','is_vegetarian'=>true]);
        $upsert(['name'=>'Black Tea','short'=>'Bold, aromatic black tea.','price'=>49,'category'=>'hot-drinks','tag'=>'hot','image'=>$this->imagePath('hot-drinks','black-tea'),
            'ingredients'=>'black tea, hot water','allergens'=>'None','calories'=>'2 kcal','serving_size'=>'~200 ml','spice_level'=>'None','prep_time'=>'2-3 minutes','is_vegan'=>true]);
        $upsert(['name'=>'Masala Tea','short'=>'Spiced Nepali chai with warm aromatics.','price'=>69,'category'=>'hot-drinks','tag'=>'hot','image'=>$this->imagePath('hot-drinks','masala-tea'),
            'ingredients'=>'black tea, milk, sugar, masala spices (cardamom, cinnamon, clove, ginger)',
            'allergens'=>'Dairy','calories'=>'190 kcal','serving_size'=>'~250 ml','spice_level'=>'Mild','prep_time'=>'3-5 minutes','is_vegetarian'=>true]);
        $upsert(['name'=>'Lemon Tea','short'=>'Refreshing hot lemon tea with a citrus lift.','price'=>59,'category'=>'hot-drinks','tag'=>'hot','image'=>$this->imagePath('hot-drinks','lemon-tea'),
            'ingredients'=>'black tea, lemon, sugar','allergens'=>'None','calories'=>'35 kcal','serving_size'=>'~250 ml','spice_level'=>'None','prep_time'=>'3-4 minutes','is_vegan'=>true]);
        $upsert(['name'=>'Hot Chocolate','short'=>'Rich cocoa topped with froth.','price'=>99,'category'=>'hot-drinks','tag'=>'hot','image'=>$this->imagePath('hot-drinks','hot-chocolate'),
            'ingredients'=>'cocoa, milk, sugar (may contain emulsifier)','allergens'=>'Dairy, Soy (lecithin)','calories'=>'210 kcal','serving_size'=>'~250 ml','spice_level'=>'None','prep_time'=>'3-4 minutes','is_vegetarian'=>true]);

        // COLD DRINKS
        $upsert(['name'=>'Coke','short'=>'Chilled Coca-Cola.','price'=>89,'category'=>'cold-drinks','tag'=>'cold','image'=>$this->imagePath('cold-drinks','coke'),
            'ingredients'=>'Carbonated water, sugar, caramel color, phosphoric acid, natural flavors, caffeine',
            'allergens'=>'None','calories'=>'140 kcal','serving_size'=>'330ml can','spice_level'=>'None','prep_time'=>'Instant','is_vegan'=>true]);
        $upsert(['name'=>'Fanta','short'=>'Chilled Fanta orange.','price'=>89,'category'=>'cold-drinks','tag'=>'cold','image'=>$this->imagePath('cold-drinks','fanta'),
            'ingredients'=>'Carbonated water, sugar, orange juice, citric acid, natural flavors',
            'allergens'=>'None','calories'=>'150 kcal','serving_size'=>'330ml can','spice_level'=>'None','prep_time'=>'Instant','is_vegan'=>true]);
        $upsert(['name'=>'Sprite','short'=>'Crisp lemon-lime refreshment.','price'=>89,'category'=>'cold-drinks','tag'=>'cold','image'=>$this->imagePath('cold-drinks','sprite'),
            'ingredients'=>'Carbonated water, sugar, citric acid, natural lemon-lime flavors',
            'allergens'=>'None','calories'=>'140 kcal','serving_size'=>'330ml can','spice_level'=>'None','prep_time'=>'Instant','is_vegan'=>true]);
        $upsert(['name'=>'Peach Ice Tea','short'=>'Iced tea with peach aroma and mellow sweetness.','price'=>89,'category'=>'cold-drinks','tag'=>'cold','image'=>$this->imagePath('cold-drinks','peach-ice-tea'),
            'ingredients'=>'brewed black tea, sugar, peach flavor/extract, citric acid, water, ice',
            'allergens'=>'None','calories'=>'120 kcal','serving_size'=>'~300 ml','spice_level'=>'None','prep_time'=>'2-3 minutes','is_vegan'=>true]);
        $upsert(['name'=>'Cold Coffee','short'=>'Iced coffee—smooth and lightly sweet.','price'=>99,'category'=>'cold-drinks','tag'=>'cold', 'image'=>$this->imagePath('cold-drinks','cold-coffee'),
            'ingredients'=>'brewed coffee, milk, sugar, ice','allergens'=>'Dairy','calories'=>'180 kcal','serving_size'=>'~300 ml','spice_level'=>'None','prep_time'=>'2-3 minutes','is_vegetarian'=>true]);
        
        // BOBA (Separate category)
        $upsert(['name'=>'Boba Drinks','short'=>'Chewy pearls with rotating flavors—ask staff for today\'s pick.','price'=>149,'category'=>'boba','tag'=>'boba','image'=>$this->imagePath('boba','boba'),
            'ingredients'=>'tapioca pearls, tea base (black/green), milk or fruit base, sugar syrup, ice',
            'allergens'=>'Dairy (if milk base)','calories'=>'360 kcal','serving_size'=>'~400 ml','spice_level'=>'None','prep_time'=>'3-5 minutes','is_vegetarian'=>true]);

        // DESSERTS
        $upsert(['name'=>'Brownie with Ice Cream','short'=>'Warm chocolate brownie topped with vanilla scoop.','price'=>189,'category'=>'desserts','tag'=>'desserts','image'=>$this->imagePath('desserts','brownie-with-ice-cream'),
            'ingredients'=>'wheat flour, cocoa, sugar, butter, eggs, chocolate, vanilla ice cream',
            'allergens'=>'Gluten, Dairy, Eggs, Soy (lecithin)','calories'=>'430 kcal','serving_size'=>'1 plate','spice_level'=>'None','prep_time'=>'5-7 minutes','is_vegetarian'=>true]);
        $upsert(['name'=>'Cheese Cake','short'=>'Creamy cheesecake with buttery base.','price'=>199,'category'=>'desserts','tag'=>'desserts','image'=>$this->imagePath('desserts','cheese-cake'),
            'ingredients'=>'cream cheese, sugar, eggs, butter, wheat biscuit base, vanilla',
            'allergens'=>'Dairy, Eggs, Gluten','calories'=>'420 kcal','serving_size'=>'1 slice','spice_level'=>'None','prep_time'=>'2-3 minutes','is_vegetarian'=>true]);
        $upsert(['name'=>'Ice Cream (Fruit/Chocolate/Oreo Topping)','short'=>'Scoops with your choice of classic toppings.','price'=>149,'category'=>'desserts','tag'=>'desserts','image'=>$this->imagePath('desserts','ice-cream-oreo-topping'),
            'ingredients'=>'milk, sugar, cream; toppings: fruit coulis, chocolate sauce, Oreo crumbs',
            'allergens'=>'Dairy; Oreo topping contains Gluten, Soy','calories'=>'320 kcal','serving_size'=>'2 scoops (~100 g) with topping','spice_level'=>'None','prep_time'=>'2-3 minutes','is_vegetarian'=>true,'is_featured'=>true]);

        // COMBOS
        $upsert(['name'=>'Big Party Combo (3 people)','short'=>'Large sharing platter for three—momos, sides, and drinks.','price'=>1999,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','group-set'),
            'ingredients'=>'24 mixed steamed momos (8 buff, 8 chicken, 8 veg), large fries (~250 g), karaage 6 pcs, any 3 cold drinks (330 ml)',
            'allergens'=>'Gluten, Soy, Sesame, Dairy (if milk drinks selected)','calories'=>'3000-3300 kcal','serving_size'=>'For 3 people','spice_level'=>'Mild-Hot','prep_time'=>'15-18 minutes','is_menu_highlight'=>true]);
        $upsert(['name'=>'Family Combo','short'=>'Family platter built for sharing.','price'=>1959,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','family-set'),
            'ingredients'=>'24 mixed steamed momos, 3 medium fries (~120 g each), 3 chicken sausage sets (2 pcs each), 3 desserts (ice cream cups), 3 cold drinks',
            'allergens'=>'Gluten, Soy, Sesame, Dairy, Eggs (desserts)','calories'=>'4200-4800 kcal','serving_size'=>'For 3-4 people','spice_level'=>'Mild-Hot','prep_time'=>'16-20 minutes']);
        $upsert(['name'=>'Family Combo with Kid Set','short'=>'Family set plus a kid-friendly portion.','price'=>2199,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','family-kid-set'),
            'ingredients'=>'Family Combo + Kid set (5 veg momos, kid juice 200 ml, pudding cup)',
            'allergens'=>'Gluten, Soy, Sesame, Dairy, Eggs (pudding)','calories'=>'4600-5200 kcal','serving_size'=>'3-4 people + 1 child','spice_level'=>'Mild','prep_time'=>'18-22 minutes']);
        $upsert(['name'=>'Couple Set','short'=>'Two-person platter—easy date night pick.','price'=>999,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','couple-set'),
            'ingredients'=>'16 mixed steamed momos (buff/chicken/veg), 1 medium fries, 2 cold drinks',
            'allergens'=>'Gluten, Soy, Sesame','calories'=>'1600-1900 kcal','serving_size'=>'For 2 people','spice_level'=>'Mild-Hot','prep_time'=>'12-15 minutes','is_menu_highlight'=>true]);
        $upsert(['name'=>'Student Combo','short'=>'Budget combo for one—quick and filling.','price'=>199,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','student-set'),
            'ingredients'=>'6 steamed momos (veg or chicken) + hot black tea (200 ml)',
            'allergens'=>'Gluten, Soy, Sesame (from chutney)','calories'=>'260-340 kcal','serving_size'=>'1 person','spice_level'=>'Mild','prep_time'=>'10-12 minutes','is_menu_highlight'=>true]);
        $upsert(['name'=>'Office Combo','short'=>'Work-lunch pack—fast and satisfying.','price'=>299,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','office-set'),
            'ingredients'=>'6 steamed chicken momos + karaage 3 pcs + Coke (330 ml)',
            'allergens'=>'Gluten, Soy, Sesame','calories'=>'750-850 kcal','serving_size'=>'1 person','spice_level'=>'Mild','prep_time'=>'12-14 minutes','is_menu_highlight'=>true]);
        $upsert(['name'=>'Kids Combo','short'=>'Kid-size set—mild flavors and fun bites.','price'=>269,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','kids-combo'),
            'ingredients'=>'5-piece kid momo (veg), kid juice (200 ml), pudding cup',
            'allergens'=>'Gluten, Soy (momo/chutney), Dairy & Eggs (pudding)','calories'=>'420-520 kcal','serving_size'=>'1 child','spice_level'=>'Mild','prep_time'=>'8-10 minutes','is_vegetarian'=>true]);
        $upsert(['name'=>'Globe & Potato','short'=>'Whole chicken leg with crispy potatoes.','price'=>429,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','globe-potato'),
            'ingredients'=>'whole chicken leg + fries (~200 g)',
            'allergens'=>'Soy/Gluten (if marinade includes soy/wheat)','calories'=>'800-900 kcal','serving_size'=>'1 plate','spice_level'=>'Mild','prep_time'=>'15-18 minutes']);
        $upsert(['name'=>'Karaage & Potato','short'=>'Crunchy karaage with fries—shareable snack set.','price'=>209,'category'=>'combos','tag'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','karaage-potato'),
            'ingredients'=>'karaage 3 pcs + fries (~120 g)',
            'allergens'=>'Soy, Gluten','calories'=>'650-720 kcal','serving_size'=>'1 plate','spice_level'=>'Mild','prep_time'=>'8-10 minutes']);
    }
}
