<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('project_pics')) {
            // Rename project_sk -> sk_project if present
            if (Schema::hasColumn('project_pics', 'project_sk')) {
                DB::statement('ALTER TABLE project_pics RENAME COLUMN project_sk TO sk_project');
            }

            // Rename user_sk -> sk_user if present
            if (Schema::hasColumn('project_pics', 'user_sk')) {
                DB::statement('ALTER TABLE project_pics RENAME COLUMN user_sk TO sk_user');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_pics')) {
            // Revert sk_project -> project_sk if present
            if (Schema::hasColumn('project_pics', 'sk_project')) {
                DB::statement('ALTER TABLE project_pics RENAME COLUMN sk_project TO project_sk');
            }

            // Revert sk_user -> user_sk if present
            if (Schema::hasColumn('project_pics', 'sk_user')) {
                DB::statement('ALTER TABLE project_pics RENAME COLUMN sk_user TO user_sk');
            }
        }
    }
};
