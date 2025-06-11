{{-- packages/pages-plugin/resources/views/partials/navbar.blade.php --}}
<nav class="filaman-navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a href="{{ route('filaman.pages.show', ['slug' => 'home']) }}" class="brand-link">
                <strong>FilaMan</strong>
                <span class="brand-subtitle">Plugin Manager</span>
            </a>
        </div>
        
        <ul class="navbar-nav">
            @php
                $currentSlug = $slug ?? 'home';
                $pages = $pages ?? filaman_get_pages();
            @endphp

            @foreach ($pages as $page)
                <li class="nav-item">
                    <a href="{{ route('filaman.pages.show', ['slug' => $page['slug']]) }}" 
                       class="nav-link {{ $currentSlug === $page['slug'] ? 'active' : '' }}"
                       title="{{ $page['description'] }}">
                        {{ $page['title'] }}
                    </a>
                </li>
            @endforeach
            
            {{-- Admin panel link for authenticated admin users --}}
            @auth
                @if(auth()->user()->isAdmin())
                    <li class="nav-item nav-admin">
                        <a href="/admin" class="nav-link admin-link" title="Admin Panel">
                            <span>⚙️</span> Admin
                        </a>
                    </li>
                @endif
            @endauth
        </ul>
    </div>
</nav>

<style>
/* Embedded CSS for the navbar - could be moved to separate CSS file */
.filaman-navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.navbar-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1rem;
}

.navbar-brand .brand-link {
    color: white;
    text-decoration: none;
    font-size: 1.5rem;
    font-weight: bold;
}

.brand-subtitle {
    font-size: 0.8rem;
    opacity: 0.8;
    margin-left: 0.5rem;
}

.navbar-nav {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 0.5rem;
}

.nav-link {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
    font-weight: 500;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
}

.nav-admin {
    margin-left: 1rem;
    padding-left: 1rem;
    border-left: 1px solid rgba(255, 255, 255, 0.3);
}

.admin-link {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.admin-link:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
    .navbar-container {
        flex-direction: column;
        gap: 1rem;
    }
    
    .navbar-nav {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .nav-admin {
        margin-left: 0;
        padding-left: 0;
        border-left: none;
    }
}
</style>