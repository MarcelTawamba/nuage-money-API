<x-laravel-ui-adminlte::adminlte-layout>
    <style>
        body, .layout-fixed{
            background-color: #B8E2F9 !important;
        }
        *::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        *::-webkit-scrollbar-track {
            background-color: white;
        }

        *::-webkit-scrollbar-thumb {
            background-color: #818181;
            border-radius: 100px;
            margin: 12px;
        }

        .toast {
            position: fixed;
            bottom: 0px;
            right: 25px;
            max-width: 300px;
            background: #fff;
            padding: 0.5rem;
            border-radius: 4px;
            box-shadow: -1px 1px 10px
            rgba(0, 0, 0, 0.3);
            z-index: 1023;
            animation: slideInRight 0.1s
            ease-in-out forwards,
            fadeOut 8s ease-in-out
            forwards 2s;
            transform: translateX(110%);
        }

        .toast.closing {
            animation: slideOutRight 8s
            ease-in-out forwards;
        }

        .toast-progress {
            position: absolute;
            display: block;
            bottom: 0;
            left: 0;
            height: 4px;
            width: 100%;
            background: #b7b7b7;
            animation: toastProgress 3s
            ease-in-out forwards;
        }

        .toast-content-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toast-icon {
            padding: 0.35rem 0.5rem;
            font-size: 1.5rem;
        }

        .toast-message {
            flex: 1;
            font-size: 0.9rem;
            color: #000000;
            padding: 0.5rem;
        }

        .toast.toast-success {
            background: #95eab8;
        }

        .toast.toast-success .toast-progress {
            background-color: #2ecc71;
        }

        .toast.toast-danger {
            background: #efaca5;
        }

        .toast.toast-danger .toast-progress {
            background-color: #e74c3c;
        }

        .toast.toast-info {
            background: #bddaed;
        }

        .toast.toast-info .toast-progress {
            background-color: #3498db;
        }

        .toast.toast-warning {
            background: #ead994;
        }

        .toast.toast-warning .toast-progress {
            background-color: #f1c40f;
        }


        @keyframes slideInRight {
            0% {
                transform: translateX(110%);
            }

            75% {
                transform: translateX(-10%);
            }

            100% {
                transform: translateX(0%);
            }
        }

        @keyframes slideOutRight {
            0% {
                transform: translateX(0%);
            }

            25% {
                transform: translateX(-10%);
            }

            100% {
                transform: translateX(110%);
            }
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        @keyframes toastProgress {
            0% {
                width: 100%;
            }

            100% {
                width: 0%;
            }
        }

        .main-header{
            top: 0;
            margin-top: 10px;
            width: calc(100vw - 322px);
            margin-left: 0 !important;
            right: 16px;
            border-radius: 10px;
            position: fixed;

        }

        .sidebar-collapse .main-header{
            top: 0;
            width: calc(100vw - 4.6rem - 72px);
            left: 0;
            position: fixed;

        }
        @media (max-width: 991.98px){
            .
            .sidebar-collapse .main-header{
                width: calc(100vw - 38px);
            }
        }
        table{
            overflow-x: auto;
        }
        table > thead {
            background-color: #5285c2 !important;
            color: white;
        }


        .bg-custom-blue {
            background-color: #6EAFFB;
            color: white !important;
        }

        .text-custom-blue{
            color: #6EAFFB;
        }

        .text-dark-blue{
            color: #2B5587;
        }

        .dataTables_wrapper.dt-bootstrap4{
            overflow-x: auto;
        }

        .nav-link.dropdown-toggle:hover{
            background-color: transparent !important; ;
            color: black !important;
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
                            <img src="{{url('build/images/apple-touch-icon-152x152.png')}}"
                                class="user-image img-circle elevation-2" alt="User Image">
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- User image -->
                            <li class="user-header bg-primary">
                                <img src="{{url('build/images/apple-touch-icon-152x152.png')}}"
                                    class="img-circle elevation-2" alt="User Image">
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
            <div class="content-wrapper mr-3" style="margin-top: 80px !important; background-color: transparent !important; ">
                @yield('content')
            </div>

        </div>

        <div class="toast-overlay" id="toast-overlay"></div>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>
