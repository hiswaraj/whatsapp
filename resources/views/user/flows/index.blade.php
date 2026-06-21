@extends('layouts.auth')

@section('title', 'Flow Builder - Chatbot Automated Flows')

@section('styles')
<style>
    .flow-card {
        background-color: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .flow-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
    }
    .flow-active-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: var(--border-radius-pill);
    }
    .keyword-badge {
        background-color: var(--input-focus-shadow);
        color: var(--primary-color);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid rgba(79, 70, 229, 0.15);
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">
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

        <!-- Menu placeholder -->
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
                <a href="{{ route('templates.index') }}" class="sidebar-menu-link">
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
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Chatbot Flow Builder</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Create interactive branching flows and automatic auto-replies for incoming messages</span>
            </div>
            <div>
                <a href="{{ route('flows.create') }}" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 700; background-color: var(--primary-color); border-color: var(--primary-color);">
                    <i class="bi bi-plus-lg"></i> Create Automated Flow
                </a>
            </div>
        </header>

        @if($wabas->isEmpty())
            <!-- WABA Empty state alert -->
            <div class="alert alert-warning py-3 px-4 rounded-3 border-0 d-flex gap-3 align-items-center fade-in-element mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1">No Active WABA Registered</h6>
                    <p class="mb-0" style="font-size: 0.88rem;">To manage chatbot flows, you must first register and connect a WhatsApp Business Account (WABA) in the <a href="{{ route('wabas.index') }}" class="fw-semibold text-decoration-underline" style="color: inherit;">WABAs module</a>.</p>
                </div>
            </div>
        @endif

        <!-- Filter bar -->
        <section class="card p-3 border mb-4 fade-in-element" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
            <form method="GET" action="{{ route('flows.index') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted fw-semibold small">Filter by WhatsApp Account</label>
                        <select name="waba_id" class="form-select form-control-custom py-2" onchange="this.form.submit()">
                            <option value="">All Accounts</option>
                            @foreach($wabas as $waba)
                                <option value="{{ $waba->id }}" {{ request('waba_id') == $waba->id ? 'selected' : '' }}>
                                    {{ $waba->display_name }} ({{ substr($waba->phone_number_id, -6) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </section>

        <!-- Flows Grid -->
        @if($flows->isEmpty())
            <div class="card p-5 text-center fade-in-element" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                <div class="mb-3 text-muted" style="font-size: 3rem;">
                    <i class="bi bi-diagram-3"></i>
                </div>
                <h5 class="fw-bold" style="color: var(--text-primary);">No Automated Flows Created Yet</h5>
                <p class="text-muted mx-auto" style="max-width: 480px; font-size: 0.9rem;">Chatbot flows automatically guide your customers and answer standard inquiries in real-time. Create your first flow now!</p>
                <a href="{{ route('flows.create') }}" class="btn btn-primary btn-sm py-2 px-4 mt-2" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-plus-lg me-1"></i> Start Building
                </a>
            </div>
        @else
            <div class="row g-4 fade-in-element">
                @foreach($flows as $flow)
                    <div class="col-lg-4 col-md-6 col-sm-12" id="flow-card-{{ $flow->id }}">
                        <div class="card flow-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold text-primary mb-1" style="font-size: 1.05rem;">{{ $flow->name }}</h5>
                                    <span class="text-muted small" style="font-size: 0.75rem;">Created {{ $flow->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-flow-active" type="checkbox" data-id="{{ $flow->id }}" {{ $flow->is_active ? 'checked' : '' }} style="cursor: pointer;">
                                </div>
                            </div>

                            <!-- WABA Account -->
                            <div class="mb-3">
                                <span class="d-block text-muted small fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">WhatsApp Account:</span>
                                @if($flow->whatsappAccount)
                                    <div class="d-flex align-items-center gap-1.5 small text-primary fw-semibold" style="font-size: 0.85rem; color: var(--primary-color);">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>{{ $flow->whatsappAccount->display_name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Not connected</span>
                                @endif
                            </div>

                            <!-- Keywords -->
                            <div class="mb-4">
                                <span class="d-block text-muted small fw-semibold mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Trigger Keywords:</span>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($flow->trigger_keywords as $keyword)
                                        <span class="keyword-badge">{{ $keyword }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="border-top pt-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-1.5 text-muted small">
                                    <i class="bi bi-diagram-2"></i>
                                    <span>{{ count($flow->compiled_data['nodes'] ?? []) }} Nodes</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('flows.edit', $flow->id) }}" class="btn btn-xs btn-outline-primary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-weight: 600;">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <button class="btn btn-xs btn-outline-danger delete-flow-btn" data-id="{{ $flow->id }}" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-weight: 600;">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle Sidebar
        $('#sidebar-toggle').on('click', function() {
            $('#dashboard-sidebar').toggleClass('show');
        });

        // Toggle flow active status
        $('.toggle-flow-active').on('change', function() {
            const flowId = $(this).data('id');
            const self = $(this);
            
            $.ajax({
                url: `/flows/${flowId}/toggle-status`,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        Notiflix.Notify.success(response.message);
                    } else {
                        Notiflix.Notify.failure('Failed to toggle status.');
                        self.prop('checked', !self.prop('checked'));
                    }
                },
                error: function() {
                    Notiflix.Notify.failure('Connection error.');
                    self.prop('checked', !self.prop('checked'));
                }
            });
        });

        // Delete flow
        $('.delete-flow-btn').on('click', function() {
            const flowId = $(this).data('id');
            
            Notiflix.Confirm.show(
                'Delete Flow',
                'Are you sure you want to delete this automated chatbot flow? This action is permanent.',
                'Delete',
                'Cancel',
                function() {
                    Notiflix.Loading.circle('Deleting Flow...');
                    
                    $.ajax({
                        url: `/flows/${flowId}`,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Notiflix.Loading.remove();
                            if (response.status) {
                                Notiflix.Notify.success(response.message);
                                $(`#flow-card-${flowId}`).fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.flow-card').length === 0) {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Notiflix.Notify.failure(response.message);
                            }
                        },
                        error: function() {
                            Notiflix.Loading.remove();
                            Notiflix.Notify.failure('Failed to delete flow.');
                        }
                    });
                },
                null,
                {
                    okButtonBackground: 'var(--danger-color)',
                    titleColor: 'var(--text-primary)',
                    messageColor: 'var(--text-secondary)',
                    backgroundColor: 'var(--card-background)'
                }
            );
        });

        // Logout
        $('#logout-btn').on('click', function() {
            $.ajax({
                url: "{{ route('logout') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    window.location.href = "{{ route('home') }}";
                }
            });
        });
    });
</script>
@endsection
