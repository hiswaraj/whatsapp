@extends('layouts.auth')

@section('title', 'Forgot Password - WhatsApp SaaS Platform')

@section('content')
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-md-6 col-lg-4">
            
            <div class="text-center mb-4 fade-in-element">
                <span style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary);">Reset <span style="color: var(--primary-color);">Password</span></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">We'll email you instructions to reset it</p>
            </div>

            <div class="auth-card p-4">
                <form id="forgot-form">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Email address</label>
                        <input type="email" name="email" id="email" class="form-control form-control-custom" placeholder="name@company.com" required>
                    </div>

                    <button type="submit" class="btn-primary-custom w-100 mt-2 d-flex align-items-center justify-content-center gap-2">
                        <span>Send Reset Link</span>
                    </button>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('home') }}" style="font-size: 0.85rem; color: var(--text-secondary); text-decoration: none;">Back to Login</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#forgot-form').on('submit', function(e) {
            e.preventDefault();
            Notiflix.Loading.circle('Processing...');

            $.ajax({
                url: "{{ route('forgot-password.submit') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        Notiflix.Notify.success(response.message);
                        Swal.fire({
                            title: 'Email Sent!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: 'var(--primary-color)',
                            background: 'var(--card-background)',
                            color: 'var(--text-primary)'
                        });
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let message = 'User not found or connection error.';
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
