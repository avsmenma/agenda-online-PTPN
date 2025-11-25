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
        Schema::table('dokumens', function (Blueprint $table) {
            // Add workflow fields
            $table->string('created_by')->default('ibuA')->after('id');
            $table->string('current_handler')->default('ibuA')->after('created_by');
            $table->timestamp('sent_to_ibub_at')->nullable()->after('current_handler');
            $table->timestamp('processed_at')->nullable()->after('sent_to_ibub_at');
            $table->timestamp('returned_to_ibua_at')->nullable()->after('processed_at');
        });
        
        // Modify status enum - use DB::statement for MySQL enum modification
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM('draft', 'sedang diproses', 'sent_to_ibub', 'approved_ibub', 'rejected_ibub', 'returned_to_ibua', 'selesai', 'dikembalikan') DEFAULT 'draft'");
        
        // Update existing records to have workflow fields
        // Set created_by and current_handler to 'ibuA' for existing records
        DB::table('dokumens')
            ->where(function($query) {
                $query->whereNull('created_by')
                      ->orWhereNull('current_handler');
            })
            ->update([
                'created_by' => 'ibuA',
                'current_handler' => 'ibuA',
            ]);
        
        // Update status for existing records that have 'sedang diproses' to 'draft' if they haven't been sent
        DB::table('dokumens')
            ->where('status', 'sedang diproses')
            ->whereNull('sent_to_ibub_at')
            ->update(['status' => 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum first
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM('sedang diproses', 'selesai') DEFAULT 'sedang diproses'");
        
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'created_by',
                'current_handler',
                'sent_to_ibub_at',
                'processed_at',
                'returned_to_ibua_at'
            ]);
        });
    }
};
