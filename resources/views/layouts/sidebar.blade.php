<aside class="main-sidebar sidebar-dark-primary elevation-3 ">

        <a href="{{ route('home') }}" class="brand-link">
            <img src="{{url('/images/logo.png')}}"
                 alt="Nuage Money"
                 class="brand-image elevation-2"
                 style="opacity: 1; max-height: 40px; width: auto;">
            <span class="brand-text font-weight-light">Nuage Money</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    @include('layouts.menu')
                </ul>
            </nav>
        </div>

</aside>

    <style>
        .main-sidebar {
            background-color: #FFFFFF !important;
            margin: 1rem;
            width: 260px;
            border-radius: var(--radius-xl);
            height: calc(100vh - 2rem);
            bottom: 1rem !important;
            border: 1px solid var(--border-subtle);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .brand-link {
            border-bottom: 1px solid var(--border-subtle) !important;
            padding: 1.5rem 1rem !important;
        }

        .brand-text {
            color: var(--secondary-midnight) !important;
            font-size: 1.25rem !important;
        }

        .nav-sidebar .nav-link {
            color: var(--secondary-midnight) !important;
            border-radius: var(--radius-md);
            margin-bottom: 0.25rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .nav-sidebar .nav-link.active {
            background-color: var(--primary-lilac-light) !important;
            color: var(--primary-lilac) !important;
            font-weight: 600;
        }

        .nav-sidebar .nav-link:hover {
            background-color: var(--primary-lilac-light) !important;
            color: var(--primary-lilac) !important;
        }

        .nav-sidebar .nav-icon {
            color: var(--primary-lilac);
            margin-right: 0.5rem;
        }

        @media (min-width: 992px) {
            .sidebar-mini.sidebar-collapse .main-sidebar {
                width: 4.6rem;
                margin: 1rem;
            }
            
            body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper,
            body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-header {
                margin-left: 300px !important;
            }
        }

        @media (max-width: 991.98px) {
            .main-sidebar {
                margin: 0;
                height: 100vh;
                border-radius: 0;
            }
            .content-wrapper {
                margin-left: 0 !important;
            }
        }
    </style>
