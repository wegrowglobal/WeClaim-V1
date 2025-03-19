<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /////////////////////////////////////////////////////////////

    public function up(): void
    {
        Schema::create('claim_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claim', 'id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamps();
        });
        
    }

    /////////////////////////////////////////////////////////////

    public function down(): void
    {
        Schema::dropIfExists('claim_history');
    }

    /////////////////////////////////////////////////////////////

};
