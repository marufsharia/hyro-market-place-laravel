<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->json('screenshots')->nullable()->after('logo_path');
            $table->json('changelog')->nullable()->after('requirements');
            $table->text('installation_instructions')->nullable()->after('changelog');
            $table->string('documentation_url')->nullable()->after('installation_instructions');
            $table->string('support_url')->nullable()->after('documentation_url');
            $table->string('demo_url')->nullable()->after('support_url');
            $table->string('repository_url')->nullable()->after('demo_url');
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn([
                'screenshots',
                'changelog',
                'installation_instructions',
                'documentation_url',
                'support_url',
                'demo_url',
                'repository_url'
            ]);
        });
    }
};
