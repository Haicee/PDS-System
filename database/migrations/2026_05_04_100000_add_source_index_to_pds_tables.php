<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // (user_id, source) on pds_training_programs already exists via add_pds_indexes migration

        Schema::table('pds_education_records', function (Blueprint $table) {
            $table->index(['user_id', 'source'], 'idx_education_user_source');
        });
    }

    public function down(): void
    {
        Schema::table('pds_education_records', function (Blueprint $table) {
            $table->dropIndex('idx_education_user_source');
        });
    }
};
