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
        Schema::create('project_data_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable();
            $table->foreignId('project_data_id')->constrained('project_data')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('metric_name')->nullable();
            $table->longText('description')->nullable();
            $table->longText('sql_query')->nullable();
            $table->longText('result')->nullable();
            $table->longText('error')->nullable();
            $table->boolean('is_successful')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_data_metrics');
    }
};
