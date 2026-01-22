<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Change unique constraint from just no_tiket to composite (no_tiket, tanggal)
     * This allows same ticket number with different dates
     */
    public function up(): void
    {
        // Drop the existing partial unique index on no_tiket only
        DB::statement('DROP INDEX IF EXISTS mtcs_no_tiket_unique');

        // Create a new composite unique index on (no_tiket, tanggal)
        // This allows same no_tiket with different dates, but not same no_tiket with same date
        // Still excluding '0' (for operational issues) and soft-deleted records
        DB::statement('CREATE UNIQUE INDEX mtcs_no_tiket_tanggal_unique ON mtcs (no_tiket, tanggal) WHERE no_tiket != \'0\' AND deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the composite unique index
        DB::statement('DROP INDEX IF EXISTS mtcs_no_tiket_tanggal_unique');

        // Restore the previous unique index on no_tiket only
        DB::statement('CREATE UNIQUE INDEX mtcs_no_tiket_unique ON mtcs (no_tiket) WHERE no_tiket != \'0\' AND deleted_at IS NULL');
    }
};
