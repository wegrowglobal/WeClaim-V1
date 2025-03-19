<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('department');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('token', 64)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('registration_requests');
    }
}; 