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
        DB::statement('ALTER TABLE pds_form5_remarks
            ALTER COLUMN duration TYPE text,
            ALTER COLUMN position_title TYPE text,
            ALTER COLUMN office_unit TYPE text,
            ALTER COLUMN immediate_supervisor TYPE text,
            ALTER COLUMN agency_location TYPE text,
            ALTER COLUMN signature_path TYPE text');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to varchar(255) for the original string columns
        DB::statement('ALTER TABLE pds_form5_remarks
            ALTER COLUMN duration TYPE varchar(255),
            ALTER COLUMN position_title TYPE varchar(255),
            ALTER COLUMN office_unit TYPE varchar(255),
            ALTER COLUMN immediate_supervisor TYPE varchar(255),
            ALTER COLUMN agency_location TYPE varchar(255),
            ALTER COLUMN signature_path TYPE varchar(255)');
    }
};
