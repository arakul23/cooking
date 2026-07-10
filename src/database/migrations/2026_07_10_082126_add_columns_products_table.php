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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('fat')->after('name')->nullable();
            $table->integer('protein')->after('fat')->nullable();
            $table->integer('carbohydrates')->after('protein')->nullable();
            $table->integer('kcal')->after('carbohydrates')->nullable();
            $table->string('brand')->after('kcal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('fat');
            $table->dropColumn('protein');
            $table->dropColumn('carbohydrates');
            $table->dropColumn('kcal');
            $table->dropColumn('brand');
        });
    }
};
