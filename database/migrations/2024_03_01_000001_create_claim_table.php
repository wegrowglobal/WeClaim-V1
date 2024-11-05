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
            $table->foreignId('user_id')->constrained()->onUpdate('cascade');
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('petrol_amount', 10, 2);
            $table->enum('status', [
                'Submitted',
                'Approved_Admin',
                'Approved_Datuk',
                'Approved_HR',
                'Approved_Finance',
                'Done',
                'Rejected',
            ])->default('Submitted')->index();
            $table->string('claim_type', 10)->default('Others')->index();
            $table->decimal('total_distance', 10, 2);
            $table->timestamp('submitted_at')->nullable();
            $table->string('claim_company');
            $table->decimal('toll_amount', 10, 2)->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('token')->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraint for reviewer_id
            $table->foreign('reviewer_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim');
    }
}; 