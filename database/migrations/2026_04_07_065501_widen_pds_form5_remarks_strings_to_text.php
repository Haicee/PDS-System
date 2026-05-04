<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Widen all string columns on pds_form5_remarks that can hold long text
        DB::statement('ALTER TABLE `pds_form5_remarks`
            MODIFY `duration` TEXT NULL,
            MODIFY `position_title` TEXT NULL,
            MODIFY `office_unit` TEXT NULL,
            MODIFY `immediate_supervisor` TEXT NULL,
            MODIFY `agency_location` TEXT NULL,
            MODIFY `signature_path` TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to varchar(255) for the original string columns
        DB::statement('ALTER TABLE `pds_form5_remarks`
            MODIFY `duration` VARCHAR(255) NULL,
            MODIFY `position_title` VARCHAR(255) NULL,
            MODIFY `office_unit` VARCHAR(255) NULL,
            MODIFY `immediate_supervisor` VARCHAR(255) NULL,
            MODIFY `agency_location` VARCHAR(255) NULL,
            MODIFY `signature_path` VARCHAR(255) NULL');
    }
};
