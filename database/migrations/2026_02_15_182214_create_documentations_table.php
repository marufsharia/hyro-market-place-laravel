<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('order');
        });

        Schema::create('documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('documentation_categories')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('version')->default('1.0');
            $table->integer('order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->json('tags')->nullable();
            $table->integer('views')->default(0);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('category_id');
            $table->index('version');
            $table->index('is_published');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
        Schema::dropIfExists('documentation_categories');
    }
};
