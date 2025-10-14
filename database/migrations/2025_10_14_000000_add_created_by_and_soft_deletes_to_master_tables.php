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
        $tables = [
            'master_applications',
            'master_project_statuses',
            'master_non_project_types',
            'holidays',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'created_by')) {
                    $table->unsignedBigInteger('created_by')
                        ->nullable()
                        ->after('id');

                    // Reference custom PK on users table: sk_user
                    $table->foreign('created_by')
                        ->references('sk_user')
                        ->on('users')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'master_applications',
            'master_project_statuses',
            'master_non_project_types',
            'holidays',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropForeign([$tableName . '_created_by_foreign']);
                    // Fallback by column name for portability
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropForeign(['created_by']);
                    }
                    $table->dropColumn('created_by');
                }

                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};


