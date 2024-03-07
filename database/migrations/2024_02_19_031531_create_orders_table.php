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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->on('media')->comment('media');
            $table->foreignId('user_id')->on('users')->comment('causer')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('passcode')->nullable()->comment('passcode to see');
            $table->boolean('is_confirmed')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
