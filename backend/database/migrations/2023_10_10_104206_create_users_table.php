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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // OIDC fields
            $table->string('iss');
            $table->string('sub');
            $table->string('name');
            $table->string('preferred_username')->nullable();
            $table->string('picture')->nullable();
            $table->string('email');

            $table->unique(['iss', 'sub']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
