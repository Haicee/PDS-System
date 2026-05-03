<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN type DROP NOT NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('type', [
                    'Permanent Employee',
                    'Contract of Service',
                    'Job Order',
                ])->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN type SET NOT NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN type SET DEFAULT 'Permanent Employee'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('type', [
                    'Permanent Employee',
                    'Contract of Service',
                    'Job Order',
                ])->default('Permanent Employee')->change();
            });
        }
    }
};
