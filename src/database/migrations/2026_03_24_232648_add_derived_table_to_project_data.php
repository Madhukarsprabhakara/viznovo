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
            $table->boolean('is_csv_derived_table_created')->nullable();
            $table->boolean('is_csv_derived_table_populated')->nullable();
            $table->string('csv_derived_table_name')->nullable();
            $table->longText('derived_json_schema')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_data', function (Blueprint $table) {
            //
            $table->dropColumn('is_csv_derived_table_created');
            $table->dropColumn('is_csv_derived_table_populated');
            $table->dropColumn('csv_derived_table_name');
            $table->dropColumn('derived_json_schema');
        });
    }
};
