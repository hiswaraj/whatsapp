@extends('layouts.auth')

@section('title', 'Campaign Analytics - WhatsApp SaaS Platform')

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
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom fade-in-element">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="mb-0" style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary);">{{ $campaign->name ?? 'Campaign Details' }}</h1>
                    <span class="badge" id="campaign-status-badge">Loading...</span>
                </div>
                <div class="d-flex flex-wrap gap-x-3 gap-y-1 text-muted small" style="font-size: 0.8rem;">
                    <span><i class="bi bi-whatsapp me-1"></i> WABA: <strong style="color: var(--text-secondary);">{{ $campaign->whatsappAccount->display_name ?? 'Deleted' }}</strong></span>
                    <span class="mx-2 d-none d-sm-inline">•</span>
                    <span><i class="bi bi-folder-fill me-1"></i> Target Group: <strong style="color: var(--text-secondary);">{{ $campaign->contactGroup->name ?? 'Excel / CSV Broadcast' }}</strong></span>
                    <span class="mx-2 d-none d-sm-inline">•</span>
                    <span><i class="bi bi-file-earmark-text-fill me-1"></i> Template: <strong style="color: var(--text-secondary);">{{ $campaign->template->name ?? 'Deleted' }}</strong></span>
                </div>
            </div>
            <div class="d-inline-flex gap-2">
                <div class="d-inline-flex gap-1" id="campaign-action-controls">
                    <!-- Control buttons added dynamically -->
                </div>
                <a href="{{ route('campaigns.export-logs', $campaign->id) }}" class="btn btn-success d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Export Logs
                </a>
                <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </header>

        <!-- Dynamic Sending Progress Bar -->
        <section class="card border p-3 mb-4 fade-in-element" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important; animation-delay: 0.05s;">
            @php
                $pct = $campaign->total_contacts > 0 ? round(($campaign->sent_count + $campaign->failed_count) / $campaign->total_contacts * 100) : 0;
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                <span class="fw-bold" style="color: var(--text-primary);">Total Broadcast Progress</span>
                <span class="fw-bold text-primary" id="progress-text">{{ $campaign->sent_count + $campaign->failed_count }} / {{ $campaign->total_contacts }} ({{ $pct }}%)</span>
            </div>
            <div class="progress" style="height: 8px; border-radius: var(--border-radius-pill);">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="progress-bar-el" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </section>

        <!-- KPI widgets -->
        <section class="row g-3 mb-4 fade-in-element" style="animation-delay: 0.1s;">
            <!-- Sent -->
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-primary" style="background-color: var(--input-focus-shadow); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-send-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" id="kpi-sent" style="color: var(--text-primary);">{{ $campaign->sent_count }}</h4>
                            <span class="text-muted small">Messages Sent</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Delivered -->
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-info" style="background-color: rgba(6, 182, 212, 0.1); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.45rem;">
                            <i class="bi bi-check-all"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" id="kpi-delivered" style="color: var(--text-primary);">{{ $campaign->delivered_count }}</h4>
                            <span class="text-muted small">Delivered</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Read -->
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-success" style="background-color: rgba(16, 185, 129, 0.1); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-eye-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" id="kpi-read" style="color: var(--text-primary);">{{ $campaign->read_count }}</h4>
                            <span class="text-muted small">Read/Opened</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Failed -->
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card p-3 border" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-danger" style="background-color: rgba(239, 68, 68, 0.1); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" id="kpi-failed" style="color: var(--text-primary);">{{ $campaign->failed_count }}</h4>
                            <span class="text-muted small">Failed Sends</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts & Meta Section -->
        <section class="row g-4 mb-4 fade-in-element" style="animation-delay: 0.15s;">
            <!-- Donut Chart -->
            <div class="col-lg-7 col-md-12">
                <div class="card border p-4 h-100" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <h5 class="fw-bold mb-3" style="color: var(--text-primary); font-size: 1.05rem;">Delivery Distribution</h5>
                    <div id="delivery-donut-chart" style="min-height: 280px;"></div>
                </div>
            </div>
            <!-- Campaign Metadata info -->
            <div class="col-lg-5 col-md-12">
                <div class="card border p-4 h-100" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <h5 class="fw-bold mb-3" style="color: var(--text-primary); font-size: 1.05rem;">Campaign Statistics</h5>
                    <div class="d-flex flex-column gap-3 mt-2">
                        <div class="d-flex justify-content-between pb-2 border-bottom" style="border-color: var(--border-color) !important;">
                            <span class="text-muted">Target Group Size</span>
                            <span class="fw-semibold" style="color: var(--text-primary);">{{ $campaign->total_contacts }} contacts</span>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom" style="border-color: var(--border-color) !important;">
                            <span class="text-muted">Created Date</span>
                            <span class="fw-semibold" style="color: var(--text-primary);">{{ $campaign->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom" style="border-color: var(--border-color) !important;">
                            <span class="text-muted">Delivery Success Rate</span>
                            <span class="fw-semibold text-success" id="stat-delivery-success">--%</span>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom" style="border-color: var(--border-color) !important;">
                            <span class="text-muted">Read Engagement Rate</span>
                            <span class="fw-semibold text-primary" id="stat-read-engagement">--%</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Scheduled For</span>
                            <span class="fw-semibold" style="color: var(--text-primary);">
                                @if($campaign->scheduled_at)
                                    {{ $campaign->scheduled_at->format('M d, Y H:i') }}
                                @else
                                    Immediate Execution
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Delivery Logs Table -->
        <section class="card border fade-in-element" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important; animation-delay: 0.2s;">
            <div class="card-header border-bottom py-3" style="background-color: var(--background-color); border-color: var(--border-color) !important;">
                <h5 class="fw-bold mb-0" style="color: var(--text-primary); font-size: 1.05rem;">Transmission Logs</h5>
            </div>
            <div class="card-body p-0">
                @if($messages->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history text-muted" style="font-size: 2.2rem; display: block; margin-bottom: 0.5rem;"></i>
                        <h6 class="fw-semibold mb-1" style="color: var(--text-primary);">No messages transmitted yet</h6>
                        <p class="text-muted small mb-0">Transmission logs will register once sending processes.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light border-bottom" style="background-color: var(--background-color);">
                                <tr>
                                    <th class="ps-4 py-3" style="color: var(--text-secondary);">Contact</th>
                                    <th class="py-3" style="color: var(--text-secondary);">Mobile Number</th>
                                    <th class="py-3" style="color: var(--text-secondary);">Status</th>
                                    <th class="py-3" style="color: var(--text-secondary);">Sent At</th>
                                    <th class="py-3 pe-4" style="color: var(--text-secondary);">Delivery Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($messages as $msg)
                                    <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                                        <td class="ps-4 fw-semibold" style="color: var(--text-primary);">{{ $msg->conversation->contact->name ?? 'Unknown Contact' }}</td>
                                        <td style="color: var(--text-secondary);">{{ $msg->conversation->contact->mobile_number ?? '-' }}</td>
                                        <td>
                                            @if($msg->status === 'read')
                                                <span class="badge bg-success border border-success px-2 py-0.5" style="font-size: 0.75rem;"><i class="bi bi-eye-fill me-1"></i> Read</span>
                                            @elseif($msg->status === 'delivered')
                                                <span class="badge bg-info border border-info px-2 py-0.5 text-dark" style="font-size: 0.75rem;"><i class="bi bi-check-all me-1"></i> Delivered</span>
                                            @elseif($msg->status === 'sent')
                                                <span class="badge bg-primary border border-primary px-2 py-0.5" style="font-size: 0.75rem;"><i class="bi bi-check me-1"></i> Sent</span>
                                            @elseif($msg->status === 'failed')
                                                <span class="badge bg-danger border border-danger px-2 py-0.5" style="font-size: 0.75rem;"><i class="bi bi-x-circle-fill me-1"></i> Failed</span>
                                            @else
                                                <span class="badge bg-secondary border border-secondary px-2 py-0.5" style="font-size: 0.75rem;"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $msg->created_at->format('M d, Y H:i:s') }}</td>
                                        <td class="pe-4 small text-danger" style="word-break: break-all;">
                                            @if($msg->error_message)
                                                <i class="bi bi-exclamation-triangle-fill"></i> {{ $msg->error_message }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center py-4">
                        {{ $messages->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </section>
    </main>
</div>
@endsection

@section('scripts')
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    let donutChart = null;
    let pollTimer = null;
    const campaignId = {{ $campaign->id }};
    let lastCampaignStatus = "{{ $campaign->status }}";

    $(document).ready(function() {
        // Toggle Sidebar
        $('#sidebar-toggle').on('click', function() {
            $('#dashboard-sidebar').toggleClass('show');
        });

        // Initialize Donut Chart
        initDonutChart();

        // Calculate and display initial metrics
        updateMetricsCalculations({{ $campaign->sent_count }}, {{ $campaign->delivered_count }}, {{ $campaign->read_count }}, {{ $campaign->failed_count }}, {{ $campaign->total_contacts }});

        // Render Action buttons based on campaign status
        renderActionButtons("{{ $campaign->status }}");

        // Start dynamic polling if campaign is active and sending (processing status)
        if (lastCampaignStatus === 'processing') {
            startPolling();
        }

        // Logout
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

    // Initialize ApexCharts donut
    function initDonutChart() {
        const sent = {{ $campaign->sent_count }};
        const delivered = {{ $campaign->delivered_count }};
        const read = {{ $campaign->read_count }};
        const failed = {{ $campaign->failed_count }};

        // Standard WhatsApp Meta style colors
        const colors = ['#25d366', '#34b7f1', '#ece5dd', '#ef4444']; // Read (Green), Delivered (Blue), Sent (Gray), Failed (Red)

        const options = {
            chart: {
                type: 'donut',
                height: 280,
                foreColor: 'var(--text-secondary)'
            },
            series: [read, delivered, sent, failed],
            labels: ['Read Messages', 'Delivered Messages', 'Sent (Pending receipt)', 'Failed Sends'],
            colors: colors,
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: '100%'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            legend: {
                position: 'right',
                offsetY: 40
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Dispatched',
                                color: 'var(--text-primary)',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            }
        };

        donutChart = new ApexCharts(document.querySelector("#delivery-donut-chart"), options);
        donutChart.render();
    }

    // Update Success & Read Engagement percentages
    function updateMetricsCalculations(sent, del, read, fail, total) {
        const dispatched = sent + fail;
        
        // Success rate: (sent - fail) / total (percentage of contacts where attempt succeeded or is pending)
        const successRate = total > 0 ? Math.round((sent / total) * 100) : 0;
        $('#stat-delivery-success').text(`${successRate}%`);

        // Read rate: read / sent
        const readRate = sent > 0 ? Math.round((read / sent) * 100) : 0;
        $('#stat-read-engagement').text(`${readRate}%`);
    }

    // Dynamic Polling logic (refreshes stats, progress bars, and chart every 5 seconds)
    function startPolling() {
        pollTimer = setInterval(function() {
            $.ajax({
                url: `/campaigns/${campaignId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        const camp = response.campaign;
                        
                        // Update metrics UI
                        $('#kpi-sent').text(camp.sent_count);
                        $('#kpi-delivered').text(camp.delivered_count);
                        $('#kpi-read').text(camp.read_count);
                        $('#kpi-failed').text(camp.failed_count);

                        // Update progress bar
                        const totalDispatched = camp.sent_count + camp.failed_count;
                        const pct = camp.total_contacts > 0 ? Math.round(totalDispatched / camp.total_contacts * 100) : 0;
                        
                        $('#progress-text').text(`${totalDispatched} / ${camp.total_contacts} (${pct}%)`);
                        $('#progress-bar-el').width(`${pct}%`).attr('aria-valuenow', pct);

                        // Update calculations
                        updateMetricsCalculations(camp.sent_count, camp.delivered_count, camp.read_count, camp.failed_count, camp.total_contacts);

                        // Update ApexCharts series data
                        donutChart.updateSeries([camp.read_count, camp.delivered_count, camp.sent_count, camp.failed_count]);

                        // Handle state changes
                        if (camp.status !== lastCampaignStatus) {
                            lastCampaignStatus = camp.status;
                            renderActionButtons(camp.status);
                            
                            if (camp.status === 'completed') {
                                clearInterval(pollTimer);
                                Swal.fire({
                                    title: 'Campaign Completed!',
                                    text: 'All broadcast messages have been processed.',
                                    icon: 'success',
                                    confirmButtonColor: 'var(--primary-color)',
                                    background: 'var(--card-background)',
                                    color: 'var(--text-primary)'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else if (camp.status !== 'processing') {
                                clearInterval(pollTimer);
                                Notiflix.Notify.warning(`Campaign status updated to: ${camp.status}`);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            }
                        }
                    }
                },
                error: function() {
                    console.error("Error polling campaign stats.");
                }
            });
        }, 5000);
    }

    // Render control buttons based on campaign status
    function renderActionButtons(status) {
        const container = $('#campaign-action-controls');
        container.empty();

        let badgeClass = 'bg-secondary';
        let statusText = status.toUpperCase();

        if (status === 'processing') {
            badgeClass = 'bg-primary';
            statusText = 'Active Sending';
            
            container.append(`
                <button class="btn btn-warning d-flex align-items-center gap-1 fw-semibold" onclick="triggerAction('pause')" style="border-radius: var(--border-radius-md);">
                    <i class="bi bi-pause-fill"></i> Pause
                </button>
                <button class="btn btn-danger d-flex align-items-center gap-1 fw-semibold" onclick="triggerAction('cancel')" style="border-radius: var(--border-radius-md);">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            `);
        } else if (status === 'scheduled') {
            badgeClass = 'bg-warning text-dark';
            statusText = 'Scheduled';
            
            container.append(`
                <button class="btn btn-danger d-flex align-items-center gap-1 fw-semibold" onclick="triggerAction('cancel')" style="border-radius: var(--border-radius-md);">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            `);
        } else if (status === 'paused') {
            badgeClass = 'bg-secondary';
            statusText = 'Paused';
            
            container.append(`
                <button class="btn btn-success d-flex align-items-center gap-1 fw-semibold" onclick="triggerAction('resume')" style="border-radius: var(--border-radius-md);">
                    <i class="bi bi-play-fill"></i> Resume
                </button>
                <button class="btn btn-danger d-flex align-items-center gap-1 fw-semibold" onclick="triggerAction('cancel')" style="border-radius: var(--border-radius-md);">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            `);
        } else if (status === 'completed') {
            badgeClass = 'bg-success';
            statusText = 'Completed';
        } else if (status === 'cancelled') {
            badgeClass = 'bg-light text-muted border border-secondary-subtle';
            statusText = 'Cancelled';
        } else if (status === 'failed') {
            badgeClass = 'bg-danger';
            statusText = 'Failed';
        }

        $('#campaign-status-badge')
            .removeClass()
            .addClass(`badge ${badgeClass} px-2.5 py-1.5`)
            .text(statusText);
    }

    // Call execution action endpoint
    function triggerAction(action) {
        let actionLabel = action === 'pause' ? 'pause' : (action === 'resume' ? 'resume' : 'cancel');
        
        Swal.fire({
            title: `Confirm ${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)}`,
            text: `Are you sure you want to ${actionLabel} this campaign?`,
            icon: action === 'cancel' ? 'error' : 'warning',
            showCancelButton: true,
            confirmButtonColor: action === 'cancel' ? 'var(--danger-color)' : (action === 'resume' ? 'var(--success-color)' : 'var(--secondary-color)'),
            cancelButtonColor: 'var(--secondary-color)',
            confirmButtonText: `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)}`,
            background: 'var(--card-background)',
            color: 'var(--text-primary)'
        }).then((result) => {
            if (result.isConfirmed) {
                Notiflix.Loading.pulse('Updating campaign status...');
                $.ajax({
                    url: `/campaigns/${campaignId}/action`,
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
                            Notiflix.Notify.failure(response.message || 'Action failed.');
                        }
                    },
                    error: function(xhr) {
                        Notiflix.Loading.remove();
                        let msg = 'Connection error performing action.';
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
@endsection
