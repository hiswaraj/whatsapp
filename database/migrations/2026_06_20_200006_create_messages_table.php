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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained('whatsapp_accounts')->cascadeOnDelete();
            
            // Meta Cloud API details
            $table->string('meta_message_id')->nullable()->index(); // ID returned by Meta
            $table->string('type')->default('incoming'); // incoming, outgoing, system
            $table->string('message_type')->default('text'); // text, image, document, video, audio, template
            
            // Content
            $table->text('body')->nullable(); // Text body or media caption
            $table->string('media_path')->nullable(); // Local store path if downloaded
            $table->string('media_mime_type')->nullable();
            $table->string('meta_template_id')->nullable(); // Used if message_type is template
            
            // Statuses
            $table->string('status')->default('pending'); // pending, sent, delivered, read, failed
            $table->text('error_message')->nullable(); // Error details if failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
