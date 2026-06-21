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
                <a href="{{ route('contacts.index') }}" class="sidebar-menu-link active">
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
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Contact Management</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Manage, group, import, and export client lists</span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
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
            <form id="create-contact-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createContactModalLabel">Create Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
            <form id="edit-contact-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-contact-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editContactModalLabel">Edit Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
            <form id="import-contacts-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importModalLabel">Import Contacts from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size: 0.82rem;">
                        <strong>CSV structure requirements:</strong><br>
                        The CSV file must contain column headers in the first row:<br>
                        <code>name, mobile_number, email, tags, notes</code>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">Select CSV File</label>
                        <input type="file" name="file" id="file" class="form-control form-control-custom" accept=".csv" required>
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

        // Form Submit: Add Contact
        $('#create-contact-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Saving contact...');

            $.ajax({
                url: "{{ route('contacts.store') }}",
                type: "POST",
                data: $(this).serialize(),
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

            $.ajax({
                url: `/contacts/${id}`,
                type: "POST",
                data: $(this).serialize(),
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
