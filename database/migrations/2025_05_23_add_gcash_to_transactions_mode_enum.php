<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to use raw SQL to modify enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN mode ENUM('cod','card','paypal','gcash')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove gcash from enum (be careful - this will fail if any records have 'gcash' value)
        DB::statement("ALTER TABLE transactions MODIFY COLUMN mode ENUM('cod','card','paypal')");
    }
};
