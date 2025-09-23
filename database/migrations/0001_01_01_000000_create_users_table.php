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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('sk_user'); // Primary Key

            $table->integer('employee_nik')->unique(); // Nomor Induk Karyawan

            $table->string('employee_name', 100); // Nama karyawan

            $table->string('employee_email', 100)->unique(); // Email karyawan

            $table->timestamp('email_verified_at')->nullable(); // Waktu verifikasi email

            $table->string('password', 100); // Password terenkripsi

            $table->string('remember_token', 100)->nullable(); // Token remember me

            $table->enum('is_active', ['Active', 'Inactive'])->default('Active'); // Status user

            $table->enum('level', ['Manager', 'Asmen', 'SH', 'Staff', 'Intern'])->default('Staff'); // Level user

            $table->date('join_date')->nullable(); // Tanggal bergabung di adira

            $table->date('end_date')->nullable(); // Tanggal selesai di adira

            $table->string('create_by', 100)->nullable(); // dibuat oleh
            $table->datetime('create_date')->nullable(); // tanggal dibuat
            $table->string('modified_by', 100)->nullable(); // diubah oleh
            $table->datetime('modified_date')->nullable(); // tanggal diubah

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
