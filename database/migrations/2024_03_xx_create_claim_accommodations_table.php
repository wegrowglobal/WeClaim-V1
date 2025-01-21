<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('claim_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claim')->onDelete('cascade');
            $table->string('location');
            $table->decimal('price', 10, 2);
            $table->date('check_in');
            $table->date('check_out');
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('claim_accommodations');
    }
}; 