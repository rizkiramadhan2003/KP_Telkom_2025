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
        Schema::create('hd_damans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('order_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('fallout_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('fallout_reports', function (Blueprint $table) {
            $table->id();
            $table->string('no_report')->unique();
            $table->date('tanggal');
            $table->foreignId('hd_daman_id')->constrained('hd_damans');
            $table->foreignId('order_type_id')->constrained('order_types');
            $table->string('order_id');
            $table->foreignId('fallout_status_id')->constrained('fallout_statuses');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fallout_reports');
        Schema::dropIfExists('fallout_statuses');
        Schema::dropIfExists('order_types');
        Schema::dropIfExists('hd_damans');
    }
};