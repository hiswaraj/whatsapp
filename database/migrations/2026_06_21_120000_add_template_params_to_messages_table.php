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
        Schema::table('messages', function (Blueprint $table) {
            $table->json('template_params')->nullable()->after('meta_template_id');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_temporary')->default(false)->after('notes')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('template_params');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('is_temporary');
        });
    }
};
