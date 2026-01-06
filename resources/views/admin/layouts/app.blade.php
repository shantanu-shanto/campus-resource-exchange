<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Campus Resource Exchange')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-blue: #0d6efd;
            --dark-blue: #0b5ed7;
            --light-blue: #e7f1ff;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .admin-sidebar .logo {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .admin-sidebar .logo h3 {
            font-weight: 700;
            margin: 0;
            font-size: 1.3rem;
        }

        .admin-sidebar .menu {
            list-style: none;
            padding: 20px 0;
        }

        .admin-sidebar .menu-item {
            margin: 0;
        }

        .admin-sidebar .menu-link {
            display: block;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .admin-sidebar .menu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }

        .admin-sidebar .menu-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: white;
            font-weight: 600;
        }

        .admin-sidebar .menu-link i {
            margin-right: 10px;
            width: 20px;
        }

        /* Main Content -->
        .admin-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar -->
        .admin-topbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-topbar h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-topbar-right .dropdown-toggle::after {
            display: none;
        }

        /* Content Area -->
        .admin-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Cards & Sections */
        .admin-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .admin-card-header {
            background: var(--light-blue);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            border-radius: 8px 8px 0 0;
        }

        .admin-card-header h3 {
            margin: 0;
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .admin-card-body {
            padding: 20px;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-blue);
        }

        .stat-card h4 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 10px 0;
        }

        .stat-card p {
            color: #666;
            margin: 0;
            font-size: 0.9rem;
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--primary-blue);
            opacity: 0.2;
            display: block;
            margin-bottom: 10px;
        }

        /* Tables -->
        .admin-table {
            margin: 0;
        }

        .admin-table thead {
            background: var(--light-gray);
        }

        .admin-table th {
            border: none;
            padding: 15px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .admin-table td {
            padding: 15px;
            border-color: var(--border-color);
            vertical-align: middle;
        }

        .admin-table tbody tr:hover {
            background: var(--light-gray);
        }

        /* Badges */
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Buttons -->
        .btn-admin {
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .btn-admin-primary {
            background: var(--primary-blue);
            color: white;
            border: none;
        }

        .btn-admin-primary:hover {
            background: var(--dark-blue);
            color: white;
        }

        .btn-admin-danger {
            background: #dc3545;
            color: white;
            border: none;
        }

        .btn-admin-danger:hover {
            background: #c82333;
            color: white;
        }

        /* Search & Filter -->
        .admin-search {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .admin-search input,
        .admin-search select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
        }

        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                position: fixed;
                left: -100%;
                transition: left 0.3s ease;
                z-index: 1100;
            }

            .admin-sidebar.show {
                left: 0;
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-content {
                padding: 20px;
            }
        }
    </style>

    @yield('extra-css')
</head>
<body>
    <!-- Sidebar -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="logo">
            <h3><i class="bi bi-shield-lock"></i> Admin</h3>
        </div>

        <ul class="menu">
            <li class="menu-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.users.index') }}"
                    class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.items.index') }}"
                    class="menu-link {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
                    <i class="bi bi-bag"></i> Items
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.reports.index') }}"
                    class="menu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-flag"></i> Reports
                    @php $pendingReports = \App\Models\Report::where('status', 'open')->count(); @endphp
                    @if ($pendingReports > 0)
                        <span class="badge bg-danger rounded-pill float-end">{{ $pendingReports }}</span>
                    @endif
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.settings.index') }}"
                    class="menu-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>

            <li class="menu-item" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px;">
                <a href="{{ route('frontend.dashboard') }}" class="menu-link">
                    <i class="bi bi-arrow-left"></i> Back to Site
                </a>
            </li>

            <li class="menu-item">
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="menu-link w-100 text-start" style="background: none; border: none; cursor: pointer;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <h2>@yield('page-title', 'Dashboard')</h2>
            <div class="admin-topbar-right">
                <button class="btn btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-link text-dark text-decoration-none" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle" style="font-size: 1.5rem;"></i>
                        {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('frontend.profile.edit') }}">My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div class="admin-content">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-triangle"></i> Error!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('show');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth < 768 && sidebar && toggle) {
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Auto-close alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>

    @yield('extra-js')
</body>
</html>
