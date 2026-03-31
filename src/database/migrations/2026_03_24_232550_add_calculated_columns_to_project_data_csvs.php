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
        Schema::table('project_data_csvs', function (Blueprint $table) {
            //
            $table->text('derived_csv_header')->nullable();
            $table->text('derived_db_column')->nullable();
            $table->longText('prompt_instructions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_data_csvs', function (Blueprint $table) {
            //
            $table->dropColumn('derived_csv_header');
            $table->dropColumn('derived_db_column');
            $table->dropColumn('prompt_instructions');
        });
    }
};
