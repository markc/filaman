<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->default('Main');
            $table->integer('order')->default(999);
            $table->boolean('published')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->longText('content');
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->string('featured_image')->nullable();
            $table->timestamps();

            $table->index(['published', 'category', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
