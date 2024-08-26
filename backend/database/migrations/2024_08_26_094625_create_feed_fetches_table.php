<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feed_fetches', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('feed_id')->unsigned();

            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->integer('status_code');
            $table->string('error')->nullable();
            $table->text('error_private')->nullable();

            $table->integer('new_items_count')->default(0);
            $table->integer('updated_items_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_fetches');
    }
};
