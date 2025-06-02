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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // belongs to customer
            $table->string('provider')->default('stripe');       // e.g., stripe, paypal(Save which company is used to handle the payment method.)
            $table->string('method_token');                      // e.g., token or id from Stripe
            $table->string('last_four')->nullable();             // last 4 digits of card
            $table->string('brand')->nullable();                 // Visa, MasterCard, etc.
            $table->boolean('is_default')->default(false);       // default payment method
            $table->timestamps();

            // How payment method works?
            // 1. Customer adds card → Stripe gives you a token (not real card)
            // 2. You save token in database (table: payment_methods)
            // 3. On checkout, you use that token to charge the card
            // 4. You record the result in payments (success, amount, etc.)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
