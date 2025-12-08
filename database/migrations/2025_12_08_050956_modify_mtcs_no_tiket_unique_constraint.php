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
        // Drop the existing unique index
        DB::statement('DROP INDEX IF EXISTS mtcs_no_tiket_unique');

        // Create a partial unique index that excludes '0'
        // This allows multiple records with no_tiket = '0' for OPERATIONAL ISSUE
        DB::statement('CREATE UNIQUE INDEX mtcs_no_tiket_unique ON mtcs (no_tiket) WHERE no_tiket != \'0\' AND deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the partial unique index
        DB::statement('DROP INDEX IF EXISTS mtcs_no_tiket_unique');

        // Restore the original unique index
        DB::statement('CREATE UNIQUE INDEX mtcs_no_tiket_unique ON mtcs (no_tiket) WHERE deleted_at IS NULL');
    }
};
