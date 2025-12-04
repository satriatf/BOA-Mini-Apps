<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('on_leaves', function (Blueprint $table) {
            $table->id();

            // users table uses `sk_user` as primary key (not `id`), reference that
            $table->unsignedBigInteger('user_id');

            // store the leave type name (not id) so the DB shows readable names
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('created_at')->useCurrent();
            // no updated_at column as requested
            $table->softDeletes();

            $table->foreign('user_id')->references('sk_user')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('on_leaves');
    }
};
