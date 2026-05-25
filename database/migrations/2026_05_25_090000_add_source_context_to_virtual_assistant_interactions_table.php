<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('virtual_assistant_interactions')) {
            return;
        }

        Schema::table('virtual_assistant_interactions', function (Blueprint $table) {
            if (!Schema::hasColumn('virtual_assistant_interactions', 'source_context')) {
                $table->string('source_context', 40)->default('owner')->after('user_role')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('virtual_assistant_interactions')) {
            return;
        }

        Schema::table('virtual_assistant_interactions', function (Blueprint $table) {
            if (Schema::hasColumn('virtual_assistant_interactions', 'source_context')) {
                $table->dropIndex(['source_context']);
                $table->dropColumn('source_context');
            }
        });
    }
};
