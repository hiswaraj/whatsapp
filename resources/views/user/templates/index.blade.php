@extends('layouts.auth')

@section('title', 'Templates - WhatsApp SaaS Platform')

@section('styles')
<style>
    .template-card {
        background-color: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        transition: var(--transition-normal);
        position: relative;
        overflow: hidden;
    }
    .template-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--input-focus-border);
    }
    .template-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background-color: var(--border-color);
        transition: var(--transition-normal);
    }
    .template-card.approved::before {
        background-color: var(--success-color);
    }
    .template-card.pending::before {
        background-color: var(--warning-color);
    }
    .template-card.rejected::before {
        background-color: var(--danger-color);
    }
    
    .category-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        text-transform: uppercase;
    }
    
    .category-utility {
        background-color: rgba(99, 102, 241, 0.12);
        color: #4f46e5;
    }
    
    .category-marketing {
        background-color: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }
    
    .category-authentication {
        background-color: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }

    /* WhatsApp Bubble Simulator & Phone Frame */
    .phone-container {
        width: 300px;
        height: 520px;
        background-color: #000000;
        border: 10px solid #1a1a1a;
        border-radius: 28px;
        box-shadow: var(--shadow-premium), 0 15px 30px rgba(0, 0, 0, 0.4);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        margin: 0 auto;
    }
    
    .phone-notch {
        width: 120px;
        height: 14px;
        background-color: #1a1a1a;
        border-radius: 0 0 10px 10px;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        z-index: 100;
    }
    
    .phone-status-bar {
        height: 20px;
        background-color: #075e54;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 0.8rem;
        font-size: 0.6rem;
        font-weight: 600;
        z-index: 99;
        margin-top: 1px;
    }
    
    [data-theme="dark"] .phone-status-bar {
        background-color: #1f2c34;
        color: #8696a0;
    }
    
    .phone-wa-header {
        background-color: #075e54;
        color: #ffffff;
        padding: 0.4rem 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 98;
    }
    
    [data-theme="dark"] .phone-wa-header {
        background-color: #1f2c34;
        color: #e9edef;
        border-bottom: 1px solid #2a3942;
    }
    
    .phone-wa-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #128c7e;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
    }
    
    .phone-screen {
        flex: 1;
        background-color: #efeae2;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%23e5ddd5' fill-opacity='0.4'%3E%3Cpath fill-rule='evenodd' d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM11 65c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 0c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM34 39c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 29c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm39-23c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM9 9c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 24c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z'/%3E%3C/g%3E%3C/svg%3E");
        overflow-y: auto;
        padding: 0.8rem 0.5rem;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
    }
    
    [data-theme="dark"] .phone-screen {
        background-color: #0b141a;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%231f2c34' fill-opacity='0.25'%3E%3Cpath fill-rule='evenodd' d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM11 65c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 0c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zM34 39c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 29c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm39-23c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM9 9c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 24c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z'/%3E%3C/g%3E%3C/svg%3E");
    }
    
    .wa-bubble-wrapper {
        max-width: 95%;
        width: 250px;
        position: relative;
    }
    
    .wa-bubble {
        background-color: #ffffff;
        border-radius: 8px 8px 8px 0;
        box-shadow: 0 1px 1px rgba(0,0,0,0.12);
        padding: 0.6rem 0.8rem;
        position: relative;
        color: #111b21;
        font-size: 0.88rem;
        line-height: 1.4;
    }
    
    [data-theme="dark"] .wa-bubble {
        background-color: #1f2c34;
        color: #e9edef;
    }
    
    .wa-bubble::before {
        content: "";
        position: absolute;
        bottom: 0;
        left: -8px;
        width: 0;
        height: 0;
        border-right: 8px solid #ffffff;
        border-top: 10px solid transparent;
    }
    
    [data-theme="dark"] .wa-bubble::before {
        border-right-color: #1f2c34;
    }
    
    .wa-header {
        font-weight: 700;
        margin-bottom: 0.35rem;
        font-size: 0.85rem;
        color: #111b21;
    }
    
    [data-theme="dark"] .wa-header {
        color: #f1f5f9;
    }
    
    .wa-media-placeholder {
        background-color: #e9edef;
        border-radius: 6px;
        height: 130px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        color: #667781;
        border: 1px dashed #cfd7df;
    }
    
    [data-theme="dark"] .wa-media-placeholder {
        background-color: #2a3942;
        color: #8696a0;
        border-color: #3b4a54;
    }
    
    .wa-body {
        white-space: pre-line;
        word-break: break-word;
    }
    
    .wa-var {
        background-color: rgba(16, 185, 129, 0.15);
        color: #047857;
        font-weight: 600;
        padding: 0.1rem 0.3rem;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.82rem;
    }
    
    [data-theme="dark"] .wa-var {
        background-color: rgba(16, 185, 129, 0.25);
        color: #34d399;
    }
    
    .wa-footer {
        color: #667781;
        font-size: 0.72rem;
        margin-top: 0.3rem;
        text-transform: none;
    }
    
    [data-theme="dark"] .wa-footer {
        color: #8696a0;
    }
    
    .wa-time-meta {
        font-size: 0.65rem;
        color: #8696a0;
        text-align: right;
        margin-top: 0.15rem;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 2px;
    }
    
    .wa-btn-container {
        display: flex;
        flex-direction: column;
        gap: 1px;
        margin-top: 4px;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }
    
    .wa-action-btn {
        background-color: #ffffff;
        border: none;
        border-radius: 8px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        padding: 0.6rem;
        color: #008069;
        font-weight: 500;
        font-size: 0.82rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        width: 100%;
        transition: background 0.1s ease;
    }
    
    [data-theme="dark"] .wa-action-btn {
        background-color: #1f2c34;
        color: #25d366;
    }
    
    .wa-action-btn:hover {
        background-color: #f8f9fa;
    }
    
    [data-theme="dark"] .wa-action-btn:hover {
        background-color: #2a3942;
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">

    <!-- Responsive Mobile Toggler -->
    <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle Sidebar">
        <i class="bi bi-list" style="font-size: 1.4rem;"></i>
    </button>

    <!-- Sidebar Navigation -->
    <aside class="dashboard-sidebar" id="dashboard-sidebar">
        <a href="{{ route('user.dashboard') }}" class="sidebar-brand">
            <div style="background-color: var(--primary-color); border-radius: 8px; padding: 6px; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                <i class="bi bi-whatsapp text-white" style="font-size: 1.1rem; line-height: 1;"></i>
            </div>
            <span>WhatsApp<span style="color: var(--primary-color);">SaaS</span></span>
        </a>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('user.dashboard') }}" class="sidebar-menu-link">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('contacts.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-people"></i>
                    <span>Contacts</span>
                </a>
            </li>
            <li>
                <a href="{{ route('groups.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-folder"></i>
                    <span>Contact Groups</span>
                </a>
            </li>
            <li>
                <a href="{{ route('chat.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-chat-dots"></i>
                    <span>Live Chat</span>
                </a>
            </li>
            <li>
                <a href="{{ route('wabas.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-whatsapp"></i>
                    <span>WABAs</span>
                </a>
            </li>
            <li>
                <a href="{{ route('templates.index') }}" class="sidebar-menu-link active">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Templates</span>
                </a>
            </li>
            <li>
                <a href="{{ route('campaigns.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-send"></i>
                    <span>Campaigns</span>
                </a>
            </li>
            <li>
                <a href="{{ route('media.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-image"></i>
                    <span>Media Library</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="background-color: var(--input-focus-shadow); color: var(--primary-color); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <h6 class="mb-0" style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary);">{{ Auth::user()->name }}</h6>
                    <span class="text-muted" style="font-size: 0.75rem;">Tenant Client</span>
                </div>
            </div>
            <button class="btn btn-outline-danger w-100 btn-sm py-2" id="logout-btn" style="border-radius: var(--border-radius-md); font-weight: 600;">
                Log Out
            </button>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="dashboard-main">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom fade-in-element">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">WhatsApp Templates</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Manage, sync and create approved WhatsApp message templates from Meta Cloud API</span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                @if(!$wabas->isEmpty())
                    <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#syncTemplatesModal">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Sync templates</span>
                    </button>
                    <button type="button" class="btn btn-primary-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Template</span>
                    </button>
                @endif
            </div>
        </header>

        @if($wabas->isEmpty())
            <!-- WABA Empty state alert -->
            <div class="alert alert-warning py-3 px-4 rounded-3 border-0 d-flex gap-3 align-items-center fade-in-element mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1">No Active WABA Registered</h6>
                    <p class="mb-0" style="font-size: 0.88rem;">To manage templates, you must first register and connect a WhatsApp Business Account (WABA) in the <a href="{{ route('wabas.index') }}" class="fw-semibold text-decoration-underline" style="color: inherit;">WABAs module</a>.</p>
                </div>
            </div>
        @endif

        <!-- Filter bar -->
        <section class="card p-3 border mb-4 fade-in-element" style="border-radius: var(--border-radius-md); background-color: var(--card-background);">
            <form method="GET" action="{{ route('templates.index') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold small">WhatsApp Account</label>
                        <select name="waba_id" class="form-select form-control-custom py-2" onchange="this.form.submit()">
                            <option value="">All Accounts</option>
                            @foreach($wabas as $waba)
                                <option value="{{ $waba->id }}" {{ request('waba_id') == $waba->id ? 'selected' : '' }}>
                                    {{ $waba->display_name }} ({{ substr($waba->phone_number_id, -6) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold small">Category</label>
                        <select name="category" class="form-select form-control-custom py-2" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="UTILITY" {{ request('category') == 'UTILITY' ? 'selected' : '' }}>Utility</option>
                            <option value="MARKETING" {{ request('category') == 'MARKETING' ? 'selected' : '' }}>Marketing</option>
                            <option value="AUTHENTICATION" {{ request('category') == 'AUTHENTICATION' ? 'selected' : '' }}>Authentication</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted fw-semibold small">Status</label>
                        <select name="status" class="form-select form-control-custom py-2" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                            <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted fw-semibold small">Search Templates</label>
                        <div class="input-group input-group-custom">
                            <input type="text" name="search" class="form-control form-control-custom py-2" placeholder="Search by name..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <!-- Templates Grid -->
        <section class="row g-4 fade-in-element">
            @if($templates->isEmpty())
                <div class="col-12 text-center py-4">
                    <div class="card empty-state-card border-0 mx-auto" style="max-width: 600px;">
                        <div class="empty-state-icon-wrapper">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h4 class="fw-extrabold mb-2" style="font-weight: 800; font-size: 1.35rem; color: var(--text-primary);">No Templates Found</h4>
                        <p class="text-secondary mx-auto mb-4" style="max-width: 440px; font-size: 0.95rem;">
                            Sync your message templates directly from Meta or create a custom one with headers, parameters, and action buttons.
                        </p>
                        @if(!$wabas->isEmpty())
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-toggle="modal" data-bs-target="#syncTemplatesModal">
                                    <i class="bi bi-arrow-repeat"></i> Sync templates
                                </button>
                                <button type="button" class="btn btn-primary-custom px-4 py-2" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                                    <i class="bi bi-plus-circle"></i> Create Template
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                @foreach($templates as $template)
                    <div class="col-md-6 col-xl-4">
                        <div class="card template-card h-100 p-4 {{ strtolower($template->status) }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0 text-truncate" style="max-width: 190px; color: var(--text-primary);" title="{{ $template->name }}">{{ $template->name }}</h5>
                                    <span class="text-muted" style="font-size: 0.78rem;">Language: <strong class="text-uppercase">{{ $template->language }}</strong></span>
                                </div>
                                <span class="badge category-badge category-{{ strtolower($template->category) }}">
                                    {{ $template->category }}
                                </span>
                            </div>

                            <hr class="my-2 text-muted">

                            <div class="mt-2 mb-4">
                                <div class="text-muted small uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Account</div>
                                <div style="font-size: 0.88rem; font-weight: 500; color: var(--text-primary);" class="text-truncate">
                                    <i class="bi bi-whatsapp text-success me-1"></i> {{ $template->whatsappAccount->display_name }}
                                </div>
                                
                                <div class="text-muted small uppercase fw-bold mt-2 mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Status</div>
                                <div class="d-flex align-items-center gap-1.5">
                                    @php
                                        $badgeColor = match(strtoupper($template->status)) {
                                            'APPROVED' => 'bg-success',
                                            'PENDING' => 'bg-warning text-dark',
                                            'REJECTED' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }} text-uppercase" style="font-size: 0.7rem; border-radius: 4px;">
                                        {{ $template->status }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-top d-flex gap-2 justify-content-between">
                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 px-3 preview-template-btn" 
                                    data-name="{{ $template->name }}" 
                                    data-components="{{ json_encode($template->components) }}"
                                    data-category="{{ $template->category }}"
                                    data-waba="{{ $template->whatsappAccount->display_name }}">
                                    <i class="bi bi-eye"></i> Preview
                                </button>
                                
                                <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 delete-template-btn" data-id="{{ $template->id }}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </section>
    </main>

</div>

<!-- Sync Templates Modal -->
<div class="modal fade" id="syncTemplatesModal" tabindex="-1" aria-labelledby="syncTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="sync-templates-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="syncTemplatesModalLabel">Sync WhatsApp Templates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sync_waba_id" class="form-label fw-semibold">Select WhatsApp Account</label>
                        <select name="whatsapp_account_id" id="sync_waba_id" class="form-select form-control-custom" required>
                            @foreach($wabas as $waba)
                                <option value="{{ $waba->id }}">{{ $waba->display_name }} (WABA ID: {{ $waba->whatsapp_business_account_id }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">This will fetch all templates from Meta and sync them locally. Stale templates deleted from Meta will also be cleaned up.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Start Sync</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template Preview Modal -->
<div class="modal fade" id="previewTemplateModal" tabindex="-1" aria-labelledby="previewTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="previewTemplateModalTitle">Template Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <!-- Phone Mock Container -->
                <div class="phone-container">
                    <div class="phone-notch"></div>
                    <div class="phone-status-bar">
                        <span>12:00</span>
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-wifi" style="font-size:0.6rem;"></i>
                            <i class="bi bi-battery-full" style="font-size:0.6rem;"></i>
                        </div>
                    </div>
                    <div class="phone-wa-header">
                        <i class="bi bi-arrow-left me-1"></i>
                        <div class="phone-wa-avatar">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div>
                            <div class="mb-0 text-white" id="preview-phone-waba-name" style="line-height:1.1; font-size:0.75rem;">WhatsApp SaaS</div>
                            <span style="font-size:0.55rem; font-weight:normal; opacity:0.85;">online</span>
                        </div>
                    </div>
                    <div class="phone-screen">
                        <div class="wa-bubble-wrapper">
                            <div class="wa-bubble">
                                <div class="wa-header" id="preview-header-val"></div>
                                <div class="wa-media-placeholder d-none" id="preview-media-val">
                                    <i class="bi bi-image" style="font-size: 1.5rem;"></i>
                                    <span class="small mt-1" style="font-size: 0.7rem;">Header Media</span>
                                </div>
                                <div class="wa-body" id="preview-body-val"></div>
                                <div class="wa-footer d-none" id="preview-footer-val"></div>
                                <div class="wa-time-meta">
                                    <span>12:00 PM</span>
                                    <i class="bi bi-check2-all text-primary" style="font-size: 0.85rem; line-height: 1;"></i>
                                </div>
                            </div>
                            <div class="wa-btn-container" id="preview-buttons-val"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1" aria-labelledby="createTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="create-template-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createTemplateModalLabel">Create Custom Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Builder Form Column -->
                        <div class="col-lg-7 border-end pr-lg-4">
                            <h5 class="fw-bold mb-3" style="font-size: 1rem; color: var(--text-primary);">Template Specifications</h5>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="waba_id_select" class="form-label fw-semibold">WhatsApp Account</label>
                                    <select name="whatsapp_account_id" id="waba_id_select" class="form-select form-control-custom" required>
                                        @foreach($wabas as $waba)
                                            <option value="{{ $waba->id }}">{{ $waba->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="category_select" class="form-label fw-semibold">Category</label>
                                    <select name="category" id="category_select" class="form-select form-control-custom" required>
                                        <option value="MARKETING">Marketing</option>
                                        <option value="UTILITY" selected>Utility</option>
                                        <option value="AUTHENTICATION">Authentication</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="name_input" class="form-label fw-semibold">Template Name</label>
                                    <input type="text" name="name" id="name_input" class="form-control form-control-custom" placeholder="e.g. order_confirmation" required>
                                    <div class="form-text">Only lowercase letters, numbers, and underscores are allowed.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="lang_select" class="form-label fw-semibold">Language</label>
                                    <select name="language" id="lang_select" class="form-select form-control-custom" required>
                                        <option value="en_US" selected>English (US) - en_US</option>
                                        <option value="en_GB">English (UK) - en_GB</option>
                                        <option value="es_ES">Spanish (Spain) - es_ES</option>
                                        <option value="pt_BR">Portuguese (Brazil) - pt_BR</option>
                                        <option value="hi_IN">Hindi (India) - hi_IN</option>
                                        <option value="de_DE">German - de_DE</option>
                                        <option value="fr_FR">French - fr_FR</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Header Section -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Header <span class="text-muted fw-normal">(Optional)</span></label>
                                    <select id="header_type" class="form-select form-select-sm" style="width: auto; font-size: 0.8rem; border-radius: 4px; padding: 0.2rem 1.5rem 0.2rem 0.5rem;">
                                        <option value="none">None</option>
                                        <option value="TEXT">Text</option>
                                        <option value="IMAGE">Image</option>
                                        <option value="VIDEO">Video</option>
                                        <option value="DOCUMENT">Document</option>
                                        <option value="LOCATION">Location</option>
                                    </select>
                                </div>
                                <div id="header_text_wrapper" class="d-none">
                                    <input type="text" id="header_text" class="form-control form-control-custom" placeholder="e.g. Thank you for your order!" maxlength="60">
                                    <div class="form-text d-flex justify-content-between">
                                        <span>Can include variables (e.g. Hello @{{1}}). Max 60 characters.</span>
                                        <span id="header_text_count">0/60</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Body Section -->
                            <div class="mb-3">
                                <label for="body_text" class="form-label fw-bold">Body Text <span class="text-danger">*</span></label>
                                <textarea id="body_text" class="form-control form-control-custom" rows="4" placeholder="Enter message body..." required></textarea>
                                <div class="form-text d-flex justify-content-between">
                                    <span>Use double curly brackets for variables like @{{1}}, @{{2}}, etc. Max 1024 characters.</span>
                                    <span id="body_text_count">0/1024</span>
                                </div>
                            </div>

                            <!-- Footer Section -->
                            <div class="mb-3">
                                <label for="footer_text" class="form-label fw-bold">Footer Text <span class="text-muted fw-normal">(Optional)</span></label>
                                <input type="text" id="footer_text" class="form-control form-control-custom" placeholder="e.g. Reply STOP to opt out" maxlength="60">
                                <div class="form-text d-flex justify-content-between">
                                    <span>Displayed as smaller muted text. Max 60 characters.</span>
                                    <span id="footer_text_count">0/60</span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Buttons Section -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Buttons <span class="text-muted fw-normal">(Optional)</span></label>
                                    <select id="button_type" class="form-select form-select-sm" style="width: auto; font-size: 0.8rem; border-radius: 4px; padding: 0.2rem 1.5rem 0.2rem 0.5rem;">
                                        <option value="none">None</option>
                                        <option value="QUICK_REPLY">Quick Reply Buttons</option>
                                        <option value="CTA">Call to Action Buttons</option>
                                    </select>
                                </div>

                                <!-- Quick Reply Builder -->
                                <div id="quick_replies_wrapper" class="d-none">
                                    <div id="quick_replies_container" class="d-flex flex-column gap-2 mb-2">
                                        <!-- Dynamic inputs inserted here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add_quick_reply_btn">
                                        <i class="bi bi-plus-circle"></i> Add Quick Reply (Max 10)
                                    </button>
                                </div>

                                <!-- CTA Builder -->
                                <div id="cta_wrapper" class="d-none">
                                    <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--background-color);">
                                        
                                        <!-- CTA Phone -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="cta_phone_enable">
                                                <label class="form-check-label fw-semibold" for="cta_phone_enable">Call Phone Number</label>
                                            </div>
                                            <div class="row g-2 d-none" id="cta_phone_inputs">
                                                <div class="col-sm-5">
                                                    <input type="text" id="cta_phone_text" class="form-control form-control-custom form-control-sm py-1.5" placeholder="Button Text (e.g. Call Us)" maxlength="25">
                                                </div>
                                                <div class="col-sm-7">
                                                    <input type="text" id="cta_phone_value" class="form-control form-control-custom form-control-sm py-1.5" placeholder="Phone Number (e.g. +15550192831)">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CTA URL -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="cta_url_enable">
                                                <label class="form-check-label fw-semibold" for="cta_url_enable">Visit Website</label>
                                            </div>
                                            <div class="row g-2 d-none" id="cta_url_inputs">
                                                <div class="col-sm-5">
                                                    <input type="text" id="cta_url_text" class="form-control form-control-custom form-control-sm py-1.5" placeholder="Button Text (e.g. Visit Website)" maxlength="25">
                                                </div>
                                                <div class="col-sm-7">
                                                    <input type="text" id="cta_url_value" class="form-control form-control-custom form-control-sm py-1.5" placeholder="URL (e.g. https://example.com/shop)">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CTA Copy Code -->
                                        <div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="cta_code_enable">
                                                <label class="form-check-label fw-semibold" for="cta_code_enable">Copy Offer Code</label>
                                            </div>
                                            <div class="row g-2 d-none" id="cta_code_inputs">
                                                <div class="col-sm-5">
                                                    <input type="text" id="cta_code_text" class="form-control form-control-custom form-control-sm py-1.5" placeholder="Button Text (e.g. Copy Code)" maxlength="25">
                                                </div>
                                                <div class="col-sm-7">
                                                    <input type="text" id="cta_code_value" class="form-control form-control-custom form-control-sm py-1.5" placeholder="Coupon Code (e.g. SUMMER25)">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Real-time Live Preview Column -->
                        <div class="col-lg-5 d-flex flex-column justify-content-start align-items-center">
                            <h5 class="fw-bold mb-3 w-100" style="font-size: 1rem; color: var(--text-primary);">WhatsApp Live Preview</h5>
                            
                            <!-- Phone Mock Container -->
                            <div class="phone-container">
                                <div class="phone-notch"></div>
                                <div class="phone-status-bar">
                                    <span>12:00</span>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-wifi" style="font-size:0.6rem;"></i>
                                        <i class="bi bi-battery-full" style="font-size:0.6rem;"></i>
                                    </div>
                                </div>
                                <div class="phone-wa-header">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    <div class="phone-wa-avatar">
                                        <i class="bi bi-whatsapp"></i>
                                    </div>
                                    <div>
                                        <div class="mb-0 text-white" id="live-phone-waba-name" style="line-height:1.1; font-size:0.75rem;">WhatsApp SaaS</div>
                                        <span style="font-size:0.55rem; font-weight:normal; opacity:0.85;">online</span>
                                    </div>
                                </div>
                                <div class="phone-screen">
                                    <div class="wa-bubble-wrapper">
                                        <div class="wa-bubble">
                                            <div class="wa-header d-none" id="live-header-val"></div>
                                            <div class="wa-media-placeholder d-none" id="live-media-val">
                                                <i class="bi bi-image" id="live-media-icon" style="font-size: 1.5rem;"></i>
                                                <span class="small mt-1" id="live-media-label" style="font-size:0.7rem;">Image Placeholder</span>
                                            </div>
                                            <div class="wa-body" id="live-body-val">Hello! Begin composing your template to preview it here.</div>
                                            <div class="wa-footer d-none" id="live-footer-val"></div>
                                            <div class="wa-time-meta">
                                                <span>12:00 PM</span>
                                                <i class="bi bi-check2-all text-primary" style="font-size: 0.85rem; line-height: 1;"></i>
                                            </div>
                                        </div>
                                        <div class="wa-btn-container" id="live-buttons-val"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Create & Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle Sidebar on mobile
        $('#sidebar-toggle').on('click', function(e) {
            e.stopPropagation();
            $('#dashboard-sidebar').toggleClass('show');
        });

        // Close sidebar on document click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#dashboard-sidebar, #sidebar-toggle').length) {
                $('#dashboard-sidebar').removeClass('show');
            }
        });

        // Sync Templates Form AJAX
        $('#sync-templates-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Syncing templates from Meta Cloud API...');

            $.ajax({
                url: "{{ route('templates.sync') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#syncTemplatesModal').modal('hide');
                        Swal.fire({
                            title: 'Sync Successful',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: 'var(--primary-color)',
                            background: 'var(--card-background)',
                            color: 'var(--text-primary)'
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Synchronization failed.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Delete Template Trigger
        $('.delete-template-btn').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "Deleting this template will delete it locally and request deletion on Meta's platform. This cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger-color)',
                cancelButtonColor: 'var(--secondary-color)',
                confirmButtonText: 'Yes, delete template',
                background: 'var(--card-background)',
                color: 'var(--text-primary)'
            }).then((result) => {
                if (result.isConfirmed) {
                    Notiflix.Loading.circle('Deleting template...');
                    $.ajax({
                        url: `/templates/${id}`,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE"
                        },
                        dataType: "json",
                        success: function(response) {
                            Notiflix.Loading.remove();
                            if (response.status) {
                                Notiflix.Notify.success(response.message);
                                window.location.reload();
                            }
                        },
                        error: function(xhr) {
                            Notiflix.Loading.remove();
                            let msg = 'Failed to delete template.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Notiflix.Notify.failure(msg);
                        }
                    });
                }
            });
        });

        // Preview Existing Template
        $('.preview-template-btn').on('click', function() {
            const name = $(this).data('name');
            const components = $(this).data('components');
            
            $('#previewTemplateModalTitle').text(name);
            
            const wabaName = $(this).data('waba');
            $('#preview-phone-waba-name').text(wabaName || 'WhatsApp SaaS');

            // Reset values
            $('#preview-header-val').addClass('d-none').text('');
            $('#preview-media-val').addClass('d-none');
            $('#preview-body-val').text('');
            $('#preview-footer-val').addClass('d-none').text('');
            $('#preview-buttons-val').html('');

            // Parse components
            components.forEach(function(comp) {
                if (comp.type === 'HEADER') {
                    if (comp.format === 'TEXT') {
                        $('#preview-header-val').removeClass('d-none').text(comp.text);
                    } else {
                        $('#preview-media-val').removeClass('d-none');
                        let iconClass = 'bi-image';
                        let labelText = 'Image';
                        if (comp.format === 'VIDEO') { iconClass = 'bi-play-circle'; labelText = 'Video'; }
                        else if (comp.format === 'DOCUMENT') { iconClass = 'bi-file-earmark-pdf'; labelText = 'Document'; }
                        else if (comp.format === 'LOCATION') { iconClass = 'bi-geo-alt'; labelText = 'Location'; }
                        
                        $('#preview-media-val i').attr('class', `bi ${iconClass}`);
                        $('#preview-media-val span').text(`${labelText} Placeholder`);
                    }
                } else if (comp.type === 'BODY') {
                    // Format body to show green variables
                    let formattedBody = comp.text.replace(/\{\{(\d+)\}\}/g, '<span class="wa-var">[$1]</span>');
                    $('#preview-body-val').html(formattedBody);
                } else if (comp.type === 'FOOTER') {
                    $('#preview-footer-val').removeClass('d-none').text(comp.text);
                } else if (comp.type === 'BUTTONS') {
                    comp.buttons.forEach(function(btn) {
                        let icon = 'bi-reply';
                        if (btn.type === 'PHONE_NUMBER') icon = 'bi-telephone';
                        else if (btn.type === 'URL') icon = 'bi-box-arrow-up-right';
                        else if (btn.type === 'COPY_CODE') icon = 'bi-copy';

                        $('#preview-buttons-val').append(`
                            <button type="button" class="wa-action-btn">
                                <i class="bi ${icon}"></i> ${btn.text}
                            </button>
                        `);
                    });
                }
            });

            $('#previewTemplateModal').modal('show');
        });

        // --- Interactive Creator Builder & Live Preview updates ---
        function updateLivePreview() {
            // Header TEXT
            const headerType = $('#header_type').val();
            if (headerType === 'none') {
                $('#live-header-val').addClass('d-none').text('');
                $('#live-media-val').addClass('d-none');
            } else if (headerType === 'TEXT') {
                $('#live-media-val').addClass('d-none');
                let hText = $('#header_text').val() || '';
                // Highlight variables
                hText = hText.replace(/\{\{(\d+)\}\}/g, '<span class="wa-var">[$1]</span>');
                if (hText) {
                    $('#live-header-val').removeClass('d-none').html(hText);
                } else {
                    $('#live-header-val').removeClass('d-none').text('Header Title');
                }
            } else {
                $('#live-header-val').addClass('d-none').text('');
                $('#live-media-val').removeClass('d-none');
                let iconClass = 'bi-image';
                let labelText = 'Image';
                if (headerType === 'VIDEO') { iconClass = 'bi-play-circle'; labelText = 'Video'; }
                else if (headerType === 'DOCUMENT') { iconClass = 'bi-file-earmark-pdf'; labelText = 'Document'; }
                else if (headerType === 'LOCATION') { iconClass = 'bi-geo-alt'; labelText = 'Location'; }
                
                $('#live-media-icon').attr('class', `bi ${iconClass}`);
                $('#live-media-label').text(`${labelText} Placeholder`);
            }

            // Body
            let bText = $('#body_text').val() || 'Hello! Begin composing your template to preview it here.';
            bText = bText.replace(/\{\{(\d+)\}\}/g, '<span class="wa-var">[$1]</span>');
            $('#live-body-val').html(bText);

            // Footer
            const fText = $('#footer_text').val() || '';
            if (fText) {
                $('#live-footer-val').removeClass('d-none').text(fText);
            } else {
                $('#live-footer-val').addClass('d-none').text('');
            }

            // Buttons
            $('#live-buttons-val').html('');
            const buttonType = $('#button_type').val();
            if (buttonType === 'QUICK_REPLY') {
                $('.quick-reply-input').each(function() {
                    const text = $(this).val().trim();
                    if (text) {
                        $('#live-buttons-val').append(`
                            <button type="button" class="wa-action-btn">
                                <i class="bi bi-reply"></i> ${text}
                            </button>
                        `);
                    }
                });
            } else if (buttonType === 'CTA') {
                if ($('#cta_phone_enable').is(':checked')) {
                    const text = $('#cta_phone_text').val().trim() || 'Call Us';
                    $('#live-buttons-val').append(`
                        <button type="button" class="wa-action-btn">
                            <i class="bi bi-telephone"></i> ${text}
                        </button>
                    `);
                }
                if ($('#cta_url_enable').is(':checked')) {
                    const text = $('#cta_url_text').val().trim() || 'Visit Website';
                    $('#live-buttons-val').append(`
                        <button type="button" class="wa-action-btn">
                            <i class="bi bi-box-arrow-up-right"></i> ${text}
                        </button>
                    `);
                }
                if ($('#cta_code_enable').is(':checked')) {
                    const text = $('#cta_code_text').val().trim() || 'Copy Code';
                    $('#live-buttons-val').append(`
                        <button type="button" class="wa-action-btn">
                            <i class="bi bi-copy"></i> ${text}
                        </button>
                    `);
                }
            }
        }

        // Live preview triggers
        $('#header_type, #button_type').on('change', function() {
            updateLivePreview();
        });
        $('#header_text, #body_text, #footer_text').on('keyup input', function() {
            updateLivePreview();
        });
        $(document).on('keyup input', '.quick-reply-input', function() {
            updateLivePreview();
        });
        $('#cta_phone_enable, #cta_url_enable, #cta_code_enable').on('change', function() {
            updateLivePreview();
            updateLivePreview();
        });
        $('#cta_phone_text, #cta_url_text, #cta_code_text').on('keyup input', function() {
            updateLivePreview();
        });

        // Update WABA display name on the phone preview header
        function updateLiveWabaName() {
            const wabaName = $('#waba_id_select option:selected').text();
            $('#live-phone-waba-name').text(wabaName || 'WhatsApp SaaS');
        }

        $('#waba_id_select').on('change', function() {
            updateLiveWabaName();
        });

        $('#createTemplateModal').on('shown.bs.modal', function() {
            updateLiveWabaName();
        });

        // Dynamic element toggling
        $('#header_type').on('change', function() {
            const val = $(this).val();
            if (val === 'TEXT') {
                $('#header_text_wrapper').removeClass('d-none');
            } else {
                $('#header_text_wrapper').addClass('d-none');
            }
        });

        $('#button_type').on('change', function() {
            const val = $(this).val();
            if (val === 'QUICK_REPLY') {
                $('#quick_replies_wrapper').removeClass('d-none');
                $('#cta_wrapper').addClass('d-none');
                // Ensure at least one input
                if ($('.quick-reply-row').length === 0) {
                    addQuickReplyInput();
                }
            } else if (val === 'CTA') {
                $('#cta_wrapper').removeClass('d-none');
                $('#quick_replies_wrapper').addClass('d-none');
            } else {
                $('#quick_replies_wrapper').addClass('d-none');
                $('#cta_wrapper').addClass('d-none');
            }
        });

        // Quick Reply logic
        function addQuickReplyInput() {
            const count = $('.quick-reply-row').length;
            if (count >= 10) {
                Notiflix.Notify.warning('Maximum of 10 Quick Reply buttons allowed.');
                return;
            }
            const rowHtml = `
                <div class="row g-2 align-items-center quick-reply-row mb-1">
                    <div class="col-10">
                        <input type="text" class="form-control form-control-custom form-control-sm py-1.5 quick-reply-input" placeholder="Button Text (e.g. Talk to Sales)" maxlength="25" required>
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 delete-quick-reply-row-btn py-1.5"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
            $('#quick_replies_container').append(rowHtml);
            updateLivePreview();
        }

        $('#add_quick_reply_btn').on('click', function() {
            addQuickReplyInput();
        });

        $(document).on('click', '.delete-quick-reply-row-btn', function() {
            $(this).closest('.quick-reply-row').remove();
            updateLivePreview();
        });

        // CTA Switch logic
        $('#cta_phone_enable').on('change', function() {
            if ($(this).is(':checked')) {
                $('#cta_phone_inputs').removeClass('d-none');
                $('#cta_phone_text, #cta_phone_value').prop('required', true);
            } else {
                $('#cta_phone_inputs').addClass('d-none');
                $('#cta_phone_text, #cta_phone_value').prop('required', false).val('');
            }
        });

        $('#cta_url_enable').on('change', function() {
            if ($(this).is(':checked')) {
                $('#cta_url_inputs').removeClass('d-none');
                $('#cta_url_text, #cta_url_value').prop('required', true);
            } else {
                $('#cta_url_inputs').addClass('d-none');
                $('#cta_url_text, #cta_url_value').prop('required', false).val('');
            }
        });

        $('#cta_code_enable').on('change', function() {
            if ($(this).is(':checked')) {
                $('#cta_code_inputs').removeClass('d-none');
                $('#cta_code_text, #cta_code_value').prop('required', true);
            } else {
                $('#cta_code_inputs').addClass('d-none');
                $('#cta_code_text, #cta_code_value').prop('required', false).val('');
            }
        });

        // Character counts helper
        $('#header_text').on('input', function() {
            $('#header_text_count').text(`${$(this).val().length}/60`);
        });
        $('#body_text').on('input', function() {
            $('#body_text_count').text(`${$(this).val().length}/1024`);
        });
        $('#footer_text').on('input', function() {
            $('#footer_text_count').text(`${$(this).val().length}/60`);
        });

        // Form Submit: Create Template
        $('#create-template-form').on('submit', function(e) {
            e.preventDefault();

            // Validate Name
            const name = $('#name_input').val().trim();
            const nameRegex = /^[a-z0-9_]+$/;
            if (!nameRegex.test(name)) {
                Notiflix.Notify.failure('Template Name must contain only lowercase letters, numbers, and underscores.');
                return;
            }

            // Assemble components payload
            const components = [];

            // Header
            const headerType = $('#header_type').val();
            if (headerType !== 'none') {
                if (headerType === 'TEXT') {
                    const hText = $('#header_text').val().trim();
                    if (!hText) {
                        Notiflix.Notify.failure('Please enter Header text.');
                        return;
                    }
                    const headerObj = {
                        type: 'HEADER',
                        format: 'TEXT',
                        text: hText
                    };
                    if (hText.includes('{{1}}')) {
                        headerObj.example = { header_text: ['Example Header'] };
                    }
                    components.push(headerObj);
                } else {
                    components.push({
                        type: 'HEADER',
                        format: headerType
                    });
                }
            }

            // Body
            const bodyText = $('#body_text').val().trim();
            if (!bodyText) {
                Notiflix.Notify.failure('Please enter Body text.');
                return;
            }
            const bodyObj = {
                type: 'BODY',
                text: bodyText
            };
            
            // Extract variables matches
            const matches = bodyText.match(/\{\{(\d+)\}\}/g);
            if (matches) {
                const uniqueMatches = [...new Set(matches)];
                const bodyExamples = uniqueMatches.map((val, idx) => `Sample ${idx + 1}`);
                bodyObj.example = { body_text: [bodyExamples] };
            }
            components.push(bodyObj);

            // Footer
            const footerText = $('#footer_text').val().trim();
            if (footerText) {
                components.push({
                    type: 'FOOTER',
                    text: footerText
                });
            }

            // Buttons
            const buttonType = $('#button_type').val();
            if (buttonType === 'QUICK_REPLY') {
                const buttons = [];
                $('.quick-reply-input').each(function() {
                    const val = $(this).val().trim();
                    if (val) {
                        buttons.push({
                            type: 'QUICK_REPLY',
                            text: val
                        });
                    }
                });
                if (buttons.length > 0) {
                    components.push({
                        type: 'BUTTONS',
                        buttons: buttons
                    });
                }
            } else if (buttonType === 'CTA') {
                const buttons = [];
                
                if ($('#cta_phone_enable').is(':checked')) {
                    const btnText = $('#cta_phone_text').val().trim();
                    const btnVal = $('#cta_phone_value').val().trim();
                    if (!btnText || !btnVal) {
                        Notiflix.Notify.failure('Please enter phone button text and number details.');
                        return;
                    }
                    buttons.push({
                        type: 'PHONE_NUMBER',
                        text: btnText,
                        phone_number: btnVal
                    });
                }

                if ($('#cta_url_enable').is(':checked')) {
                    const btnText = $('#cta_url_text').val().trim();
                    const btnVal = $('#cta_url_value').val().trim();
                    if (!btnText || !btnVal) {
                        Notiflix.Notify.failure('Please enter website button text and URL details.');
                        return;
                    }
                    
                    const urlObj = {
                        type: 'URL',
                        text: btnText,
                        url: btnVal
                    };
                    if (btnVal.includes('{{1}}')) {
                        urlObj.example = ['https://example.com/details/123'];
                    }
                    buttons.push(urlObj);
                }

                if ($('#cta_code_enable').is(':checked')) {
                    const btnText = $('#cta_code_text').val().trim();
                    const btnVal = $('#cta_code_value').val().trim();
                    if (!btnText || !btnVal) {
                        Notiflix.Notify.failure('Please enter copy code button text and discount code.');
                        return;
                    }
                    buttons.push({
                        type: 'COPY_CODE',
                        text: btnText,
                        code: btnVal
                    });
                }

                if (buttons.length > 0) {
                    components.push({
                        type: 'BUTTONS',
                        buttons: buttons
                    });
                }
            }

            Notiflix.Loading.circle('Creating template...');

            const formData = {
                _token: "{{ csrf_token() }}",
                whatsapp_account_id: $('#waba_id_select').val(),
                name: name,
                category: $('#category_select').val(),
                language: $('#lang_select').val(),
                components: components
            };

            $.ajax({
                url: "{{ route('templates.store') }}",
                type: "POST",
                data: JSON.stringify(formData),
                contentType: "application/json",
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#createTemplateModal').modal('hide');
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: 'var(--primary-color)',
                            background: 'var(--card-background)',
                            color: 'var(--text-primary)'
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to create template.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // AJAX Logout handler
        $('#logout-btn').on('click', function() {
            Notiflix.Loading.circle('Logging you out...');
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
