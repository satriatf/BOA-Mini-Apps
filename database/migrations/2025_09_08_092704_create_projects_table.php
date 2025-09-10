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

            $table->string('pmo_id')->unique();

            $table->string('project_name');

            $table->string('status')->index();

            $table->unsignedBigInteger('tech_lead')->nullable();

            $table->json('pics')->nullable(); 

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedInteger('days')->default(0);

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
