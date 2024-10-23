<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReviewerIdToClaimsTable extends Migration
{
    public function up()
    {
        Schema::table('claim', function (Blueprint $table) {
            // Add the reviewer_id column
            $table->unsignedBigInteger('reviewer_id')->nullable()->after('user_id');

            // Add foreign key constraint
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('claims', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['reviewer_id']);

            // Then drop the reviewer_id column
            $table->dropColumn('reviewer_id');
        });
    }
}
