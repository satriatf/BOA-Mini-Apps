<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_pics', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Match `projects.sk_project` type (uuid) to allow FK constraint on PostgreSQL
            $table->uuid('sk_project')->index();
            $table->string('user_sk');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();


            $table->foreign('sk_project')->references('sk_project')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_pics');
    }
};
