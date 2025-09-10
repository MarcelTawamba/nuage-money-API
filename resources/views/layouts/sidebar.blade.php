<aside class="main-sidebar sidebar-dark-primary elevation-3 ">

        <a href="{{ route('home') }}" class="brand-link">
            <img src="{{url('build/images/apple-touch-icon-152x152.png')}}"
                 alt="{{ config('app.name') }}"
                 class="brand-image img-circle elevation-3">
            <span class="brand-text font-weight-light">Nuage Pay</span>
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
    .main-sidebar{
        background-color: white;
        margin: 10px 20px 20px;
        width: 250px;
        border-radius: 10px;
        height: 98vh;
        bottom: 10px !important;

    }
    @media (min-width: 992px){
        .sidebar-mini.sidebar-collapse .main-sidebar, .sidebar-mini.sidebar-collapse .main-sidebar:before {
            margin: 10px 20px 20px;
            width: 4.6rem;
        }
    }



    .main-sidebar .sidebar .nav a{
        color: #2B5587;
        width: calc(250px - 1rem)

    }
    .main-sidebar .brand-link{
        width: calc(266px - 1rem)
    }

    a.nav-link.active{
        background-color:  #6EAFFB !important;
        color: white !important;
    }
    a.nav-link:hover{
        background-color:  #6EAFFB !important;
        color: white !important;
    }

    .main-sidebar  .brand-text{
        color: black;
    }

    @media (min-width: 768px){
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-footer, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-header {
            transition: margin-left .3s ease-in-out;
            margin-left: 290px;
        }
    }
    @media (max-width: 991.98px){
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-footer, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-header {
            margin-left: 0;
        }
    }
    @media (min-width: 992px){
        .sidebar-mini.sidebar-collapse .content-wrapper, .sidebar-mini.sidebar-collapse .main-footer, .sidebar-mini.sidebar-collapse .main-header {
            margin-left: calc(4.6rem + 40px)!important;
        }
    }

    @media (max-width: 991.98px){
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-footer, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-header {
             margin-left: 10px !important;
            margin-right: 10px !important;
            width: calc(100% - 20px)  !important;
        }
    }




</style>
