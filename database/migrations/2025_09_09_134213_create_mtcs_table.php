<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mtcs', function (Blueprint $table) {
            $table->bigIncrements('sk_mtc'); // Primary Key

            $table->foreignId('created_by_id')->constrained('users', 'sk_user')->cascadeOnDelete();
            $table->foreignId('resolver_id')->nullable()->constrained('users', 'sk_user')->nullOnDelete();

            $table->string('no_tiket')->unique();

            $table->text('deskripsi');
            $table->string('type', 32)->index(); 
            $table->text('solusi')->nullable();
            $table->string('application')->index();
            $table->date('tanggal');

            $table->json('attachments')->nullable();

            $table->unsignedSmallInteger('attachments_count')->default(0);

            $table->boolean('is_delete')->default(false); // Status data mtc

            $table->string('create_by', 100)->nullable(); // dibuat oleh
            $table->datetime('create_date')->nullable(); // tanggal dibuat
            $table->string('modified_by', 100)->nullable(); // diubah oleh
            $table->datetime('modified_date')->nullable(); // tanggal diubah

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('mtcs');
    }
};
