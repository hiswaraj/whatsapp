@extends('layouts.auth')

@section('title', 'Dashboard - WhatsApp SaaS Platform')

@section('content')
    <div class="dashboard-wrapper">

        <!-- Responsive Mobile Toggler -->
        <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle Sidebar">
            <i class="bi bi-list" style="font-size: 1.4rem;"></i>
        </button>

        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar" id="dashboard-sidebar">
            <a href="{{ route('user.dashboard') }}" class="sidebar-brand">
                <div
                    style="background-color: var(--primary-color); border-radius: 8px; padding: 6px; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    <i class="bi bi-whatsapp text-white" style="font-size: 1.1rem; line-height: 1;"></i>
                </div>
                <span>WhatsApp<span style="color: var(--primary-color);">SaaS</span></span>
            </a>

            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('user.dashboard') }}" class="sidebar-menu-link active">
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
                    <div
                        style="background-color: var(--input-focus-shadow); color: var(--primary-color); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <h6 class="mb-0" style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary);">
                            {{ Auth::user()->name }}</h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Tenant Client</span>
                    </div>
                </div>
                <button class="btn btn-outline-danger w-100 btn-sm py-2" id="logout-btn"
                    style="border-radius: var(--border-radius-md); font-weight: 600;">
                    Log Out
                </button>
            </div>
        </aside>

        <!-- Main Content Frame -->
        <main class="dashboard-main">
            <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom fade-in-element">
                <div>
                    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">
                        Dashboard</h1>
                    <span class="text-muted" style="font-size: 0.85rem;">{{ now()->format('l, d F Y') }}</span>
                </div>
                <div>
                    <!-- Extra top header content if needed -->
                </div>
            </header>

            <!-- KPI Widgets Grid -->
            <section class="row g-4 mb-5 fade-in-element" style="animation-delay: 0.1s;">
                <!-- Contacts -->
                <div class="col-sm-6 col-lg-3">
                    <div class="widget-card">
                        <div>
                            <div class="widget-label">Total Contacts</div>
                            <div class="widget-value">{{ $metrics['total_contacts'] }}</div>
                        </div>
                        <div class="widget-icon-wrapper">
                            <i class="bi bi-people" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
                <!-- Groups -->
                <div class="col-sm-6 col-lg-3">
                    <div class="widget-card">
                        <div>
                            <div class="widget-label">Total Groups</div>
                            <div class="widget-value">{{ $metrics['total_groups'] }}</div>
                        </div>
                        <div class="widget-icon-wrapper">
                            <i class="bi bi-folder" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
                <!-- Conversations -->
                <div class="col-sm-6 col-lg-3">
                    <div class="widget-card">
                        <div>
                            <div class="widget-label">Conversations</div>
                            <div class="widget-value">{{ $metrics['total_conversations'] }}</div>
                        </div>
                        <div class="widget-icon-wrapper">
                            <i class="bi bi-chat-dots" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
                <!-- Active Accounts -->
                <div class="col-sm-6 col-lg-3">
                    <div class="widget-card">
                        <div>
                            <div class="widget-label">Active WABAs</div>
                            <div class="widget-value">{{ $metrics['active_accounts'] }}</div>
                        </div>
                        <div class="widget-icon-wrapper">
                            <i class="bi bi-whatsapp" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                </div>
            </section>

            <!-- KPI Daily Statuses Grid (Today) -->
            <h4 class="mb-3 fade-in-element"
                style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); animation-delay: 0.15s;">Today's
                Messaging Telemetry</h4>
            <section class="row g-4 mb-5 fade-in-element" style="animation-delay: 0.2s;">
                <!-- Sent Today -->
                <div class="col-6 col-md-3">
                    <div class="widget-card" style="border-left: 4px solid var(--primary-color);">
                        <div>
                            <div class="widget-label" style="color: var(--primary-color);">Sent</div>
                            <div class="widget-value" style="font-size: 1.4rem;">{{ $metrics['sent_today'] }}</div>
                        </div>
                    </div>
                </div>
                <!-- Delivered Today -->
                <div class="col-6 col-md-3">
                    <div class="widget-card" style="border-left: 4px solid var(--success-color);">
                        <div>
                            <div class="widget-label" style="color: var(--success-color);">Delivered</div>
                            <div class="widget-value" style="font-size: 1.4rem;">{{ $metrics['delivered_today'] }}</div>
                        </div>
                    </div>
                </div>
                <!-- Read Today -->
                <div class="col-6 col-md-3">
                    <div class="widget-card" style="border-left: 4px solid var(--input-focus-border);">
                        <div>
                            <div class="widget-label" style="color: var(--input-focus-border);">Read</div>
                            <div class="widget-value" style="font-size: 1.4rem;">{{ $metrics['read_today'] }}</div>
                        </div>
                    </div>
                </div>
                <!-- Failed Today -->
                <div class="col-6 col-md-3">
                    <div class="widget-card" style="border-left: 4px solid var(--danger-color);">
                        <div>
                            <div class="widget-label" style="color: var(--danger-color);">Failed</div>
                            <div class="widget-value" style="font-size: 1.4rem;">{{ $metrics['failed_today'] }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Message Volume Trends Chart -->
            <section class="card border p-4 mb-5 fade-in-element"
                style="border-radius: var(--border-radius-md); background-color: var(--card-background); animation-delay: 0.25s;">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0" style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">
                            Messaging Trends</h5>
                        <span class="text-muted" style="font-size: 0.8rem;">Compare incoming and outgoing message
                            rates</span>
                    </div>
                    <!-- Chart Scale Tabs -->
                    <div class="role-tabs mb-0" style="background-color: var(--background-color);">
                        <button class="role-tab-btn active chart-tab" data-scale="daily">Daily</button>
                        <button class="role-tab-btn chart-tab" data-scale="weekly">Weekly</button>
                        <button class="role-tab-btn chart-tab" data-scale="monthly">Monthly</button>
                    </div>
                </div>
                <!-- Chart wrapper -->
                <div id="trends-chart" style="min-height: 350px; width: 100%;"></div>
            </section>

            <!-- Activity & Campaigns Grid -->
            <section class="row g-4 fade-in-element" style="animation-delay: 0.3s;">

                <!-- Recent Activity Panel -->
                <div class="col-lg-6">
                    <div class="card border p-4 h-100"
                        style="border-radius: var(--border-radius-md); background-color: var(--card-background);">
                        <h5 class="mb-4" style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Recent
                            Messaging Activity</h5>

                        @if ($recentActivity->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-chat-left-dots text-muted mb-2"
                                    style="font-size: 2.25rem; display: block;"></i>
                                <p class="text-muted mb-0" style="font-size: 0.88rem;">No messaging activity found today.
                                </p>
                            </div>
                        @else
                            <ul class="timeline-list">
                                @foreach ($recentActivity as $msg)
                                    <li class="timeline-item">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-0"
                                                style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                                                {{ $msg->type === 'incoming' ? '📥 Message from' : '📤 Message to' }}
                                                <strong>{{ $msg->conversation->contact->name ?? 'Unknown Contact' }}</strong>
                                            </h6>
                                            <span class="text-muted"
                                                style="font-size: 0.72rem;">{{ $msg->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mb-1 text-secondary" style="font-size: 0.85rem; word-break: break-all;">
                                            {{ Str::limit($msg->body ?? '[Media Message]', 60) }}
                                        </p>
                                        <div>
                                            <span class="badge"
                                                style="font-size: 0.7rem; background-color: var(--background-color); color: {{ $msg->status === 'failed' ? 'var(--danger-color)' : ($msg->status === 'read' ? 'var(--success-color)' : 'var(--text-secondary)') }}; border: 1px solid var(--border-color);">
                                                {{ ucfirst($msg->status) }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <!-- Campaign Summary Panel -->
                <div class="col-lg-6">
                    <div class="card border p-4 h-100"
                        style="border-radius: var(--border-radius-md); background-color: var(--card-background);">
                        <h5 class="mb-4" style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">
                            Campaign Performance</h5>

                        @if ($campaigns->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-send-check text-muted mb-2"
                                    style="font-size: 2.25rem; display: block;"></i>
                                <p class="text-muted mb-0" style="font-size: 0.88rem;">No active or completed campaigns
                                    found.</p>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-4">
                                @foreach ($campaigns as $camp)
                                    @php
                                        $progress =
                                            $camp->total_contacts > 0
                                                ? round(($camp->sent_count / $camp->total_contacts) * 100)
                                                : 0;
                                    @endphp
                                    <div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <h6 class="mb-0"
                                                style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                                                {{ $camp->name }}
                                            </h6>
                                            <span class="text-muted"
                                                style="font-size: 0.78rem; font-weight: 600;">{{ $progress }}%</span>
                                        </div>
                                        <div class="progress"
                                            style="height: 6px; background-color: var(--background-color);">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $progress }}%; background-color: var(--primary-color); border-radius: 4px;"
                                                aria-valuenow="{{ $progress }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1" style="font-size: 0.75rem;">
                                            <span class="text-muted">Status: <strong
                                                    style="color: var(--primary-color);">{{ ucfirst($camp->status) }}</strong></span>
                                            <span class="text-muted">Sent:
                                                {{ $camp->sent_count }}/{{ $camp->total_contacts }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </section>
        </main>

    </div>
@endsection

@section('scripts')
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        $(document).ready(function() {
            // Toggle Sidebar on mobile
            $('#sidebar-toggle').on('click', function(e) {
                e.stopPropagation();
                $('#dashboard-sidebar').toggleClass('show');
            });

            // Close sidebar on document click (outside sidebar click)
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#dashboard-sidebar, #sidebar-toggle').length) {
                    $('#dashboard-sidebar').removeClass('show');
                }
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
                            Notiflix.Notify.success(response.message);
                            window.location.href = response.redirect_url;
                        }
                    },
                    error: function() {
                        Notiflix.Loading.remove();
                        Notiflix.Notify.failure('Failed to logout. Please refresh.');
                    }
                });
            });

            // --- Chart Configuration ---
            const chartData = @json($chartData);

            const options = {
                chart: {
                    height: 350,
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Inter, sans-serif',
                    foreColor: 'var(--text-secondary)'
                },
                colors: ['#4f46e5', '#10b981'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2.5
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                series: [{
                    name: 'Outgoing Messages',
                    data: chartData.daily.sent
                }, {
                    name: 'Incoming Messages',
                    data: chartData.daily.received
                }],
                xaxis: {
                    categories: chartData.daily.labels,
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                grid: {
                    borderColor: 'var(--border-color)',
                    strokeDashArray: 4,
                    padding: {
                        left: 20,
                        right: 20
                    }
                },
                tooltip: {
                    theme: 'light',
                    x: {
                        format: 'dd MMM yyyy'
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#trends-chart"), options);
            chart.render();

            // Listen for dark/light mode toggle to update chart tooltip theme
            const observer = new MutationObserver(() => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                chart.updateOptions({
                    tooltip: {
                        theme: currentTheme === 'dark' ? 'dark' : 'light'
                    }
                });
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });

            // Handle Chart Scale switching
            $('.chart-tab').on('click', function() {
                $('.chart-tab').removeClass('active');
                $(this).addClass('active');

                const scale = $(this).data('scale');
                const dataSet = chartData[scale];

                chart.updateOptions({
                    xaxis: {
                        categories: dataSet.labels
                    }
                });

                chart.updateSeries([{
                    name: 'Outgoing Messages',
                    data: dataSet.sent
                }, {
                    name: 'Incoming Messages',
                    data: dataSet.received
                }]);
            });

        });
    </script>
@endsection
