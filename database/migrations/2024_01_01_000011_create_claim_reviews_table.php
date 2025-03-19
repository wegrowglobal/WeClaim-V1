<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->string('reviewer_type')->comment('HR, Manager, Finance, Datuk, etc.');
            $table->text('comments')->nullable();
            $table->enum('status', [
                'Approved',
                'Rejected',
                'Pending'
            ])->default('Pending');
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_reviews');
    }
};
