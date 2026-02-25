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
                $table->string('csv_text_table_name')->nullable();
                $table->string('csv_data_type_table_name')->nullable();
                $table->boolean('is_csv_text_table_created')->nullable();
                $table->boolean('is_csv_data_type_table_created')->nullable();
                $table->boolean('is_csv_text_table_populated')->nullable();
                $table->boolean('is_csv_data_type_table_populated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_data', function (Blueprint $table) {
            //
            $table->dropColumn('csv_text_table_name');
            $table->dropColumn('csv_data_type_table_name');
            $table->dropColumn('is_csv_text_table_created');
            $table->dropColumn('is_csv_data_type_table_created');
            $table->dropColumn('is_csv_text_table_populated');
            $table->dropColumn('is_csv_data_type_table_populated');
        });
    }
};
