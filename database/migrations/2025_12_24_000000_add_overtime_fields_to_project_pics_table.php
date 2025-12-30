<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_pics', function (Blueprint $table) {
            $table->boolean('has_overtime')->default(false)->after('end_date');
            $table->date('overtime_start_date')->nullable()->after('has_overtime');
            $table->date('overtime_end_date')->nullable()->after('overtime_start_date');
            $table->integer('total_days')->default(0)->after('overtime_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('project_pics', function (Blueprint $table) {
            $table->dropColumn(['has_overtime', 'overtime_start_date', 'overtime_end_date', 'total_days']);
        });
    }
};
