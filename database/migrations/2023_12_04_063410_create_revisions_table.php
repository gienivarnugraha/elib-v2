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
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('New Document');
            $table->string('body')->nullable();
            $table->string('index')->default('A');
            $table->nullableMorphs('revisable', 'revisable');
            $table->foreignId('user_id')->on('users')->comment('causer')->nullable();
            $table->date('index_date')->nullable();
            $table->boolean('is_closed')->default(0);
            $table->boolean('is_canceled')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
