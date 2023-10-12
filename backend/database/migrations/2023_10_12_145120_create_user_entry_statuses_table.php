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
        Schema::create('user_entry_statuses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('user_id');
            $table->bigInteger('entry_id');
            $table->unique(['user_id', 'entry_id']);

            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_entry_statuses');
    }
};
