<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('pending_reviewer_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Next user who needs to review this claim');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('claim_company');
            $table->decimal('petrol_amount', 10, 2)->default(0);
            $table->decimal('toll_amount', 10, 2)->default(0);
            $table->decimal('total_distance', 10, 2)->default(0);
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('status', [
                'Submitted',
                'Approved Admin',
                'Approved Manager',
                'Approved HR',
                'Pending Datuk',
                'Approved Datuk', 
                'Approved Finance',
                'Rejected',
                'Done',
                'Cancelled'
            ])->default('Submitted');
            $table->string('approval_token')->nullable();
            $table->timestamp('approval_token_expires_at')->nullable();
            $table->string('claim_type')->default('Petrol');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
}; 