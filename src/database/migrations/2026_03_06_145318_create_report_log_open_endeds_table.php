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
        Schema::create('report_log_open_endeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('report_log_id')->constrained()->onDelete('cascade');
            $table->text('table_name')->nullable();
            $table->text('agent')->nullable();
            $table->longText('response')->nullable();
            $table->longText('error')->nullable();
            $table->bigInteger('total_chunks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_log_open_endeds');
    }
};
