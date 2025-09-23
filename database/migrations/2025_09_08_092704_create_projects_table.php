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
            $table->uuid('sk_project')->primary(); // Primary key (uuid)

            $table->string('project_ticket_no', 100); // Nomor tiket project

            $table->string('project_name', 255); // Nama project

            $table->string('project_status', 50)->index(); // Status project

            $table->string('technical_lead', 100)->nullable(); // Lead teknis project

            $table->json('pics')->nullable(); // PIC project

            $table->date('start_date')->nullable(); // Tanggal mulai project

            $table->date('end_date')->nullable(); // Tanggal selesai project

            $table->unsignedInteger('total_day')->default(0); // Lama pengerjaan project

            $table->unsignedTinyInteger('percent_done')->default(0); // Progress project (%)

            $table->boolean('is_delete')->default(false); // Status data project

            $table->string('create_by', 100)->nullable(); // dibuat oleh
            $table->datetime('create_date')->nullable(); // tanggal dibuat
            $table->string('modified_by', 100)->nullable(); // diubah oleh
            $table->datetime('modified_date')->nullable(); // tanggal diubah

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
