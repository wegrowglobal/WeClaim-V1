<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claim')->onDelete('cascade');
            $table->text('from_location');
            $table->text('to_location');
            $table->decimal('distance', 10, 2);
            $table->integer('order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_locations');
    }
}; 