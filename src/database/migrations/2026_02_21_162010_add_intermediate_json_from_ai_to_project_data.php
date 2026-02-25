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
        Schema::table('project_data', function (Blueprint $table) {
            //
            $table->longText('json_from_ai')->nullable();
            $table->longText('json_from_ai_string')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_data', function (Blueprint $table) {
            //
            $table->dropColumn('intermediate_json_from_ai');
            $table->dropColumn('intermediate_json_from_ai_string');
        });
    }
};
