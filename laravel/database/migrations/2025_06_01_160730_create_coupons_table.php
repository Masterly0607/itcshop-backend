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
        $table->string('code')->unique(); // e.g. WELCOME10
        $table->enum('discount_type', ['percent', 'fixed']); // 'percent' or 'fixed'
        $table->decimal('discount_value', 8, 2);
        $table->decimal('min_order_amount', 8, 2)->default(0);
        $table->unsignedInteger('used')->default(0);
        $table->timestamp('expires_at')->nullable();
        
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
