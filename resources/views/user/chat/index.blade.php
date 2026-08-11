@extends('layouts.auth')

@section('title', 'Live Chat - WhatsApp SaaS Platform')

@section('styles')
<style>
    /* Full height chat window layout */
    .chat-layout {
        display: flex;
        height: calc(100vh - 120px);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        overflow: hidden;
        background-color: var(--card-background);
        box-shadow: var(--shadow-sm);
        transition: var(--transition-fast);
    }

    /* Chat layout Fullscreen mode */
    .chat-layout.is-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        border-radius: 0 !important;
        margin: 0 !important;
        border: none !important;
    }
    
    :fullscreen .chat-layout,
    :-webkit-full-screen .chat-layout,
    :-moz-full-screen .chat-layout {
        width: 100vw !important;
        height: 100vh !important;
        border-radius: 0 !important;
        border: none !important;
    }
    
    /* Left sidebar: conversations list */
    .chat-threads-sidebar {
        width: 320px;
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        background-color: var(--card-background);
    }
    
    .chat-search-wrapper {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .chat-search-wrapper .input-group-text {
        background-color: var(--background-color) !important;
        border-color: var(--border-color) !important;
        color: var(--text-secondary) !important;
    }
    
    .chat-search-wrapper input {
        background-color: var(--background-color) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    .chat-search-wrapper input::placeholder {
        color: var(--text-muted) !important;
        opacity: 0.7;
    }
    
    .chat-search-wrapper input:focus {
        border-color: var(--input-focus-border) !important;
        box-shadow: none !important;
    }
    
    .chat-threads-list {
        flex: 1;
        overflow-y: auto;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .chat-thread-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid var(--border-color);
        cursor: pointer;
        transition: var(--transition-fast);
        user-select: none;
    }
    
    .chat-thread-item:hover {
        background-color: var(--background-color);
    }
    
    .chat-thread-item.active {
        background-color: var(--input-focus-shadow);
        border-left: 4px solid var(--primary-color);
    }
    
    .chat-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    
    .chat-thread-info {
        flex: 1;
        min-width: 0;
    }
    
    .chat-thread-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 0.15rem;
    }
    
    .chat-thread-name {
        font-weight: 600;
        font-size: 0.92rem;
        color: var(--text-primary);
        margin: 0;
        text-truncate: true;
    }
    
    .chat-thread-time {
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    
    .chat-thread-body-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }
    
    .chat-thread-snippet {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
    }
    
    .chat-unread-badge {
        background-color: var(--success-color);
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.45rem;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }
    
    /* Right side: message workspace */
    .chat-workspace {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: #efeae2;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%23e5ddd5' fill-opacity='0.4'%3E%3Cpath fill-rule='evenodd' d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM11 65c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 0c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM34 39c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 29c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm39-23c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM9 9c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 24c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z'/%3E%3C/g%3E%3C/svg%3E");
    }
    
    [data-theme="dark"] .chat-workspace {
        background-color: #0b141a;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%231f2c34' fill-opacity='0.25'%3E%3Cpath fill-rule='evenodd' d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM11 65c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 0c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM34 39c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 29c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm39-23c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM9 9c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 24c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z'/%3E%3C/g%3E%3C/svg%3E");
    }
    
    /* Workspace header styling */
    .chat-header {
        background-color: var(--card-background);
        border-bottom: 1px solid var(--border-color);
        padding: 0.6rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 5;
    }
    
    .chat-active-contact {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .chat-active-name {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-primary);
        margin: 0;
    }
    
    .chat-active-status {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: block;
    }
    
    /* Empty chat screen state */
    .chat-empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        background-color: var(--card-background);
        text-align: center;
    }
    
    /* Messages area and GPU acceleration */
    .chat-threads-list,
    .chat-messages-container {
        transform: translateZ(0);
        will-change: scroll-position;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }

    /* Custom smooth React-like scrollbars */
    .chat-threads-list::-webkit-scrollbar,
    .chat-messages-container::-webkit-scrollbar {
        width: 5px;
    }
    .chat-threads-list::-webkit-scrollbar-track,
    .chat-messages-container::-webkit-scrollbar-track {
        background: transparent;
    }
    .chat-threads-list::-webkit-scrollbar-thumb,
    .chat-messages-container::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.3);
        border-radius: 999px;
    }
    .chat-threads-list::-webkit-scrollbar-thumb:hover,
    .chat-messages-container::-webkit-scrollbar-thumb:hover {
        background-color: rgba(148, 163, 184, 0.6);
    }
    
    .chat-messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }
    
    /* Message Bubble styling */
    .chat-bubble-wrapper {
        display: flex;
        flex-direction: column;
        max-width: 70%;
        width: fit-content;
        transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.15s ease;
        animation: popBubble 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes popBubble {
        0% { opacity: 0; transform: scale(0.96) translateY(4px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    
    .chat-bubble-wrapper.outgoing {
        align-self: flex-end;
    }
    
    .chat-bubble-wrapper.incoming {
        align-self: flex-start;
    }
    
    .chat-bubble {
        padding: 0.55rem 0.8rem;
        border-radius: 8px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.12);
        font-size: 0.88rem;
        line-height: 1.4;
        position: relative;
        word-break: break-word;
    }
    
    .chat-bubble-wrapper.incoming .chat-bubble {
        background-color: var(--card-background);
        color: var(--text-primary);
        border-radius: 0 8px 8px 8px;
    }
    
    .chat-bubble-wrapper.outgoing .chat-bubble {
        background-color: #d9fdd3;
        color: #111b21;
        border-radius: 8px 0 8px 8px;
    }
    
    [data-theme="dark"] .chat-bubble-wrapper.outgoing .chat-bubble {
        background-color: #005c4b;
        color: #e9edef;
    }
    
    .chat-meta {
        font-size: 0.68rem;
        color: var(--text-muted);
        margin-top: 0.2rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .chat-bubble-wrapper.outgoing .chat-meta {
        justify-content: flex-end;
        color: #667781;
    }
    
    [data-theme="dark"] .chat-bubble-wrapper.outgoing .chat-meta {
        color: #8696a0;
    }
    
    /* Status indicators */
    .status-tick {
        font-size: 0.82rem;
        line-height: 1;
    }
    .status-tick.read {
        color: #53bdeb; /* WhatsApp blue checkmarks */
    }
    .status-tick.delivered {
        color: #8696a0;
    }
    .status-tick.failed {
        color: var(--danger-color);
    }
    
    /* System & date dividers */
    .chat-divider {
        align-self: center;
        background-color: rgba(225, 230, 235, 0.8);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        margin: 0.5rem 0;
        box-shadow: var(--shadow-sm);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        user-select: none;
    }
    
    [data-theme="dark"] .chat-divider {
        background-color: #1f2c34;
        color: #8696a0;
    }
    
    /* Outgoing bubble action buttons (like CTA/Quick replies inside templates) */
    .chat-bubble-btn-container {
        display: flex;
        flex-direction: column;
        gap: 1px;
        margin-top: 3px;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
        width: 100%;
        max-width: 100%;
    }
    
    .chat-bubble-action-btn {
        background-color: var(--card-background);
        border: none;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.08);
        padding: 0.5rem;
        color: #008069;
        font-weight: 600;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        width: 100%;
        transition: background 0.1s ease;
    }
    
    [data-theme="dark"] .chat-bubble-action-btn {
        background-color: #1f2c34;
        color: #25d366;
    }
    
    .chat-bubble-action-btn:hover {
        background-color: var(--background-color);
    }
    
    /* Footer input box */
    .chat-footer {
        background-color: var(--card-background);
        border-top: 1px solid var(--border-color);
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 5;
    }
    
    .chat-input-field {
        flex: 1;
        background-color: var(--background-color);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        color: var(--text-primary);
        padding: 0.6rem 1rem;
        font-size: 0.92rem;
        resize: none;
        max-height: 100px;
        outline: none;
        transition: var(--transition-fast);
    }
    
    .chat-input-field:focus {
        border-color: var(--input-focus-border);
        background-color: var(--card-background);
    }
    
    .chat-action-trigger {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 1.35rem;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        width: 38px;
        height: 38px;
        transition: var(--transition-fast);
    }
    
    .chat-action-trigger:hover {
        background-color: var(--background-color);
        color: var(--primary-color);
        transform: scale(1.05);
    }
    
    .chat-action-trigger.btn-send {
        background-color: var(--primary-color);
        color: #ffffff;
    }
    
    .chat-action-trigger.btn-send:hover {
        background-color: var(--primary-hover);
        color: #ffffff;
    }
    
    .chat-var-badge {
        background-color: rgba(99, 102, 241, 0.12);
        color: #4f46e5;
        font-weight: 700;
        font-family: monospace;
        padding: 0.15rem 0.35rem;
        border-radius: 4px;
        font-size: 0.78rem;
    }

    /* Attachment preview styling */
    .attachment-preview-bar {
        position: relative;
        z-index: 10;
        background-color: var(--card-background);
        margin-bottom: 8px;
    }
    
    /* Media picker grid styling */
    .picker-item-card {
        border: 2px solid transparent;
        border-radius: var(--border-radius-md);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: var(--background-color);
        height: 110px;
        position: relative;
    }
    
    .picker-item-card:hover {
        border-color: var(--border-color);
        transform: scale(1.02);
    }
    
    .picker-item-card.selected {
        border-color: var(--primary-color);
        background-color: var(--input-focus-shadow);
    }
    
    .picker-item-preview {
        height: 70px;
        background-color: var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .picker-item-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .picker-item-info {
        padding: 4px 6px;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-primary);
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        text-align: center;
    }
    
    .picker-item-type {
        position: absolute;
        top: 2px;
        left: 2px;
        font-size: 0.55rem;
        background-color: rgba(0,0,0,0.7);
        color: white;
        padding: 1px 3px;
        border-radius: var(--border-radius-sm);
        text-transform: uppercase;
        font-weight: 700;
        z-index: 5;
    }

    /* Upload zone within modal picker */
    .upload-dropzone {
        border: 2px dashed var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 2rem 1rem;
        text-align: center;
        background-color: var(--background-color);
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }
    .upload-dropzone:hover, .upload-dropzone.dragover {
        border-color: var(--input-focus-border);
        background-color: var(--input-focus-shadow);
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">

    @include('layouts.sidebar', ['active' => 'chat'])

    <!-- Main Workspace -->
    <main class="dashboard-main">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom fade-in-element">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">WhatsApp Live Chat</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Converse in real-time with your contacts and trigger message templates</span>
            </div>
            <div>
                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2 px-3 py-2 chat-fullscreen-trigger" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-arrows-fullscreen chat-fullscreen-icon"></i>
                    <span class="chat-fullscreen-text">Full Screen Chat</span>
                </button>
            </div>
        </header>

        <!-- Live Chat Main Section -->
        <section class="chat-layout fade-in-element" id="chat-layout-container">
            
            <!-- Sidebar: Threads List -->
            <div class="chat-threads-sidebar">
                <div class="chat-search-wrapper">
                    <div class="mb-2">
                        <select id="waba-chat-filter" class="form-select form-control-custom py-1.5" style="font-size: 0.8rem; background-color: var(--background-color);">
                            <option value="">All WABA Accounts</option>
                            @foreach($wabas as $wb)
                                <option value="{{ $wb->id }}">{{ $wb->display_name }} ({{ substr($wb->phone_number_id, -6) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-custom flex-grow-1">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="contact-search" class="form-control form-control-custom form-control-sm py-2" placeholder="Search chats...">
                        </div>
                        <button class="btn btn-sm btn-primary-custom d-flex align-items-center justify-content-center" id="new-chat-btn" title="Start New Chat" style="width: 38px; height: 38px; border-radius: var(--border-radius-md); padding: 0; flex-shrink: 0;">
                            <i class="bi bi-plus-lg text-white" style="font-size: 1.2rem; color: #ffffff !important;"></i>
                        </button>
                    </div>
                </div>
                
                <ul class="chat-threads-list" id="threads-container">
                    <!-- Loaded dynamically via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="text-muted small mt-2">Loading conversations...</p>
                    </div>
                </ul>
            </div>
            
            <!-- Message thread content box -->
            <div class="chat-workspace" id="chat-workspace-panel">
                <!-- Default empty state -->
                <div class="chat-empty-state w-100 h-100">
                    <div class="empty-state-icon-wrapper" style="width:70px; height:70px; font-size:2rem; margin-bottom: 1rem;">
                        <i class="bi bi-chat-left-dots-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Select a Conversation</h5>
                    <p class="text-muted small mx-auto" style="max-width: 320px;">
                        Choose an active client thread from the sidebar list to view message logs, send attachments, and compose templates.
                    </p>
                </div>
            </div>

        </section>
    </main>

</div>

<!-- Send Template Modal -->
<div class="modal fade" id="sendTemplateModal" tabindex="-1" aria-labelledby="sendTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="send-template-form">
                @csrf
                <input type="hidden" name="conversation_id" id="template-conv-id">
                <input type="hidden" name="message_type" value="template">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="sendTemplateModalLabel">Send WhatsApp Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="template_select" class="form-label fw-semibold">Choose Approved Template</label>
                        <select name="template_id" id="template_select" class="form-select form-control-custom" required>
                            <option value="" selected disabled>-- Select Template --</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" data-components="{{ json_encode($tpl->components) }}">
                                    {{ $tpl->name }} ({{ $tpl->language }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Template layout previewer -->
                    <div class="mb-3 d-none" id="template-preview-card">
                        <label class="form-label fw-semibold small text-muted">Message Template Preview</label>
                        <div class="p-3 border rounded bg-light text-dark small" style="white-space: pre-wrap; font-family: inherit;" id="template-preview-body"></div>
                    </div>

                    <!-- Header media input container -->
                    <div class="mb-3 d-none" id="template-header-media-container">
                        <label class="form-label fw-semibold" id="template-header-media-label">Header Media</label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-between w-100 py-2" id="template-header-media-btn" style="border-radius: var(--border-radius-md); border-color: var(--border-color); background: transparent; color: var(--text-secondary); font-size: 0.9rem; font-weight: 600; text-align: left;">
                                <span class="d-flex align-items-center gap-1">
                                    <i class="bi bi-paperclip" style="font-size: 1.1rem;"></i>
                                    <span id="template-header-media-btn-text">Select Media File</span>
                                </span>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </button>
                            <input type="hidden" name="header_media_id" id="template-header-media-id">
                            <button type="button" class="btn btn-outline-danger d-none d-flex align-items-center justify-content-center" id="template-header-media-clear" title="Clear media" style="height: 38px; width: 38px; border-radius: var(--border-radius-md); border-color: var(--border-color); flex-shrink: 0;"><i class="bi bi-x" style="font-size: 1.25rem;"></i></button>
                        </div>
                    </div>

                    <!-- Dynamic parameter variables input container -->
                    <div class="mb-3 d-none" id="template-variables-container">
                        <label class="form-label fw-semibold">Bind Template Variables</label>
                        <div class="d-flex flex-column gap-3" id="template-variables-inputs">
                            <!-- Populated dynamically based on variables inside selected template -->
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom d-flex align-items-center gap-1">
                        <i class="bi bi-send"></i> Send Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Start New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="new-chat-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="newChatModalLabel">Start New Conversation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Select WABA -->
                    <div class="mb-3">
                        <label for="new_chat_waba_id" class="form-label fw-semibold">Select WABA Account</label>
                        <select name="whatsapp_account_id" id="new_chat_waba_id" class="form-select form-control-custom" required>
                            <option value="" disabled>-- Select WABA Account --</option>
                            @foreach($wabas as $wb)
                                <option value="{{ $wb->id }}" {{ $loop->first ? 'selected' : '' }}>
                                    {{ $wb->display_name }} ({{ $wb->phone_number_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type selection radio toggles -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Contact Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="type-existing" value="existing" checked autocomplete="off">
                            <label class="btn btn-outline-primary" for="type-existing" style="font-weight:600;">Existing Contact</label>

                            <input type="radio" class="btn-check" name="type" id="type-new" value="new" autocomplete="off">
                            <label class="btn btn-outline-primary" for="type-new" style="font-weight:600;">New Phone Number</label>
                        </div>
                    </div>

                    <!-- Option 1: Existing Contact Dropdown -->
                    <div class="mb-3" id="new-chat-existing-section">
                        <label for="new_chat_contact_id" class="form-label fw-semibold">Select Contact</label>
                        <select name="contact_id" id="new_chat_contact_id" class="form-select form-control-custom">
                            <option value="" selected disabled>-- Select Contact --</option>
                            @foreach($contacts as $ct)
                                <option value="{{ $ct->id }}">{{ $ct->name }} ({{ $ct->mobile_number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Option 2: New Mobile Number & Name -->
                    <div class="d-none" id="new-chat-new-section">
                        <div class="mb-3">
                            <label for="new_chat_mobile" class="form-label fw-semibold">Mobile Number</label>
                            <input type="text" name="mobile_number" id="new_chat_mobile" class="form-control form-control-custom" placeholder="e.g. +1234567890">
                            <div class="form-text text-muted" style="font-size: 0.75rem;">Include country code prefix (e.g. +1).</div>
                        </div>
                        <div class="mb-3">
                            <label for="new_chat_name" class="form-label fw-semibold">Contact Name (Optional)</label>
                            <input type="text" name="name" id="new_chat_name" class="form-control form-control-custom" placeholder="e.g. Jane Doe">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg text-white" style="font-size: 1rem; color: #ffffff !important;"></i> Start Chat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Media Picker Modal -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true" style="z-index: 2050;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--border-radius-lg); background-color: var(--card-background); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="mediaPickerModalLabel" style="color: var(--text-primary);">Select Attachment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <!-- Picker Tabs -->
                <ul class="nav nav-tabs mb-3 border-bottom" id="pickerTabs" role="tablist" style="border-bottom-color: var(--border-color) !important;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold border-0 bg-transparent text-primary" id="browse-tab" data-bs-toggle="tab" data-bs-target="#browse-pane" type="button" role="tab" aria-controls="browse-pane" aria-selected="true" style="color: var(--primary-color) !important;">Browse Library</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold border-0 bg-transparent text-secondary" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab" aria-controls="upload-pane" aria-selected="false">Upload New</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="pickerTabContent">
                    <!-- Browse Pane -->
                    <div class="tab-pane fade show active" id="browse-pane" role="tabpanel" aria-labelledby="browse-tab">
                        <div class="row g-2 mb-3">
                            <div class="col-md-8 col-sm-12">
                                <div class="d-flex gap-1 flex-wrap" id="picker-type-filters">
                                    <button type="button" class="btn btn-sm btn-outline-secondary active" data-type="all" style="font-size: 0.8rem; border-radius: var(--border-radius-pill);">All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-type="image" style="font-size: 0.8rem; border-radius: var(--border-radius-pill);">Images</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-type="video" style="font-size: 0.8rem; border-radius: var(--border-radius-pill);">Videos</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-type="audio" style="font-size: 0.8rem; border-radius: var(--border-radius-pill);">Audio</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-type="document" style="font-size: 0.8rem; border-radius: var(--border-radius-pill);">Docs</button>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <input type="text" id="picker-search" class="form-control form-control-sm form-control-custom" placeholder="Search by name...">
                            </div>
                        </div>
                        
                        <!-- Items Grid -->
                        <div class="row g-2 overflow-y-auto" id="picker-items-grid" style="max-height: 320px; min-height: 200px;">
                            <div class="text-center py-5 w-100">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Pane -->
                    <div class="tab-pane fade" id="upload-pane" role="tabpanel" aria-labelledby="upload-tab">
                        <div class="upload-dropzone" id="picker-dropzone">
                            <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;"></i>
                            <span class="fw-semibold d-block" style="color: var(--text-primary); font-size: 0.9rem;">Drag & drop your file here</span>
                            <span class="text-muted d-block my-1" style="font-size: 0.75rem;">or click to browse</span>
                            <input type="file" id="picker-file-input" class="d-none">
                            <div class="text-muted mt-2" style="font-size: 0.7rem;">Max size: 16MB. Supports Images, Videos, Audio, Docs.</div>
                        </div>
                        
                        <div class="d-none mt-2" id="picker-progress-container">
                            <div class="d-flex justify-content-between mb-1" style="font-size: 0.75rem;">
                                <span class="fw-semibold text-primary" id="picker-upload-filename">file.ext</span>
                                <span class="text-muted" id="picker-upload-percent">0%</span>
                            </div>
                            <div class="progress" style="height: 5px; border-radius: var(--border-radius-pill);">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="picker-upload-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="picker-select-btn" style="padding: 0.5rem 1.5rem;" disabled>Attach</button>
            </div>
        </div>
    </div>
</div>

<!-- Media Preview Lightbox Modal (WhatsApp-style Popup Viewer) -->
<div class="modal fade" id="mediaPreviewLightboxModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background-color: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--border-radius-lg); color: #fff;">
            <div class="modal-header border-bottom-0 py-2 px-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid rgba(255,255,255,0.08) !important;">
                <h6 class="modal-title fw-semibold text-truncate text-white mb-0" id="lightbox-title" style="max-width: 70%;">Media Preview</h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="lightbox-download-btn" download class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" style="border-radius: var(--border-radius-md); font-weight: 600;">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body text-center p-3 d-flex align-items-center justify-content-center" style="min-height: 380px; max-height: 82vh; overflow: hidden;">
                <div id="lightbox-content-container" class="w-100 h-100 d-flex align-items-center justify-content-center">
                    <!-- Dynamic image/video/audio preview injected here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Image Editor Modal (Doodle, Text, Rotate, Filters, Crop/Flip, Caption) -->
<div class="modal fade" id="imageEditorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 2070;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background-color: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: var(--border-radius-lg); color: #fff; overflow: hidden;">
            <!-- Top Toolbar -->
            <div class="modal-header border-bottom py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.1) !important;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-white fs-6"><i class="bi bi-pencil-square text-success"></i> WhatsApp Image Editor</span>
                    <div class="vr bg-secondary opacity-50 mx-1 d-none d-sm-block"></div>
                    
                    <!-- Tools Toggle -->
                    <div class="btn-group btn-group-sm" role="group" id="editor-tool-group">
                        <button type="button" class="btn btn-outline-light active img-tool-btn" data-tool="draw" title="Draw / Doodle">
                            <i class="bi bi-pencil-fill"></i> Draw
                        </button>
                        <button type="button" class="btn btn-outline-light img-tool-btn" data-tool="text" title="Add Text">
                            <i class="bi bi-type"></i> Text
                        </button>
                        <button type="button" class="btn btn-outline-light img-tool-btn" data-tool="emoji" title="Add Sticker">
                            <i class="bi bi-emoji-smile-fill"></i> Sticker
                        </button>
                    </div>

                    <!-- Color Picker -->
                    <div class="d-flex align-items-center gap-1 ms-1">
                        <label class="small text-muted mb-0 d-none d-md-inline">Color:</label>
                        <input type="color" id="editor-color-picker" value="#22c55e" style="width:28px; height:28px; border:1px solid #fff; padding:0; background:transparent; cursor:pointer; border-radius:50%;">
                    </div>

                    <!-- Brush Size -->
                    <div class="d-flex align-items-center gap-1 ms-1">
                        <select id="editor-brush-size" class="form-select form-select-sm bg-dark text-white border-secondary py-0 px-2" style="width: auto; height: 28px; font-size: 0.8rem;">
                            <option value="3">Thin</option>
                            <option value="7" selected>Medium</option>
                            <option value="15">Thick</option>
                        </select>
                    </div>

                    <!-- Rotate & Flip -->
                    <button type="button" class="btn btn-sm btn-outline-light" id="btn-editor-rotate" title="Rotate 90°">
                        <i class="bi bi-arrow-clockwise"></i> Rotate
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btn-editor-flip" title="Flip Horizontal">
                        <i class="bi bi-symmetry-vertical"></i> Flip
                    </button>

                    <!-- Filter Options -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="editorFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-magic"></i> Filter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" aria-labelledby="editorFilterDropdown">
                            <li><a class="dropdown-item editor-filter-opt active" data-filter="none" href="#">Normal</a></li>
                            <li><a class="dropdown-item editor-filter-opt" data-filter="grayscale" href="#">Grayscale</a></li>
                            <li><a class="dropdown-item editor-filter-opt" data-filter="sepia" href="#">Sepia</a></li>
                            <li><a class="dropdown-item editor-filter-opt" data-filter="invert" href="#">Invert</a></li>
                            <li><a class="dropdown-item editor-filter-opt" data-filter="brightness" href="#">Vibrant</a></li>
                        </ul>
                    </div>

                    <!-- Undo & Reset -->
                    <button type="button" class="btn btn-sm btn-outline-warning" id="btn-editor-undo" title="Undo Last Stroke">
                        <i class="bi bi-arrow-counterclockwise"></i> Undo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-editor-reset" title="Reset Image">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </button>
                </div>
                
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Editor Canvas Container -->
            <div class="modal-body text-center p-3 d-flex align-items-center justify-content-center position-relative" style="min-height: 420px; max-height: 65vh; background: #020617; overflow: auto;">
                <div class="position-relative d-inline-block" id="editor-canvas-container">
                    <canvas id="image-editor-canvas" class="rounded" style="max-height: 60vh; max-width: 100%; cursor: crosshair; box-shadow: 0 10px 30px rgba(0,0,0,0.8); display: block; margin: 0 auto;"></canvas>
                </div>
            </div>

            <!-- Emoji Picker Popup Tray -->
            <div class="bg-dark border-top border-secondary p-2 d-none" id="editor-emoji-picker-tray" style="background: #1e293b !important;">
                <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap fs-4">
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">❤️</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">🔥</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">👍</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">😂</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">🎉</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">⭐</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">💯</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">🚀</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">😍</span>
                    <span class="editor-emoji-item" style="cursor:pointer;" title="Click to stamp">✅</span>
                </div>
            </div>

            <!-- Bottom Caption & Send Bar -->
            <div class="modal-footer border-top p-3 d-flex align-items-center gap-2" style="background: rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.1) !important;">
                <div class="flex-grow-1 position-relative">
                    <input type="text" id="editor-caption-input" class="form-control bg-dark text-white border-secondary rounded-pill px-3" placeholder="Add a caption..." style="font-size: 0.95rem;">
                </div>
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold d-flex align-items-center gap-2" id="btn-editor-send">
                    <i class="bi bi-send-fill"></i> Attach & Send
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let activeConversationId = null;
        let conversationsList = [];
        let pollingInterval = null;
        let chatCountdownInterval = null;
        let pickerTarget = 'chat';

        // Parse conversation_id from URL query parameter on load
        const urlParams = new URLSearchParams(window.location.search);
        let urlConvId = urlParams.get('conversation_id');
        if (urlConvId) {
            activeConversationId = parseInt(urlConvId);
            renderWorkspaceSkeleton();
        }

        let lastThreadsHash = '';
        let lastMessagesHash = {};
        let searchDebounceTimer = null;

        // Fetch conversation list and update sidebar UI
        function fetchConversations(isFirstLoad = false) {
            const wabaId = $('#waba-chat-filter').val() || '';
            $.ajax({
                url: "{{ route('chat.conversations') }}",
                type: "GET",
                data: {
                    waba_id: wabaId
                },
                dataType: "json",
                success: function(response) {
                    if (response.status) {
                        conversationsList = response.conversations;
                        renderConversationsList();
                        
                        // If it's first load and we have query param session, fetch its messages
                        if (isFirstLoad && activeConversationId) {
                            const exists = conversationsList.some(c => c.id === activeConversationId);
                            if (exists) {
                                fetchMessages(activeConversationId);
                            }
                        } else if (!isFirstLoad && activeConversationId) {
                            fetchMessages(activeConversationId, true);
                        }
                    }
                },
                error: function() {
                    console.error('Failed to load conversations list.');
                }
            });
        }

        // Render Sidebar List with state hashing to prevent unnecessary repaints
        function renderConversationsList(forceRender = false) {
            const searchQuery = $('#contact-search').val().toLowerCase().trim();
            const container = $('#threads-container');

            const filtered = conversationsList.filter(function(conv) {
                const name = conv.contact.name.toLowerCase();
                const number = conv.contact.mobile_number.toLowerCase();
                return name.includes(searchQuery) || number.includes(searchQuery);
            });

            // State signature hash comparison
            const currentHash = searchQuery + '|' + activeConversationId + '|' + JSON.stringify(filtered.map(c => [
                c.id, 
                c.unread_count, 
                c.last_message ? c.last_message.id : 0, 
                c.last_message_at,
                c.contact.name,
                c.contact.avatar_url
            ]));

            if (!forceRender && currentHash === lastThreadsHash) {
                return; // Nothing changed, skip DOM repaints!
            }
            lastThreadsHash = currentHash;

            if (filtered.length === 0) {
                container.html('<li class="text-center py-5 text-muted small">No conversations found</li>');
                return;
            }

            const htmlArr = filtered.map(function(conv) {
                const isActive = (conv.id === activeConversationId) ? 'active' : '';
                const unreadBadge = conv.unread_count > 0 ? `<span class="chat-unread-badge">${conv.unread_count}</span>` : '';
                
                let lastMsgText = 'No messages yet';
                if (conv.last_message) {
                    const m = conv.last_message;
                    if (m.message_type === 'image') {
                        lastMsgText = '📷 Photo' + (m.body ? `: ${m.body}` : '');
                    } else if (m.message_type === 'sticker') {
                        lastMsgText = '🎨 Sticker';
                    } else if (m.message_type === 'video') {
                        lastMsgText = '🎥 Video' + (m.body ? `: ${m.body}` : '');
                    } else if (m.message_type === 'audio') {
                        lastMsgText = '🎵 Audio message';
                    } else if (m.message_type === 'document') {
                        lastMsgText = '📄 Document' + (m.body ? `: ${m.body}` : '');
                    } else {
                        lastMsgText = m.body || '[Media Attachment]';
                    }
                }

                let timeStr = '';
                if (conv.last_message_at) {
                    const date = new Date(conv.last_message_at);
                    timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                let avatarHtml = '';
                if (conv.contact.avatar_url) {
                    avatarHtml = `<img src="${window.location.origin}/${conv.contact.avatar_url}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;" loading="lazy">`;
                } else {
                    avatarHtml = `<img src="https://ui-avatars.com/api/?name=${encodeURIComponent(conv.contact.name)}&background=random&color=fff&size=128" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;" loading="lazy">`;
                }

                return `
                    <li class="chat-thread-item ${isActive}" data-id="${conv.id}">
                        <div class="chat-avatar">${avatarHtml}</div>
                        <div class="chat-thread-info">
                            <div class="chat-thread-header">
                                <h6 class="chat-thread-name text-truncate">${conv.contact.name}</h6>
                                <span class="chat-thread-time">${timeStr}</span>
                            </div>
                            <div class="chat-thread-body-wrapper">
                                <p class="chat-thread-snippet">${$('<div>').text(lastMsgText).html()}</p>
                                ${unreadBadge}
                            </div>
                        </div>
                    </li>
                `;
            });

            container.html(htmlArr.join(''));
        }

        // Debounced search conversations listener
        $('#contact-search').on('keyup input', function() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                renderConversationsList(true);
            }, 120);
        });

        // WABA filter change listener
        $('#waba-chat-filter').on('change', function() {
            fetchConversations();
        });

        // Click Conversation Thread
        $(document).on('click', '.chat-thread-item', function() {
            const id = $(this).data('id');
            if (id === activeConversationId) return;
            
            activeConversationId = id;
            $('.chat-thread-item').removeClass('active');
            $(this).addClass('active');

            // Render workspace layout and update query URL
            window.history.pushState({}, '', '/chat?conversation_id=' + activeConversationId);
            renderWorkspaceSkeleton();
            fetchMessages(activeConversationId);
            
            // Mark immediately read locally
            $(this).find('.chat-unread-badge').remove();
        });

        // Render Workspace skeleton once active chat is clicked
        function renderWorkspaceSkeleton() {
            const panel = $('#chat-workspace-panel');
            panel.empty().html(`
                <!-- Chat header -->
                <div class="chat-header">
                    <div class="chat-active-contact">
                        <div class="chat-avatar" id="active-chat-avatar">-</div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="chat-active-name mb-0" id="active-chat-name">Contact</h6>
                                <button type="button" class="btn btn-link p-0 text-muted d-flex align-items-center justify-content-center" id="active-chat-sync-dp" title="Sync Contact DP" style="line-height: 1; transition: transform 0.2s ease;">
                                    <i class="bi bi-arrow-repeat" style="font-size: 1.05rem;"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                <span class="chat-active-status" id="active-chat-status">WABA Account</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" id="chat-24hr-badge" style="font-size: 0.75rem; border-radius: var(--border-radius-pill); display: none; font-weight: 600;">
                                    <i class="bi bi-lightning-charge-fill text-success"></i> 24h Free Chat: <span id="chat-countdown-timer">--:--:--</span>
                                </span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5" id="chat-expired-badge" style="font-size: 0.75rem; border-radius: var(--border-radius-pill); display: none; font-weight: 600;">
                                    <i class="bi bi-lock-fill text-danger"></i> Window Expired (Templates Only)
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 chat-fullscreen-trigger" title="Toggle Fullscreen Chat" style="border-radius: var(--border-radius-md); font-weight: 600;">
                            <i class="bi bi-arrows-fullscreen chat-fullscreen-icon"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 fw-bold" id="trigger-template-btn">
                            <i class="bi bi-file-earmark-text"></i> Send Template
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="chat-messages-container" id="chat-messages-feed">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>

                <!-- Input area -->
                <form id="send-chat-msg-form">
                    @csrf
                    <input type="hidden" name="conversation_id" id="chat-conv-id" value="">
                    <input type="hidden" name="message_type" id="chat-msg-type" value="text">
                    <input type="hidden" name="media_id" id="chat-media-id" value="">
                    
                    <div class="chat-footer d-flex align-items-center gap-2">
                        <button type="button" class="chat-action-trigger btn-attach btn btn-outline-secondary d-flex align-items-center justify-content-center p-0" id="chat-attachment-btn" title="Attach File" style="width: 38px; height: 38px; border-radius: var(--border-radius-md); border-color: var(--border-color); flex-shrink: 0; background: transparent; color: var(--text-secondary);">
                            <i class="bi bi-paperclip" style="font-size: 1.25rem;"></i>
                        </button>
                        <div class="position-relative flex-grow-1 d-flex flex-column">
                            <!-- Attachment Preview Bar -->
                            <div class="attachment-preview-bar d-none w-100" id="attachment-preview-container">
                                <div class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded" style="border-radius: var(--border-radius-md); background-color: var(--background-color);">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <i class="bi bi-file-earmark-fill text-primary" id="preview-file-icon" style="font-size: 1.2rem;"></i>
                                        <span class="text-truncate small fw-semibold" id="preview-file-name" style="max-width: 250px; color: var(--text-primary);">filename.ext</span>
                                    </div>
                                    <button type="button" class="btn-close" id="clear-attachment-btn" style="font-size: 0.75rem;" aria-label="Remove Attachment"></button>
                                </div>
                            </div>
                            <textarea name="body" class="chat-input-field w-100" rows="1" placeholder="Type a message..." required style="resize: none;"></textarea>
                        </div>
                        <button type="submit" class="chat-action-trigger btn-send" title="Send Message">
                            <i class="bi bi-send-fill" style="font-size:1.1rem;"></i>
                        </button>
                    </div>
                </form>
            `);

            // Set conversation id value explicitly
            $('#chat-conv-id').val(activeConversationId);

            // Attach scroll submit handler
            $('#send-chat-msg-form').on('submit', function(e) {
                e.preventDefault();
                if ($('.chat-input-field').prop('disabled')) {
                    return;
                }
                sendTextMessage();
            });

            // Trigger enter submit inside textarea
            $('.chat-input-field').on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!$('.chat-input-field').prop('disabled')) {
                        $('#send-chat-msg-form').submit();
                    }
                }
            });

            // Template selector trigger
            $('#trigger-template-btn').on('click', function() {
                $('#template-conv-id').val(activeConversationId);
                $('#template_select').val('').trigger('change');
                $('#template-preview-card').addClass('d-none');
                $('#template-variables-container').addClass('d-none');
                $('#sendTemplateModal').modal('show');
            });

            // Trigger Media Picker modal
            $('#chat-attachment-btn').on('click', function() {
                if ($(this).prop('disabled')) return;
                pickerTarget = 'chat';
                openMediaPickerModal();
            });

            // Clear selected attachment
            $('#clear-attachment-btn').on('click', function() {
                clearChatAttachment();
            });

            // Sync active contact DP handler
            $('#active-chat-sync-dp').on('click', function() {
                const btn = $(this);
                btn.css('transform', 'rotate(180deg)');
                btn.prop('disabled', true);
                Notiflix.Loading.circle('Syncing display picture...');

                const activeConv = conversationsList.find(c => c.id === activeConversationId);
                if (!activeConv || !activeConv.contact_id) {
                    btn.prop('disabled', false).css('transform', 'none');
                    Notiflix.Loading.remove();
                    Notiflix.Notify.failure('Cannot resolve active contact.');
                    return;
                }

                $.ajax({
                    url: `/contacts/${activeConv.contact_id}/sync-dp`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function(response) {
                        Notiflix.Loading.remove();
                        btn.prop('disabled', false).css('transform', 'none');
                        if (response.status) {
                            Notiflix.Notify.success(response.message);
                            const updatedAvatarHtml = `<img src="${response.avatar_url}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                            $('#active-chat-avatar').html(updatedAvatarHtml);

                            activeConv.contact.avatar_url = response.avatar_url.replace(window.location.origin + '/', '');
                            
                            const threadItem = $(`.chat-thread-item[data-id="${activeConversationId}"]`);
                            if (threadItem.length) {
                                threadItem.find('.chat-avatar').html(updatedAvatarHtml);
                            }
                        }
                    },
                    error: function(xhr) {
                        Notiflix.Loading.remove();
                        btn.prop('disabled', false).css('transform', 'none');
                        let msg = 'Failed to sync display picture.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Notiflix.Notify.failure(msg);
                    }
                });
            });
        }

        // Toggles active/expired state of chat message input controls
        function toggleChatInputState(isActive) {
            const textarea = $('.chat-input-field');
            const attachBtn = $('#chat-attachment-btn');
            const sendBtn = $('.btn-send');
            
            if (isActive) {
                textarea.prop('disabled', false);
                if (textarea.attr('placeholder') !== 'Type a message...') {
                    textarea.attr('placeholder', 'Type a message...');
                }
                attachBtn.prop('disabled', false).css({
                    'pointer-events': 'auto',
                    'opacity': '1',
                    'cursor': 'pointer'
                });
                sendBtn.prop('disabled', false).css({
                    'pointer-events': 'auto',
                    'opacity': '1',
                    'cursor': 'pointer'
                });
            } else {
                textarea.prop('disabled', true);
                textarea.val('');
                textarea.attr('placeholder', 'Chat window expired. Use "Send Template" to initiate chat.');
                attachBtn.prop('disabled', true).css({
                    'pointer-events': 'none',
                    'opacity': '0.5',
                    'cursor': 'not-allowed'
                });
                sendBtn.prop('disabled', true).css({
                    'pointer-events': 'none',
                    'opacity': '0.5',
                    'cursor': 'not-allowed'
                });
                clearChatAttachment();
            }
        }

        // Clear template header media selection
        function clearTemplateHeaderMedia() {
            $('#template-header-media-id').val('');
            $('#template-header-media-btn-text').text('Select Media File');
            $('#template-header-media-clear').addClass('d-none');
        }

        // Setup Modal Transition Event Listeners
        $('#sendTemplateModal').on('hidden.bs.modal', function () {
            if (pickerTarget === 'template_header') {
                setTimeout(function() {
                    openMediaPickerModal();
                }, 350);
            }
        });

        $('#mediaPickerModal').on('hidden.bs.modal', function () {
            if (pickerTarget === 'template_header') {
                pickerTarget = 'chat'; // Reset target
                setTimeout(function() {
                    $('#sendTemplateModal').modal('show');
                }, 350);
            }
        });

        // Event handler for opening media library from template header attachment selector
        $(document).on('click', '#template-header-media-btn', function(e) {
            e.preventDefault();
            pickerTarget = 'template_header';
            $('#sendTemplateModal').modal('hide');
        });

        // Event handler for clearing selected media header attachment
        $(document).on('click', '#template-header-media-clear', function(e) {
            e.preventDefault();
            clearTemplateHeaderMedia();
        });

        // Fetch Messages for open chat with state diffing
        function fetchMessages(convId, isQuietPoll = false) {
            $.ajax({
                url: `/chat/conversations/${convId}/messages`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status && activeConversationId === convId) {
                        // Populate active contact details
                        const initials = response.conversation.contact.name.substring(0, 1).toUpperCase();
                        let activeAvatarHtml = '';
                        if (response.conversation.contact.avatar_url) {
                            activeAvatarHtml = `<img src="${window.location.origin}/${response.conversation.contact.avatar_url}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                        } else {
                            activeAvatarHtml = `<img src="https://ui-avatars.com/api/?name=${encodeURIComponent(response.conversation.contact.name)}&background=random&color=fff&size=128" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                        }
                        $('#active-chat-avatar').html(activeAvatarHtml);
                        $('#active-chat-name').text(response.conversation.contact.name);
                        
                        const statusDetails = `Connected via: <strong>${response.conversation.whatsapp_account.display_name}</strong>`;
                        $('#active-chat-status').html(statusDetails);

                        // Handle 24-hour customer service window countdown
                        if (response.window) {
                            if (response.window.active) {
                                $('#chat-expired-badge').hide();
                                $('#chat-24hr-badge').show();
                                
                                let remainingSeconds = response.window.remaining_seconds;
                                updateCountdownDisplay(remainingSeconds);
                                
                                if (!isQuietPoll || !chatCountdownInterval) {
                                    clearInterval(chatCountdownInterval);
                                    chatCountdownInterval = setInterval(function() {
                                        remainingSeconds--;
                                        if (remainingSeconds <= 0) {
                                            clearInterval(chatCountdownInterval);
                                            $('#chat-24hr-badge').hide();
                                            $('#chat-expired-badge').show();
                                            toggleChatInputState(false);
                                        } else {
                                            updateCountdownDisplay(remainingSeconds);
                                        }
                                    }, 1000);
                                }
                                toggleChatInputState(true);
                            } else {
                                clearInterval(chatCountdownInterval);
                                chatCountdownInterval = null;
                                $('#chat-24hr-badge').hide();
                                $('#chat-expired-badge').show();
                                toggleChatInputState(false);
                            }
                        } else {
                            clearInterval(chatCountdownInterval);
                            chatCountdownInterval = null;
                            $('#chat-24hr-badge').hide();
                            $('#chat-expired-badge').hide();
                            toggleChatInputState(true);
                        }

                        // Keep track of scroll height
                        const feed = $('#chat-messages-feed');
                        const isAtBottom = feed.scrollTop() + feed.innerHeight() >= feed[0].scrollHeight - 50;

                        // Render messages smartly
                        renderMessagesFeed(response.messages, isQuietPoll);

                        // If it's a first load or user was at bottom, scroll down
                        if (!isQuietPoll || isAtBottom) {
                            scrollToBottom(isQuietPoll);
                        }
                    }
                },
            });
        }

        // Update countdown timer display
        function updateCountdownDisplay(seconds) {
            const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
            const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
            const s = Math.floor(seconds % 60).toString().padStart(2, '0');
            $('#chat-countdown-timer').text(`${h}:${m}:${s}`);
        }

        // Render Chat Feed Bubbles with DOM diffing & quiet update support
        function renderMessagesFeed(messages, isQuietPoll = false) {
            const feed = $('#chat-messages-feed');
            
            const currentHash = JSON.stringify(messages.map(m => [m.id, m.status, m.updated_at, m.body, m.media_path]));
            if (isQuietPoll && lastMessagesHash[activeConversationId] === currentHash) {
                return; // Nothing changed, prevent feed re-render!
            }
            lastMessagesHash[activeConversationId] = currentHash;

            if (messages.length === 0) {
                feed.html('<div class="chat-divider">No messages. Send a message to start conversation.</div>');
                return;
            }

            let htmlArr = [];
            let lastDate = '';

            messages.forEach(function(msg) {
                const date = new Date(msg.created_at);
                const dateString = date.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                
                if (dateString !== lastDate) {
                    htmlArr.push(`<div class="chat-divider">${dateString}</div>`);
                    lastDate = dateString;
                }

                const isOutgoing = msg.type === 'outgoing';
                const wrapperClass = isOutgoing ? 'outgoing' : 'incoming';
                
                let ticks = '';
                if (isOutgoing) {
                    if (msg.status === 'read') {
                        ticks = '<i class="bi bi-check2-all status-tick read" title="Read"></i>';
                    } else if (msg.status === 'delivered') {
                        ticks = '<i class="bi bi-check2-all status-tick delivered" title="Delivered"></i>';
                    } else if (msg.status === 'sent') {
                        ticks = '<i class="bi bi-check2 status-tick" title="Sent"></i>';
                    } else if (msg.status === 'failed') {
                        ticks = '<i class="bi bi-exclamation-circle-fill status-tick failed" title="Failed"></i>';
                    } else {
                        ticks = '<i class="bi bi-clock status-tick text-muted" title="Sending..."></i>';
                    }
                }

                const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                let messageBody = '';
                if (msg.media_path) {
                    const mediaUrl = window.location.origin + '/' + msg.media_path;
                    let mediaHtml = '';
                    
                    const isSticker = msg.message_type === 'sticker' || (/\.webp$/i.test(msg.media_path) && msg.message_type !== 'image');
                    const isImg = (msg.message_type === 'image' || /\.(jpeg|jpg|gif|png|webp)$/i.test(msg.media_path)) && !isSticker;
                    const isVid = msg.message_type === 'video' || /\.(mp4|webm|ogg|3gp)$/i.test(msg.media_path);
                    const isAud = msg.message_type === 'audio' || /\.(mp3|wav|ogg|m4a|aac|opus)$/i.test(msg.media_path);
                    
                    if (isSticker) {
                        const stickerFileName = msg.media_path.split('/').pop() || 'whatsapp_sticker.webp';
                        mediaHtml = `
                            <div class="position-relative d-inline-block text-start mb-1">
                                <img src="${mediaUrl}" class="img-fluid media-preview-trigger" style="max-height: 160px; cursor: pointer; object-fit: contain; display: block;" data-media-url="${mediaUrl}" data-media-type="image" data-media-title="WhatsApp Sticker" title="Click to view sticker">
                                <a href="${mediaUrl}" download="${stickerFileName}" class="btn btn-sm btn-light border py-1 px-2 mt-1 d-inline-flex align-items-center gap-1 shadow-sm" style="font-size: 0.72rem; border-radius: var(--border-radius-pill); font-weight: 600; text-decoration: none;">
                                    <i class="bi bi-download text-primary"></i> Download Sticker
                                </a>
                            </div>
                        `;
                    } else if (isImg) {
                        mediaHtml = `<div class="mb-1"><img src="${mediaUrl}" class="img-fluid rounded media-preview-trigger" style="max-height: 220px; cursor: pointer; object-fit: cover; display: block; transition: opacity 0.2s ease;" data-media-url="${mediaUrl}" data-media-type="image" data-media-title="${$('<div>').text(msg.body || 'Photo Preview').html()}" title="Click to open popup preview"></div>`;
                    } else if (isVid) {
                        mediaHtml = `<div class="mb-1"><video src="${mediaUrl}" controls class="img-fluid rounded" style="max-height: 220px; max-width: 100%; display: block;"></video></div>`;
                    } else if (isAud) {
                        mediaHtml = `<div class="mb-1"><audio src="${mediaUrl}" controls style="max-width: 100%; display: block;"></audio></div>`;
                    } else {
                        const filename = msg.body || 'Attached Document';
                        const docName = msg.message_type === 'template' ? msg.media_path.split('/').pop() : filename;
                        mediaHtml = `
                            <a href="${mediaUrl}" download="${docName}" target="_blank" class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none mb-1" style="background-color: var(--background-color); border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); min-width: 180px; display: inline-flex;">
                                <i class="bi bi-file-earmark-arrow-down-fill text-primary" style="font-size: 1.5rem;"></i>
                                <div class="overflow-hidden" style="text-align: left;">
                                    <div class="text-truncate small fw-semibold text-primary" style="max-width: 180px;">${$('<div>').text(docName).html()}</div>
                                    <div class="text-muted" style="font-size: 0.65rem;">Download Document</div>
                                </div>
                            </a>
                        `;
                    }
                    
                    messageBody = mediaHtml;
                    if (msg.body && !isImg && !isVid && !isAud) {
                        let textBody = $('<div>').text(msg.body).html().replace(/\n/g, '<br>');
                        messageBody += `<div class="mt-2 text-wrap">${textBody}</div>`;
                    } else if (msg.body && (isImg || isVid || isAud)) {
                        let textBody = $('<div>').text(msg.body).html().replace(/\n/g, '<br>');
                        messageBody += `<div class="mt-1 small text-wrap">${textBody}</div>`;
                    }
                } else if (['image', 'video', 'audio', 'document'].includes(msg.message_type)) {
                    let iconClass = 'bi-file-earmark-fill';
                    let label = 'Media Attachment';
                    if (msg.message_type === 'image') { iconClass = 'bi-image'; label = 'Image Attachment'; }
                    else if (msg.message_type === 'video') { iconClass = 'bi-camera-reels'; label = 'Video Attachment'; }
                    else if (msg.message_type === 'audio') { iconClass = 'bi-file-music'; label = 'Audio Message'; }
                    else if (msg.message_type === 'document') { iconClass = 'bi-file-earmark-text'; label = 'Document Attachment'; }

                    let mediaHtml = `
                        <div class="d-flex align-items-center gap-2 p-2 rounded mb-1" style="background-color: rgba(0,0,0,0.05); border: 1px dashed var(--border-color);">
                            <i class="bi ${iconClass} text-primary" style="font-size: 1.2rem;"></i>
                            <span class="small fw-semibold text-secondary">${label}</span>
                        </div>
                    `;
                    messageBody = mediaHtml;
                    if (msg.body) {
                        let textBody = $('<div>').text(msg.body).html().replace(/\n/g, '<br>');
                        messageBody += `<div class="mt-1 text-wrap">${textBody}</div>`;
                    }
                } else {
                    const rawContent = msg.body || '';
                    if (rawContent.trim() === '') {
                        messageBody = '<span class="text-muted fst-italic small">[Empty Message]</span>';
                    } else {
                        messageBody = $('<div>').text(rawContent).html().replace(/\n/g, '<br>');
                    }
                }

                htmlArr.push(`
                    <div class="chat-bubble-wrapper ${wrapperClass}" id="msg-bubble-${msg.id}">
                        <div class="chat-bubble">
                            <div class="chat-body">${messageBody}</div>
                            <div class="chat-meta">
                                <span>${timeStr}</span>
                                <span id="msg-tick-${msg.id}">${ticks}</span>
                            </div>
                        </div>
                    </div>
                `);
            });

            // Fast innerHTML update
            feed.html(htmlArr.join(''));
        }

        // Smooth Scroll feed to bottom
        function scrollToBottom(smooth = false) {
            const feed = $('#chat-messages-feed');
            if (feed.length) {
                if (smooth && feed[0].scrollTo) {
                    feed[0].scrollTo({ top: feed[0].scrollHeight, behavior: 'smooth' });
                } else {
                    feed.scrollTop(feed[0].scrollHeight);
                }
            }
        }

        // Optimistic Send Text Message
        function sendTextMessage() {
            const form = $('#send-chat-msg-form');
            const textarea = $('.chat-input-field');
            const body = textarea.val().trim();
            const mediaId = $('#chat-media-id').val();
            if (!body && !mediaId) return;

            const tempId = 'temp-' + Date.now();
            const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const safeText = $('<div>').text(body).html().replace(/\n/g, '<br>');

            // Optimistic bubble rendering for instant feedback
            const optimisticBubble = `
                <div class="chat-bubble-wrapper outgoing" id="msg-bubble-${tempId}">
                    <div class="chat-bubble">
                        <div class="chat-body">${safeText}</div>
                        <div class="chat-meta">
                            <span>${timeStr}</span>
                            <span id="msg-tick-${tempId}"><i class="bi bi-clock status-tick text-muted" title="Sending..."></i></span>
                        </div>
                    </div>
                </div>
            `;
            
            const feed = $('#chat-messages-feed');
            feed.find('.chat-divider:contains("No messages")').remove();
            feed.append(optimisticBubble);
            scrollToBottom(true);

            // Clear input field immediately for React-like instant responsiveness
            const formData = form.serialize();
            textarea.val('');
            clearChatAttachment();

            $.ajax({
                url: "{{ route('chat.messages.send') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.status) {
                        $(`#msg-tick-${tempId}`).html('<i class="bi bi-check2 status-tick" title="Sent"></i>');
                        fetchMessages(activeConversationId, true);
                        fetchConversations();
                    } else {
                        $(`#msg-tick-${tempId}`).html('<i class="bi bi-exclamation-circle-fill status-tick failed" title="Failed"></i>');
                        Notiflix.Notify.failure(response.message || 'Failed to send message.');
                    }
                },
                error: function(xhr) {
                    $(`#msg-tick-${tempId}`).html('<i class="bi bi-exclamation-circle-fill status-tick failed" title="Failed"></i>');
                    let msg = 'Failed to dispatch message.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        }

        // --- Template Sender Modal Logic ---
        $('#template_select').on('change', function() {
            const selected = $(this).find('option:selected');
            const container = $('#template-variables-inputs');
            
            $('#template-preview-card').addClass('d-none');
            $('#template-variables-container').addClass('d-none');
            $('#template-header-media-container').addClass('d-none');
            container.empty();
            clearTemplateHeaderMedia();

            if (!$(this).val()) return;

            const components = selected.data('components') || [];
            let bodyText = '';
            let headerComp = null;
            
            // Extract components
            components.forEach(function(comp) {
                if (comp.type === 'BODY') {
                    bodyText = comp.text;
                } else if (comp.type === 'HEADER') {
                    headerComp = comp;
                }
            });

            // Handle header media if applicable
            if (headerComp && ['IMAGE', 'VIDEO', 'DOCUMENT'].includes(headerComp.format)) {
                const formatLabel = headerComp.format.charAt(0) + headerComp.format.slice(1).toLowerCase();
                $('#template-header-media-label').text(`Header Attachment (${formatLabel})`);
                $('#template-header-media-container').removeClass('d-none');
                $('#template-header-media-id').prop('required', true);
            } else {
                $('#template-header-media-id').prop('required', false);
            }

            if (bodyText) {
                $('#template-preview-body').text(bodyText);
                $('#template-preview-card').removeClass('d-none');

                // Extract variables
                const matches = bodyText.match(/\{\{(\d+)\}\}/g);
                if (matches) {
                    const uniqueMatches = [...new Set(matches)];
                    $('#template-variables-container').removeClass('d-none');
                    
                    uniqueMatches.forEach(function(match, idx) {
                        const num = idx + 1;
                        container.append(`
                            <div>
                                <label class="form-label fw-semibold small mb-1">Variable <span class="chat-var-badge">@{{${num}}}</span></label>
                                <input type="text" name="template_variables[]" class="form-control form-control-custom form-control-sm py-2" placeholder="Value for variable ${num}" required>
                            </div>
                        `);
                    });
                }
            }
        });

        // Form Submit: Send Template
        $('#send-template-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Dispatching template...');

            $.ajax({
                url: "{{ route('chat.messages.send') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#sendTemplateModal').modal('hide');
                        Notiflix.Notify.success('Template sent successfully!');
                        
                        fetchMessages(activeConversationId);
                        fetchConversations();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to send template.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // --- Start New Chat Modal Scripts ---
        $('#new-chat-btn').on('click', function() {
            $('#new-chat-form')[0].reset();
            $('#new-chat-existing-section').removeClass('d-none');
            $('#new-chat-new-section').addClass('d-none');
            $('#type-existing').prop('checked', true);
            $('#new_chat_contact_id').val('');
            $('#newChatModal').modal('show');
        });

        $('input[name="type"]').on('change', function() {
            const val = $(this).val();
            if (val === 'existing') {
                $('#new-chat-existing-section').removeClass('d-none');
                $('#new-chat-new-section').addClass('d-none');
                $('#new_chat_contact_id').prop('required', true);
                $('#new_chat_mobile').prop('required', false);
            } else {
                $('#new-chat-existing-section').addClass('d-none');
                $('#new-chat-new-section').removeClass('d-none');
                $('#new_chat_contact_id').prop('required', false);
                $('#new_chat_mobile').prop('required', true);
            }
        });

        $('#new-chat-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Starting conversation...');

            $.ajax({
                url: "{{ route('chat.start') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#newChatModal').modal('hide');
                        Notiflix.Notify.success(response.message);

                        activeConversationId = response.conversation_id;
                        window.history.pushState({}, '', '/chat?conversation_id=' + response.conversation_id);

                        renderWorkspaceSkeleton();
                        fetchConversations();
                        fetchMessages(activeConversationId);
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to start chat session.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // --- Start Polling and load lists ---
        fetchConversations(true);

        // Poll conversation list and active chat details every 3 seconds
        pollingInterval = setInterval(function() {
            fetchConversations();
        }, 3000);

        // Handle page visibility change to clear/restore polling interval
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(pollingInterval);
            } else {
                fetchConversations();
                pollingInterval = setInterval(function() {
                    fetchConversations();
                }, 3000);
            }
        });

        // --- Media Picker Modal Logic ---
        let pickerSelectedMediaId = null;
        let pickerSelectedMediaType = null;
        let pickerSelectedMediaName = null;

        function openMediaPickerModal() {
            pickerSelectedMediaId = null;
            pickerSelectedMediaType = null;
            pickerSelectedMediaName = null;
            $('#picker-select-btn').prop('disabled', true);
            
            try {
                // Set browse tab active
                const triggerEl = document.querySelector('#pickerTabs button[data-bs-target="#browse-pane"]');
                if (triggerEl && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            } catch (e) {
                console.error('Error switching tab:', e);
            }
            
            // Determine default filter based on target
            let defaultFilter = 'all';
            try {
                if (pickerTarget === 'template_header') {
                    const selectedOption = $('#template_select option:selected');
                    if (selectedOption.length) {
                        let components = selectedOption.data('components') || [];
                        if (typeof components === 'string') {
                            components = JSON.parse(components);
                        }
                        if (Array.isArray(components)) {
                            const headerComp = components.find(c => c.type === 'HEADER');
                            if (headerComp && headerComp.format) {
                                defaultFilter = headerComp.format.toLowerCase();
                            }
                        }
                    }
                }
            } catch (e) {
                console.error('Error parsing template components:', e);
            }
            
            // Reset filters and search input
            $('#picker-type-filters button').removeClass('active');
            $(`#picker-type-filters button[data-type="${defaultFilter}"]`).addClass('active');
            $('#picker-search').val('');

            // Load media library list
            try {
                fetchPickerMedia(defaultFilter, '');
            } catch (e) {
                console.error('Error fetching picker media:', e);
            }

            // Show modal
            try {
                $('#mediaPickerModal').modal('show');
            } catch (e) {
                console.error('Error showing media picker modal:', e);
            }
        }

        // Reset attachment fields on chat panel
        function clearChatAttachment() {
            $('#chat-msg-type').val('text');
            $('#chat-media-id').val('');
            $('#attachment-preview-container').addClass('d-none');
            $('.chat-input-field').prop('required', true);
        }

        // Handle Browse selection list
        function fetchPickerMedia(type, search) {
            const grid = $('#picker-items-grid');
            grid.html(`
                <div class="text-center py-5 w-100">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
            `);

            $.ajax({
                url: "{{ route('media.picker') }}",
                type: 'GET',
                data: {
                    type: type,
                    search: search
                },
                success: function(response) {
                    if (response.status) {
                        grid.empty();
                        if (response.media.length === 0) {
                            grid.html('<div class="text-center py-5 text-muted small w-100">No media library assets found.</div>');
                            return;
                        }

                        response.media.forEach(function(item) {
                            let previewContent = '';
                            if (item.file_type === 'image') {
                                previewContent = `<img src="${item.file_url}" alt="${item.filename}">`;
                            } else if (item.file_type === 'video') {
                                previewContent = `<i class="bi bi-film text-danger" style="font-size: 1.5rem;"></i>`;
                            } else if (item.file_type === 'audio') {
                                previewContent = `<i class="bi bi-music-note-beamed text-warning" style="font-size: 1.5rem;"></i>`;
                            } else {
                                previewContent = `<i class="bi bi-file-earmark-text text-primary" style="font-size: 1.5rem;"></i>`;
                            }

                            const card = $(`
                                <div class="col-md-3 col-sm-4 col-6">
                                    <div class="picker-item-card" data-id="${item.id}" data-type="${item.file_type}" data-filename="${item.filename}" data-fileurl="${item.file_url}">
                                        <span class="picker-item-type">${item.file_type}</span>
                                        <div class="picker-item-preview">${previewContent}</div>
                                        <div class="picker-item-info" title="${item.filename}">${item.filename}</div>
                                    </div>
                                </div>
                            `);
                            grid.append(card);
                        });
                    }
                },
                error: function() {
                    grid.html('<div class="text-center py-5 text-danger small w-100">Failed to load media.</div>');
                }
            });
        }

        // Selection in Browse Pane
        $(document).on('click', '.picker-item-card', function() {
            $('.picker-item-card').removeClass('selected');
            $(this).addClass('selected');
            
            pickerSelectedMediaId = $(this).data('id');
            pickerSelectedMediaType = $(this).data('type');
            pickerSelectedMediaName = $(this).data('filename');
            
            $('#picker-select-btn').prop('disabled', false);
        });

        // Type filter click
        $(document).on('click', '#picker-type-filters button', function() {
            $('#picker-type-filters button').removeClass('active');
            $(this).addClass('active');
            
            const type = $(this).data('type');
            const search = $('#picker-search').val();
            fetchPickerMedia(type, search);
        });

        // Search input keyup
        let pickerSearchTimeout = null;
        $(document).on('input', '#picker-search', function() {
            clearTimeout(pickerSearchTimeout);
            const search = $(this).val();
            const type = $('#picker-type-filters button.active').data('type') || 'all';
            
            pickerSearchTimeout = setTimeout(function() {
                fetchPickerMedia(type, search);
            }, 300);
        });

        function attachSelectedMediaToChat() {
            $('#chat-msg-type').val(pickerSelectedMediaType);
            $('#chat-media-id').val(pickerSelectedMediaId);

            let iconClass = 'bi-file-earmark-fill';
            if (pickerSelectedMediaType === 'image') iconClass = 'bi-image-fill';
            else if (pickerSelectedMediaType === 'video') iconClass = 'bi-film';
            else if (pickerSelectedMediaType === 'audio') iconClass = 'bi-music-note-beamed';

            $('#preview-file-icon').attr('class', 'bi ' + iconClass);
            $('#preview-file-name').text(pickerSelectedMediaName);
            $('#attachment-preview-container').removeClass('d-none');
            $('.chat-input-field').removeAttr('required');

            $('#mediaPickerModal').modal('hide');
        }

        // Select asset button trigger
        $(document).on('click', '#picker-select-btn', function() {
            if (!pickerSelectedMediaId) return;

            if (pickerTarget === 'template_header') {
                // Update template form hidden inputs
                $('#template-header-media-id').val(pickerSelectedMediaId);
                $('#template-header-media-btn-text').text(pickerSelectedMediaName);
                $('#template-header-media-clear').removeClass('d-none');
                $('#mediaPickerModal').modal('hide');
            } else if (pickerSelectedMediaType === 'image') {
                const selectedCard = $('.picker-item-card.selected');
                const fileUrl = selectedCard.attr('data-fileurl');
                if (fileUrl) {
                    openImageEditorModal(fileUrl, pickerSelectedMediaName);
                } else {
                    attachSelectedMediaToChat();
                }
            } else {
                attachSelectedMediaToChat();
            }
        });

        // Dropzone in Modal Upload Pane
        const pickerDropzone = $('#picker-dropzone');
        const pickerFileInput = $('#picker-file-input');
        const pickerProgressContainer = $('#picker-progress-container');
        const pickerProgressBar = $('#picker-upload-bar');
        const pickerProgressPercent = $('#picker-upload-percent');
        const pickerProgressFilename = $('#picker-upload-filename');

        pickerDropzone.on('click', function(e) {
            if (e.target !== pickerFileInput[0]) {
                pickerFileInput.trigger('click');
            }
        });

        pickerFileInput.on('click', function(e) {
            e.stopPropagation();
        });

        pickerFileInput.on('change', function() {
            if (this.files && this.files[0]) {
                handlePickerDirectUpload(this.files[0]);
            }
        });

        pickerDropzone.on('dragover', function(e) {
            e.preventDefault();
            pickerDropzone.addClass('dragover');
        });

        pickerDropzone.on('dragleave', function() {
            pickerDropzone.removeClass('dragover');
        });

        pickerDropzone.on('drop', function(e) {
            e.preventDefault();
            pickerDropzone.removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                pickerFileInput[0].files = files;
                handlePickerDirectUpload(files[0]);
            }
        });

        function handlePickerDirectUpload(file) {
            if (file.size > 16 * 1024 * 1024) {
                Notiflix.Notify.failure('File size exceeds the 16MB limit.');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', "{{ csrf_token() }}");

            pickerProgressContainer.removeClass('d-none');
            pickerProgressFilename.text(file.name);

            $.ajax({
                url: "{{ route('media.store') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            pickerProgressBar.width(percentComplete + '%');
                            pickerProgressPercent.text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.status) {
                        Notiflix.Notify.success('File uploaded successfully!');
                        
                        // Select this newly uploaded media item
                        pickerSelectedMediaId = response.media.id;
                        pickerSelectedMediaType = response.media.file_type;
                        pickerSelectedMediaName = response.media.filename;

                        // Click the select button to attach it automatically
                        $('#picker-select-btn').prop('disabled', false).click();
                        
                        // Hide progress bar and clear input
                        pickerProgressContainer.addClass('d-none');
                        pickerProgressBar.width('0%');
                        pickerFileInput.val('');
                    } else {
                        Notiflix.Notify.failure(response.message || 'Upload failed.');
                        pickerProgressContainer.addClass('d-none');
                    }
                },
                error: function(xhr) {
                    let msg = 'Upload connection error.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                    pickerProgressContainer.addClass('d-none');
                }
            });
        }

        // Live Chat Specific Fullscreen Mode Toggle Handler
        $(document).on('click', '.chat-fullscreen-trigger', function(e) {
            e.preventDefault();
            const chatElem = document.getElementById('chat-layout-container');
            if (!chatElem) return;
            
            const isFs = !!(document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement || $('#chat-layout-container').hasClass('is-fullscreen'));

            if (!isFs) {
                if (chatElem.requestFullscreen) {
                    chatElem.requestFullscreen().catch(() => $('#chat-layout-container').addClass('is-fullscreen'));
                } else if (chatElem.webkitRequestFullscreen) {
                    chatElem.webkitRequestFullscreen();
                } else if (chatElem.msRequestFullscreen) {
                    chatElem.msRequestFullscreen();
                } else {
                    $('#chat-layout-container').addClass('is-fullscreen');
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => $('#chat-layout-container').removeClass('is-fullscreen'));
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                $('#chat-layout-container').removeClass('is-fullscreen');
            }
        });

        $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function() {
            const isFullscreen = !!(document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement);
            if (isFullscreen) {
                $('.chat-fullscreen-icon').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
                $('.chat-fullscreen-text').text('Exit Fullscreen');
                $('#chat-layout-container').addClass('is-fullscreen');
            } else {
                $('.chat-fullscreen-icon').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
                $('.chat-fullscreen-text').text('Full Screen Chat');
                $('#chat-layout-container').removeClass('is-fullscreen');
            }
        });

        // Media Preview Lightbox Popup Handler (WhatsApp-style)
        $(document).on('click', '.media-preview-trigger', function(e) {
            e.preventDefault();
            const url = $(this).attr('data-media-url');
            const type = $(this).attr('data-media-type') || 'image';
            const title = $(this).attr('data-media-title') || 'Media Preview';

            $('#lightbox-title').text(title);
            $('#lightbox-download-btn').attr('href', url).attr('download', url.split('/').pop());

            let contentHtml = '';
            if (type === 'image') {
                contentHtml = `<img src="${url}" class="img-fluid rounded" style="max-height: 75vh; max-width: 100%; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">`;
            } else if (type === 'video') {
                contentHtml = `<video src="${url}" controls autoplay class="w-100 rounded" style="max-height: 75vh; box-shadow: 0 10px 30px rgba(0,0,0,0.6);"></video>`;
            } else if (type === 'audio') {
                contentHtml = `<audio src="${url}" controls autoplay class="w-100" style="max-width: 500px;"></audio>`;
            }

            $('#lightbox-content-container').html(contentHtml);
            $('#mediaPreviewLightboxModal').modal('show');
        });

        // --- WhatsApp Image Editor Logic ---
        let editorCanvas = document.getElementById('image-editor-canvas');
        let editorCtx = editorCanvas ? editorCanvas.getContext('2d') : null;
        let editorOriginalImg = new Image();
        let editorBaseImgUrl = '';
        let editorRotation = 0;
        let editorFlipped = false;
        let editorActiveTool = 'draw';
        let editorCurrentFilter = 'none';
        let editorUndoHistory = [];
        let editorIsDrawing = false;
        let editorSelectedEmoji = '❤️';

        function openImageEditorModal(imageUrl, filename = 'Image') {
            editorBaseImgUrl = imageUrl;
            editorRotation = 0;
            editorFlipped = false;
            editorActiveTool = 'draw';
            editorCurrentFilter = 'none';
            editorUndoHistory = [];
            $('#editor-caption-input').val('');
            $('#editor-tool-group button').removeClass('active');
            $('#editor-tool-group button[data-tool="draw"]').addClass('active');
            $('.editor-filter-opt').removeClass('active');
            $('.editor-filter-opt[data-filter="none"]').addClass('active');
            $('#editor-emoji-picker-tray').addClass('d-none');

            editorOriginalImg = new Image();
            editorOriginalImg.crossOrigin = 'Anonymous';
            editorOriginalImg.onload = function() {
                let maxW = 900;
                let maxH = 600;
                let width = editorOriginalImg.width;
                let height = editorOriginalImg.height;

                if (width > maxW) {
                    height = Math.round((height * maxW) / width);
                    width = maxW;
                }
                if (height > maxH) {
                    width = Math.round((width * maxH) / height);
                    height = maxH;
                }

                if (!editorCanvas) {
                    editorCanvas = document.getElementById('image-editor-canvas');
                }
                if (editorCanvas) {
                    editorCanvas.width = width;
                    editorCanvas.height = height;
                    editorCtx = editorCanvas.getContext('2d');
                }

                redrawEditorCanvas();
                saveEditorUndoState();

                $('#mediaPickerModal').modal('hide');
                $('#imageEditorModal').modal('show');
            };
            editorOriginalImg.src = imageUrl;
        }

        function redrawEditorCanvas() {
            if (!editorCanvas || !editorCtx || !editorOriginalImg.complete) return;

            editorCtx.save();
            editorCtx.clearRect(0, 0, editorCanvas.width, editorCanvas.height);

            // Apply Filters
            if (editorCurrentFilter === 'grayscale') editorCtx.filter = 'grayscale(100%)';
            else if (editorCurrentFilter === 'sepia') editorCtx.filter = 'sepia(100%)';
            else if (editorCurrentFilter === 'invert') editorCtx.filter = 'invert(100%)';
            else if (editorCurrentFilter === 'brightness') editorCtx.filter = 'brightness(130%) contrast(110%)';
            else editorCtx.filter = 'none';

            // Apply Rotation & Flip Transformations
            editorCtx.translate(editorCanvas.width / 2, editorCanvas.height / 2);
            editorCtx.rotate((editorRotation * Math.PI) / 180);
            if (editorFlipped) {
                editorCtx.scale(-1, 1);
            }

            let drawW = (editorRotation % 180 === 0) ? editorCanvas.width : editorCanvas.height;
            let drawH = (editorRotation % 180 === 0) ? editorCanvas.height : editorCanvas.width;

            editorCtx.drawImage(editorOriginalImg, -drawW / 2, -drawH / 2, drawW, drawH);
            editorCtx.restore();
        }

        function saveEditorUndoState() {
            if (!editorCanvas || !editorCtx) return;
            if (editorUndoHistory.length >= 25) {
                editorUndoHistory.shift();
            }
            editorUndoHistory.push(editorCtx.getImageData(0, 0, editorCanvas.width, editorCanvas.height));
        }

        function getCanvasCoordinates(e) {
            const rect = editorCanvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else if (e.originalEvent && e.originalEvent.touches && e.originalEvent.touches.length > 0) {
                clientX = e.originalEvent.touches[0].clientX;
                clientY = e.originalEvent.touches[0].clientY;
            }
            return {
                x: (clientX - rect.left) * (editorCanvas.width / rect.width),
                y: (clientY - rect.top) * (editorCanvas.height / rect.height)
            };
        }

        // Canvas Drawing Events
        $(document).on('mousedown touchstart', '#image-editor-canvas', function(e) {
            if (!editorCanvas || !editorCtx) return;
            const pos = getCanvasCoordinates(e);
            const color = $('#editor-color-picker').val();
            const size = parseInt($('#editor-brush-size').val()) || 7;

            if (editorActiveTool === 'draw') {
                saveEditorUndoState();
                editorIsDrawing = true;
                editorCtx.beginPath();
                editorCtx.moveTo(pos.x, pos.y);
                editorCtx.strokeStyle = color;
                editorCtx.lineWidth = size;
                editorCtx.lineCap = 'round';
                editorCtx.lineJoin = 'round';
            } else if (editorActiveTool === 'text') {
                const text = prompt('Enter text to place on image:');
                if (text && text.trim() !== '') {
                    saveEditorUndoState();
                    editorCtx.fillStyle = color;
                    editorCtx.font = 'bold ' + (size * 2 + 18) + 'px sans-serif';
                    editorCtx.textBaseline = 'middle';
                    editorCtx.fillText(text, pos.x, pos.y);
                }
            } else if (editorActiveTool === 'emoji') {
                if (editorSelectedEmoji) {
                    saveEditorUndoState();
                    editorCtx.font = (size * 2 + 28) + 'px sans-serif';
                    editorCtx.textBaseline = 'middle';
                    editorCtx.fillText(editorSelectedEmoji, pos.x, pos.y);
                }
            }
        });

        $(document).on('mousemove touchmove', '#image-editor-canvas', function(e) {
            if (!editorIsDrawing || editorActiveTool !== 'draw' || !editorCtx) return;
            e.preventDefault();
            const pos = getCanvasCoordinates(e);
            editorCtx.lineTo(pos.x, pos.y);
            editorCtx.stroke();
        });

        $(document).on('mouseup mouseleave touchend', '#image-editor-canvas', function() {
            editorIsDrawing = false;
        });

        // Tool Selector Buttons
        $(document).on('click', '.img-tool-btn', function() {
            $('.img-tool-btn').removeClass('active');
            $(this).addClass('active');
            editorActiveTool = $(this).attr('data-tool');
            if (editorActiveTool === 'emoji') {
                $('#editor-emoji-picker-tray').removeClass('d-none');
            } else {
                $('#editor-emoji-picker-tray').addClass('d-none');
            }
        });

        // Emoji Item Click
        $(document).on('click', '.editor-emoji-item', function() {
            editorSelectedEmoji = $(this).text();
            Notiflix.Notify.info('Selected emoji: ' + editorSelectedEmoji + '. Click canvas to place.');
        });

        // Rotate & Flip Handlers
        $(document).on('click', '#btn-editor-rotate', function() {
            saveEditorUndoState();
            editorRotation = (editorRotation + 90) % 360;
            redrawEditorCanvas();
        });

        $(document).on('click', '#btn-editor-flip', function() {
            saveEditorUndoState();
            editorFlipped = !editorFlipped;
            redrawEditorCanvas();
        });

        // Filter Handlers
        $(document).on('click', '.editor-filter-opt', function(e) {
            e.preventDefault();
            $('.editor-filter-opt').removeClass('active');
            $(this).addClass('active');
            saveEditorUndoState();
            editorCurrentFilter = $(this).attr('data-filter');
            redrawEditorCanvas();
        });

        // Undo & Reset Handlers
        $(document).on('click', '#btn-editor-undo', function() {
            if (editorUndoHistory.length > 1) {
                editorUndoHistory.pop();
                const lastState = editorUndoHistory[editorUndoHistory.length - 1];
                editorCtx.putImageData(lastState, 0, 0);
            } else if (editorUndoHistory.length === 1) {
                redrawEditorCanvas();
            }
        });

        $(document).on('click', '#btn-editor-reset', function() {
            editorRotation = 0;
            editorFlipped = false;
            editorCurrentFilter = 'none';
            editorUndoHistory = [];
            redrawEditorCanvas();
            saveEditorUndoState();
        });

        // Export & Send Edited Image
        $(document).on('click', '#btn-editor-send', function() {
            if (!editorCanvas) return;

            const btn = $(this);
            btn.prop('disabled', true);
            Notiflix.Loading.circle('Exporting & uploading edited image...');

            editorCanvas.toBlob(function(blob) {
                if (!blob) {
                    Notiflix.Loading.remove();
                    btn.prop('disabled', false);
                    Notiflix.Notify.failure('Failed to export image canvas.');
                    return;
                }

                const formData = new FormData();
                const fileName = 'edited_' + Date.now() + '.png';
                formData.append('file', blob, fileName);
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('media.store') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Notiflix.Loading.remove();
                        btn.prop('disabled', false);

                        if (response.status && response.media) {
                            $('#chat-msg-type').val('image');
                            $('#chat-media-id').val(response.media.id);

                            const caption = $('#editor-caption-input').val().trim();
                            if (caption) {
                                $('.chat-input-field').val(caption);
                            }

                            $('#preview-file-icon').attr('class', 'bi bi-image-fill');
                            $('#preview-file-name').text(response.media.filename || fileName);
                            $('#attachment-preview-container').removeClass('d-none');
                            $('.chat-input-field').removeAttr('required');

                            $('#imageEditorModal').modal('hide');
                            $('#mediaPickerModal').modal('hide');

                            setTimeout(function() {
                                $('#send-chat-msg-form').submit();
                            }, 300);
                        } else {
                            Notiflix.Notify.failure(response.message || 'Failed to upload edited image.');
                        }
                    },
                    error: function() {
                        Notiflix.Loading.remove();
                        btn.prop('disabled', false);
                        Notiflix.Notify.failure('Upload error occurred.');
                    }
                });
            }, 'image/png');
        });

        // AJAX Logout handler
        $('#logout-btn').on('click', function() {
            Notiflix.Loading.circle('Logging you out...');
            clearInterval(pollingInterval);
            $.ajax({
                url: "{{ route('logout') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        window.location.href = response.redirect_url;
                    }
                }
            });
        });
    });
</script>
@endsection
