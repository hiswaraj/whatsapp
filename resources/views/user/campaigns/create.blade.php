@extends('layouts.auth')

@section('title', 'Create Campaign - WhatsApp SaaS Platform')

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
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom fade-in-element">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Create Campaign</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Configure and launch a bulk notification broadcast</span>
            </div>
            <div>
                <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-arrow-left"></i> Back to list
                </a>
            </div>
        </header>

        <section class="row g-4 fade-in-element" style="animation-delay: 0.1s;">
            <!-- Builder Form -->
            <div class="col-lg-7 col-md-12">
                <div class="card border p-4" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <form id="create-campaign-form">
                        @csrf
                        <!-- Campaign Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">Campaign Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-custom" placeholder="e.g. June Promotional Broadcast" required>
                        </div>

                        <!-- Select WABA -->
                        <div class="mb-4">
                            <label for="whatsapp_account_id" class="form-label fw-semibold">Select Sender WABA</label>
                            <select name="whatsapp_account_id" id="whatsapp_account_id" class="form-select form-control-custom" required>
                                <option value="" selected disabled>-- Select WhatsApp Account --</option>
                                @foreach($wabas as $wb)
                                    <option value="{{ $wb->id }}">{{ $wb->display_name }} ({{ $wb->phone_number_id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Target Contact Group -->
                        <div class="mb-4">
                            <label for="contact_group_id" class="form-label fw-semibold">Target Contact Group</label>
                            <select name="contact_group_id" id="contact_group_id" class="form-select form-control-custom" required>
                                <option value="" selected disabled>-- Select Contact Group --</option>
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->id }}">{{ $grp->name }} ({{ $grp->contacts()->count() }} contacts)</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Template -->
                        <div class="mb-4">
                            <label for="template_id" class="form-label fw-semibold">Select Message Template</label>
                            <select name="template_id" id="template_id" class="form-select form-control-custom" required>
                                <option value="" selected disabled>-- Select Approved Template --</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}" data-components="{{ json_encode($tpl->components) }}">
                                        {{ $tpl->name }} ({{ $tpl->language }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dynamic Variable Inputs -->
                        <div class="mb-4 d-none" id="variables-wrapper">
                            <label class="form-label fw-semibold">Map Template Variables</label>
                            <div class="p-3 border rounded mb-2" style="background-color: var(--background-color); border-radius: var(--border-radius-md); border-color: var(--border-color) !important;">
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-info-circle-fill text-primary"></i> 
                                    Bind custom values or use tags like <code>@{{name}}</code>, <code>@{{email}}</code>, or <code>@{{mobile}}</code> to map contact properties dynamically.
                                </small>
                                <div class="d-flex flex-column gap-3" id="variables-inputs-container">
                                    <!-- Populated dynamically via JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Scheduling Toggle -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold d-block">Sending Schedule</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="schedule_type" id="schedule-immediate" value="immediate" checked>
                                <label class="form-check-label fw-semibold" for="schedule-immediate">Send Immediately</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="schedule_type" id="schedule-later" value="later">
                                <label class="form-check-label fw-semibold" for="schedule-later">Schedule for Later</label>
                            </div>

                            <div class="mt-3 d-none" id="scheduled-time-container">
                                <label for="scheduled_at" class="form-label fw-semibold small text-muted">Select Date & Time</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control form-control-custom py-2" style="max-width: 280px;">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                            <button type="submit" class="btn btn-primary-custom w-100 py-2.5" style="border-radius: var(--border-radius-md);">
                                <i class="bi bi-rocket-takeoff-fill me-2"></i> Launch Broadcast Campaign
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="col-lg-5 col-md-12">
                <div class="card border p-4" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important; position: sticky; top: 2rem;">
                    <h5 class="fw-bold mb-3" style="color: var(--text-primary);">Template Live Preview</h5>
                    
                    <!-- Simulating a Phone Chat screen preview -->
                    <div class="phone-mockup border p-3 bg-light text-dark shadow-sm" style="border-radius: 20px; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-size: cover; min-height: 380px; position: relative;">
                        <!-- Top status bar -->
                        <div class="d-flex justify-content-between text-muted mb-3" style="font-size: 0.75rem;">
                            <span>9:41</span>
                            <div>
                                <i class="bi bi-reception-4 me-1"></i>
                                <i class="bi bi-wifi me-1"></i>
                                <i class="bi bi-battery-full"></i>
                            </div>
                        </div>

                        <!-- Chat Bubbles Container -->
                        <div class="d-flex flex-column gap-2" style="height: 100%;">
                            <!-- WhatsApp Template Preview Bubble -->
                            <div class="whatsapp-bubble p-3 rounded shadow-sm ms-auto bg-white" style="max-width: 85%; border-radius: 12px 12px 0 12px !important; font-size: 0.85rem; line-height: 1.4; border: 1px solid #e1e9eb;">
                                <div class="text-secondary small fw-semibold mb-1" id="preview-header-category" style="font-size: 0.75rem;">System Notification</div>
                                <div id="preview-bubble-body" style="white-space: pre-wrap;">Select a template to view preview...</div>
                                <div class="text-end text-muted small mt-1" style="font-size: 0.65rem;">9:41 AM <i class="bi bi-check-all text-primary"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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

        // Toggle Scheduling fields
        $('input[name="schedule_type"]').on('change', function() {
            if ($(this).val() === 'later') {
                $('#scheduled-time-container').removeClass('d-none');
                $('#scheduled_at').prop('required', true);
            } else {
                $('#scheduled-time-container').addClass('d-none');
                $('#scheduled_at').prop('required', false).val('');
            }
        });

        // Handle Template selection change
        $('#template_id').on('change', function() {
            const selectedOpt = $(this).find('option:selected');
            const componentsJson = selectedOpt.data('components');
            
            if (!componentsJson) {
                $('#variables-wrapper').addClass('d-none');
                $('#preview-bubble-body').text('Select a template to view preview...');
                return;
            }

            // Find Body component
            let bodyText = '';
            componentsJson.forEach(comp => {
                if (comp.type === 'BODY') {
                    bodyText = comp.text;
                }
            });

            $('#preview-bubble-body').text(bodyText);
            $('#preview-header-category').text(selectedOpt.text().trim());

            // Extract variable numbers e.g. {{1}}, {{2}}
            const regex = /\{\{(\d+)\}\}/g;
            let match;
            const variableIds = [];
            
            while ((match = regex.exec(bodyText)) !== null) {
                const varNum = parseInt(match[1]);
                if (!variableIds.includes(varNum)) {
                    variableIds.push(varNum);
                }
            }

            // Sort variable IDs numerically
            variableIds.sort((a, b) => a - b);

            const container = $('#variables-inputs-container');
            container.empty();

            if (variableIds.length > 0) {
                $('#variables-wrapper').removeClass('d-none');

                variableIds.forEach(id => {
                    const inputHtml = `
                        <div class="row align-items-center">
                            <div class="col-sm-4">
                                <label class="form-label fw-bold mb-0 small text-muted">Variable @{{${id}}}</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="template_variables[]" class="form-control form-control-sm form-control-custom variable-input" data-id="${id}" placeholder="e.g. @{{name}} or promotion text" required>
                            </div>
                        </div>
                    `;
                    container.append(inputHtml);
                });
            } else {
                $('#variables-wrapper').addClass('d-none');
            }
        });

        // Real-time variable binding preview inside phone bubble
        $(document).on('input', '.variable-input', function() {
            const selectedOpt = $('#template_id').find('option:selected');
            const componentsJson = selectedOpt.data('components');
            if (!componentsJson) return;

            let bodyText = '';
            componentsJson.forEach(comp => {
                if (comp.type === 'BODY') {
                    bodyText = comp.text;
                }
            });

            // Replace variables in real time with input values
            $('.variable-input').each(function() {
                const id = $(this).data('id');
                const val = $(this).val() || ('{' + '{' + id + '}' + '}');
                bodyText = bodyText.replace(new RegExp('\\\\{\\\\{' + id + '\\\\}\\\\}', 'g'), val);
            });

            $('#preview-bubble-body').text(bodyText);
        });

        // Submit form via AJAX
        $('#create-campaign-form').on('submit', function(e) {
            e.preventDefault();

            Notiflix.Loading.pulse('Launching campaign...');
            const formData = $(this).serialize();

            $.ajax({
                url: "{{ route('campaigns.store') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        Notiflix.Notify.success(response.message);
                        setTimeout(function() {
                            window.location.href = response.redirect_url;
                        }, 1200);
                    } else {
                        Notiflix.Notify.failure(response.message || 'Saving campaign failed.');
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to create campaign.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });
    });
</script>
@endsection
