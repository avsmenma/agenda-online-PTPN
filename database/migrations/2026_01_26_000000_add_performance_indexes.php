<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Add performance indexes to critical tables
     * Expected impact: 10-50x faster queries
     */
    public function up(): void
    {
        // ============================================
        // DOKUMENS TABLE - Most critical indexes
        // ============================================
        Schema::table('dokumens', function (Blueprint $table) {
            // Most critical - filtering by status and handler (used in all dashboards)
            // Example query: WHERE status = 'X' AND current_handler = 'Y'
            if (!$this->indexExists('dokumens', 'idx_status_handler')) {
                $table->index(['status', 'current_handler'], 'idx_status_handler');
            }

            // Year/month filtering (used in rekapan)
            // Example query: WHERE tahun = '2026' ORDER BY created_at
            if (!$this->indexExists('dokumens', 'idx_tahun_created')) {
                $table->index(['tahun', 'created_at'], 'idx_tahun_created');
            }

            // Search by nomor agenda (most common search)
            if (!$this->indexExists('dokumens', 'idx_nomor_agenda')) {
                $table->index('nomor_agenda', 'idx_nomor_agenda');
            }

            // Search by nomor SPP
            if (!$this->indexExists('dokumens', 'idx_nomor_spp')) {
                $table->index('nomor_spp', 'idx_nomor_spp');
            }

            // CSV import filtering (pembayaran module)
            if (Schema::hasColumn('dokumens', 'imported_from_csv')) {
                if (!$this->indexExists('dokumens', 'idx_csv_import')) {
                    $table->index('imported_from_csv', 'idx_csv_import');
                }
            }

            // Bagian filtering
            if (!$this->indexExists('dokumens', 'idx_bagian')) {
                $table->index('bagian', 'idx_bagian');
            }

            // Payment status filtering
            if (Schema::hasColumn('dokumens', 'status_pembayaran')) {
                if (!$this->indexExists('dokumens', 'idx_status_pembayaran')) {
                    $table->index('status_pembayaran', 'idx_status_pembayaran');
                }
            }
        });

        // ============================================
        // DOKUMEN_ROLE_DATA TABLE - Role-based queries
        // ============================================
        Schema::table('dokumen_role_data', function (Blueprint $table) {
            // Most common query: Get documents by role and received date
            // Example: WHERE role_code = 'perpajakan' AND received_at IS NOT NULL
            if (!$this->indexExists('dokumen_role_data', 'idx_role_received')) {
                $table->index(['role_code', 'received_at'], 'idx_role_received');
            }

            // Query for processed documents
            // Example: WHERE role_code = 'akutansi' AND processed_at IS NOT NULL
            if (!$this->indexExists('dokumen_role_data', 'idx_role_processed')) {
                $table->index(['role_code', 'processed_at'], 'idx_role_processed');
            }

            // Foreign key lookup (JOIN operations)
            if (!$this->indexExists('dokumen_role_data', 'idx_dokumen_id')) {
                $table->index('dokumen_id', 'idx_dokumen_id');
            }
        });

        // ============================================
        // DOKUMEN_STATUSES TABLE - Status tracking
        // ============================================
        if (Schema::hasTable('dokumen_statuses')) {
            Schema::table('dokumen_statuses', function (Blueprint $table) {
                // Composite index for dokumen + role lookup
                if (!$this->indexExists('dokumen_statuses', 'idx_dokumen_role')) {
                    $table->index(['dokumen_id', 'role_code'], 'idx_dokumen_role');
                }

                // Status filtering
                if (!$this->indexExists('dokumen_statuses', 'idx_status')) {
                    $table->index('status', 'idx_status');
                }
            });
        }

        // ============================================
        // USERS TABLE - Login and role queries
        // ============================================
        Schema::table('users', function (Blueprint $table) {
            // Login query optimization
            if (!$this->indexExists('users', 'idx_username')) {
                $table->index('username', 'idx_username');
            }

            // Role-based queries
            if (!$this->indexExists('users', 'idx_role')) {
                $table->index('role', 'idx_role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from dokumens
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropIndex('idx_status_handler');
            $table->dropIndex('idx_tahun_created');
            $table->dropIndex('idx_nomor_agenda');
            $table->dropIndex('idx_nomor_spp');
            $table->dropIndex('idx_bagian');

            if ($this->indexExists('dokumens', 'idx_csv_import')) {
                $table->dropIndex('idx_csv_import');
            }
            if ($this->indexExists('dokumens', 'idx_status_pembayaran')) {
                $table->dropIndex('idx_status_pembayaran');
            }
        });

        // Drop indexes from dokumen_role_data
        Schema::table('dokumen_role_data', function (Blueprint $table) {
            $table->dropIndex('idx_role_received');
            $table->dropIndex('idx_role_processed');
            $table->dropIndex('idx_dokumen_id');
        });

        // Drop indexes from dokumen_statuses
        if (Schema::hasTable('dokumen_statuses')) {
            Schema::table('dokumen_statuses', function (Blueprint $table) {
                $table->dropIndex('idx_dokumen_role');
                $table->dropIndex('idx_status');
            });
        }

        // Drop indexes from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_username');
            $table->dropIndex('idx_role');
        });
    }

    /**
     * Check if index exists on table using raw SQL
     * Compatible with Laravel 11+ (no Doctrine DBAL)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        $result = $connection->select(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$databaseName, $table, $indexName]
        );

        return $result[0]->count > 0;
    }
};
