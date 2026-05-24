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
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('student_code')->unique();
            $table->string('name');
            $table->string('gender', 20);
            $table->date('birth_date')->nullable();
            $table->string('school_name')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->date('registration_date');
            $table->string('status', 30)->default('Calon Siswa');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
