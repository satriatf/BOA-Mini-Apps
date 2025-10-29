<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Add deleted_at to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        // Drop old unique constraint on employee_nik and employee_email
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_employee_nik_unique");
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_employee_email_unique");
        // Add partial unique index for employee_nik and employee_email (only active)
        DB::statement("CREATE UNIQUE INDEX users_employee_nik_unique ON users (employee_nik) WHERE deleted_at IS NULL");
        DB::statement("CREATE UNIQUE INDEX users_employee_email_unique ON users (employee_email) WHERE deleted_at IS NULL");
    }
    public function down(): void {
        // Remove partial unique indexes
        DB::statement("DROP INDEX IF EXISTS users_employee_nik_unique");
        DB::statement("DROP INDEX IF EXISTS users_employee_email_unique");
        // Add back regular unique constraints
        Schema::table('users', function (Blueprint $table) {
            $table->unique('employee_nik');
            $table->unique('employee_email');
        });
        // Remove deleted_at column
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
