<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('peran', 30)
                ->default(UserRole::Admin->value)
                ->after('kata_sandi')
                ->index();
        });

        DB::table('pengguna')
            ->whereNull('peran')
            ->update(['peran' => UserRole::Admin->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropIndex(['peran']);
            $table->dropColumn('peran');
        });
    }
};
