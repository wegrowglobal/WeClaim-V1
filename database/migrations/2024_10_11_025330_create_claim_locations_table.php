<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    /////////////////////////////////////////////////////////////

    public function up()
    {
        Schema::create('claim_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_id');
            $table->foreign('claim_id')->references('id')->on('claim')->onDelete('cascade')->onUpdate('cascade');
            $table->string('location');
            $table->integer('order');
            $table->timestamps();
            $table->unique(['claim_id', 'order']);
        });
    }

    /////////////////////////////////////////////////////////////

    public function down(): void
    {
        Schema::dropIfExists('claim_locations');
    }

    /////////////////////////////////////////////////////////////

};
