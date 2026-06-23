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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');         // Nama asli file yang diunggah
            $table->string('file_path');         // Path lokasi penyimpanan file di storage
            $table->string('file_type');         // Ekstensi/Mime-type file (jpg, pdf, mp4, dll)
            $table->bigInteger('file_size');     // Ukuran file dalam bytes
            $table->string('category')->nullable(); // Kategori aset
            $table->string('tags')->nullable();  // Tagging (bisa disimpan dalam format string dipisah koma atau JSON)
            $table->integer('version')->default(1); // Pelacakan versi dokumen/aset
            $table->string('share_token')->unique()->nullable(); // Token acak untuk link download klien
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
        Schema::dropIfExists('assets');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
