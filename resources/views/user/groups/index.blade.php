@extends('layouts.auth')

@section('title', 'Contact Groups - WhatsApp SaaS Platform')

@section('styles')
<!-- Select2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    .group-card {
        background-color: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        transition: var(--transition-normal);
    }
    .group-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--input-focus-border);
    }
    .select2-container {
        z-index: 2050 !important;
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
                <a href="{{ route('groups.index') }}" class="sidebar-menu-link active">
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
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Contact Groups</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Organize contacts into custom lists and campaigns</span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                    <i class="bi bi-folder-plus"></i>
                    <span>Create Group</span>
                </button>
            </div>
        </header>

        <!-- Groups Cards Grid -->
        <section class="row g-4 fade-in-element">
            @if($groups->isEmpty())
                <div class="col-12 text-center py-4">
                    <div class="card empty-state-card border-0 mx-auto" style="max-width: 600px;">
                        <div class="empty-state-icon-wrapper">
                            <i class="bi bi-folder-x"></i>
                        </div>
                        <h4 class="fw-extrabold mb-2" style="font-weight: 800; font-size: 1.35rem; color: var(--text-primary);">No Contact Groups Found</h4>
                        <p class="text-secondary mx-auto mb-4" style="max-width: 440px; font-size: 0.95rem;">
                            Organize your contact list into custom categories to launch targeted WhatsApp campaigns and manage broadcasts efficiently.
                        </p>
                        <button type="button" class="btn btn-primary-custom d-inline-flex align-items-center gap-2 px-4 py-2 mx-auto" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                            <i class="bi bi-folder-plus"></i>
                            <span>Create Your First Group</span>
                        </button>
                    </div>
                </div>
            @else
                @foreach($groups as $group)
                    <div class="col-md-6 col-lg-4">
                        <div class="group-card p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h4 class="mb-0 fw-bold text-primary-hover" style="font-size: 1.15rem;">{{ $group->name }}</h4>
                                <span class="badge bg-secondary text-white" style="border-radius: 4px; padding: 6px 10px;">
                                    {{ $group->contacts_count }} Contacts
                                </span>
                            </div>
                            
                            <p class="text-muted" style="font-size: 0.8rem;">Created: {{ $group->created_at->format('M d, Y') }}</p>

                            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                <button class="btn btn-sm btn-outline-primary flex-grow-1 assign-contacts-btn" 
                                    data-id="{{ $group->id }}" 
                                    data-name="{{ $group->name }}">
                                    Assign
                                </button>
                                <button class="btn btn-sm btn-outline-secondary flex-grow-1 view-contacts-btn" 
                                    data-id="{{ $group->id }}" 
                                    data-name="{{ $group->name }}">
                                    View members
                                </button>
                                <button class="btn btn-sm btn-outline-danger rename-group-btn" 
                                    data-id="{{ $group->id }}" 
                                    data-name="{{ $group->name }}" 
                                    title="Rename group">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-group-btn" 
                                    data-id="{{ $group->id }}" 
                                    title="Delete group">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </section>
    </main>

</div>

<!-- Create Group Modal -->
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="create-group-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createGroupModalLabel">Create Contact Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Group Name</label>
                        <input type="text" name="name" class="form-control form-control-custom" placeholder="e.g. VIP Customers" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Save Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Group Modal -->
<div class="modal fade" id="renameGroupModal" tabindex="-1" aria-labelledby="renameGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="rename-group-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="rename-group-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="renameGroupModalLabel">Rename Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rename-name" class="form-label fw-semibold">Group Name</label>
                        <input type="text" name="name" id="rename-name" class="form-control form-control-custom" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Rename</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Contacts Modal -->
<div class="modal fade" id="assignContactsModal" tabindex="-1" aria-labelledby="assignContactsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="assign-contacts-form">
                @csrf
                <input type="hidden" name="group_id" id="assign-group-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="assignContactsModalLabel">Assign Contacts to <span id="assign-group-title" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Contacts</label>
                        <select name="contact_ids[]" id="assign-contacts-select" class="form-select select2-contacts" multiple="multiple" style="width: 100%;" required>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->mobile_number }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Assign Contacts</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Group Contacts Modal -->
<div class="modal fade" id="viewContactsModal" tabindex="-1" aria-labelledby="viewContactsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Members of <span id="view-group-title" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" id="members-count">0 members in this group</span>
                    <button type="button" class="btn btn-sm btn-outline-danger d-none" id="bulk-remove-btn">
                        Remove Selected
                    </button>
                </div>
                
                <div class="table-responsive" style="max-height: 350px;">
                    <table class="table align-middle" id="group-members-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="check-all-members">
                                </th>
                                <th>Name</th>
                                <th>Mobile Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody id="group-members-list">
                            <!-- Populated dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        // Initialize Select2 Selector
        $('.select2-contacts').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select contacts'
        });

        // Form Submit: Create Group
        $('#create-group-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Saving group...');

            $.ajax({
                url: "{{ route('groups.store') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#createGroupModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to create group.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Populate Rename modal
        $('.rename-group-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#rename-group-id').val(id);
            $('#rename-name').val(name);
            $('#renameGroupModal').modal('show');
        });

        // Form Submit: Rename Group
        $('#rename-group-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#rename-group-id').val();
            Notiflix.Loading.circle('Renaming group...');

            $.ajax({
                url: `/groups/${id}`,
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#renameGroupModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to rename group.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Delete Group
        $('.delete-group-btn').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "Deleting this group does not delete the contacts inside it.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger-color)',
                cancelButtonColor: 'var(--secondary-color)',
                confirmButtonText: 'Yes, delete it!',
                background: 'var(--card-background)',
                color: 'var(--text-primary)'
            }).then((result) => {
                if (result.isConfirmed) {
                    Notiflix.Loading.circle('Deleting group...');
                    $.ajax({
                        url: `/groups/${id}`,
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
                        error: function() {
                            Notiflix.Loading.remove();
                            Notiflix.Notify.failure('Failed to delete group.');
                        }
                    });
                }
            });
        });

        // Open Assign Modal
        $('.assign-contacts-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#assign-group-id').val(id);
            $('#assign-group-title').text(name);
            
            // Reset Select2 input
            $('#assign-contacts-select').val(null).trigger('change');
            
            $('#assignContactsModal').modal('show');
        });

        // Form Submit: Assign Contacts
        $('#assign-contacts-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Assigning contacts...');

            $.ajax({
                url: "{{ route('groups.assign') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#assignContactsModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to assign contacts.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Eager loaded groups dataset in JSON
        const groupsData = @json($groups);

        // Global variables for viewing group contacts
        let activeGroupId = null;
        let selectedMemberIds = [];

        // Open View Contacts Modal
        $('.view-contacts-btn').on('click', function() {
            activeGroupId = $(this).data('id');
            const groupName = $(this).data('name');
            
            $('#view-group-title').text(groupName);
            $('#group-members-list').empty();
            $('#check-all-members').prop('checked', false);
            $('#bulk-remove-btn').addClass('d-none');
            selectedMemberIds = [];

            // Find group in local JSON dataset
            const group = groupsData.find(g => g.id == activeGroupId);
            const members = group ? group.contacts : [];

            $('#members-count').text(`${members.length} member(s) in this group`);

            if (members.length === 0) {
                $('#group-members-list').append('<tr><td colspan="4" class="text-center text-muted py-4">No contacts assigned to this group.</td></tr>');
            } else {
                members.forEach(member => {
                    $('#group-members-list').append(`
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input member-checkbox" value="${member.id}">
                            </td>
                            <td><strong>${member.name}</strong></td>
                            <td><code>${member.mobile_number}</code></td>
                            <td>${member.email || '-'}</td>
                        </tr>
                    `);
                });
            }

            $('#viewContactsModal').modal('show');
        });

        // Check/Uncheck all members in modal
        $('#check-all-members').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.member-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Monitor selections
        $(document).on('change', '.member-checkbox', function() {
            selectedMemberIds = [];
            $('.member-checkbox:checked').each(function() {
                selectedMemberIds.push($(this).val());
            });

            if (selectedMemberIds.length > 0) {
                $('#bulk-remove-btn').removeClass('d-none').text(`Remove Selected (${selectedMemberIds.length})`);
            } else {
                $('#bulk-remove-btn').addClass('d-none');
            }
        });

        // Bulk Remove Contacts from Group
        $('#bulk-remove-btn').on('click', function() {
            if (selectedMemberIds.length === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: "Remove selected contacts from this group?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger-color)',
                cancelButtonColor: 'var(--secondary-color)',
                confirmButtonText: 'Yes, remove them!',
                background: 'var(--card-background)',
                color: 'var(--text-primary)'
            }).then((result) => {
                if (result.isConfirmed) {
                    Notiflix.Loading.circle('Removing contacts...');
                    $.ajax({
                        url: "{{ route('groups.remove') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            group_id: activeGroupId,
                            contact_ids: selectedMemberIds
                        },
                        dataType: "json",
                        success: function(response) {
                            Notiflix.Loading.remove();
                            if (response.status) {
                                $('#viewContactsModal').modal('hide');
                                Notiflix.Notify.success(response.message);
                                window.location.reload();
                            }
                        },
                        error: function() {
                            Notiflix.Loading.remove();
                            Notiflix.Notify.failure('Failed to remove contacts.');
                        }
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
