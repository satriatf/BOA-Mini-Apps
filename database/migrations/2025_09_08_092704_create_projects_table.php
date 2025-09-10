<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // PMO ID
            $table->string('pmo_id')->unique();

            // PROJECT NAME
            $table->string('project_name');

            // STATUS
            $table->string('status')->index();

            // TECH LEAD
            $table->string('tech_lead')->nullable();

            // PIC bisa sampai 2 orang
            $table->string('pic_1')->nullable();
            $table->string('pic_2')->nullable();

            // START & END
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // DAYS (selisih hari START–END, bisa auto dihitung di model)
            $table->unsignedInteger('days')->default(0);

            // % DONE
            $table->unsignedTinyInteger('percent_done')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
