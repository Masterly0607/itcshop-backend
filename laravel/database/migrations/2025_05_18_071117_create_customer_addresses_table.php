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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // onDelete = when id in customer table is deleted, all customer addessses will delete
            $table->enum('type', ['shipping', 'billing'])->default('shipping');   // type of address(shipping use when checkout(Where the store should send the productb), billing use when doing payment = where your bank/card is registered )
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable(); // made nullable for optional 2nd line
            $table->string('city', 255);
            $table->string('state', 45)->nullable();
            $table->string('zipcode', 45);
            $table->string('country_code', 3)->nullable();
            $table->timestamps();

            // If you have a countries table, keep this foreign key
            $table->foreign('country_code')->references('code')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
