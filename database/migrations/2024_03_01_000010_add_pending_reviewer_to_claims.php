<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim', function (Blueprint $table) {
            $table->foreignId('pending_reviewer_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Next user who needs to review this claim');
        });
    }

    public function down(): void
    {
        Schema::table('claim', function (Blueprint $table) {
            $table->dropForeign(['pending_reviewer_id']);
            $table->dropColumn('pending_reviewer_id');
        });
    }
}; 