<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `claim` MODIFY COLUMN `status` ENUM('Submitted', 'Approved Admin', 'Approved Datuk', 'Approved HR', 'Approved Finance', 'Rejected', 'Done', 'Cancelled') DEFAULT 'Submitted'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `claim` MODIFY COLUMN `status` ENUM('Submitted', 'Approved Admin', 'Approved Datuk', 'Approved HR', 'Approved Finance', 'Rejected', 'Done') DEFAULT 'Submitted'");
    }
};