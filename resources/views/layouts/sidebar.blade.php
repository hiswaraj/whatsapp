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
            <a href="{{ route('user.dashboard') }}" class="sidebar-menu-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('chat.index') }}" class="sidebar-menu-link {{ ($active ?? '') === 'chat' ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i>
                <span>Live Chat</span>
            </a>
        </li>
        <li>
            <a href="{{ route('contacts.index') }}" class="sidebar-menu-link {{ ($active ?? '') === 'contacts' ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Contacts</span>
            </a>
        </li>
        <li>
            <a href="{{ route('groups.index') }}" class="sidebar-menu-link {{ ($active ?? '') === 'groups' ? 'active' : '' }}">
                <i class="bi bi-folder"></i>
                <span>Contact Groups</span>
            </a>
        </li>
        <li>
            <a href="{{ route('wabas.index') }}" class="sidebar-menu-link {{ ($active ?? '') === 'wabas' ? 'active' : '' }}">
                <i class="bi bi-whatsapp"></i>
                <span>WABAs</span>
            </a>
        </li>
        <li>
            <a href="{{ route('templates.index') }}" class="sidebar-menu-link {{ ($active ?? '') === 'templates' ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Templates</span>
            </a>
        </li>
        <li>
            <a href="{{ route('campaigns.index') }}" class="sidebar-menu-link {{ ($active ?? '') === 'campaigns' ? 'active' : '' }}">
                <i class="bi bi-send"></i>
                <span>Campaigns</span>
            </a>
        </li>
        <li>
            <a href="{{ route('media.index') }}" class="sidebar-menu-link {{ ($active ?? '') === 'media' ? 'active' : '' }}">
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
