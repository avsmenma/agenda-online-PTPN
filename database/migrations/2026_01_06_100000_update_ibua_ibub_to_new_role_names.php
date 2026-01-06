<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update dokumens table
        // Update created_by: ibua -> ibutarapul
        DB::table('dokumens')
            ->whereIn('created_by', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'Ibu Tarapul'])
            ->update(['created_by' => 'ibutarapul']);

        // Update current_handler: ibua -> ibutarapul
        DB::table('dokumens')
            ->whereIn('current_handler', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'Ibu Tarapul'])
            ->update(['current_handler' => 'ibutarapul']);

        // Update current_handler: ibub -> verifikasi
        DB::table('dokumens')
            ->whereIn('current_handler', ['ibub', 'ibuB', 'IbuB', 'ibu b', 'Ibu B', 'Ibu Yuni', 'ibu yuni', 'verifikasi'])
            ->update(['current_handler' => 'verifikasi']);

        // Update dokumen_statuses table
        // Update role_code: ibua -> ibutarapul
        DB::table('dokumen_statuses')
            ->whereIn('role_code', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'Ibu Tarapul'])
            ->update(['role_code' => 'ibutarapul']);

        // Update role_code: ibub -> verifikasi
        DB::table('dokumen_statuses')
            ->whereIn('role_code', ['ibub', 'ibuB', 'IbuB', 'ibu b', 'Ibu B', 'Ibu Yuni', 'ibu yuni', 'verifikasi'])
            ->update(['role_code' => 'verifikasi']);

        // Update changed_by: ibua -> ibutarapul
        DB::table('dokumen_statuses')
            ->whereIn('changed_by', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'Ibu Tarapul'])
            ->update(['changed_by' => 'ibutarapul']);

        // Update changed_by: ibub -> verifikasi
        DB::table('dokumen_statuses')
            ->whereIn('changed_by', ['ibub', 'ibuB', 'IbuB', 'ibu b', 'Ibu B', 'Ibu Yuni', 'ibu yuni', 'verifikasi'])
            ->update(['changed_by' => 'verifikasi']);

        // Update dokumen_role_data table
        // Update role_code: ibua -> ibutarapul
        DB::table('dokumen_role_data')
            ->whereIn('role_code', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'Ibu Tarapul'])
            ->update(['role_code' => 'ibutarapul']);

        // Update role_code: ibub -> verifikasi
        DB::table('dokumen_role_data')
            ->whereIn('role_code', ['ibub', 'ibuB', 'IbuB', 'ibu b', 'Ibu B', 'Ibu Yuni', 'ibu yuni', 'verifikasi'])
            ->update(['role_code' => 'verifikasi']);

        // Update users table
        // Update role: ibua -> ibutarapul
        DB::table('users')
            ->whereIn('role', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'Ibu Tarapul'])
            ->update(['role' => 'ibutarapul']);

        // Update role: ibub -> verifikasi
        DB::table('users')
            ->whereIn('role', ['ibub', 'ibuB', 'IbuB', 'ibu b', 'Ibu B', 'Ibu Yuni', 'ibu yuni', 'verifikasi'])
            ->update(['role' => 'verifikasi']);

        // Update target_department in dokumens table
        DB::table('dokumens')
            ->whereIn('target_department', ['ibua', 'ibuA', 'IbuA', 'ibu a', 'Ibu A', 'Ibu Tarapul'])
            ->update(['target_department' => 'ibutarapul']);

        DB::table('dokumens')
            ->whereIn('target_department', ['ibub', 'ibuB', 'IbuB', 'ibu b', 'Ibu B', 'Ibu Yuni', 'ibu yuni', 'verifikasi'])
            ->update(['target_department' => 'verifikasi']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: ibutarapul -> ibua
        DB::table('dokumens')
            ->where('created_by', 'ibutarapul')
            ->update(['created_by' => 'ibua']);

        DB::table('dokumens')
            ->where('current_handler', 'ibutarapul')
            ->update(['current_handler' => 'ibua']);

        DB::table('dokumens')
            ->where('current_handler', 'verifikasi')
            ->update(['current_handler' => 'ibub']);

        DB::table('dokumen_statuses')
            ->where('role_code', 'ibutarapul')
            ->update(['role_code' => 'ibua']);

        DB::table('dokumen_statuses')
            ->where('role_code', 'verifikasi')
            ->update(['role_code' => 'ibub']);

        DB::table('dokumen_statuses')
            ->where('changed_by', 'ibutarapul')
            ->update(['changed_by' => 'ibua']);

        DB::table('dokumen_statuses')
            ->where('changed_by', 'verifikasi')
            ->update(['changed_by' => 'ibub']);

        DB::table('dokumen_role_data')
            ->where('role_code', 'ibutarapul')
            ->update(['role_code' => 'ibua']);

        DB::table('dokumen_role_data')
            ->where('role_code', 'verifikasi')
            ->update(['role_code' => 'ibub']);

        DB::table('users')
            ->where('role', 'ibutarapul')
            ->update(['role' => 'ibua']);

        DB::table('users')
            ->where('role', 'verifikasi')
            ->update(['role' => 'ibub']);

        DB::table('dokumens')
            ->where('target_department', 'ibutarapul')
            ->update(['target_department' => 'ibua']);

        DB::table('dokumens')
            ->where('target_department', 'verifikasi')
            ->update(['target_department' => 'ibub']);
    }
};
