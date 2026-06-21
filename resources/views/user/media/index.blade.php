@extends('layouts.auth')

@section('title', 'Media Library - WhatsApp SaaS Platform')

@section('styles')
<style>
    /* Premium Grid & Card Layouts */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.5rem;
    }
    
    .media-card {
        background-color: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }
    
    .media-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--input-focus-border);
    }
    
    .media-preview-container {
        height: 160px;
        background-color: var(--background-color);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 1px solid var(--border-color);
        position: relative;
    }
    
    .media-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .media-icon-fallback {
        font-size: 3rem;
        color: var(--text-secondary);
        opacity: 0.85;
    }

    .media-type-badge {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        background-color: rgba(15, 23, 42, 0.75);
        color: #ffffff;
        backdrop-filter: blur(4px);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.25rem 0.6rem;
        border-radius: var(--border-radius-sm);
        letter-spacing: 0.5px;
    }
    
    .media-card-body {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .media-filename {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        word-break: break-all;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.8em; /* Force height constraint */
    }
    
    .media-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .media-card-actions {
        display: flex;
        border-top: 1px solid var(--border-color);
        background-color: var(--background-color);
    }
    
    .media-action-btn {
        flex: 1;
        padding: 0.6rem 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background-color 0.2s, color 0.2s;
        border: none;
        background: transparent;
        cursor: pointer;
    }
    
    .media-action-btn:not(:last-child) {
        border-right: 1px solid var(--border-color);
    }
    
    .media-action-btn:hover {
        background-color: var(--input-focus-shadow);
        color: var(--primary-color);
    }
    
    .media-action-btn.delete-btn:hover {
        background-color: rgba(239, 68, 68, 0.08);
        color: var(--danger-color);
    }

    /* Category Navigation Tabs */
    .media-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none; /* Hide scrollbar Firefox */
        -webkit-overflow-scrolling: touch;
    }
    .media-tabs::-webkit-scrollbar {
        display: none; /* Hide scrollbar Chrome/Safari/Webkit */
    }

    .media-tab-link {
        padding: 0.6rem 1.2rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-decoration: none;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .media-tab-link:hover {
        color: var(--primary-color);
    }

    .media-tab-link.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    /* Premium Drag & Drop Zone */
    .upload-dropzone {
        border: 2px dashed var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 3rem 2rem;
        text-align: center;
        background-color: var(--background-color);
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }
    .upload-dropzone:hover, .upload-dropzone.dragover {
        border-color: var(--input-focus-border);
        background-color: var(--input-focus-shadow);
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
                <a href="{{ route('campaigns.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-send"></i>
                    <span>Campaigns</span>
                </a>
            </li>
            <li>
                <a href="{{ route('media.index') }}" class="sidebar-menu-link active">
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
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">Media Library</h1>
                <span class="text-muted" style="font-size: 0.85rem;">Upload, browse, and manage files for conversations and templates</span>
            </div>
            <div class="flex-shrink-0">
                <button type="button" class="btn btn-primary-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                    <i class="bi bi-cloud-upload"></i>
                    <span>Upload File</span>
                </button>
            </div>
        </header>

        <!-- Media Library Filters & Search Grid -->
        <section class="row g-4 mb-4 align-items-center fade-in-element" style="animation-delay: 0.1s;">
            <!-- Category Tabs -->
            <div class="col-lg-8 col-md-12">
                <div class="media-tabs">
                    <a href="{{ route('media.index') }}" class="media-tab-link {{ !request('type') ? 'active' : '' }}">
                        <i class="bi bi-collection"></i> All
                    </a>
                    <a href="{{ route('media.index', ['type' => 'image', 'search' => request('search')]) }}" class="media-tab-link {{ request('type') === 'image' ? 'active' : '' }}">
                        <i class="bi bi-image"></i> Images
                    </a>
                    <a href="{{ route('media.index', ['type' => 'video', 'search' => request('search')]) }}" class="media-tab-link {{ request('type') === 'video' ? 'active' : '' }}">
                        <i class="bi bi-film"></i> Videos
                    </a>
                    <a href="{{ route('media.index', ['type' => 'audio', 'search' => request('search')]) }}" class="media-tab-link {{ request('type') === 'audio' ? 'active' : '' }}">
                        <i class="bi bi-music-note-beamed"></i> Audio
                    </a>
                    <a href="{{ route('media.index', ['type' => 'document', 'search' => request('search')]) }}" class="media-tab-link {{ request('type') === 'document' ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Documents
                    </a>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="col-lg-4 col-md-12">
                <form method="GET" action="{{ route('media.index') }}">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="input-group input-group-custom">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-custom py-2" placeholder="Search by file name..." value="{{ request('search') }}" style="font-size: 0.9rem;">
                    </div>
                </form>
            </div>
        </section>

        <!-- Media Grid -->
        <section class="fade-in-element" style="animation-delay: 0.15s;">
            @if($media->isEmpty())
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <div class="empty-state-card py-5">
                            <div class="empty-state-icon-wrapper mx-auto mb-3" style="background-color: var(--input-focus-shadow); color: var(--primary-color); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="fw-bold" style="color: var(--text-primary);">No files found</h5>
                            <p class="text-muted small">Upload your images, videos, audio clips, or documents to get started.</p>
                            <button type="button" class="btn btn-primary-custom btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                                Upload Your First File
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="media-grid">
                    @foreach($media as $item)
                        <div class="media-card" id="media-card-{{ $item->id }}">
                            <div class="media-preview-container">
                                <span class="media-type-badge">{{ $item->file_type }}</span>
                                @if($item->file_type === 'image')
                                    <img src="{{ asset($item->file_path) }}" alt="{{ $item->filename }}" class="media-thumbnail">
                                @elseif($item->file_type === 'video')
                                    <i class="bi bi-film media-icon-fallback text-danger"></i>
                                @elseif($item->file_type === 'audio')
                                    <i class="bi bi-music-note-beamed media-icon-fallback text-warning"></i>
                                @else
                                    <i class="bi bi-file-earmark-text media-icon-fallback text-primary"></i>
                                @endif
                            </div>
                            <div class="media-card-body">
                                <div class="media-filename" title="{{ $item->filename }}">{{ $item->filename }}</div>
                                <div class="media-meta">
                                    <span>{{ round($item->file_size / 1024, 1) }} KB</span>
                                    <span>{{ $item->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="media-card-actions">
                                <button type="button" class="media-action-btn" onclick="copyMediaLink('{{ asset($item->file_path) }}', this)">
                                    <i class="bi bi-link-45deg"></i> Copy Link
                                </button>
                                <a href="{{ asset($item->file_path) }}" download="{{ $item->filename }}" class="media-action-btn">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <button type="button" class="media-action-btn delete-btn" onclick="deleteMedia({{ $item->id }})">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="d-flex justify-content-center mt-5">
                    {{ $media->appends(request()->input())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </main>
</div>

<!-- Upload Media Modal -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-labelledby="uploadMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--border-radius-lg); background-color: var(--card-background); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="uploadMediaModalLabel" style="color: var(--text-primary);">Upload Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="media-upload-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pt-3">
                    <div class="upload-dropzone" id="dropzone">
                        <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                        <span class="fw-semibold d-block" style="color: var(--text-primary); font-size: 0.95rem;">Drag & drop your file here</span>
                        <span class="text-muted d-block my-2" style="font-size: 0.8rem;">or click to browse from files</span>
                        <input type="file" name="file" id="media-file-input" class="d-none" required>
                        <div class="text-muted mt-2" style="font-size: 0.75rem;">
                            Supports Images (jpg, png, webp), Videos (mp4), Audio (mp3), Documents (pdf, doc, xls). Max size: 16MB.
                        </div>
                    </div>
                    
                    <div class="d-none mt-3" id="upload-progress-container">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem;">
                            <span class="fw-semibold text-primary" id="upload-filename">file.ext</span>
                            <span class="text-muted" id="upload-percent">0%</span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: var(--border-radius-pill);">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="upload-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: var(--border-radius-md);">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="upload-submit-btn" style="padding: 0.5rem 1.5rem;" disabled>Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const dropzone = $('#dropzone');
        const fileInput = $('#media-file-input');
        const submitBtn = $('#upload-submit-btn');
        const progressContainer = $('#upload-progress-container');
        const progressBar = $('#upload-progress-bar');
        const progressPercent = $('#upload-percent');
        const progressFilename = $('#upload-filename');

        // Sidebar Toggle logic
        $('#sidebar-toggle').on('click', function() {
            $('#dashboard-sidebar').toggleClass('show');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#dashboard-sidebar, #sidebar-toggle').length) {
                $('#dashboard-sidebar').removeClass('show');
            }
        });

        // Logout logic
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

        // Dropzone interactions
        dropzone.on('click', function(e) {
            if (e.target !== fileInput[0]) {
                fileInput.trigger('click');
            }
        });

        fileInput.on('click', function(e) {
            e.stopPropagation();
        });

        fileInput.on('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                dropzone.find('.fw-semibold').text('Selected: ' + file.name);
                submitBtn.prop('disabled', false);
            }
        });

        dropzone.on('dragover', function(e) {
            e.preventDefault();
            dropzone.addClass('dragover');
        });

        dropzone.on('dragleave', function() {
            dropzone.removeClass('dragover');
        });

        dropzone.on('drop', function(e) {
            e.preventDefault();
            dropzone.removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                fileInput[0].files = files;
                const file = files[0];
                dropzone.find('.fw-semibold').text('Selected: ' + file.name);
                submitBtn.prop('disabled', false);
            }
        });

        // Upload Form Submission
        $('#media-upload-form').on('submit', function(e) {
            e.preventDefault();
            
            const file = fileInput[0].files[0];
            if (!file) return;

            // Enforce max size client side (16MB)
            if (file.size > 16 * 1024 * 1024) {
                Notiflix.Notify.failure('File size exceeds the 16MB limit.');
                return;
            }

            const formData = new FormData(this);
            submitBtn.prop('disabled', true);
            progressContainer.removeClass('d-none');
            progressFilename.text(file.name);

            $.ajax({
                url: "{{ route('media.store') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.width(percentComplete + '%').attr('aria-valuenow', percentComplete);
                            progressPercent.text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.status) {
                        Notiflix.Notify.success('File uploaded successfully!');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Notiflix.Notify.failure(response.message || 'Upload failed.');
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    let msg = 'Upload connection error.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(msg);
                    submitBtn.prop('disabled', false);
                }
            });
        });
    });

    // Delete Media asset
    function deleteMedia(id) {
        Swal.fire({
            title: 'Delete Asset',
            text: 'Are you sure you want to permanently delete this media asset?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--danger-color)',
            cancelButtonColor: 'var(--secondary-color)',
            confirmButtonText: 'Delete',
            background: 'var(--card-background)',
            color: 'var(--text-primary)'
        }).then((result) => {
            if (result.isConfirmed) {
                Notiflix.Loading.pulse('Deleting asset...');
                $.ajax({
                    url: `/media/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Notiflix.Loading.remove();
                        if (response.status) {
                            Notiflix.Notify.success('Asset deleted.');
                            $(`#media-card-${id}`).fadeOut(400, function() {
                                $(this).remove();
                                // Reload if grid is now empty to display the empty state
                                if ($('.media-card').length === 0) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Notiflix.Notify.failure(response.message || 'Deletion failed.');
                        }
                    },
                    error: function(xhr) {
                        Notiflix.Loading.remove();
                        let msg = 'Deletion connection error.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Notiflix.Notify.failure(msg);
                    }
                });
            }
        });
    }

    // Copy Media link to clipboard
    function copyMediaLink(path, btn) {
        // Resolve absolute URL
        const fullUrl = new URL(path, window.location.origin).href;
        
        navigator.clipboard.writeText(fullUrl).then(() => {
            const originalHtml = $(btn).html();
            $(btn).html('<i class="bi bi-check2 text-success"></i> Copied').prop('disabled', true);
            Notiflix.Notify.success('Link copied to clipboard!');
            setTimeout(() => {
                $(btn).html(originalHtml).prop('disabled', false);
            }, 2000);
        }).catch(err => {
            Notiflix.Notify.failure('Failed to copy link.');
        });
    }
</script>
@endsection
