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
            ALTER COLUMN duration TYPE TEXT,
            ALTER COLUMN position_title TYPE TEXT,
            ALTER COLUMN office_unit TYPE TEXT,
            ALTER COLUMN immediate_supervisor TYPE TEXT,
            ALTER COLUMN agency_location TYPE TEXT,
            ALTER COLUMN signature_path TYPE TEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to varchar(255) for the original string columns
        DB::statement('ALTER TABLE pds_form5_remarks
            ALTER COLUMN duration TYPE VARCHAR(255),
            ALTER COLUMN position_title TYPE VARCHAR(255),
            ALTER COLUMN office_unit TYPE VARCHAR(255),
            ALTER COLUMN immediate_supervisor TYPE VARCHAR(255),
            ALTER COLUMN agency_location TYPE VARCHAR(255),
            ALTER COLUMN signature_path TYPE VARCHAR(255)');
    }
};
