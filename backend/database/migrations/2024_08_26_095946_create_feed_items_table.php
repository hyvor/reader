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
        Schema::create('feed_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('feed_id');

            $table->string('guid');
            $table->string('url', 1024);
            $table->string('title');
            $table->timestamp('published_at');
            $table->text('content_html')->nullable();
            $table->text('content_text')->nullable();
            $table->string('summary')->nullable();
            $table->string('image')->nullable();
            $table->json('authors')->nullable();
            $table->json('tags')->nullable();
            $table->string('language')->nullable();

            $table->unique(['feed_id', 'guid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_items');
    }
};
