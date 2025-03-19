<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    /////////////////////////////////////////////////////////////

    public function up(): void
    {
        Schema::create('claim_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claim', 'id')->onDelete('cascade');
            $table->foreignId('reviewer_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->integer('review_order');
            $table->string('department');
            $table->string('status');
            $table->timestamp('reviewed_at');
            $table->json('rejection_details')->nullable();
            $table->boolean('requires_basic_info')->default(false);
            $table->boolean('requires_trip_details')->default(false);
            $table->boolean('requires_accommodation_details')->default(false);
            $table->boolean('requires_documents')->default(false);
            $table->timestamps();
        });
    }

    /////////////////////////////////////////////////////////////

    public function down(): void
    {
        Schema::dropIfExists('claim_reviews');
    }
};
