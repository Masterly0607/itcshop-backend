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
        // Payment table = It stores payment details after a customer places an order.


        Schema::create('payments', function (Blueprint $table) {
           $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->decimal('amount', 8, 2);
    $table->string('status'); // pending, succeeded, failed
    $table->string('type')->default('stripe');
    $table->foreignId('created_by');
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
