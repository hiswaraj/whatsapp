@extends('layouts.auth')

@section('title', 'Login - WhatsApp SaaS Platform')

@section('content')
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-md-6 col-lg-4">
            
            <!-- Logo Section -->
            <div class="text-center mb-4 fade-in-element">
                <div class="d-inline-flex align-items-center gap-2 mb-2">
                    <!-- SVG logo resembling chat bubbles -->
                    <div style="background-color: var(--primary-color); border-radius: 12px; padding: 10px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" style="color: white;">
                            <path d="M12 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.9 5.86L2.1 21.9c-.27.7.37 1.37 1.07 1.1l4.04-1.8C8.88 21.73 10.4 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/>
                        </svg>
                    </div>
                    <span style="font-size: 1.4rem; font-weight: 800; tracking: -0.5px; color: var(--text-primary);">WhatsApp<span style="color: var(--primary-color);">SaaS</span></span>
                </div>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Modern multi-tenant WhatsApp communication</p>
            </div>

            <!-- Login Card -->
            <div class="auth-card p-4">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Welcome back</h2>
                <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 1.5rem;">Please select your role and sign in to continue.</p>

                <!-- Role Selection Tabs -->
                <div class="role-tabs">
                    <button type="button" class="role-tab-btn active" data-role="user">
                        User Portal
                    </button>
                    <button type="button" class="role-tab-btn" data-role="admin">
                        Admin Portal
                    </button>
                </div>

                <!-- Form -->
                <form id="login-form">
                    @csrf
                    <!-- Hidden field to send matching role -->
                    <input type="hidden" name="user_type" id="user-role-input" value="user">

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Email address</label>
                        <input type="email" name="email" id="email" class="form-control form-control-custom" placeholder="name@company.com" required autocomplete="username">
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0;">Password</label>
                            <a href="{{ route('forgot-password') }}" style="font-size: 0.8rem; color: var(--primary-color); text-decoration: none; font-weight: 500;">Forgot password?</a>
                        </div>
                        <input type="password" name="password" id="password" class="form-control form-control-custom" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary-custom w-100 mt-2 d-flex align-items-center justify-content-center gap-2">
                        <span>Sign In</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Hint helper -->
            <div class="text-center mt-4 fade-in-element">
                <span class="text-muted" style="font-size: 0.8rem;">Demo accounts: <br>
                Admin: <code>admin@example.com</code> / User: <code>user@example.com</code> (Password: <code>password</code>)</span>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Handle Tab role switches
        $('.role-tab-btn').on('click', function() {
            $('.role-tab-btn').removeClass('active');
            $(this).addClass('active');
            
            const selectedRole = $(this).data('role');
            $('#user-role-input').val(selectedRole);

            // Subtle feedback notification
            Notiflix.Notify.info(`Switched to ${selectedRole.toUpperCase()} Login Portal`);
        });

        // Handle AJAX submit
        $('#login-form').on('submit', function(e) {
            e.preventDefault();
            
            // Start Notiflix Loading spinner
            Notiflix.Loading.circle('Signing you in...');

            $.ajax({
                url: "{{ route('login-api') }}",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Notiflix.Loading.remove();
                    if (response.status) {
                        Notiflix.Notify.success(response.message || 'Login Successful!');
                        
                        // SweetAlert success screen before redirecting for best UX feel
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Redirecting to your dashboard...',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            background: 'var(--card-background)',
                            color: 'var(--text-primary)'
                        }).then(() => {
                            window.location.href = response.redirect_url;
                        });
                    }
                },
                error: function(xhr) {
                    Notiflix.Loading.remove();
                    let message = 'Something went wrong. Please try again.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    // Alert error feedback
                    Notiflix.Notify.failure(message);
                    
                    Swal.fire({
                        title: 'Error!',
                        text: message,
                        icon: 'error',
                        confirmButtonText: 'Try Again',
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
