<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->string('type')->default('text'); // text, number, boolean, json
            $table->string('description')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_configs');
    }
};
