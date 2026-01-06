<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="{{ route('home') }}" style="font-weight: 700; font-size: 1.3rem;">
            <i class="bi bi-book"></i> Campus Exchange
        </a>

        <!-- Toggler for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Public Links -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('frontend.items.index') }}">
                        <i class="bi bi-bag"></i> Browse Items
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('frontend.search.index') }}">
                        <i class="bi bi-search"></i> Search
                    </a>
                </li>

                @auth
                    <!-- Authenticated User Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.dashboard') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.items.my') }}">
                            <i class="bi bi-bookmark"></i> My Items
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.transactions.index') }}">
                            <i class="bi bi-arrow-left-right"></i> Transactions
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.messages.index') }}">
                            <i class="bi bi-chat-dots"></i> Messages
                        </a>
                    </li>

                    @if (auth()->user()->is_admin)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-shield-lock"></i> Admin
                            </a>
                        </li>
                    @endif

                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('frontend.profile.edit') }}">
                                    <i class="bi bi-person-gear"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <!-- Guest Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light" href="{{ route('register') }}">
                            <i class="bi bi-person-plus"></i> Sign Up
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
