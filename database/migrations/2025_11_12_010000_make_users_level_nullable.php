<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeUsersLevelNullable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL it's safest to use a raw statement to drop NOT NULL
        DB::statement('ALTER TABLE users ALTER COLUMN level DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add NOT NULL constraint. This will fail if nulls exist.
        DB::statement('ALTER TABLE users ALTER COLUMN level SET NOT NULL');
    }
}
