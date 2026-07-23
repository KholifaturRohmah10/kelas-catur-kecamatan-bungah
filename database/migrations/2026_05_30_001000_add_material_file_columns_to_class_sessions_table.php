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
        Schema::table('sesi_kelas', function (Blueprint $table): void {
            $table->string('path_file_materi')->nullable()->after('materi');
            $table->string('nama_file_materi')->nullable()->after('path_file_materi');
            $table->string('mime_file_materi', 120)->nullable()->after('nama_file_materi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_kelas', function (Blueprint $table): void {
            $table->dropColumn([
                'path_file_materi',
                'nama_file_materi',
                'mime_file_materi',
            ]);
        });
    }
};
