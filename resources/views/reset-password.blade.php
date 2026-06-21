@extends('layouts.auth')

@section('title', 'Reset Password - WhatsApp SaaS Platform')

@section('content')
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-md-6 col-lg-4">
            
            <div class="text-center mb-4 fade-in-element">
                <span style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary);">New <span style="color: var(--primary-color);">Password</span></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Set a secure password for your account</p>
            </div>

            <div class="auth-card p-4">
                <form id="reset-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Email address</label>
                        <input type="email" name="email" id="email" class="form-control form-control-custom" value="{{ $email ?? old('email') }}" required readonly>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">New Password</label>
                        <input type="password" name="password" id="password" class="form-control form-control-custom" placeholder="••••••••" required>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-custom" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn-primary-custom w-100 mt-2 d-flex align-items-center justify-content-center gap-2">
                        <span>Reset Password</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#reset-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Processing...');

            $.ajax({
                url: "{{ route('password.update') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        Notiflix.Notify.success(response.message);
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Login Now',
                            confirmButtonColor: 'var(--primary-color)',
                            background: 'var(--card-background)',
                            color: 'var(--text-primary)'
                        }).then(() => {
                            window.location.href = "{{ route('home') }}";
                        });
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let message = 'Failed to reset password. Please check form constraints.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Notiflix.Notify.failure(message);
                    Swal.fire({
                        title: 'Error!',
                        text: message,
                        icon: 'error',
                        confirmButtonColor: 'var(--primary-color)',
                        background: 'var(--card-background)',
                        color: 'var(--text-primary)'
                    });
                }
            });
        });
    });
</script>
@endsection
