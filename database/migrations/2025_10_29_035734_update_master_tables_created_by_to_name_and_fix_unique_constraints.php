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
            $masterTables = [
            'master_applications',
            'master_project_statuses',
            'master_non_project_types',
        ];

            // Handle master tables (with 'name' column)
            foreach ($masterTables as $tableName) {
            // 1. Drop foreign key constraint on created_by
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });

            // 2. Change created_by from integer to string (employee name)
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('created_by', 100)->nullable()->change();
            });

                // 3. Drop old unique constraint on 'name' using DB statement
                DB::statement("ALTER TABLE {$tableName} DROP CONSTRAINT IF EXISTS {$tableName}_name_unique");

            // 4. Add new unique index that excludes soft deleted records
            // PostgreSQL: use partial index with WHERE deleted_at IS NULL
            DB::statement("CREATE UNIQUE INDEX {$tableName}_name_unique ON {$tableName} (name) WHERE deleted_at IS NULL");
        }

            // Handle holidays table separately (uses 'date' column)
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });

            Schema::table('holidays', function (Blueprint $table) {
                $table->string('created_by', 100)->nullable()->change();
            });

            // Drop and recreate unique constraint for 'date' column to allow same date if soft deleted
            DB::statement("ALTER TABLE holidays DROP CONSTRAINT IF EXISTS holidays_date_unique");
            DB::statement("CREATE UNIQUE INDEX holidays_date_unique ON holidays (date) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            $masterTables = [
            'master_applications',
            'master_project_statuses',
            'master_non_project_types',
        ];

            // Rollback master tables
            foreach ($masterTables as $tableName) {
            // 1. Drop partial unique index
            DB::statement("DROP INDEX IF EXISTS {$tableName}_name_unique");

            // 2. Add back regular unique constraint
            Schema::table($tableName, function (Blueprint $table) {
                $table->unique('name');
            });

            // 3. Change created_by back to integer
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
            });

            // 4. Add back foreign key constraint
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('created_by')
                    ->references('sk_user')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

            // Rollback holidays table
            DB::statement("DROP INDEX IF EXISTS holidays_date_unique");
        
            Schema::table('holidays', function (Blueprint $table) {
                $table->unique('date');
            });
        
            Schema::table('holidays', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
            });
        
            Schema::table('holidays', function (Blueprint $table) {
                $table->foreign('created_by')
                    ->references('sk_user')
                    ->on('users')
                    ->nullOnDelete();
            });
    }
};
