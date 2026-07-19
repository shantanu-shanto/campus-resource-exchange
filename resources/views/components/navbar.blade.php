<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">

        {{-- ================================
             BRAND LOGO
             ================================ --}}
        <a class="navbar-brand" href="{{ route('landing') }}">
            <i class="bi bi-bag-check-fill"></i> UniShare
        </a>

        {{-- Mobile toggle --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation"
            style="border-color: rgba(255,255,255,0.4);">
            <span style="color: #fff; font-size: 1.3rem;"><i class="bi bi-list"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            {{-- ================================
                 GUEST LINKS (not logged in)
                 ================================ --}}
            @guest
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('landing') ? 'active' : '' }}"
                            href="{{ route('landing') }}">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('frontend.items.index') ? 'active' : '' }}"
                            href="{{ route('frontend.items.index') }}">
                            <i class="bi bi-grid"></i> Browse Items
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('frontend.search.index') ? 'active' : '' }}"
                            href="{{ route('frontend.search.index') }}">
                            <i class="bi bi-search"></i> Search
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}"
                            class="btn btn-light btn-sm fw-600 px-3"
                            style="color: #0d6efd; font-weight: 600; border-radius: 20px; margin-top: 4px;">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    </li>
                </ul>
            @endguest

            {{-- ================================
                 SUPER ADMIN LINKS
                 ================================ --}}
            @auth
                @if (auth()->user()->role === 'super_admin')
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('super-admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.universities.*') ? 'active' : '' }}"
                                href="{{ route('super-admin.universities.index') }}">
                                <i class="bi bi-building"></i> Universities
                                {{-- Pending badge --}}
                                @php
                                    $pendingCount = \App\Models\University::where('status', 'pending')->count();
                                @endphp
                                @if ($pendingCount > 0)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">
                                        {{ $pendingCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}"
                                href="{{ route('super-admin.users.index') }}">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.reports.*') ? 'active' : '' }}"
                                href="{{ route('super-admin.reports.index') }}">
                                <i class="bi bi-bar-chart"></i> Reports
                            </a>
                        </li>
                    </ul>

                    {{-- Super admin profile dropdown --}}
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                                href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span style="width: 32px; height: 32px; background: rgba(255,255,255,0.25);
                                    border-radius: 50%; display: inline-flex; align-items: center;
                                    justify-content: center;">
                                    <i class="bi bi-shield-check" style="font-size: 1rem;"></i>
                                </span>
                                <span style="font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 200px;">
                                <li>
                                    <span class="dropdown-item-text text-muted" style="font-size: 0.78rem;">
                                        Super Administrator
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                @endif

                {{-- ================================
                     UNI ADMIN LINKS
                     ================================ --}}
                @if (auth()->user()->role === 'uni_admin')
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('uni-admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('uni-admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('uni-admin.users.*') ? 'active' : '' }}"
                                href="{{ route('uni-admin.users.index') }}">
                                <i class="bi bi-person-check"></i> Users
                                {{-- Pending verification badge --}}
                                @php
                                    $pendingUsers = \App\Models\User::where('university_id', auth()->user()->university_id)
                                        ->where('status', 'pending')
                                        ->count();
                                @endphp
                                @if ($pendingUsers > 0)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">
                                        {{ $pendingUsers }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('uni-admin.items.*') ? 'active' : '' }}"
                                href="{{ route('uni-admin.items.index') }}">
                                <i class="bi bi-grid"></i> Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('uni-admin.penalties.*') ? 'active' : '' }}"
                                href="{{ route('uni-admin.penalties.index') }}">
                                <i class="bi bi-exclamation-triangle"></i> Penalties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('uni-admin.reports.*') ? 'active' : '' }}"
                                href="{{ route('uni-admin.reports.index') }}">
                                <i class="bi bi-bar-chart"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('uni-admin.support.*') ? 'active' : '' }}"
                                href="{{ route('uni-admin.support.index') }}">
                                <i class="bi bi-headset"></i> Support
                                @php
                                    $openTicketsAdmin = \App\Models\SupportTicket::where('university_id', auth()->user()->university_id)
                                        ->whereIn('status', ['open', 'in_progress'])
                                        ->count();
                                @endphp
                                @if ($openTicketsAdmin > 0)
                                    <span class="badge bg-danger ms-1" style="font-size: 0.7rem;">
                                        {{ $openTicketsAdmin }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    </ul>

                    {{-- Uni admin profile dropdown --}}
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                                href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span style="width: 32px; height: 32px; background: rgba(255,255,255,0.25);
                                    border-radius: 50%; display: inline-flex; align-items: center;
                                    justify-content: center;">
                                    <i class="bi bi-building" style="font-size: 1rem;"></i>
                                </span>
                                <span style="font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 220px;">
                                <li>
                                    <span class="dropdown-item-text text-muted" style="font-size: 0.78rem;">
                                        {{ auth()->user()->university->name ?? 'University Admin' }}
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                @endif

                {{-- ================================
                     STUDENT / TEACHER LINKS
                     ================================ --}}
                @if (auth()->user()->role === 'user')
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                                href="{{ route('home') }}">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.items.index') ? 'active' : '' }}"
                                href="{{ route('frontend.items.index') }}">
                                <i class="bi bi-grid"></i> Browse
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.items.create') ? 'active' : '' }}"
                                href="{{ route('frontend.items.create') }}">
                                <i class="bi bi-plus-circle"></i> Post Item
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.transactions.*') ? 'active' : '' }}"
                                href="{{ route('frontend.transactions.index') }}">
                                <i class="bi bi-arrow-left-right"></i> Transactions
                            </a>
                        </li>

                        {{-- Messages with unread count --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.messages.*') ? 'active' : '' }}"
                                href="{{ route('frontend.messages.index') }}">
                                <i class="bi bi-chat-dots"></i> Messages
                                @php
                                    $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())
                                        ->whereNull('read_at')
                                        ->count();
                                @endphp
                                @if ($unreadCount > 0)
                                    <span class="badge bg-danger ms-1" style="font-size: 0.7rem;">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>
                                @endif
                            </a>
                        </li>

                        {{-- Support / Help --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.support.*') ? 'active' : '' }}"
                                href="{{ route('frontend.support.index') }}">
                                <i class="bi bi-headset"></i> Support
                                @php
                                    $openTickets = \App\Models\SupportTicket::where('user_id', auth()->id())
                                        ->whereIn('status', ['open', 'in_progress'])
                                        ->count();
                                @endphp
                                @if ($openTickets > 0)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">
                                        {{ $openTickets }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    </ul>

                    {{-- Student profile dropdown --}}
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                        {{-- Search icon --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.search.index') ? 'active' : '' }}"
                                href="{{ route('frontend.search.index') }}"
                                title="Search">
                                <i class="bi bi-search"></i>
                            </a>
                        </li>

                        {{-- Profile dropdown --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                                href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span style="width: 32px; height: 32px; background: rgba(255,255,255,0.25);
                                    border-radius: 50%; display: inline-flex; align-items: center;
                                    justify-content: center;">
                                    <i class="bi bi-person" style="font-size: 1rem;"></i>
                                </span>
                                <span style="font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 220px;">

                                {{-- User info header --}}
                                <li class="px-3 py-2">
                                    <div style="font-weight: 600; font-size: 0.9rem; color: #333;">
                                        {{ auth()->user()->name }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: #888;">
                                        {{ auth()->user()->university->name ?? '' }}
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <a class="dropdown-item" href="{{ route('frontend.profile.edit') }}">
                                        <i class="bi bi-person-gear me-2"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('frontend.items.my') }}">
                                        <i class="bi bi-box-seam me-2"></i> My Listings
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('frontend.transactions.borrowing-history') }}">
                                        <i class="bi bi-clock-history me-2"></i> Borrow History
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('frontend.transactions.lending-history') }}">
                                        <i class="bi bi-arrow-up-circle me-2"></i> Lend History
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                @endif
            @endauth

        </div>{{-- end collapse --}}
    </div>{{-- end container --}}
</nav>