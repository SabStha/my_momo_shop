<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('referrals', function (Blueprint $table) {
            if (Schema::hasColumn('referrals', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }
            // Remove any other coupon-related fields if present
        });
    }

    public function down()
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->string('coupon_code')->unique()->nullable();
            // Add back any other coupon-related fields if needed
        });
    }
}; 