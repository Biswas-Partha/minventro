<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_address_id')->constrained('customer_addresses')->cascadeOnDelete();
            $table->enum('transport_method', ['own_rider', 'courier']);
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->json('items');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'dispatched', 'delivered'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
