@extends('layouts.auth')

@section('title', 'Admin Dashboard - WhatsApp SaaS')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8 text-center fade-in-element">
            <h1 class="mb-4">👑 Admin Dashboard Placeholder</h1>
            <p class="lead text-secondary">You have logged in successfully as <strong>Admin</strong>.</p>
            <div class="alert alert-info d-inline-block px-4 py-2">
                This is a placeholder page for future admin management functionalities.
            </div>
            
            <div class="mt-4">
                <button type="button" class="btn btn-danger px-4 py-2" id="logout-btn">
                    Logout
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
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
    });
</script>
@endsection
