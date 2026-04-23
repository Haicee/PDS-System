<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen pds_voluntary_work columns
        Schema::table('pds_voluntary_work', function (Blueprint $table) {
            $table->text('organization')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('from')->nullable()->change();
            $table->text('to')->nullable()->change();
            $table->text('hours')->nullable()->change();
            $table->text('position')->nullable()->change();
        });

        // Widen pds_training_programs columns
        Schema::table('pds_training_programs', function (Blueprint $table) {
            $table->text('title')->nullable()->change();
            $table->text('from')->nullable()->change();
            $table->text('to')->nullable()->change();
            $table->text('hours')->nullable()->change();
            $table->text('type_of_ld')->nullable()->change();
            $table->text('conducted_by')->nullable()->change();
        });

        // Widen pds_other_info columns
        Schema::table('pds_other_info', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Revert pds_voluntary_work columns
        Schema::table('pds_voluntary_work', function (Blueprint $table) {
            $table->string('organization')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('from')->nullable()->change();
            $table->string('to')->nullable()->change();
            $table->string('hours')->nullable()->change();
            $table->string('position')->nullable()->change();
        });

        // Revert pds_training_programs columns
        Schema::table('pds_training_programs', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('from')->nullable()->change();
            $table->string('to')->nullable()->change();
            $table->string('hours')->nullable()->change();
            $table->string('type_of_ld')->nullable()->change();
            $table->string('conducted_by')->nullable()->change();
        });

        // Revert pds_other_info columns
        Schema::table('pds_other_info', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
        });
    }
};
