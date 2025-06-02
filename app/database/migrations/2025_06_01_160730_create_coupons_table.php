<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();              // e.g. SAVE10, BLACKFRIDAY
            $table->enum('type', ['fixed', 'percent']);    // discount type(fixed = SAVE5 = 5.00 → means $5 discount, percent = OFF10 = 10.00 → means 10% discount)
            $table->decimal('value', 8, 2);                // e.g. 10.00 or 25%
            $table->unsignedInteger('usage_limit')->nullable(); // optional usage cap
            $table->unsignedInteger('used')->default(0);   // track usage(It stores how many times the coupon has been used)
            $table->date('start_date')->nullable();        // optional start date
            $table->date('end_date')->nullable();          // optional expiry
            $table->boolean('is_active')->default(true);   // active or not(Sometimes, you want to disable a coupon without deleting it.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
