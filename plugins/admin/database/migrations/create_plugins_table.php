<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->string('version')->default('1.0.0');
            $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->string('author')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index('enabled');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
