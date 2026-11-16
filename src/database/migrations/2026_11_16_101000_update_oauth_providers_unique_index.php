<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_providers', function (Blueprint $table) {
            $table->dropUnique('oauth_providers_provider_unique');
            $table->dropUnique('oauth_providers_provider_id_unique');
            $table->unique(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('oauth_providers', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_id']);
            $table->unique('provider');
            $table->unique('provider_id');
        });
    }
};
