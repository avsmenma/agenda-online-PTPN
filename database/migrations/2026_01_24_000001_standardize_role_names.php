<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration to standardize role names across the application
 * 
 * Mapping:
 * - ibua/ibuA/IbuA/ibu a/Ibu A/ibutarapul/IbuTarapul → operator
 * - ibub/ibuB/IbuB/verifikasi/Verifikasi/teamverifikasi → team_verifikasi
 */
return new class extends Migration {
    /**
     * Old role names that should be replaced with 'operator'
     */
    private array $oldOperatorNames = [
        'ibua',
        'ibuA',
        'IbuA',
        'ibu a',
        'Ibu A',
        'ibutarapul',
        'IbuTarapul',
        'Ibu Tarapul',
        'ibu tarapul'
    ];

    /**
     * Old role names that should be replaced with 'team_verifikasi'
     */
    private array $oldTeamVerifikasiNames = [
        'ibub',
        'ibuB',
        'IbuB',
        'verifikasi',
        'Verifikasi',
        'teamverifikasi',
        'TeamVerifikasi',
        'Team Verifikasi'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks to allow updates across related tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // ========================================
            // 1. Update dokumens table - created_by
            // ========================================
            foreach ($this->oldOperatorNames as $oldName) {
                DB::table('dokumens')
                    ->where('created_by', $oldName)
                    ->update(['created_by' => 'operator']);
            }

            foreach ($this->oldTeamVerifikasiNames as $oldName) {
                DB::table('dokumens')
                    ->where('created_by', $oldName)
                    ->update(['created_by' => 'team_verifikasi']);
            }

            // ========================================
            // 2. Update dokumens table - current_handler
            // ========================================
            foreach ($this->oldOperatorNames as $oldName) {
                DB::table('dokumens')
                    ->where('current_handler', $oldName)
                    ->update(['current_handler' => 'operator']);
            }

            foreach ($this->oldTeamVerifikasiNames as $oldName) {
                DB::table('dokumens')
                    ->where('current_handler', $oldName)
                    ->update(['current_handler' => 'team_verifikasi']);
            }

            // ========================================
            // 3. Update dokumens table - target_department
            // ========================================
            foreach ($this->oldOperatorNames as $oldName) {
                DB::table('dokumens')
                    ->where('target_department', $oldName)
                    ->update(['target_department' => 'operator']);
            }

            foreach ($this->oldTeamVerifikasiNames as $oldName) {
                DB::table('dokumens')
                    ->where('target_department', $oldName)
                    ->update(['target_department' => 'team_verifikasi']);
            }

            // ========================================
            // 4. Update dokumens table - status values containing role names
            // ========================================
            DB::table('dokumens')
                ->where('status', 'sent_to_ibub')
                ->update(['status' => 'sent_to_team_verifikasi']);

            DB::table('dokumens')
                ->where('status', 'processed_by_ibub')
                ->update(['status' => 'processed_by_team_verifikasi']);

            DB::table('dokumens')
                ->where('status', 'returned_to_ibua')
                ->update(['status' => 'returned_to_operator']);

            DB::table('dokumens')
                ->where('status', 'returned_to_ibub')
                ->update(['status' => 'returned_to_team_verifikasi']);

            DB::table('dokumens')
                ->where('status', 'pending_approval_ibub')
                ->update(['status' => 'pending_approval_team_verifikasi']);

            // ========================================
            // 5. Update dokumen_role_data table - role_code
            // ========================================
            if (Schema::hasTable('dokumen_role_data')) {
                foreach ($this->oldOperatorNames as $oldName) {
                    DB::table('dokumen_role_data')
                        ->where('role_code', $oldName)
                        ->update(['role_code' => 'operator']);
                }

                foreach ($this->oldTeamVerifikasiNames as $oldName) {
                    DB::table('dokumen_role_data')
                        ->where('role_code', $oldName)
                        ->update(['role_code' => 'team_verifikasi']);
                }
            }

            // ========================================
            // 6. Update document_trackings table - changed_by (if column exists)
            // ========================================
            if (Schema::hasTable('document_trackings') && Schema::hasColumn('document_trackings', 'changed_by')) {
                foreach ($this->oldOperatorNames as $oldName) {
                    DB::table('document_trackings')
                        ->where('changed_by', $oldName)
                        ->update(['changed_by' => 'operator']);
                }

                foreach ($this->oldTeamVerifikasiNames as $oldName) {
                    DB::table('document_trackings')
                        ->where('changed_by', $oldName)
                        ->update(['changed_by' => 'team_verifikasi']);
                }
            }

            // ========================================
            // 7. Update role_deadline_configs table FIRST (before roles)
            // ========================================
            if (Schema::hasTable('role_deadline_configs')) {
                foreach ($this->oldOperatorNames as $oldName) {
                    DB::table('role_deadline_configs')
                        ->where('role_code', $oldName)
                        ->update(['role_code' => 'operator']);
                }

                foreach ($this->oldTeamVerifikasiNames as $oldName) {
                    DB::table('role_deadline_configs')
                        ->where('role_code', $oldName)
                        ->update(['role_code' => 'team_verifikasi']);
                }
            }

            // ========================================
            // 8. Update roles table - Handle duplicates carefully
            // ========================================
            if (Schema::hasTable('roles')) {
                // Check if new roles already exist
                $operatorExists = DB::table('roles')->where('code', 'operator')->exists();
                $teamVerifikasiExists = DB::table('roles')->where('code', 'team_verifikasi')->exists();

                // Delete old role entries if new role already exists
                if ($operatorExists) {
                    foreach ($this->oldOperatorNames as $oldName) {
                        DB::table('roles')->where('code', $oldName)->delete();
                    }
                } else {
                    // Update the first old role to new name, delete the rest
                    $updated = false;
                    foreach ($this->oldOperatorNames as $oldName) {
                        if (!$updated) {
                            $affected = DB::table('roles')
                                ->where('code', $oldName)
                                ->update([
                                    'code' => 'operator',
                                    'name' => 'Operator'
                                ]);
                            if ($affected > 0) {
                                $updated = true;
                            }
                        } else {
                            DB::table('roles')->where('code', $oldName)->delete();
                        }
                    }
                    // If no update happened, insert new role
                    if (!$updated) {
                        DB::table('roles')->insert([
                            'code' => 'operator',
                            'name' => 'Operator',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                if ($teamVerifikasiExists) {
                    foreach ($this->oldTeamVerifikasiNames as $oldName) {
                        DB::table('roles')->where('code', $oldName)->delete();
                    }
                } else {
                    // Update the first old role to new name, delete the rest
                    $updated = false;
                    foreach ($this->oldTeamVerifikasiNames as $oldName) {
                        if (!$updated) {
                            $affected = DB::table('roles')
                                ->where('code', $oldName)
                                ->update([
                                    'code' => 'team_verifikasi',
                                    'name' => 'Team Verifikasi'
                                ]);
                            if ($affected > 0) {
                                $updated = true;
                            }
                        } else {
                            DB::table('roles')->where('code', $oldName)->delete();
                        }
                    }
                    // If no update happened, insert new role
                    if (!$updated) {
                        DB::table('roles')->insert([
                            'code' => 'team_verifikasi',
                            'name' => 'Team Verifikasi',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // ========================================
            // 9. Update users table - role
            // ========================================
            foreach ($this->oldOperatorNames as $oldName) {
                DB::table('users')
                    ->where('role', $oldName)
                    ->update(['role' => 'operator']);
            }

            foreach ($this->oldTeamVerifikasiNames as $oldName) {
                DB::table('users')
                    ->where('role', $oldName)
                    ->update(['role' => 'team_verifikasi']);
            }

            // ========================================
            // 10. Update document_activities table (if column exists)
            // ========================================
            if (Schema::hasTable('document_activities') && Schema::hasColumn('document_activities', 'performed_by')) {
                foreach ($this->oldOperatorNames as $oldName) {
                    DB::table('document_activities')
                        ->where('performed_by', $oldName)
                        ->update(['performed_by' => 'operator']);
                }

                foreach ($this->oldTeamVerifikasiNames as $oldName) {
                    DB::table('document_activities')
                        ->where('performed_by', $oldName)
                        ->update(['performed_by' => 'team_verifikasi']);
                }
            }

        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Reverse: operator → ibutarapul (legacy default)
            DB::table('dokumens')
                ->where('created_by', 'operator')
                ->update(['created_by' => 'ibutarapul']);

            DB::table('dokumens')
                ->where('current_handler', 'operator')
                ->update(['current_handler' => 'ibutarapul']);

            // Reverse: team_verifikasi → verifikasi (legacy default)
            DB::table('dokumens')
                ->where('created_by', 'team_verifikasi')
                ->update(['created_by' => 'verifikasi']);

            DB::table('dokumens')
                ->where('current_handler', 'team_verifikasi')
                ->update(['current_handler' => 'verifikasi']);

            // Reverse status values
            DB::table('dokumens')
                ->where('status', 'sent_to_team_verifikasi')
                ->update(['status' => 'sent_to_ibub']);

            DB::table('dokumens')
                ->where('status', 'returned_to_operator')
                ->update(['status' => 'returned_to_ibua']);

            // Reverse users
            DB::table('users')
                ->where('role', 'operator')
                ->update(['role' => 'ibutarapul']);

            DB::table('users')
                ->where('role', 'team_verifikasi')
                ->update(['role' => 'verifikasi']);

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
