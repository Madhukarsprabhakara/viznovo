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
        Schema::table('project_data_metrics', function (Blueprint $table) {
            $table->foreignId('report_id')->nullable()->constrained()->onDelete('cascade');
            $table->dropConstrainedForeignId('project_data_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_data_metrics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('report_id');
            $table->foreignId('project_data_id')->constrained()->onDelete('cascade');
        });
    }
};
