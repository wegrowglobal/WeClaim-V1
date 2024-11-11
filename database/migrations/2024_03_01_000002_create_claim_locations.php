<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('claim_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claim')->onDelete('cascade');
            $table->string('from_location');
            $table->string('to_location');
            $table->decimal('distance', 10, 2)->nullable();
            $table->integer('order');
            $table->timestamps();
            $table->unique(['claim_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_locations');
    }
}; 