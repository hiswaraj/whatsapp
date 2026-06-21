<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WhatsApp SaaS Platform')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <!-- Notiflix CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.2.7/src/notiflix.min.css">

    <!-- Custom System Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    
    @yield('styles')
</head>
<body>

    @yield('content')

    <!-- Floating Theme Toggle Button -->
    <button class="theme-toggle-trigger theme-toggle-float btn p-0" id="theme-toggle-float" aria-label="Toggle Theme">
        <i class="bi bi-moon" style="font-size: 1.4rem;"></i>
    </button>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Notiflix JS -->
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.7/dist/notiflix-aio-3.2.7.min.js"></script>

    <!-- Theme Switcher Script -->
    <script>
        const root = document.documentElement;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        root.setAttribute('data-theme', savedTheme);
        
        // Wait for page to render and sync icon states
        $(document).ready(function() {
            const sidebar = $('.dashboard-sidebar');
            if (sidebar.length) {
                // Insert the theme switch capsule below the logo (brand)
                const brand = sidebar.find('.sidebar-brand');
                if (brand.length) {
                    const themeSwitchHtml = `
                        <div class="sidebar-theme-switch-wrapper mb-4">
                            <div class="theme-switch-capsule" data-active="${savedTheme}">
                                <button class="theme-switch-btn ${savedTheme === 'light' ? 'active' : ''}" data-theme-val="light">
                                    <i class="bi bi-sun"></i>
                                    <span>Light</span>
                                </button>
                                <button class="theme-switch-btn ${savedTheme === 'dark' ? 'active' : ''}" data-theme-val="dark">
                                    <i class="bi bi-moon"></i>
                                    <span>Dark</span>
                                </button>
                                <div class="theme-switch-slider"></div>
                            </div>
                        </div>
                    `;
                    brand.after(themeSwitchHtml);
                }
                
                // Mirrored mobile toggle button on top-right matching the menu button
                const mobileToggleBtn = $(`
                    <button class="theme-toggle-trigger btn p-0" id="mobile-theme-toggle" aria-label="Toggle Theme" style="position: fixed; top: 1.25rem; right: 1.25rem; z-index: 1100; background-color: var(--card-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); width: 2.75rem; height: 2.75rem; display: none; align-items: center; justify-content: center; box-shadow: var(--shadow-md); cursor: pointer; color: var(--text-primary);">
                        <i class="bi ${savedTheme === 'dark' ? 'bi-sun' : 'bi-moon'}" style="font-size: 1.3rem;"></i>
                    </button>
                `);
                
                $('body').append(mobileToggleBtn);
                
                // Add stylesheet dynamically
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
                        .sidebar-theme-switch-wrapper {
                            padding: 0 1.25rem;
                        }
                        .theme-switch-capsule {
                            position: relative;
                            display: flex;
                            background-color: var(--input-focus-shadow);
                            border: 1px solid var(--border-color);
                            border-radius: var(--border-radius-pill);
                            padding: 3px;
                            z-index: 1;
                            overflow: hidden;
                        }
                        .theme-switch-btn {
                            flex: 1;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            border: none;
                            background: transparent;
                            padding: 6px 12px;
                            font-size: 0.82rem;
                            font-weight: 600;
                            color: var(--text-secondary);
                            z-index: 2;
                            cursor: pointer;
                            transition: color 0.25s ease;
                            border-radius: var(--border-radius-pill);
                        }
                        .theme-switch-btn.active {
                            color: var(--text-primary) !important;
                        }
                        .theme-switch-slider {
                            position: absolute;
                            top: 3px;
                            bottom: 3px;
                            left: 3px;
                            width: calc(50% - 3px);
                            background-color: var(--card-background);
                            border-radius: var(--border-radius-pill);
                            box-shadow: var(--shadow-sm);
                            z-index: 1;
                            transition: transform 0.25s cubic-bezier(0.25, 1, 0.5, 1);
                        }
                        .theme-switch-capsule[data-active="light"] .theme-switch-slider {
                            transform: translateX(0);
                        }
                        .theme-switch-capsule[data-active="dark"] .theme-switch-slider {
                            transform: translateX(100%);
                        }
                        @media (max-width: 991.98px) {
                            #mobile-theme-toggle {
                                display: flex !important;
                            }
                        }
                    `)
                    .appendTo('head');
                
                // Remove the floating theme toggle button
                $('#theme-toggle-float').remove();
            } else {
                // No sidebar present (auth pages like login, forgot password).
                // Reposition the floating button to top-right to be clean and out of the way of any forms.
                $('#theme-toggle-float').css({
                    'top': '1.25rem',
                    'right': '1.25rem',
                    'bottom': 'auto',
                    'width': '2.75rem',
                    'height': '2.75rem',
                    'border-radius': 'var(--border-radius-md)',
                    'box-shadow': 'var(--shadow-md)'
                }).removeClass('theme-toggle-float');
            }

            // Inject Flow Builder link dynamically into sidebar menu to keep all pages synced
            const sidebarMenu = $('.sidebar-menu');
            if (sidebarMenu.length) {
                const templatesItem = sidebarMenu.find('a[href*="/templates"]').parent();
                if (templatesItem.length) {
                    const isActive = window.location.pathname.includes('/flows');
                    const flowItemHtml = `
                        <li>
                            <a href="{{ route('flows.index') }}" class="sidebar-menu-link ${isActive ? 'active' : ''}">
                                <i class="bi bi-diagram-3"></i>
                                <span>Flow Builder</span>
                            </a>
                        </li>
                    `;
                    templatesItem.after(flowItemHtml);
                }
            }

            updateThemeSwitcherState(savedTheme);
        });

        $(document).on('click', '.theme-switch-btn', function() {
            const selectedTheme = $(this).data('theme-val');
            root.setAttribute('data-theme', selectedTheme);
            localStorage.setItem('theme', selectedTheme);
            updateThemeSwitcherState(selectedTheme);
        });

        $(document).on('click', '#mobile-theme-toggle, #theme-toggle-float', function() {
            const currentTheme = root.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeSwitcherState(newTheme);
        });

        function updateThemeSwitcherState(theme) {
            const capsule = $('.theme-switch-capsule');
            if (capsule.length) {
                capsule.attr('data-active', theme);
                capsule.find('.theme-switch-btn').removeClass('active');
                capsule.find(`.theme-switch-btn[data-theme-val="${theme}"]`).addClass('active');
            }
            
            // Sync mobile toggle icon
            const mobileIcon = $('#mobile-theme-toggle i');
            if (mobileIcon.length) {
                if (theme === 'dark') {
                    mobileIcon.removeClass('bi-moon').addClass('bi-sun');
                } else {
                    mobileIcon.removeClass('bi-sun').addClass('bi-moon');
                }
            }

            // Sync fallback float icon
            const floatIcon = $('#theme-toggle-float i');
            if (floatIcon.length) {
                if (theme === 'dark') {
                    floatIcon.removeClass('bi-moon').addClass('bi-sun');
                } else {
                    floatIcon.removeClass('bi-sun').addClass('bi-moon');
                }
            }
        }

        // Configure Notiflix default behavior
        Notiflix.Notify.init({
            width: '320px',
            position: 'right-top',
            distance: '15px',
            opacity: 1,
            borderRadius: '8px',
            fontFamily: 'Inter, sans-serif',
            useIcon: true,
            fontAwesomeIconStyle: 'shadow',
            cssAnimation: true,
            cssAnimationDuration: 400,
            cssAnimationStyle: 'fade',
        });
        
        Notiflix.Loading.init({
            className: 'notiflix-loading',
            zindex: 4000,
            backgroundColor: 'rgba(0,0,0,0.5)',
            rtl: false,
            useGoogleFont: true,
            fontFamily: 'Inter, sans-serif',
            svgColor: '#4f46e5',
            svgSize: '50px',
            clickToClose: false,
            customSvgUrl: null,
            customSvgCode: null
        });

        // Flash session notification handler
        @if(session('success'))
            Notiflix.Notify.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            Notiflix.Notify.failure("{{ session('error') }}");
        @endif
    </script>

    @yield('scripts')
</body>
</html>
