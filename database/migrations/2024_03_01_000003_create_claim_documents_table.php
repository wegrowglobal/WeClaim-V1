<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, drop the table if it exists
        Schema::dropIfExists('claim_documents');

        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claim')->onDelete('cascade');
            $table->string('toll_file_name')->nullable();
            $table->string('toll_file_path')->nullable();
            $table->string('email_file_name')->nullable();
            $table->string('email_file_path')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_documents');
    }
}; 