<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Customer foreign key
            $table->foreignIdFor(Customer::class)->constrained()->onDelete('cascade');

            // Product foreign key
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->integer('quantity');

            $table->timestamps();

            // Prevent duplicates (very important for merging logic)
            $table->unique(['customer_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
