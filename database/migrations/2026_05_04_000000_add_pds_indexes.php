<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes to all pds tables for user_id lookups
        Schema::table('pds_family_members', function (Blueprint $table) {
            $table->index('user_id', 'pds_family_members_user_id_idx');
            $table->index(['user_id', 'type'], 'pds_family_members_user_type_idx');
        });

        Schema::table('pds_education_records', function (Blueprint $table) {
            $table->index('user_id', 'pds_education_user_id_idx');
            $table->index(['user_id', 'level'], 'pds_education_user_level_idx');
        });

        Schema::table('pds_training_programs', function (Blueprint $table) {
            $table->index('user_id', 'pds_training_user_id_idx');
            $table->index(['user_id', 'source'], 'pds_training_user_source_idx');
            $table->index(['user_id', 'id'], 'pds_training_user_id_order_idx');
        });

        Schema::table('pds_work_experiences', function (Blueprint $table) {
            $table->index('user_id', 'pds_work_user_id_idx');
            $table->index(['user_id', 'from'], 'pds_work_user_from_idx');
        });

        Schema::table('pds_voluntary_work', function (Blueprint $table) {
            $table->index('user_id', 'pds_voluntary_user_id_idx');
            $table->index(['user_id', 'from'], 'pds_voluntary_user_from_idx');
        });

        Schema::table('pds_eligibilities', function (Blueprint $table) {
            $table->index('user_id', 'pds_eligibility_user_id_idx');
        });

        Schema::table('pds_other_info', function (Blueprint $table) {
            $table->index('user_id', 'pds_other_info_user_id_idx');
            $table->index(['user_id', 'category'], 'pds_other_info_user_cat_idx');
        });

        Schema::table('pds_references', function (Blueprint $table) {
            $table->index('user_id', 'pds_references_user_id_idx');
        });

        Schema::table('pds_form5_remarks', function (Blueprint $table) {
            $table->index('user_id', 'pds_form5_remarks_user_id_idx');
            $table->index(['user_id', 'id'], 'pds_form5_remarks_user_id_order_idx');
        });

        Schema::table('pds_drafts', function (Blueprint $table) {
            $table->index('user_id', 'pds_drafts_user_id_idx');
        });

        Schema::table('pds_submissions', function (Blueprint $table) {
            $table->index('user_id', 'pds_submissions_user_id_idx');
            $table->index(['status', 'submitted'], 'pds_submissions_status_submitted_idx');
        });

        Schema::table('pds_rejections', function (Blueprint $table) {
            $table->index('user_id', 'pds_rejections_user_id_idx');
        });

        Schema::table('pds_signature_files', function (Blueprint $table) {
            $table->index('user_id', 'pds_signature_files_user_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pds_family_members', function (Blueprint $table) {
            $table->dropIndex('pds_family_members_user_id_idx');
            $table->dropIndex('pds_family_members_user_type_idx');
        });

        Schema::table('pds_education_records', function (Blueprint $table) {
            $table->dropIndex('pds_education_user_id_idx');
            $table->dropIndex('pds_education_user_level_idx');
        });

        Schema::table('pds_training_programs', function (Blueprint $table) {
            $table->dropIndex('pds_training_user_id_idx');
            $table->dropIndex('pds_training_user_source_idx');
            $table->dropIndex('pds_training_user_id_order_idx');
        });

        Schema::table('pds_work_experiences', function (Blueprint $table) {
            $table->dropIndex('pds_work_user_id_idx');
            $table->dropIndex('pds_work_user_from_idx');
        });

        Schema::table('pds_voluntary_work', function (Blueprint $table) {
            $table->dropIndex('pds_voluntary_user_id_idx');
            $table->dropIndex('pds_voluntary_user_from_idx');
        });

        Schema::table('pds_eligibilities', function (Blueprint $table) {
            $table->dropIndex('pds_eligibility_user_id_idx');
        });

        Schema::table('pds_other_info', function (Blueprint $table) {
            $table->dropIndex('pds_other_info_user_id_idx');
            $table->dropIndex('pds_other_info_user_cat_idx');
        });

        Schema::table('pds_references', function (Blueprint $table) {
            $table->dropIndex('pds_references_user_id_idx');
        });

        Schema::table('pds_form5_remarks', function (Blueprint $table) {
            $table->dropIndex('pds_form5_remarks_user_id_idx');
            $table->dropIndex('pds_form5_remarks_user_id_order_idx');
        });

        Schema::table('pds_drafts', function (Blueprint $table) {
            $table->dropIndex('pds_drafts_user_id_idx');
        });

        Schema::table('pds_submissions', function (Blueprint $table) {
            $table->dropIndex('pds_submissions_user_id_idx');
            $table->dropIndex('pds_submissions_status_submitted_idx');
        });

        Schema::table('pds_rejections', function (Blueprint $table) {
            $table->dropIndex('pds_rejections_user_id_idx');
        });

        Schema::table('pds_signature_files', function (Blueprint $table) {
            $table->dropIndex('pds_signature_files_user_id_idx');
        });
    }
};
