<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->index(); // UN/LOCODE or custom code (e.g., IDTPP, NLRTM)
            $table->string('name', 150);
            $table->string('city', 100);
            $table->string('country', 100);
            $table->enum('type', ['PORT', 'AIRPORT', 'WAREHOUSE', 'HUB'])->default('PORT');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
