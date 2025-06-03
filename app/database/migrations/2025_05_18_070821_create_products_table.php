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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 2000);
            $table->string('slug', 2000); // Slug: is a URL-friendly version of a title.(Convert "iPhone 15 Pro Max" => "iphone-15-pro-max"). Make URL like this ecommerce.com/product/iphone-15-pro-max not ecommerce.com/product/123
            $table->string('image', 2000)->nullable; // image path
            $table->string('image_mime')->nullable(); // image file type 
            $table->integer('image_size')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->foreignIdFor(User::class, 'created_by')->nullable();
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            $table->softDeletes();
            $table->foreignIdFor(User::class, 'deleted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
