<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration adds an index on the created_by column for better
     * query performance when filtering documents by bagian creator.
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Add index on created_by for faster filtering by bagian
            if (!Schema::hasIndex('dokumens', 'idx_dokumens_created_by')) {
                $table->index('created_by', 'idx_dokumens_created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropIndex('idx_dokumens_created_by');
        });
    }
};
