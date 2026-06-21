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
        Schema::create('flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('trigger_keywords')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->longText('canvas_data')->nullable();
            $table->json('compiled_data')->nullable();
            $table->timestamps();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('active_flow_id')->nullable()->after('whatsapp_account_id');
            $table->string('current_flow_node_id')->nullable()->after('active_flow_id');

            $table->foreign('active_flow_id')->references('id')->on('flows')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['active_flow_id']);
            $table->dropColumn(['active_flow_id', 'current_flow_node_id']);
        });

        Schema::dropIfExists('flows');
    }
};
