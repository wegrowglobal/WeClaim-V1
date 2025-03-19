<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
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
                'Approved Datuk', 
                'Approved HR',
                'Approved Finance',
                'Rejected',
                'Done'
            ])->default('Submitted');
            $table->string('claim_type')->default('Petrol');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim');
    }
};