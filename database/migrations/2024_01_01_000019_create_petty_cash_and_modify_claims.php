<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new columns to the existing claims table for Petty Cash specific data
        Schema::table('claims', function (Blueprint $table) {
            $table->string('park_location')->nullable()->after('claim_type')->comment('Specific park location for Petty Cash claims');
            // Removed purpose from here
            $table->string('advised_by')->nullable()->after('park_location')->comment('Person who advised the Petty Cash claim');
            $table->decimal('total_amount', 10, 2)->default(0)->after('total_distance')->comment('Total amount for the claim, calculated differently for Petty Cash');
        });

        // Create a new table for Petty Cash line items
        Schema::create('petty_cash_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('quantity', 8, 2); // Using decimal for quantity flexibility (e.g., 1.5 units)
            $table->string('supplier')->nullable();
            $table->decimal('price_per_unit', 10, 2);
            $table->text('purpose')->nullable()->comment('Purpose of this specific petty cash item'); // Added purpose here
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_items');

        Schema::table('claims', function (Blueprint $table) {
            // Drop in reverse order of creation
            $table->dropColumn('total_amount');
            $table->dropColumn('advised_by');
            // Removed purpose drop from here
            $table->dropColumn('park_location');
        });
    }
}; 