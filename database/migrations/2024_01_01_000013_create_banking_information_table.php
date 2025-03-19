<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banking_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banking_information');
    }
}; 