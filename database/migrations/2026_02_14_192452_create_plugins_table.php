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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The author/uploader
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('logo_path')->nullable();
            
            // Technical Details
            $table->string('version');
            $table->string('status')->default('pending'); // active, inactive, update_available
            $table->string('compatibility'); // e.g., "Hyro v1.5+"
            $table->json('requirements'); // Stores {php: ">=8.1", laravel: ">=10.0"}
            $table->string('license_type');
            
            // Stats
            $table->integer('downloads')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            
            $table->timestamps();
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
