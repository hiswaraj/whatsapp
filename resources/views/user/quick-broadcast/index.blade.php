@extends('layouts.auth')

@section('title', 'Quick Broadcast - WhatsApp SaaS Platform')

@section('styles')
<style>
    .upload-zone {
        border: 2px dashed var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 2.5rem 1.5rem;
        text-align: center;
        background-color: var(--background-color);
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        position: relative;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--primary-color);
        background-color: var(--input-focus-shadow);
    }
    .upload-icon {
        font-size: 2.5rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
        display: block;
        transition: transform 0.25s ease;
    }
    .upload-zone:hover .upload-icon {
        transform: translateY(-4px);
        color: var(--primary-color);
    }
    .file-details-card {
        background-color: var(--background-color);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 1rem;
        margin-top: 1rem;
    }
    .mapping-card {
        border-left: 4px solid var(--primary-color) !important;
    }
    .phone-mockup {
        background-color: #efeae2;
        border-radius: 24px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
        border: 12px solid #222;
    }
    .phone-header {
        background-color: #075e54;
        color: #fff;
        padding: 10px 15px;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .phone-body {
        padding: 15px;
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-size: cover;
        min-height: 380px;
    }
    .whatsapp-bubble {
        background-color: #fff;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.82rem;
        max-width: 85%;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        position: relative;
        margin-left: auto;
        border-top-right-radius: 0;
    }
    .whatsapp-bubble::after {
        content: "";
        position: absolute;
        top: 0;
        right: -8px;
        width: 0;
        height: 0;
        border: 8px solid transparent;
        border-left-color: #fff;
        border-top-color: #fff;
    }
    .bubble-time {
        font-size: 0.65rem;
        color: #8696a0;
        text-align: right;
        margin-top: 4px;
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
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Quick Excel/CSV Broadcast</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Upload spreadsheet numbers, map template variables, and send instantly</span>
            </div>
            <div>
                <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-arrow-left"></i> Back to list
                </a>
            </div>
        </header>

        <section class="row g-4 fade-in-element" style="animation-delay: 0.1s;">
            <!-- Setup & Mapping Form -->
            <div class="col-lg-7 col-md-12">
                <div class="card border p-4 mb-4" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <h5 class="fw-bold mb-4" style="color: var(--text-primary); font-size: 1.05rem;">
                        <i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i> Step 1: Upload Broadcast File
                    </h5>
                    
                    <!-- File Drag & Drop Zone -->
                    <input type="file" id="file-input" class="d-none" accept=".csv,.xlsx,.xls,text/csv">
                    <div class="upload-zone" id="drag-drop-zone">
                        <i class="bi bi-cloud-arrow-up upload-icon"></i>
                        <h6 class="fw-semibold text-primary mb-1">Click to upload or drag & drop</h6>
                        <p class="text-muted small mb-0">Supported file formats: CSV, XLSX, XLS (Max 8MB)</p>
                        <div class="mt-3 pt-2 border-top border-secondary-subtle">
                            <a href="{{ route('quick-broadcast.sample') }}" class="text-decoration-none fw-bold small" onclick="event.stopPropagation();" style="color: var(--primary-color);">
                                <i class="bi bi-download me-1"></i> Download Sample Spreadsheet Template
                            </a>
                        </div>
                    </div>

                    <!-- File Details Preview -->
                    <div class="file-details-card d-none" id="file-preview-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-success" style="font-size: 1.75rem;">
                                    <i class="bi bi-file-earmark-check-fill"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-primary" id="loaded-filename">data.xlsx</h6>
                                    <span class="text-muted small" id="loaded-filecount">Found 0 data rows</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="remove-file-btn">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Broadcast Configuration (Visible only when file is loaded) -->
                <div class="card border p-4 d-none mapping-card" id="configuration-card" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <form id="broadcast-send-form">
                        @csrf
                        <input type="hidden" name="filepath" id="hidden-filepath">

                        <h5 class="fw-bold mb-4 text-primary" style="font-size: 1.05rem;">
                            <i class="bi bi-sliders me-2"></i> Step 2: Configure & Map Broadcast
                        </h5>

                        <!-- Broadcast Name -->
                        <div class="mb-4">
                            <label for="campaign_name" class="form-label fw-semibold">Broadcast Campaign Name</label>
                            <input type="text" name="campaign_name" id="campaign_name" class="form-control form-control-custom" placeholder="e.g. Quick Promo - June 2026" required>
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

                        <!-- Save Contacts Checkbox -->
                        <div class="mb-4 p-3 border rounded" style="background-color: var(--background-color); border-color: var(--border-color) !important;">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="save_contacts" id="save_contacts" value="1">
                                <label class="form-check-label fw-semibold text-primary" for="save_contacts">Save recipients permanently to Contacts directory</label>
                            </div>
                            <small class="text-muted d-block mt-1">If unchecked, messages will be sent instantly as a quick broadcast, without saving the numbers to your contacts list.</small>
                        </div>

                        <hr class="my-4" style="border-color: var(--border-color);">

                        <h6 class="fw-bold mb-3 text-secondary" style="font-size: 0.95rem;">
                            <i class="bi bi-grid-3x3-gap me-2"></i> Column Mapping Workspace
                        </h6>

                        <!-- Map Mobile Number -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-sm-5">
                                <label for="phone_column" class="form-label fw-semibold mb-sm-0">Mobile Number Column <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-sm-7">
                                <select name="phone_column" id="phone_column" class="form-select form-control-custom select-mapping" required>
                                    <option value="" selected disabled>-- Select Column --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Map Contact Name (Optional) -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-sm-5">
                                <label for="name_column" class="form-label fw-semibold mb-sm-0">Recipient Name Column <span class="text-muted">(Optional)</span></label>
                            </div>
                            <div class="col-sm-7">
                                <select name="name_column" id="name_column" class="form-select form-control-custom select-mapping">
                                    <option value="" selected>-- Use Default Name (Guest) --</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4" style="border-color: var(--border-color);">

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

                        <!-- Template Header Media Attachment (Visible only when template requires it) -->
                        <div class="mb-4 d-none" id="header-media-wrapper">
                            <label class="form-label fw-semibold" id="header-media-label">Template Header Attachment</label>
                            <div class="p-3 border rounded" style="background-color: var(--background-color); border-radius: var(--border-radius-md); border-color: var(--border-color) !important;">
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Upload Media File</label>
                                    <input type="file" id="header-media-file" class="form-control form-control-sm form-control-custom">
                                    <div class="progress progress-sm mt-2 d-none" id="header-media-progress-bar" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small text-muted">Or Paste Media URL directly</label>
                                    <input type="text" name="header_attachment" id="header_attachment" class="form-control form-control-sm form-control-custom" placeholder="https://example.com/image.png">
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Variable Inputs -->
                        <div class="mb-4 d-none" id="variables-wrapper">
                            <label class="form-label fw-semibold">Map Template Variables to Columns</label>
                            <div class="p-3 border rounded" style="background-color: var(--background-color); border-radius: var(--border-radius-md); border-color: var(--border-color) !important;">
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-info-circle-fill text-primary"></i> 
                                    For each template placeholder, select the Excel/CSV column that contains the value.
                                </small>
                                <div class="d-flex flex-column gap-3" id="variables-inputs-container">
                                    <!-- Populated dynamically via JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Submit Broadcast -->
                        <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                            <button type="submit" class="btn btn-primary-custom w-100 py-2.5" style="border-radius: var(--border-radius-md); font-weight: 700;">
                                <i class="bi bi-rocket-takeoff-fill me-2"></i> Launch Broadcast to <span id="btn-row-count">0</span> Recipients
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Live Mock Preview Card -->
            <div class="col-lg-5 col-md-12">
                <div class="card border p-4" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important; position: sticky; top: 2rem;">
                    <h5 class="fw-bold mb-3" style="color: var(--text-primary); font-size: 1.05rem;">Live Broadcast Preview</h5>
                    
                    <div class="phone-mockup border shadow-sm">
                        <!-- Top status bar -->
                        <div class="phone-header">
                            <div style="background-color: rgba(255,255,255,0.2); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                W
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.85rem;" id="preview-sender-name">WhatsApp Broadcast</div>
                                <div class="text-white-50" style="font-size: 0.65rem;">Active today</div>
                            </div>
                        </div>

                        <!-- Chat Bubbles Container -->
                        <div class="phone-body">
                            <div class="whatsapp-bubble rounded shadow-sm">
                                <div id="preview-bubble-header" class="mb-2 d-none" style="border-radius: 6px; overflow: hidden; background-color: #f0f0f0;"></div>
                                <div id="preview-bubble-body" style="white-space: pre-wrap; font-size: 0.82rem; color: #303030;">Select a template and map variables to view live preview...</div>
                                <div class="bubble-time">9:41 AM <i class="bi bi-check2"></i></div>
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
    let fileColumns = [];

    $(document).ready(function() {
        // Toggle Sidebar
        $('#sidebar-toggle').on('click', function() {
            $('#dashboard-sidebar').toggleClass('show');
        });

        // Trigger file input click
        $('#drag-drop-zone').on('click', function() {
            $('#file-input').click();
        });

        // Drag and drop events
        const dropZone = document.getElementById('drag-drop-zone');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            $('#drag-drop-zone').addClass('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            $('#drag-drop-zone').removeClass('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            $('#drag-drop-zone').removeClass('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                const file = e.dataTransfer.files[0];
                uploadAndParseFile(file);
            }
        });

        // File input change
        $('#file-input').on('change', function() {
            if (this.files.length > 0) {
                uploadAndParseFile(this.files[0]);
            }
        });

        // Remove file
        $('#remove-file-btn').on('click', function() {
            Notiflix.Confirm.show(
                'Remove File',
                'Are you sure you want to remove the uploaded file?',
                'Yes, Remove',
                'Cancel',
                function() {
                    $('#hidden-filepath').val('');
                    $('#file-input').val('');
                    $('#file-preview-card').addClass('d-none');
                    $('#drag-drop-zone').removeClass('d-none');
                    $('#configuration-card').addClass('d-none');
                    fileColumns = [];
                    $('.select-mapping').html('<option value="" selected disabled>-- Select Column --</option>');
                    $('#name_column').prepend('<option value="" selected>-- Use Default Name (Guest) --</option>');
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

        // Handle Template selection change
        $('#template_id').on('change', function() {
            updateVariableMappingFields();
        });

        // Real-time variable binding preview
        $(document).on('change', '.variable-mapping-select', function() {
            updateLivePreview();
        });

        // Toggle between select (column mapping) and text input (fixed value)
        $(document).on('click', '.toggle-var-input', function() {
            const id = $(this).data('id');
            const wrapper = $(`.var-input-wrapper[data-id="${id}"]`);
            const selectEl = wrapper.find('.variable-mapping-select');
            const inputEl = wrapper.find('.variable-value-input');

            if (selectEl.hasClass('d-none')) {
                selectEl.removeClass('d-none').prop('required', true);
                inputEl.addClass('d-none').prop('required', false).val('');
                $(this).text('Use Fixed Value');
            } else {
                selectEl.addClass('d-none').prop('required', false).val('');
                inputEl.removeClass('d-none').prop('required', true);
                $(this).text('Map Column');
            }
            updateLivePreview();
        });

        // Trigger preview refresh on custom text input
        $(document).on('input change', '.variable-value-input', function() {
            updateLivePreview();
        });

        // Trigger preview refresh on media attachment text change
        $('#header_attachment').on('input change', function() {
            updateLivePreview();
        });

        // Handle media attachment file upload via AJAX
        $('#header-media-file').on('change', function() {
            if (this.files.length === 0) return;

            const file = this.files[0];
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', "{{ csrf_token() }}");

            const progressBarWrapper = $('#header-media-progress-bar');
            const progressBar = progressBarWrapper.find('.progress-bar');
            
            progressBarWrapper.removeClass('d-none');
            progressBar.css('width', '0%').attr('aria-valuenow', 0);
            
            $.ajax({
                url: "{{ route('media.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.css('width', percentComplete + '%').attr('aria-valuenow', percentComplete);
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    progressBarWrapper.addClass('d-none');
                    if (response.status) {
                        Notiflix.Notify.success('Media uploaded successfully!');
                        const relativePath = response.media.file_path;
                        const absoluteUrl = window.location.origin + '/' + relativePath;
                        $('#header_attachment').val(absoluteUrl).trigger('change');
                    } else {
                        Notiflix.Notify.failure(response.message || 'Media upload failed.');
                    }
                },
                error: function(xhr) {
                    progressBarWrapper.addClass('d-none');
                    let msg = 'Failed to upload media.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Submit form
        $('#broadcast-send-form').on('submit', function(e) {
            e.preventDefault();

            Notiflix.Loading.circle('Processing Broadcast Scheduling...');
            const formData = $(this).serialize();

            $.ajax({
                url: "{{ route('quick-broadcast.send') }}",
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
                        Notiflix.Notify.failure(response.message || 'Scheduling failed.');
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to execute broadcast scheduling.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });
    });

    // Upload & parse spreadsheet file via AJAX
    function uploadAndParseFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', "{{ csrf_token() }}");

        Notiflix.Loading.pulse('Parsing spreadsheet file columns...');

        $.ajax({
            url: "{{ route('quick-broadcast.parse') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Notiflix.Loading.remove();
                if (response.status) {
                    Notiflix.Notify.success(response.message);
                    
                    // Update file details card
                    $('#loaded-filename').text(file.name);
                    $('#loaded-filecount').text(`Found ${response.total_rows} valid records for sending`);
                    $('#btn-row-count').text(response.total_rows);
                    
                    $('#hidden-filepath').val(response.filepath);
                    $('#drag-drop-zone').addClass('d-none');
                    $('#file-preview-card').removeClass('d-none');
                    
                    // Save columns list
                    fileColumns = response.headers;
                    
                    // Populate columns selectors
                    populateMappingDropdowns();
                    
                    // Reveal configuration section
                    $('#configuration-card').removeClass('d-none');

                    // Pre-fill campaign name default
                    const rawName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                    $('#campaign_name').val(`Quick Broadcast - ${rawName}`);
                } else {
                    Notiflix.Notify.failure(response.message || 'Parsing failed.');
                }
            },
            error: function(xhr) {
                Notiflix.Loading.remove();
                let msg = 'Failed to upload and parse file.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Notiflix.Notify.failure(msg);
            }
        });
    }

    // Populate mapping dropdowns with extracted column headers
    function populateMappingDropdowns() {
        const selects = $('.select-mapping');
        
        // Reset selections
        selects.empty();
        selects.append('<option value="" selected disabled>-- Select Column --</option>');
        
        // Add optional default for name
        $('#name_column').empty().append('<option value="" selected>-- Use Default Name (Guest) --</option>');

        fileColumns.forEach(col => {
            selects.append(`<option value="${col}">${col}</option>`);
        });

        // Autofill logic based on common names
        fileColumns.forEach(col => {
            const lower = col.toLowerCase();
            if (lower === 'phone' || lower === 'mobile' || lower === 'number' || lower === 'mobile_number' || lower === 'phonenumber' || lower === 'contact') {
                $('#phone_column').val(col);
            }
            if (lower === 'name' || lower === 'customer' || lower === 'customer_name' || lower === 'fullname' || lower === 'full_name' || lower === 'recipient') {
                $('#name_column').val(col);
            }
        });
    }

    // Generate dynamic template placeholder variables mappings
    function updateVariableMappingFields() {
        const selectedOpt = $('#template_id').find('option:selected');
        const componentsJson = selectedOpt.data('components');
        
        if (!componentsJson) {
            $('#variables-wrapper').addClass('d-none');
            $('#header-media-wrapper').addClass('d-none');
            $('#preview-bubble-body').text('Select a template and map variables to view live preview...');
            $('#preview-bubble-header').addClass('d-none').empty();
            return;
        }

        // Find Body component text
        let bodyText = '';
        componentsJson.forEach(comp => {
            if (comp.type === 'BODY') {
                bodyText = comp.text;
            }
        });

        $('#preview-bubble-body').text(bodyText);

        // Check for media header
        let hasMediaHeader = false;
        let mediaHeaderFormat = '';
        componentsJson.forEach(comp => {
            if (comp.type === 'HEADER' && ['IMAGE', 'VIDEO', 'DOCUMENT'].includes(comp.format)) {
                hasMediaHeader = true;
                mediaHeaderFormat = comp.format;
            }
        });

        const headerWrapper = $('#header-media-wrapper');
        const headerLabel = $('#header-media-label');
        const headerInput = $('#header_attachment');
        const headerFileInput = $('#header-media-file');

        if (hasMediaHeader) {
            headerWrapper.removeClass('d-none');
            headerLabel.html(`<i class="bi bi-paperclip me-1 text-primary"></i> Template Header Attachment (${mediaHeaderFormat}) <span class="text-danger">*</span>`);
            headerInput.prop('required', true);
            if (mediaHeaderFormat === 'IMAGE') {
                headerFileInput.attr('accept', 'image/*');
            } else if (mediaHeaderFormat === 'VIDEO') {
                headerFileInput.attr('accept', 'video/*');
            } else if (mediaHeaderFormat === 'DOCUMENT') {
                headerFileInput.attr('accept', '.pdf,.doc,.docx,.xls,.xlsx,.txt');
            }
        } else {
            headerWrapper.addClass('d-none');
            headerInput.prop('required', false).val('');
            headerFileInput.val('');
            headerFileInput.attr('accept', '');
        }

        // Find all placeholders like {1}, {2}, etc.
        const regex = /\{\{(\d+)\}\}/g;
        let match;
        const variableIds = [];
        
        while ((match = regex.exec(bodyText)) !== null) {
            const varNum = parseInt(match[1]);
            if (!variableIds.includes(varNum)) {
                variableIds.push(varNum);
            }
        }

        variableIds.sort((a, b) => a - b);

        const container = $('#variables-inputs-container');
        container.empty();

        if (variableIds.length > 0) {
            $('#variables-wrapper').removeClass('d-none');

            variableIds.forEach(id => {
                let optionsHtml = '';
                fileColumns.forEach(col => {
                    optionsHtml += `<option value="${col}">${col}</option>`;
                });

                const selectHtml = `
                    <div class="row align-items-center mb-3">
                        <div class="col-sm-5 d-flex justify-content-between align-items-center pr-2">
                            <label class="form-label fw-bold mb-0 small text-muted">Placeholder Variable ${'{'}${'{'}${id}${'}'}${'}'}</label>
                            <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none toggle-var-input" data-id="${id}" style="font-size: 0.72rem; color: var(--primary-color);">Use Fixed Value</button>
                        </div>
                        <div class="col-sm-7 var-input-wrapper" data-id="${id}">
                            <select name="variable_mappings[${id}]" class="form-select form-select-sm form-control-custom variable-mapping-select" data-id="${id}" required>
                                <option value="" selected disabled>-- Map to Column --</option>
                                ${optionsHtml}
                            </select>
                            <input type="text" name="variable_values[${id}]" class="form-control form-control-sm form-control-custom variable-value-input d-none" data-id="${id}" placeholder="Enter custom value...">
                        </div>
                    </div>
                `;
                container.append(selectHtml);
            });
            
            // Try to auto-map based on indexing or typical patterns
            $('.variable-mapping-select').each(function(index) {
                const id = $(this).data('id');
                fileColumns.forEach(col => {
                    const lower = col.toLowerCase();
                    if (lower === `var${id}` || lower === `variable${id}` || lower === `val${id}` || lower === `value${id}`) {
                        $(this).val(col);
                    }
                });
            });
            
            updateLivePreview();
        } else {
            $('#variables-wrapper').addClass('d-none');
            updateLivePreview();
        }
    }

    // Refresh bubble preview inside mockup phone screen
    function updateLivePreview() {
        const selectedOpt = $('#template_id').find('option:selected');
        const componentsJson = selectedOpt.data('components');
        if (!componentsJson) return;

        let bodyText = '';
        componentsJson.forEach(comp => {
            if (comp.type === 'BODY') {
                bodyText = comp.text;
            }
        });

        // Replace variable templates in preview with mapped column labels or custom values
        $('.variable-mapping-select').each(function() {
            const id = $(this).data('id');
            const selectEl = $(this);
            const inputEl = selectEl.siblings('.variable-value-input');
            
            let replaceVal = '{' + '{' + id + '}' + '}';
            
            if (!selectEl.hasClass('d-none')) {
                const mappedCol = selectEl.val();
                replaceVal = mappedCol ? `[${mappedCol}]` : '{' + '{' + id + '}' + '}';
            } else {
                const customVal = inputEl.val();
                replaceVal = customVal ? customVal : '{' + '{' + id + '}' + '}';
            }
            
            bodyText = bodyText.replace(new RegExp('\\\\{\\\\{' + id + '\\\\}\\\\}', 'g'), replaceVal);
        });

        $('#preview-bubble-body').text(bodyText);
        $('#preview-sender-name').text(selectedOpt.text().trim().split(' ')[0]);

        // Render Media Header Preview
        const headerPreview = $('#preview-bubble-header');
        headerPreview.addClass('d-none').empty();

        let hasMediaHeader = false;
        let mediaHeaderFormat = '';
        componentsJson.forEach(comp => {
            if (comp.type === 'HEADER' && ['IMAGE', 'VIDEO', 'DOCUMENT'].includes(comp.format)) {
                hasMediaHeader = true;
                mediaHeaderFormat = comp.format;
            }
        });

        if (hasMediaHeader) {
            headerPreview.removeClass('d-none');
            const mediaUrl = $('#header_attachment').val();
            
            if (mediaUrl) {
                if (mediaHeaderFormat === 'IMAGE') {
                    headerPreview.html(`<img src="${mediaUrl}" style="width: 100%; max-height: 140px; object-fit: cover;" onerror="this.src='https://placehold.co/400x200?text=Invalid+Image+URL'">`);
                } else if (mediaHeaderFormat === 'VIDEO') {
                    headerPreview.html(`<video src="${mediaUrl}" controls style="width: 100%; max-height: 140px;" onerror="this.style.display='none'"></video><div class="p-2 small text-muted text-center bg-light" style="font-size: 0.72rem;"><i class="bi bi-play-btn me-1"></i> Play Video</div>`);
                } else if (mediaHeaderFormat === 'DOCUMENT') {
                    headerPreview.html(`<div class="p-3 text-center bg-light"><i class="bi bi-file-earmark-pdf text-danger" style="font-size: 1.8rem;"></i><div class="small fw-semibold mt-1 text-truncate" style="max-width: 100%; font-size: 0.75rem;">${mediaUrl.split('/').pop()}</div></div>`);
                }
            } else {
                headerPreview.html(`<div class="p-4 text-center text-muted bg-light" style="font-size: 0.75rem;"><i class="bi bi-image me-1"></i> [Media Header Placeholder]</div>`);
            }
        }
    }
</script>
@endsection
