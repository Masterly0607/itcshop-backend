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
        // customer table = stores customer account info — basically people who buy products from your e-commerce site. When someone registers as a customer: POST /api/customer/register → inserts into customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('address')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('status', 45)->nullable();
            $table->boolean('is_verified')->default(false);

            $table->timestamps();
            $table->foreignIdFor(User::class, 'created_by')->nullable();
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
