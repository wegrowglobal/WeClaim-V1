<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim', function (Blueprint $table) {
            $table->string('approval_token')->nullable()->after('status');
            $table->timestamp('approval_token_expires_at')->nullable()->after('approval_token');
        });
    }

    public function down(): void
    {
        Schema::table('claim', function (Blueprint $table) {
            $table->dropColumn(['approval_token', 'approval_token_expires_at']);
        });
    }
}; 