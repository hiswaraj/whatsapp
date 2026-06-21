@extends('layouts.auth')

@section('title', 'WABAs - WhatsApp SaaS Platform')

@section('styles')
<style>
    .waba-card {
        background-color: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        transition: var(--transition-normal);
        position: relative;
        overflow: hidden;
    }
    .waba-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--input-focus-border);
    }
    .waba-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background-color: var(--border-color);
        transition: var(--transition-normal);
    }
    .waba-card.active::before {
        background-color: var(--success-color);
    }
    .waba-card.inactive::before {
        background-color: var(--danger-color);
    }
    .waba-detail-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        margin-bottom: 0.1rem;
    }
    .waba-detail-value {
        font-size: 0.92rem;
        color: var(--text-primary);
        word-break: break-all;
        margin-bottom: 0.8rem;
        font-family: var(--font-mono, monospace);
    }
    .copy-btn {
        background: transparent;
        border: none;
        color: var(--primary-color);
        font-size: 0.82rem;
        cursor: pointer;
        padding: 0;
        margin-left: 0.4rem;
        transition: transform 0.2s ease;
    }
    .copy-btn:hover {
        transform: scale(1.15);
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
                <a href="{{ route('wabas.index') }}" class="sidebar-menu-link active">
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
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">WhatsApp Business Accounts</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Manage WhatsApp accounts and authenticate credentials with Meta API</span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-primary-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createWabaModal">
                    <i class="bi bi-plus-circle"></i>
                    <span>Add WABA</span>
                </button>
            </div>
        </header>

        <!-- Webhook Settings Info Box -->
        <div class="card mb-4 p-4 fade-in-element" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); box-shadow: var(--shadow-sm);">
            <div class="d-flex align-items-start gap-3">
                <div style="background-color: rgba(37, 211, 102, 0.15); color: #25D366; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.1);">
                    <i class="bi bi-link-45deg" style="font-size: 1.6rem;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--text-primary); font-size: 1.05rem;">Meta Webhook Integration</h5>
                    <p class="text-muted mb-0" style="font-size: 0.88rem; line-height: 1.5; max-width: 750px;">
                        To enable real-time messaging, chatbot auto-responses, and message status updates, you must configure webhook details.
                        <strong>Each WhatsApp Business Account (WABA) below has its own unique Callback URL and Verify Token</strong>.
                        Please copy the credentials directly from the respective card below and configure them in your <strong>Meta Developer Console</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- WABAs Grid -->
        <section class="row g-4 fade-in-element">
            @if($wabas->isEmpty())
                <div class="col-12 text-center py-4">
                    <div class="card empty-state-card border-0 mx-auto" style="max-width: 600px;">
                        <div class="empty-state-icon-wrapper">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <h4 class="fw-extrabold mb-2" style="font-weight: 800; font-size: 1.35rem; color: var(--text-primary);">No WABA Registered</h4>
                        <p class="text-secondary mx-auto mb-4" style="max-width: 440px; font-size: 0.95rem;">
                            Connect your Meta WhatsApp Business Account (WABA) to begin managing templates, campaigns, and real-time live chat conversations.
                        </p>
                        <button type="button" class="btn btn-primary-custom d-inline-flex align-items-center gap-2 px-4 py-2 mx-auto" data-bs-toggle="modal" data-bs-target="#createWabaModal">
                            <i class="bi bi-plus-circle"></i>
                            <span>Connect WABA Credentials</span>
                        </button>
                    </div>
                </div>
            @else
                @foreach($wabas as $waba)
                    <div class="col-md-6 col-xl-4">
                        <div class="card waba-card h-100 p-4 {{ $waba->status ? 'active' : 'inactive' }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0 text-truncate" style="max-width: 180px; color: var(--text-primary);">{{ $waba->display_name }}</h5>
                                    <span class="text-muted" style="font-size: 0.78rem;">ID: {{ $waba->id }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $waba->status ? 'bg-success' : 'bg-danger' }} text-white" style="font-size: 0.72rem; border-radius: 4px;">
                                        {{ $waba->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <hr class="my-2 text-muted">

                            <div class="waba-details mt-2">
                                <div class="waba-detail-label">Phone Number ID</div>
                                <div class="waba-detail-value">
                                    <span>{{ $waba->phone_number_id }}</span>
                                    <button class="copy-btn" onclick="copyToClipboard('{{ $waba->phone_number_id }}')" title="Copy Phone Number ID">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>

                                <div class="waba-detail-label">WABA ID</div>
                                <div class="waba-detail-value">
                                    <span>{{ $waba->whatsapp_business_account_id }}</span>
                                    <button class="copy-btn" onclick="copyToClipboard('{{ $waba->whatsapp_business_account_id }}')" title="Copy WABA ID">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>

                                <div class="waba-detail-label">App ID</div>
                                <div class="waba-detail-value">{{ $waba->meta_app_id }}</div>

                                <div class="waba-detail-label">Webhook Callback URL</div>
                                <div class="waba-detail-value mb-2">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm bg-transparent border-secondary-subtle text-muted" value="{{ url('/webhook/whatsapp/' . $waba->verify_token) }}" id="waba-webhook-url-text-{{ $waba->id }}" readonly style="font-size: 0.75rem; font-family: monospace;">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyToClipboard($('#waba-webhook-url-text-{{ $waba->id }}').val())">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="waba-detail-label">Verify Token</div>
                                <div class="waba-detail-value d-flex align-items-center justify-content-between gap-2">
                                    <span id="waba-verify-token-text-{{ $waba->id }}" class="font-monospace" style="font-size: 0.85rem; font-family: monospace; word-break: break-all; color: var(--text-primary);">{{ $waba->verify_token }}</span>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <button class="btn btn-xs btn-link p-0 text-decoration-none" onclick="copyToClipboard($('#waba-verify-token-text-{{ $waba->id }}').text())" title="Copy Verify Token" style="font-size: 0.8rem; color: var(--primary-color);">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                        <span class="text-muted" style="font-size: 0.8rem;">|</span>
                                        <button class="btn btn-xs btn-link p-0 text-decoration-none regenerate-token-btn" data-id="{{ $waba->id }}" title="Regenerate Verify Token" style="font-size: 0.8rem; color: var(--warning-color);">
                                            <i class="bi bi-arrow-repeat"></i> Regenerate
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-top d-flex flex-wrap gap-2 justify-content-between">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-success verify-conn-btn" data-id="{{ $waba->id }}" title="Verify WABA Meta API Connection">
                                        <i class="bi bi-shield-check"></i> Verify
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary test-msg-btn" data-id="{{ $waba->id }}" title="Send Test WhatsApp Message">
                                        <i class="bi bi-send"></i> Test Msg
                                    </button>
                                </div>

                                <div class="d-flex gap-1">
                                    <!-- Edit -->
                                    <button class="btn btn-sm btn-light border edit-waba-btn"
                                        data-id="{{ $waba->id }}"
                                        data-name="{{ $waba->display_name }}"
                                        data-token="{{ $waba->meta_access_token }}"
                                        data-phoneid="{{ $waba->phone_number_id }}"
                                        data-wabaid="{{ $waba->whatsapp_business_account_id }}"
                                        data-appid="{{ $waba->meta_app_id }}"
                                        title="Edit WABA Settings">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <!-- Toggle status -->
                                    <button class="btn btn-sm btn-light border toggle-status-btn" data-id="{{ $waba->id }}" title="Toggle WABA status">
                                        <i class="bi {{ $waba->status ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' }}" style="font-size: 1.1rem; line-height: 1;"></i>
                                    </button>
                                    <!-- Delete -->
                                    <button class="btn btn-sm btn-light border text-danger delete-waba-btn" data-id="{{ $waba->id }}" title="Delete Account">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </section>
    </main>

</div>

<!-- Create WABA Modal -->
<div class="modal fade" id="createWabaModal" tabindex="-1" aria-labelledby="createWabaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="create-waba-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createWabaModalLabel">Add WhatsApp Business Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="display_name" class="form-label fw-semibold">Display Name</label>
                        <input type="text" name="display_name" class="form-control form-control-custom" placeholder="e.g. Sales Account" required>
                        <div class="form-text">A friendly name to identify this account in the dashboard.</div>
                    </div>
                    <div class="mb-3">
                        <label for="meta_access_token" class="form-label fw-semibold">Meta Access Token</label>
                        <textarea name="meta_access_token" class="form-control form-control-custom" rows="3" placeholder="EAAG..." required></textarea>
                        <div class="form-text">System User Access Token with WhatsApp business permissions. Encrypted at rest.</div>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number_id" class="form-label fw-semibold">Phone Number ID</label>
                        <input type="text" name="phone_number_id" class="form-control form-control-custom" placeholder="1029384756..." required>
                    </div>
                    <div class="mb-3">
                        <label for="whatsapp_business_account_id" class="form-label fw-semibold">WhatsApp Business Account ID (WABA ID)</label>
                        <input type="text" name="whatsapp_business_account_id" class="form-control form-control-custom" placeholder="5647382910..." required>
                    </div>
                    <div class="mb-3">
                        <label for="meta_app_id" class="form-label fw-semibold">Meta App ID</label>
                        <input type="text" name="meta_app_id" class="form-control form-control-custom" placeholder="4738291029..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit WABA Modal -->
<div class="modal fade" id="editWabaModal" tabindex="-1" aria-labelledby="editWabaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="edit-waba-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-waba-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editWabaModalLabel">Edit WhatsApp Business Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label fw-semibold">Display Name</label>
                        <input type="text" name="display_name" id="edit-name" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-token" class="form-label fw-semibold">Meta Access Token</label>
                        <textarea name="meta_access_token" id="edit-token" class="form-control form-control-custom" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-phoneid" class="form-label fw-semibold">Phone Number ID</label>
                        <input type="text" name="phone_number_id" id="edit-phoneid" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-wabaid" class="form-label fw-semibold">WhatsApp Business Account ID (WABA ID)</label>
                        <input type="text" name="whatsapp_business_account_id" id="edit-wabaid" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-appid" class="form-label fw-semibold">Meta App ID</label>
                        <input type="text" name="meta_app_id" id="edit-appid" class="form-control form-control-custom" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Message Modal -->
<div class="modal fade" id="testMessageModal" tabindex="-1" aria-labelledby="testMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="test-message-form">
                @csrf
                <input type="hidden" id="test-waba-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="testMessageModalLabel">Send Test Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="to_number" class="form-label fw-semibold">Recipient Mobile Number</label>
                        <input type="text" name="to_number" class="form-control form-control-custom" placeholder="+1234567890" required>
                        <div class="form-text">Make sure to include international country code (e.g. +1... or +91...).</div>
                    </div>
                    <div class="mb-3">
                        <label for="message_type" class="form-label fw-semibold">Message Type</label>
                        <select name="message_type" id="test-message-type" class="form-select form-control-custom" style="background-image: none;" required>
                            <option value="text">Custom Text Message</option>
                            <option value="template" selected>Meta Default Template (hello_world)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="template-name-wrapper">
                        <label for="template_name" class="form-label fw-semibold">Template Name</label>
                        <input type="text" name="template_name" id="test-template-name" class="form-control form-control-custom" value="hello_world" required>
                        <div class="form-text">Defaults to 'hello_world' which is built into every Meta test sandbox.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            Notiflix.Notify.success('Copied to clipboard!');
        }, function(err) {
            Notiflix.Notify.failure('Could not copy text.');
        });
    }

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

        // Toggle Template Name display depending on message type selected
        $('#test-message-type').on('change', function() {
            if ($(this).val() === 'template') {
                $('#template-name-wrapper').show();
                $('#test-template-name').prop('required', true);
            } else {
                $('#template-name-wrapper').hide();
                $('#test-template-name').prop('required', false);
            }
        });

        // Form Submit: Add WABA
        $('#create-waba-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Adding WABA Account...');

            $.ajax({
                url: "{{ route('wabas.store') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#createWabaModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to add WABA Account.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Populate Edit Modal
        $('.edit-waba-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const token = $(this).data('token');
            const phoneid = $(this).data('phoneid');
            const wabaid = $(this).data('wabaid');
            const appid = $(this).data('appid');

            $('#edit-waba-id').val(id);
            $('#edit-name').val(name);
            $('#edit-token').val(token);
            $('#edit-phoneid').val(phoneid);
            $('#edit-wabaid').val(wabaid);
            $('#edit-appid').val(appid);

            $('#editWabaModal').modal('show');
        });

        // Form Submit: Update WABA
        $('#edit-waba-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit-waba-id').val();
            Notiflix.Loading.circle('Updating WABA Account...');

            $.ajax({
                url: `/wabas/${id}`,
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#editWabaModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to update WABA Account.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Regenerate Verify Token
        $(document).on('click', '.regenerate-token-btn', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Regenerating the Webhook Verify Token will invalidate the old webhook URL. You must update the URL and token in Meta Developer Console immediately to prevent disruption.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--primary-color)',
                cancelButtonColor: 'var(--secondary-color)',
                confirmButtonText: 'Yes, regenerate token',
                background: 'var(--card-background)',
                color: 'var(--text-primary)'
            }).then((result) => {
                if (result.isConfirmed) {
                    Notiflix.Loading.circle('Regenerating verify token...');
                    $.ajax({
                        url: `/wabas/${id}/regenerate-token`,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
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
                            let msg = 'Failed to regenerate token.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Notiflix.Notify.failure(msg);
                        }
                    });
                }
            });
        });

        // Toggle Status
        $('.toggle-status-btn').on('click', function() {
            const id = $(this).data('id');
            Notiflix.Loading.circle('Updating status...');

            $.ajax({
                url: `/wabas/${id}/toggle-status`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
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
                    Notiflix.Notify.failure('Failed to update status.');
                }
            });
        });

        // Delete WABA
        $('.delete-waba-btn').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "Deleting this WABA will disconnect all campaigns, template bindings, and message histories. This cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger-color)',
                cancelButtonColor: 'var(--secondary-color)',
                confirmButtonText: 'Yes, delete WABA',
                background: 'var(--card-background)',
                color: 'var(--text-primary)'
            }).then((result) => {
                if (result.isConfirmed) {
                    Notiflix.Loading.circle('Deleting account...');
                    $.ajax({
                        url: `/wabas/${id}`,
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
                            Notiflix.Notify.failure('Failed to delete account.');
                        }
                    });
                }
            });
        });

        // Connection Verification
        $('.verify-conn-btn').on('click', function() {
            const id = $(this).data('id');
            Notiflix.Loading.circle('Contacting Meta Graph API...');

            $.ajax({
                url: `/wabas/${id}/verify`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        let detailsHtml = '<div class="text-start mt-3" style="font-size:0.9rem;">';
                        if (response.meta_details) {
                            detailsHtml += `<p><strong>Verified Name:</strong> ${response.meta_details.verified_name || '-'}</p>`;
                            detailsHtml += `<p><strong>Meta Account Status:</strong> <span class="badge bg-success">${response.meta_details.account_status || '-'}</span></p>`;
                            detailsHtml += `<p><strong>Quality Rating:</strong> ${response.meta_details.quality_rating || '-'}</p>`;
                            if (response.meta_details.warning) {
                                detailsHtml += `<div class="alert alert-warning py-1" style="font-size:0.75rem;"><i class="bi bi-exclamation-triangle"></i> ${response.meta_details.warning}</div>`;
                            }
                        }
                        detailsHtml += '</div>';

                        Swal.fire({
                            title: 'Connection Success',
                            html: `<strong>${response.message}</strong>${detailsHtml}`,
                            icon: 'success',
                            confirmButtonColor: 'var(--primary-color)',
                            background: 'var(--card-background)',
                            color: 'var(--text-primary)'
                        });
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'WABA validation failed. Please check credentials.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Connection Failed',
                        text: msg,
                        icon: 'error',
                        confirmButtonColor: 'var(--primary-color)',
                        background: 'var(--card-background)',
                        color: 'var(--text-primary)'
                    });
                }
            });
        });

        // Populate Test Message Modal
        $('.test-msg-btn').on('click', function() {
            const id = $(this).data('id');
            $('#test-waba-id').val(id);
            $('#testMessageModal').modal('show');
        });

        // Submit Test Message
        $('#test-message-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#test-waba-id').val();
            Notiflix.Loading.circle('Dispatching message payload to Meta...');

            $.ajax({
                url: `/wabas/${id}/test-message`,
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#testMessageModal').modal('hide');
                        
                        let detailsHtml = '<div class="text-start mt-3" style="font-size:0.82rem;">';
                        if (response.payload_sent) {
                            detailsHtml += '<p class="mb-1"><strong>Outgoing JSON Payload:</strong></p>';
                            detailsHtml += `<pre class="bg-light p-2 border" style="border-radius:4px; max-height:150px; overflow-y:auto; color:#000;">${JSON.stringify(response.payload_sent, null, 2)}</pre>`;
                        }
                        if (response.warning) {
                            detailsHtml += `<div class="alert alert-warning py-1" style="font-size:0.75rem;"><i class="bi bi-exclamation-triangle"></i> ${response.warning}</div>`;
                        }
                        detailsHtml += '</div>';

                        Swal.fire({
                            title: 'Message Dispatched',
                            html: `<strong>${response.message}</strong>${detailsHtml}`,
                            icon: 'success',
                            confirmButtonColor: 'var(--primary-color)',
                            background: 'var(--card-background)',
                            color: 'var(--text-primary)'
                        });
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to dispatch test message.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Dispatch Failed',
                        text: msg,
                        icon: 'error',
                        confirmButtonColor: 'var(--primary-color)',
                        background: 'var(--card-background)',
                        color: 'var(--text-primary)'
                    });
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
