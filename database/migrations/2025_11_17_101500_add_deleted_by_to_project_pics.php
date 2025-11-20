<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_pics', function (Blueprint $table) {
            if (! Schema::hasColumn('project_pics', 'deleted_by')) {
                $table->string('deleted_by')->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('project_pics', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_pics', function (Blueprint $table) {
            if (Schema::hasColumn('project_pics', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }

            if (Schema::hasColumn('project_pics', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};