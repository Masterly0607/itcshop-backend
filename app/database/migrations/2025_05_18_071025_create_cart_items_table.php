<?php

use App\Models\User;
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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class); // Short way to creates a foreign key column called user_id and links it to the id field of the users table. Longer way: $table->unsignedBigInteger('user_id'); and $table->foreign('user_id')->references('id')->on('users');
            $table->foreignId('product_id')->constrained(); // create column name prodouct_id that link to product.id in product table. Longer way: $table->foreignId('product_id')->references('id')->on('products');
            // foreignIdFor and foreignId work the same way. Just different style
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
