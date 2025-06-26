<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        // Create wallets table
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('wallet_number', 19); // 16 chars + 3 hyphens
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('is_active');
        });

        // Try to create trigger to automatically create wallet when user is created
        // This requires SUPER privileges, so we make it optional
        try {
            DB::unprepared('
                CREATE TRIGGER create_user_wallet
                AFTER INSERT ON users
                FOR EACH ROW
                BEGIN
                    INSERT INTO wallets (user_id, balance, created_at, updated_at)
                    VALUES (NEW.id, 0, NOW(), NOW());
                END
            ');
        } catch (\Exception $e) {
            // Log the error but don't fail the migration
            \Log::warning('Could not create wallet trigger: ' . $e->getMessage());
            \Log::warning('Wallets will need to be created manually or through application logic');
        }
    }

    public function down() {
        // Drop the trigger first
        DB::unprepared('DROP TRIGGER IF EXISTS create_user_wallet');
        
        Schema::dropIfExists('wallets');
    }
}; 