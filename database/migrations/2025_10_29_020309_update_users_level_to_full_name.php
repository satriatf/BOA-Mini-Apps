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
        // Drop old constraint first
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_level_check');
        
        // Update existing data
        DB::table('users')->where('level', 'Asmen')->update(['level' => 'Asisten Manager']);
        DB::table('users')->where('level', 'SH')->update(['level' => 'Section Head']);
        
        // Add new constraint with full names
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_level_check CHECK (level::text = ANY (ARRAY['Manager'::character varying, 'Asisten Manager'::character varying, 'Section Head'::character varying, 'Staff'::character varying, 'Intern'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data back to abbreviations
        DB::table('users')->where('level', 'Asisten Manager')->update(['level' => 'Asmen']);
        DB::table('users')->where('level', 'Section Head')->update(['level' => 'SH']);
        
        // Drop new constraint
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_level_check');
        
        // Add old constraint back
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_level_check CHECK (level::text = ANY (ARRAY['Manager'::character varying, 'Asmen'::character varying, 'SH'::character varying, 'Staff'::character varying, 'Intern'::character varying]::text[]))");
    }
};
