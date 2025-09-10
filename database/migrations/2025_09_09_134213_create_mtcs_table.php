<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mtcs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('resolver_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title')->unique();
            $table->text('deskripsi');
            $table->string('type', 32)->index(); // MTC/PRJ/INCIDENT/SERVICE REQUEST/SUPPORT UAT
            $table->text('solusi')->nullable();
            $table->string('application')->index();
            $table->date('tanggal');

            $table->unsignedSmallInteger('attachments_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('mtcs'); }
};
