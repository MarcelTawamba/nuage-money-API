<x-laravel-ui-adminlte::adminlte-layout>
    @include('layouts.coinflow_design')

    <style>

        /* Cards */
        .card, .modal-content {
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-subtle);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            background-color: #FFFFFF;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-subtle);
            padding: 1.25rem 1.5rem;
        }

        /* Buttons */
        .btn {
            border-radius: var(--radius-md);
            font-weight: 500;
            padding: 0.6rem 1.25rem;
            transition: all 0.2s ease;
            font-family: 'Outfit', sans-serif;
        }

        .btn-primary, .bg-custom-blue {
            background-color: var(--primary-lilac) !important;
            border-color: var(--primary-lilac) !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 14px 0 rgba(116, 58, 237, 0.3);
        }

        .btn-primary:hover, .bg-custom-blue:hover {
            background-color: var(--primary-lilac-hover) !important;
            border-color: var(--primary-lilac-hover) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(116, 58, 237, 0.4);
        }

        .btn-success {
            background-color: #10B981 !important;
            border-color: #10B981 !important;
            box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.3);
        }

        /* Form Controls */
        .form-control, .custom-select {
            background-color: var(--bg-input);
            border: 0.5px solid transparent;
            border-radius: var(--radius-xl);
            height: 3rem;
            padding: 0.5rem 1rem;
            color: var(--secondary-midnight);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            background-color: #FFFFFF;
            border-color: var(--primary-lilac);
            box-shadow: 0 0 0 2px var(--primary-lilac-light);
            outline: none;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--secondary-midnight);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        /* Navbar */
        .main-header {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border-subtle) !important;
            margin-top: 0 !important;
            top: 0 !important;
            right: 0 !important;
            width: 100% !important;
            border-radius: 0 !important;
            position: sticky !important;
        }

        /* Sidebar Item - Active State */
        .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
            background-color: var(--primary-lilac-light) !important;
            color: var(--primary-lilac) !important;
            font-weight: 600;
        }

        .nav-sidebar .nav-link:hover {
            background-color: var(--primary-lilac-light) !important;
            color: var(--primary-lilac) !important;
        }

        /* Toast Styles (Simplified) */
        .toast {
            border-radius: var(--radius-xl);
            padding: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Avatar Initials */
        .user-avatar-small, .user-avatar-large {
            background: var(--primary-lilac) !important;
            font-family: 'Outfit', sans-serif;
        }

        .user-header.bg-primary {
            background: var(--secondary-midnight) !important;
        }

    </style>

    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Main Header -->
            <nav class="elevation-3 main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                                class="fas fa-bars"></i></a>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <div class="user-avatar-small" data-initials="{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- User image -->
                            <li class="user-header bg-primary">
                                <div class="user-avatar-large" data-initials="{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <a href="{{route('users.profile.edit')}}" class="btn btn-default btn-flat">Profile</a>
                                <a href="#" class="btn btn-default btn-flat float-right"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Sign out
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- Left side column. contains the logo and sidebar -->
            @include('layouts.sidebar')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper mr-3" style="margin-top: 80px !important; background-color: transparent !important;">
                <div class="content-inner">
                    @yield('content')
                </div>
            </div>

        </div>

        <div class="toast-overlay" id="toast-overlay"></div>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>
