<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    /////////////////////////////////////////////////////////////

    public function up(): void
    {
        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claim', 'id')->onDelete('cascade');
            $table->string('toll_file_name');
            $table->string('toll_file_path');
            $table->string('email_file_name');
            $table->string('email_file_path');
            $table->foreignId('uploaded_by')->constrained('users')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /////////////////////////////////////////////////////////////

    public function down(): void
    {
        Schema::dropIfExists('claim_documents');
    }
};
