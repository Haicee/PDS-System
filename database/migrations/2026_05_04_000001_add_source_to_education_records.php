<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pds_education_records', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->default('main')->after('user_id');
            $table->index(['user_id', 'source'], 'pds_education_user_source_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pds_education_records', function (Blueprint $table) {
            $table->dropIndex('pds_education_user_source_idx');
            $table->dropColumn('source');
        });
    }
};
