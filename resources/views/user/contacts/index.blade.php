@extends('layouts.auth')

@section('title', 'Contacts - WhatsApp SaaS Platform')

@section('styles')
<!-- DataTables CSS & Select2 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    .select2-container {
        z-index: 2050 !important; /* Forces Select2 to sit cleanly over Bootstrap Modals */
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">

    @include('layouts.sidebar', ['active' => 'contacts'])

    <!-- Main Workspace -->
    <main class="dashboard-main">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom fade-in-element">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Contact Management</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Manage, group, import, and export client lists</span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary d-none align-items-center gap-2" id="bulk-assign-btn" style="border-radius: var(--border-radius-md); font-weight: 600;" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                    <i class="bi bi-folder-plus"></i>
                    <span id="bulk-assign-text">Assign to Group (0)</span>
                </button>
                <a href="{{ route('contacts.sample') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Sample Excel</span>
                </a>
                <a href="{{ route('contacts.export') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    <i class="bi bi-download"></i>
                    <span>Export CSV</span>
                </a>
                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2" style="border-radius: var(--border-radius-md); font-weight: 600;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload"></i>
                    <span>Import CSV</span>
                </button>
                <button type="button" class="btn btn-primary-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createContactModal">
                    <i class="bi bi-person-plus"></i>
                    <span>Add Contact</span>
                </button>
            </div>
        </header>

        <!-- Contacts Table Card -->
        <section class="card border p-4 fade-in-element" style="border-radius: var(--border-radius-md); background-color: var(--card-background);">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="contacts-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="check-all-contacts">
                            </th>
                            <th style="width: 50px;">Avatar</th>
                            <th>Name</th>
                            <th>Mobile Number</th>
                            <th>Email</th>
                            <th>Groups</th>
                            <th>Tags</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts as $contact)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input contact-select-checkbox" value="{{ $contact->id }}">
                                </td>
                                <td>
                                    <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background-color: var(--input-focus-shadow); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
                                        @if($contact->avatar_url)
                                            <img src="{{ asset($contact->avatar_url) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($contact->name) }}&background=random&color=fff&size=128" style="width: 100%; height: 100%; object-fit: cover;">
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary-hover">{{ $contact->name }}</div>
                                </td>
                                <td><code>{{ $contact->mobile_number }}</code></td>
                                <td>{{ $contact->email ?? '-' }}</td>
                                <td>
                                    @if($contact->groups->isEmpty())
                                        <span class="text-muted" style="font-size: 0.8rem;">None</span>
                                    @else
                                        @foreach($contact->groups as $gp)
                                            <span class="badge bg-secondary text-white" style="font-size: 0.72rem; border-radius: 4px;">{{ $gp->name }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if(empty($contact->tags))
                                        -
                                    @else
                                        @foreach($contact->tags as $tg)
                                            <span class="badge bg-info text-white" style="font-size: 0.72rem; border-radius: 4px;">{{ $tg }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted text-truncate d-inline-block" style="max-width: 150px;" title="{{ $contact->notes }}">
                                        {{ $contact->notes ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary quick-assign-btn" data-id="{{ $contact->id }}" data-name="{{ $contact->name }}" data-groups="{{ json_encode($contact->groups->pluck('id')) }}" title="Assign to Group">
                                            <i class="bi bi-folder-plus"></i> Group
                                        </button>
                                        <button class="btn btn-sm btn-outline-info sync-contact-dp-btn d-inline-flex align-items-center gap-1" data-id="{{ $contact->id }}" title="Sync Display Picture">
                                            <i class="bi bi-arrow-repeat"></i> Sync DP
                                        </button>
                                        <a href="{{ route('chat.start-contact', $contact->id) }}" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" style="border-radius: var(--border-radius-sm);">
                                            <i class="bi bi-chat-text"></i> Chat
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary edit-contact-btn" 
                                            data-id="{{ $contact->id }}"
                                            data-name="{{ $contact->name }}"
                                            data-mobile="{{ $contact->mobile_number }}"
                                            data-email="{{ $contact->email }}"
                                            data-tags="{{ !empty($contact->tags) ? implode(', ', $contact->tags) : '' }}"
                                            data-notes="{{ $contact->notes }}"
                                            data-groups="{{ json_encode($contact->groups->pluck('id')) }}">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-contact-btn" data-id="{{ $contact->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</div>

<!-- Create Contact Modal -->
<div class="modal fade" id="createContactModal" tabindex="-1" aria-labelledby="createContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="create-contact-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createContactModalLabel">Create Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="avatar" class="form-label fw-semibold">Profile Picture / Avatar</label>
                        <input type="file" name="avatar" class="form-control form-control-custom" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control form-control-custom" placeholder="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label for="mobile_number" class="form-label fw-semibold">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control form-control-custom" placeholder="+1234567890" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email (Optional)</label>
                        <input type="email" name="email" class="form-control form-control-custom" placeholder="john@doe.com">
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label fw-semibold">Tags (Comma separated, optional)</label>
                        <input type="text" name="tags" class="form-control form-control-custom" placeholder="lead, customer, vip">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Groups (Optional)</label>
                        <select name="group_ids[]" class="form-select select2-groups" multiple="multiple" style="width: 100%;">
                            @foreach($groups as $gp)
                                <option value="{{ $gp->id }}">{{ $gp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea name="notes" class="form-control form-control-custom" rows="3" placeholder="Additional info..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Save Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="edit-contact-form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-contact-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editContactModalLabel">Edit Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-avatar" class="form-label fw-semibold">Profile Picture / Avatar (Optional)</label>
                        <input type="file" name="avatar" id="edit-avatar" class="form-control form-control-custom" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="edit-name" class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" id="edit-name" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-mobile" class="form-label fw-semibold">Mobile Number</label>
                        <input type="text" name="mobile_number" id="edit-mobile" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-email" class="form-label fw-semibold">Email (Optional)</label>
                        <input type="email" name="email" id="edit-email" class="form-control form-control-custom">
                    </div>
                    <div class="mb-3">
                        <label for="edit-tags" class="form-label fw-semibold">Tags (Comma separated, optional)</label>
                        <input type="text" name="tags" id="edit-tags" class="form-control form-control-custom">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Groups (Optional)</label>
                        <select name="group_ids[]" id="edit-groups" class="form-select select2-groups" multiple="multiple" style="width: 100%;">
                            @foreach($groups as $gp)
                                <option value="{{ $gp->id }}">{{ $gp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-notes" class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea name="notes" id="edit-notes" class="form-control form-control-custom" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Update Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-labelledby="bulkAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="bulk-assign-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="bulkAssignModalLabel">Assign Selected Contacts to Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size: 0.88rem;">Assigning <strong id="bulk-modal-count" class="text-primary">0</strong> selected contact(s) to a group:</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Group</label>
                        <select name="group_id" class="form-select select2-groups" style="width: 100%;" required>
                            <option value="">Select a Group...</option>
                            @foreach($groups as $gp)
                                <option value="{{ $gp->id }}">{{ $gp->name }}</option>
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

<!-- Quick Assign Single Contact Modal -->
<div class="modal fade" id="quickAssignModal" tabindex="-1" aria-labelledby="quickAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="quick-assign-form">
                @csrf
                <input type="hidden" id="quick-contact-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="quickAssignModalLabel">Assign Groups for <span id="quick-contact-name" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Groups</label>
                        <select name="group_ids[]" id="quick-groups-select" class="form-select select2-groups" multiple="multiple" style="width: 100%;">
                            @foreach($groups as $gp)
                                <option value="{{ $gp->id }}">{{ $gp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Save Groups</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="import-contacts-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importModalLabel">Import Contacts from Excel/CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted" style="font-size: 0.85rem;">Need a template? Download sample spreadsheet:</span>
                        <a href="{{ route('contacts.sample') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="font-weight: 600;">
                            <i class="bi bi-download"></i> Sample Excel
                        </a>
                    </div>
                    <div class="alert alert-info py-2" style="font-size: 0.82rem;">
                        <strong>CSV/Excel structure requirements:</strong><br>
                        Headers in the first row can include:<br>
                        <code>name, mobile_number, email, group_name, tags, notes</code><br>
                        <em>Note: If <strong>group_name</strong> is provided, contacts will automatically be assigned to that group.</em>
                    </div>
                    <div class="mb-3">
                        <label for="import-group-id" class="form-label fw-semibold">Default Group Assignment (Optional)</label>
                        <select name="group_id" id="import-group-id" class="form-select select2-groups">
                            <option value="">-- No Default Group (Use Excel Group Column) --</option>
                            @foreach($groups as $gp)
                                <option value="{{ $gp->id }}">{{ $gp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">Select Excel/CSV File</label>
                        <input type="file" name="file" id="file" class="form-control form-control-custom" accept=".csv,.xlsx,.xls,.txt" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-primary-custom">Import List</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- DataTables JS & Select2 JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
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

        // Initialize Select2 selectors
        $('.select2-groups').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select contact groups'
        });

        // Initialize Datatable
        const table = $('#contacts-table').DataTable({
            order: [[0, 'asc']],
            language: {
                searchPlaceholder: "Search contacts...",
                search: ""
            }
        });
        
        // Style search bar wrapper
        $('.dataTables_filter input').addClass('form-control form-control-custom d-inline-block w-auto ms-2');

        // Global selected contacts tracking
        let selectedContactIds = [];

        // Handle Check All Contacts
        $('#check-all-contacts').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.contact-select-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Monitor contact checkbox selection
        $(document).on('change', '.contact-select-checkbox', function() {
            selectedContactIds = [];
            $('.contact-select-checkbox:checked').each(function() {
                selectedContactIds.push($(this).val());
            });

            if (selectedContactIds.length > 0) {
                $('#bulk-assign-btn').removeClass('d-none').addClass('d-inline-flex');
                $('#bulk-assign-text').text(`Assign to Group (${selectedContactIds.length})`);
                $('#bulk-modal-count').text(selectedContactIds.length);
            } else {
                $('#bulk-assign-btn').addClass('d-none').removeClass('d-inline-flex');
                $('#check-all-contacts').prop('checked', false);
            }
        });

        // Form Submit: Bulk Assign Contacts
        $('#bulk-assign-form').on('submit', function(e) {
            e.preventDefault();
            if (selectedContactIds.length === 0) return;

            Notiflix.Loading.circle('Assigning contacts to group...');

            const groupId = $(this).find('select[name="group_id"]').val();

            $.ajax({
                url: "{{ route('groups.assign') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    group_id: groupId,
                    contact_ids: selectedContactIds
                },
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#bulkAssignModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to assign contacts to group.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Quick Assign Single Contact
        $(document).on('click', '.quick-assign-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const groups = $(this).data('groups');

            $('#quick-contact-id').val(id);
            $('#quick-contact-name').text(name);
            $('#quick-groups-select').val(groups).trigger('change');

            $('#quickAssignModal').modal('show');
        });

        // Form Submit: Quick Assign Single Contact Groups
        $('#quick-assign-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#quick-contact-id').val();
            Notiflix.Loading.circle('Saving contact groups...');

            const groupIds = $('#quick-groups-select').val() || [];

            const editBtn = $(`.edit-contact-btn[data-id="${id}"]`);
            const name = editBtn.data('name');
            const mobile = editBtn.data('mobile');
            const email = editBtn.data('email');
            const tags = editBtn.data('tags');
            const notes = editBtn.data('notes');

            $.ajax({
                url: `/contacts/${id}`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    _method: "PUT",
                    name: name,
                    mobile_number: mobile,
                    email: email,
                    tags: tags,
                    notes: notes,
                    group_ids: groupIds
                },
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#quickAssignModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to save groups.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Form Submit: Add Contact
        $('#create-contact-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Saving contact...');

            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('contacts.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#createContactModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to create contact.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Populate Edit Modal
        $('.edit-contact-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const mobile = $(this).data('mobile');
            const email = $(this).data('email');
            const tags = $(this).data('tags');
            const notes = $(this).data('notes');
            const groups = $(this).data('groups'); // array

            $('#edit-contact-id').val(id);
            $('#edit-name').val(name);
            $('#edit-mobile').val(mobile);
            $('#edit-email').val(email);
            $('#edit-tags').val(tags);
            $('#edit-notes').val(notes);
            
            // Set Select2 values
            $('#edit-groups').val(groups).trigger('change');

            $('#editContactModal').modal('show');
        });

        // Form Submit: Update Contact
        $('#edit-contact-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit-contact-id').val();
            Notiflix.Loading.circle('Updating contact...');

            const formData = new FormData(this);

            $.ajax({
                url: `/contacts/${id}`,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#editContactModal').modal('hide');
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let msg = 'Failed to update contact.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Sync Contact DP
        $(document).on('click', '.sync-contact-dp-btn', function() {
            const id = $(this).data('id');
            const btn = $(this);
            const originalHtml = btn.html();
            
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Syncing...');
            Notiflix.Loading.circle('Syncing display picture...');

            $.ajax({
                url: `/contacts/${id}/sync-dp`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    btn.prop('disabled', false).html(originalHtml);
                    if (response.status) {
                        Notiflix.Notify.success(response.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    btn.prop('disabled', false).html(originalHtml);
                    let msg = 'Failed to sync display picture.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                }
            });
        });

        // Delete Contact
        $('.delete-contact-btn').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This action will permanently delete this contact.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger-color)',
                cancelButtonColor: 'var(--secondary-color)',
                confirmButtonText: 'Yes, delete it!',
                background: 'var(--card-background)',
                color: 'var(--text-primary)'
            }).then((result) => {
                if (result.isConfirmed) {
                    Notiflix.Loading.circle('Deleting contact...');
                    $.ajax({
                        url: `/contacts/${id}`,
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
                            Notiflix.Notify.failure('Failed to delete contact.');
                        }
                    });
                }
            });
        });

        // Form Submit: Import CSV
        $('#import-contacts-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Uploading and parsing CSV...');

            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('contacts.import') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        $('#importModal').modal('hide');
                        Swal.fire({
                            title: 'Import Result',
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
                    let msg = 'Failed to import CSV.';
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
