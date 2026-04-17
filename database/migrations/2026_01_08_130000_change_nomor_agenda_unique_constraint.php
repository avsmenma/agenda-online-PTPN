<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration changes the unique constraint on nomor_agenda from a 
     * global unique to a composite unique (nomor_agenda + created_by) so that
     * different bagian can have documents with the same nomor_agenda.
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Drop the old unique constraint on nomor_agenda
            // Need to handle both possible index names
            try {
                $table->dropUnique('dokumens_nomor_agenda_unique');
            } catch (\Exception $e) {
                // Try alternative naming convention
                try {
                    $table->dropUnique(['nomor_agenda']);
                } catch (\Exception $e2) {
                    // Index might not exist, continue
                    \Log::info('nomor_agenda unique index does not exist or already dropped');
                }
            }
        });

        // Add composite unique constraint (nomor_agenda + created_by)
        // This allows different bagian/roles to have the same nomor_agenda
        Schema::table('dokumens', function (Blueprint $table) {
            $table->unique(['nomor_agenda', 'created_by'], 'dokumens_nomor_agenda_created_by_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Drop composite unique
            $table->dropUnique('dokumens_nomor_agenda_created_by_unique');

            // Restore original unique constraint
            $table->unique('nomor_agenda', 'dokumens_nomor_agenda_unique');
        });
    }
};
