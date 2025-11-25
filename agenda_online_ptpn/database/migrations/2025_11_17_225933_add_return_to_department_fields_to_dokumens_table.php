<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->string('target_department')->nullable()->after('alasan_pengembalian')->comment('Target department for return: perpajakan, akutansi, pembayaran');
            $table->timestamp('department_returned_at')->nullable()->after('target_department')->comment('When document was returned to department');
            $table->text('department_return_reason')->nullable()->after('department_returned_at')->comment('Reason for returning to department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'target_department',
                'department_returned_at',
                'department_return_reason'
            ]);
        });
    }
};
