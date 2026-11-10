<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_tariffs', function (Blueprint $table) {
            $table->id();
            $table->enum('transport_mode', ['SEA', 'AIR', 'LAND'])->unique();
            $table->string('service_name', 100);
            $table->decimal('base_fee_usd', 10, 2);
            $table->decimal('rate_per_kg_usd', 10, 4);
            $table->decimal('rate_per_km_usd', 10, 4);
            $table->integer('min_weight_kg')->default(1);
            $table->integer('avg_speed_kmh')->default(35);
            $table->integer('handling_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_tariffs');
    }
};
