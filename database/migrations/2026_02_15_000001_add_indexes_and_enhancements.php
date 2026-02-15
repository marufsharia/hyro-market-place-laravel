<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add soft deletes and published_at to plugins
        Schema::table('plugins', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('rating_count');
            $table->softDeletes();
            
            // Add indexes for performance
            $table->index('slug');
            $table->index('status');
            $table->index('category_id');
            $table->index('user_id');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
        });

        // Add indexes to reviews
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('plugin_id');
            $table->index('user_id');
            $table->unique(['plugin_id', 'user_id']);
        });

        // Add indexes to favorites (already has unique constraint)
        Schema::table('favorites', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('plugin_id');
        });

        // Add is_admin to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'deleted_at']);
            $table->dropIndex(['plugins_slug_index']);
            $table->dropIndex(['plugins_status_index']);
            $table->dropIndex(['plugins_category_id_index']);
            $table->dropIndex(['plugins_user_id_index']);
            $table->dropIndex(['plugins_published_at_index']);
            $table->dropIndex(['plugins_status_published_at_index']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['reviews_plugin_id_index']);
            $table->dropIndex(['reviews_user_id_index']);
            $table->dropUnique(['reviews_plugin_id_user_id_unique']);
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex(['favorites_user_id_index']);
            $table->dropIndex(['favorites_plugin_id_index']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
