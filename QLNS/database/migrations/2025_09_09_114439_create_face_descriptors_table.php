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
        Schema::create('face_descriptors', function (Blueprint $table) {
            $table->id();
            // Liên kết với user, vì việc xác thực khuôn mặt gắn với tài khoản đăng nhập
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('descriptor'); // Lưu vector khuôn mặt dưới dạng JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_descriptors');
    }
};
