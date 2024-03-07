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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Owner ID');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('date_format');
            $table->string('time_format');
            $table->string('first_day_of_week')->default(0);
            $table->string('locale')->default('en');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('currency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
