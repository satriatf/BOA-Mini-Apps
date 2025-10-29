<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Add deleted_at to projects
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        // Add deleted_at to mtcs
        Schema::table('mtcs', function (Blueprint $table) {
            if (!Schema::hasColumn('mtcs', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        // Drop old unique constraint on project_name
        DB::statement("ALTER TABLE projects DROP CONSTRAINT IF EXISTS projects_project_name_unique");
        // Add partial unique index for project_name (only active)
        DB::statement("CREATE UNIQUE INDEX projects_project_name_unique ON projects (project_name) WHERE deleted_at IS NULL");
        // Drop old unique constraint on no_tiket
        DB::statement("ALTER TABLE mtcs DROP CONSTRAINT IF EXISTS mtcs_no_tiket_unique");
        // Add partial unique index for no_tiket (only active)
        DB::statement("CREATE UNIQUE INDEX mtcs_no_tiket_unique ON mtcs (no_tiket) WHERE deleted_at IS NULL");
    }
    public function down(): void {
        // Remove partial unique indexes
        DB::statement("DROP INDEX IF EXISTS projects_project_name_unique");
        DB::statement("DROP INDEX IF EXISTS mtcs_no_tiket_unique");
        // Add back regular unique constraints
        Schema::table('projects', function (Blueprint $table) {
            $table->unique('project_name');
        });
        Schema::table('mtcs', function (Blueprint $table) {
            $table->unique('no_tiket');
        });
        // Remove deleted_at columns
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('mtcs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
