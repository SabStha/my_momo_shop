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
        return null;
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

        // Optional categories bootstrap (safe if table/slug doesn’t exist)
        if (Schema::hasTable('categories')) {
            $hasSlug = Schema::hasColumn('categories','slug');
            $cats = [
                ['name'=>'Momo','slug'=>'momo'],
                ['name'=>'Sides','slug'=>'sides'],
                ['name'=>'Hot Drinks','slug'=>'hot-drinks'],
                ['name'=>'Cold Drinks','slug'=>'cold-drinks'],
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
                'is_active'   => true,
                'updated_at'  => $now,
                'created_at'  => $now,
            ];

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
        $upsert(['name'=>'Amako Steamed Momo (Buff)',    'code'=>'amako-steamed-momo-buff',
                 'short'=>'Signature steamed buff momos—juicy filling, handmade wrappers, served with house chutney.',
                 'price'=>199, 'category'=>'momo',
                 'image'=>$this->imagePath('momo','amako-special-buff-momo')]);

        $upsert(['name'=>'Amako Steamed Momo (Chicken)', 'code'=>'amako-steamed-momo-chicken',
                 'short'=>'Classic steamed chicken momos with balanced spice and a clean, savory finish.',
                 'price'=>229, 'category'=>'momo',
                 'image'=>$this->imagePath('momo','amako-special-chicken-momo')]);

        $upsert(['name'=>'Amako Steamed Momo (Veg)',     'code'=>'amako-steamed-momo-veg',
                 'short'=>'Vegetarian steamed momos—fresh veggies and herbs inside delicate wrappers.',
                 'price'=>179, 'category'=>'momo',
                 'image'=>$this->imagePath('momo','amako-special-veg-momo')]);

        foreach ([
            ['base'=>'Fried Momo','basePrice'=>219,'delta'=>20,'img'=>'fried-momo',
                'buff'=>'Crispy fried buff momos with smoky notes and house dip.',
                'chx' =>'Golden fried chicken momos; crunchy outside, tender inside.',
                'veg' =>'Crispy veg momos—light, crunchy, and satisfying.'],
            ['base'=>'Kothey Momo','basePrice'=>219,'delta'=>21,'img'=>'kothey-momo',
                'buff'=>'Pan-fried buff momos: seared bottoms, soft tops, best of both worlds.',
                'chx' =>'Kothey chicken momos with a caramelized sear for extra depth.',
                'veg' =>'Vegetable kothey momos—charred edges, soft centers.'],
            ['base'=>'C-Momo','basePrice'=>219,'delta'=>22,'img'=>'c-momo',
                'buff'=>'Buff C-Momo tossed in spicy-tangy sauce; Nepali street favorite.',
                'chx' =>'Chicken C-Momo in bold, garlicky chili sauce.',
                'veg' =>'Veg C-Momo—zesty, saucy, and addictive heat.'],
            ['base'=>'Sadeko Momo','basePrice'=>219,'delta'=>23,'img'=>'sadeko-momo',
                'buff'=>'Buff sadeko momos—mustard oil, timur zing, fresh herbs.',
                'chx' =>'Chicken sadeko momos with citrusy heat and crunchy onions.',
                'veg' =>'Veg sadeko—bright, herby, and chili-forward.'],
        ] as $m) {
            $imgPath = $this->imagePath('momo', $m['img']);
            $slug = Str::slug($m['base']);
            $upsert(['name'=>"$m[base] (Buff)",    'code'=>"$slug-buff",    'short'=>$m['buff'], 'price'=>$m['basePrice'],               'category'=>'momo', 'image'=>$imgPath]);
            $upsert(['name'=>"$m[base] (Chicken)", 'code'=>"$slug-chicken", 'short'=>$m['chx'],  'price'=>$m['basePrice'] + $m['delta'], 'category'=>'momo', 'image'=>$imgPath]);
            $upsert(['name'=>"$m[base] (Veg)",     'code'=>"$slug-veg",     'short'=>$m['veg'],  'price'=>$m['basePrice'] - $m['delta'], 'category'=>'momo', 'image'=>$imgPath]);
        }

        // SIDES
        $upsert(['name'=>'Chicken Sausage','short'=>'Grilled chicken sausages; quick, savory side.','price'=>99,  'category'=>'sides','image'=>$this->imagePath('sides','sausage')]);
        $upsert(['name'=>'Buff Sausage',   'short'=>'Juicy buff sausages with a peppery snap.',    'price'=>99,  'category'=>'sides','image'=>$this->imagePath('sides','sausage')]);
        $upsert(['name'=>'French Fries',   'short'=>'Crispy shoestring fries—salted and snackable.','price'=>99, 'category'=>'sides','image'=>$this->imagePath('sides','french-fries')]);
        $upsert(['name'=>'Fried Mushroom', 'short'=>'Button mushrooms, lightly battered and fried.','price'=>139,'category'=>'sides','image'=>$this->imagePath('sides','fried-mushroom')]);
        $upsert(['name'=>'Karaage (3 pcs)','short'=>'Japanese-style fried chicken bites—juicy and crunchy.','price'=>99,'category'=>'sides','image'=>$this->imagePath('sides','karaage')]);
        $upsert(['name'=>'Globe (Chicken Leg, whole)','short'=>'Whole chicken leg—crispy skin, tender meat.','price'=>349,'category'=>'sides','image'=>$this->imagePath('sides','globe-chicken-leg')]);

        // HOT DRINKS
        $upsert(['name'=>'Coffee','short'=>'Hot brewed coffee—balanced and smooth.','price'=>69,'category'=>'hot-drinks','image'=>$this->imagePath('hot-drinks','hot-coffee')]);
        $upsert(['name'=>'Milk Tea','short'=>'Creamy milk tea with gentle sweetness.','price'=>69,'category'=>'hot-drinks','image'=>$this->imagePath('hot-drinks','milk-tea')]);
        $upsert(['name'=>'Black Tea','short'=>'Bold, aromatic black tea.','price'=>49,'category'=>'hot-drinks','image'=>$this->imagePath('hot-drinks','black-tea')]);
        $upsert(['name'=>'Masala Tea','short'=>'Spiced Nepali chai with warm aromatics.','price'=>69,'category'=>'hot-drinks','image'=>$this->imagePath('hot-drinks','masala-tea')]);
        $upsert(['name'=>'Lemon Tea','short'=>'Refreshing hot lemon tea with a citrus lift.','price'=>59,'category'=>'hot-drinks','image'=>$this->imagePath('hot-drinks','lemon-tea')]);
        $upsert(['name'=>'Hot Chocolate','short'=>'Rich cocoa topped with froth.','price'=>99,'category'=>'hot-drinks','image'=>$this->imagePath('hot-drinks','hot-chocolate')]);

        // COLD DRINKS
        $upsert(['name'=>'Coke',        'short'=>'Chilled Coca-Cola.','price'=>89,'category'=>'cold-drinks','image'=>$this->imagePath('cold-drinks','coke')]);
        $upsert(['name'=>'Fanta',       'short'=>'Chilled Fanta orange.','price'=>89,'category'=>'cold-drinks','image'=>$this->imagePath('cold-drinks','fanta')]);
        $upsert(['name'=>'Sprite',      'short'=>'Crisp lemon-lime refreshment.','price'=>89,'category'=>'cold-drinks','image'=>$this->imagePath('cold-drinks','sprite')]);
        $upsert(['name'=>'Peach Ice Tea','short'=>'Iced tea with peach aroma and mellow sweetness.','price'=>89,'category'=>'cold-drinks','image'=>$this->imagePath('cold-drinks','peach-ice-tea')]);
        $upsert(['name'=>'Cold Coffee', 'short'=>'Iced coffee—smooth and lightly sweet.','price'=>99,'category'=>'cold-drinks']);
        $upsert(['name'=>'Boba Drinks', 'short'=>'Chewy pearls with rotating flavors—ask staff for today’s pick.','price'=>149,'category'=>'cold-drinks']);

        // DESSERTS
        $upsert(['name'=>'Brownie with Ice Cream','short'=>'Warm chocolate brownie topped with vanilla scoop.','price'=>189,'category'=>'desserts','image'=>$this->imagePath('desserts','brownie-with-ice-cream')]);
        $upsert(['name'=>'Cheese Cake','short'=>'Creamy cheesecake with buttery base.','price'=>199,'category'=>'desserts','image'=>$this->imagePath('desserts','cheese-cake')]);
        $upsert(['name'=>'Ice Cream (Fruit/Chocolate/Oreo Topping)','short'=>'Scoops with your choice of classic toppings.','price'=>149,'category'=>'desserts','image'=>$this->imagePath('desserts','ice-cream-oreo-topping')]);

        // COMBOS
        $upsert(['name'=>'Big Party Combo (3 people)','short'=>'Large sharing platter for three—momos, sides, and drinks.','price'=>1999,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','group-set')]);
        $upsert(['name'=>'Family Combo','short'=>'Family platter built for sharing.','price'=>1959,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','family-set')]);
        $upsert(['name'=>'Family Combo with Kid Set','short'=>'Family set plus a kid-friendly portion.','price'=>2199,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','family-set')]);
        $upsert(['name'=>'Couple Set','short'=>'Two-person platter—easy date night pick.','price'=>999,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','couple-set')]);
        $upsert(['name'=>'Student Combo','short'=>'Budget combo for one—quick and filling.','price'=>199,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','student-set')]);
        $upsert(['name'=>'Office Combo','short'=>'Work-lunch pack—fast and satisfying.','price'=>299,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','office-set')]);
        $upsert(['name'=>'Kids Combo','short'=>'Kid-size set—mild flavors and fun bites.','price'=>269,'category'=>'combos','unit'=>'set']);
        $upsert(['name'=>'Globe & Potato','short'=>'Whole chicken leg with crispy potatoes.','price'=>429,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','globe-and-potato')]);
        $upsert(['name'=>'Karaage & Potato','short'=>'Crunchy karaage with fries—shareable snack set.','price'=>209,'category'=>'combos','unit'=>'set','image'=>$this->imagePath('combos','karaage-and-potato')]);
    }
}
