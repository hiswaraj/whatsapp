@extends('layouts.auth')

@section('title', 'Campaign broadcasts - WhatsApp SaaS Platform')

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
                <a href="{{ route('templates.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Templates</span>
                </a>
            </li>
            <li>
                <a href="{{ route('campaigns.index') }}" class="sidebar-menu-link active">
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
        <header class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 pb-3 border-bottom fade-in-element">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Campaign Broadcasts</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Send automated template notifications to targeted customer groups</span>
            </div>
            <div class="flex-shrink-0 d-flex gap-2">
                <a href="{{ route('quick-broadcast.index') }}" class="btn btn-outline-primary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600; border-color: var(--primary-color); color: var(--primary-color);">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Quick Broadcast</span>
                </a>
                <a href="{{ route('campaigns.create') }}" class="btn btn-primary-custom d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>New Campaign</span>
                </a>
            </div>
        </header>

        <!-- KPI widgets -->
        <section class="row g-3 mb-4 fade-in-element" style="animation-delay: 0.1s;">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="background-color: var(--input-focus-shadow); color: var(--primary-color); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-send-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--text-primary);">{{ $campaigns->total() }}</h4>
                            <span class="text-muted small">Total Campaigns</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-primary" style="background-color: rgba(99, 102, 241, 0.1); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-arrow-repeat spin-element"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--text-primary);">{{ \App\Models\Campaign::where('user_id', Auth::id())->where('status', 'processing')->count() }}</h4>
                            <span class="text-muted small">Active Sending</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-success" style="background-color: rgba(16, 185, 129, 0.1); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--text-primary);">{{ \App\Models\Campaign::where('user_id', Auth::id())->where('status', 'completed')->count() }}</h4>
                            <span class="text-muted small">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-danger" style="background-color: rgba(239, 68, 68, 0.1); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--text-primary);">{{ \App\Models\Campaign::where('user_id', Auth::id())->whereIn('status', ['failed', 'cancelled'])->count() }}</h4>
                            <span class="text-muted small">Failed / Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Campaigns List Table -->
        <section class="card border fade-in-element" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important; animation-delay: 0.15s;">
            <div class="card-body p-0">
                @if($campaigns->isEmpty())
                    <div class="text-center py-5">
                        <div class="empty-state-icon-wrapper mx-auto mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-send-fill" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold" style="color: var(--text-primary);">No campaigns created</h5>
                        <p class="text-muted small mb-3">Broadcast message templates to your contact groups in one click.</p>
                        <a href="{{ route('campaigns.create') }}" class="btn btn-primary-custom btn-sm">Create First Campaign</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light border-bottom" style="background-color: var(--background-color);">
                                <tr>
                                    <th class="ps-4 py-3" style="color: var(--text-secondary);">Campaign Name</th>
                                    <th class="py-3" style="color: var(--text-secondary);">Target Group</th>
                                    <th class="py-3" style="color: var(--text-secondary);">Template</th>
                                    <th class="py-3" style="color: var(--text-secondary);">Scheduled For</th>
                                    <th class="py-3" style="color: var(--text-secondary);">Status</th>
                                    <th class="py-3" style="color: var(--text-secondary);" width="20%">Sending Progress</th>
                                    <th class="py-3 text-end pe-4" style="color: var(--text-secondary);">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($campaigns as $camp)
                                    <tr id="campaign-row-{{ $camp->id }}" class="border-bottom" style="border-color: var(--border-color) !important;">
                                        <td class="ps-4 fw-semibold" style="color: var(--text-primary);">{{ $camp->name }}</td>
                                        <td>
                                            @if($camp->contactGroup)
                                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.75rem; border-color: var(--border-color) !important;">
                                                    <i class="bi bi-folder-fill text-secondary me-1"></i> {{ $camp->contactGroup->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border px-2 py-1" style="font-size: 0.75rem; border-color: var(--primary-color) !important;">
                                                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Excel / CSV Upload
                                                </span>
                                            @endif
                                        </td>
                                        <td style="color: var(--text-secondary);">{{ $camp->template->name ?? 'Deleted' }}</td>
                                        <td class="text-muted small">
                                            @if($camp->scheduled_at)
                                                <i class="bi bi-clock me-1"></i> {{ $camp->scheduled_at->format('M d, Y H:i') }}
                                            @else
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5" style="font-size:0.7rem;">Immediate</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($camp->status === 'processing')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 align-items-center gap-1 d-inline-flex">
                                                    <span class="spinner-border spinner-border-sm text-primary spin-element" role="status" style="width: 10px; height: 10px; border-width: 1.5px;"></span> Sending
                                                </span>
                                            @elseif($camp->status === 'scheduled')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                    <i class="bi bi-calendar-event me-1"></i> Scheduled
                                                </span>
                                            @elseif($camp->status === 'paused')
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                                    <i class="bi bi-pause-fill me-1"></i> Paused
                                                </span>
                                            @elseif($camp->status === 'completed')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Completed
                                                </span>
                                            @elseif($camp->status === 'cancelled')
                                                <span class="badge bg-light text-muted border px-2 py-1">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Cancelled
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Failed
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $pct = $camp->total_contacts > 0 ? round(($camp->sent_count + $camp->failed_count) / $camp->total_contacts * 100) : 0;
                                            @endphp
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.75rem;">
                                                    <span class="text-muted font-semibold">{{ $camp->sent_count + $camp->failed_count }} / {{ $camp->total_contacts }}</span>
                                                    <span class="text-muted fw-bold">{{ $pct }}%</span>
                                                </div>
                                                <div class="progress" style="height: 5px; border-radius: var(--border-radius-pill);">
                                                    <div class="progress-bar {{ $camp->status === 'processing' ? 'progress-bar-striped progress-bar-animated' : '' }} {{ $camp->status === 'failed' ? 'bg-danger' : ($camp->status === 'completed' ? 'bg-success' : 'bg-primary') }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex gap-1">
                                                @if($camp->status === 'processing')
                                                    <button class="btn btn-sm btn-outline-secondary p-1 d-flex align-items-center justify-content-center" onclick="changeCampaignStatus({{ $camp->id }}, 'pause')" title="Pause Sending" style="width: 28px; height: 28px;">
                                                        <i class="bi bi-pause-fill" style="font-size: 1rem;"></i>
                                                    </button>
                                                @endif
                                                @if(in_array($camp->status, ['paused', 'draft']))
                                                    <button class="btn btn-sm btn-outline-success p-1 d-flex align-items-center justify-content-center" onclick="changeCampaignStatus({{ $camp->id }}, 'resume')" title="Resume Sending" style="width: 28px; height: 28px;">
                                                        <i class="bi bi-play-fill" style="font-size: 1rem;"></i>
                                                    </button>
                                                @endif
                                                @if(in_array($camp->status, ['processing', 'scheduled', 'paused']))
                                                    <button class="btn btn-sm btn-outline-danger p-1 d-flex align-items-center justify-content-center" onclick="changeCampaignStatus({{ $camp->id }}, 'cancel')" title="Cancel Broadcast" style="width: 28px; height: 28px;">
                                                        <i class="bi bi-x-circle" style="font-size: 0.95rem;"></i>
                                                    </button>
                                                @endif
                                                <a href="{{ route('campaigns.show', $camp->id) }}" class="btn btn-sm btn-outline-primary p-1 d-flex align-items-center justify-content-center" title="View Analytics" style="width: 28px; height: 28px;">
                                                    <i class="bi bi-bar-chart-fill" style="font-size: 0.9rem;"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger p-1 d-flex align-items-center justify-content-center" onclick="deleteCampaign({{ $camp->id }})" title="Delete Campaign" style="width: 28px; height: 28px;">
                                                    <i class="bi bi-trash" style="font-size: 0.9rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="d-flex justify-content-center py-4">
                        {{ $campaigns->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </section>
    </main>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle Sidebar Navigation
        $('#sidebar-toggle').on('click', function() {
            $('#dashboard-sidebar').toggleClass('show');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#dashboard-sidebar, #sidebar-toggle').length) {
                $('#dashboard-sidebar').removeClass('show');
            }
        });

        // Logout workflow
        $('#logout-btn').on('click', function() {
            Swal.fire({
                title: 'Log Out',
                text: 'Are you sure you want to end your session?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger-color)',
                cancelButtonColor: 'var(--secondary-color)',
                confirmButtonText: 'Log Out',
                background: 'var(--card-background)',
                color: 'var(--text-primary)'
            }).then((result) => {
                if (result.isConfirmed) {
                    Notiflix.Loading.pulse('Logging out...');
                    $.ajax({
                        url: "{{ route('logout') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function() {
                            window.location.href = "{{ route('home') }}";
                        },
                        error: function() {
                            Notiflix.Loading.remove();
                            Notiflix.Notify.failure('Logout failed. Reload and try again.');
                        }
                    });
                }
            });
        });
    });

    // Control campaign execution statuses
    function changeCampaignStatus(id, action) {
        let actionLabel = action === 'pause' ? 'pause' : (action === 'resume' ? 'resume' : 'cancel');
        
        Swal.fire({
            title: `Confirm ${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)}`,
            text: `Are you sure you want to ${actionLabel} this campaign broadcast?`,
            icon: action === 'cancel' ? 'error' : 'warning',
            showCancelButton: true,
            confirmButtonColor: action === 'cancel' ? 'var(--danger-color)' : (action === 'resume' ? 'var(--success-color)' : 'var(--secondary-color)'),
            cancelButtonColor: 'var(--secondary-color)',
            confirmButtonText: `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)}`,
            background: 'var(--card-background)',
            color: 'var(--text-primary)'
        }).then((result) => {
            if (result.isConfirmed) {
                Notiflix.Loading.pulse('Updating broadcast status...');
                $.ajax({
                    url: `/campaigns/${id}/action`,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        action: action
                    },
                    success: function(response) {
                        Notiflix.Loading.remove();
                        if (response.status) {
                            Notiflix.Notify.success(response.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Notiflix.Notify.failure(response.message || 'Operation failed.');
                        }
                    },
                    error: function(xhr) {
                        Notiflix.Loading.remove();
                        let msg = 'Connection error updating status.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Notiflix.Notify.failure(msg);
                    }
                });
            }
        });
    }

    // Delete Campaign
    function deleteCampaign(id) {
        Swal.fire({
            title: 'Delete Campaign',
            text: 'Are you sure you want to permanently delete this campaign? This action is irreversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--danger-color)',
            cancelButtonColor: 'var(--secondary-color)',
            confirmButtonText: 'Delete',
            background: 'var(--card-background)',
            color: 'var(--text-primary)'
        }).then((result) => {
            if (result.isConfirmed) {
                Notiflix.Loading.pulse('Deleting campaign...');
                $.ajax({
                    url: `/campaigns/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Notiflix.Loading.remove();
                        if (response.status) {
                            Notiflix.Notify.success('Campaign deleted successfully.');
                            $(`#campaign-row-${id}`).fadeOut(400, function() {
                                $(this).remove();
                                if ($('tbody tr').length === 0) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Notiflix.Notify.failure(response.message || 'Deletion failed.');
                        }
                    },
                    error: function(xhr) {
                        Notiflix.Loading.remove();
                        let msg = 'Connection error deleting campaign.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Notiflix.Notify.failure(msg);
                    }
                });
            }
        });
    }
</script>

<style>
    /* Continuous Spin animation for processing icons */
    .spin-element {
        animation: spin 1.8s linear infinite;
        display: inline-block;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection
