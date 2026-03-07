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
        Schema::table('reports', function (Blueprint $table) {
            //
            $table->bigInteger('start_epoch')->nullable();
            $table->bigInteger('end_epoch')->nullable();
            $table->double('time_taken_seconds')->nullable();
            $table->boolean('is_renderable')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('start_epoch');
            $table->dropColumn('end_epoch');
            $table->dropColumn('time_taken_seconds');
            $table->dropColumn('is_renderable');
        });
    }
};
