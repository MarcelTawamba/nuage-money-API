

<x-laravel-ui-adminlte::adminlte-layout>

    <body class="hold-transition login-page vh-100 vw-100 m-0 p-0">


    <div class="row h-100 vw-100 m-0 p-0">
        <div class="col-lg-6 col-md-12 col-sm-12 d-none d-lg-block">
            <div class="pages-left vh-100">
                <div class="login-content">
                    <a href="{{route('login')}}"><img src="{{url('/images/logo-white.png')}}" class="mb-3" alt=""></a>

                    <p>Your true value is determined by how much more you give in value than you take in payment. ...</p>
                </div>
                <div class="login-media text-center">
                    <img src="https://dompet.dexignlab.com/codeigniter/demo/public/assets/images/login.png" alt="">
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 mx-auto align-self-center">
            <div class="login-form">
                <div class="d-flex justify-content-center">
                    <a  href="{{route('login')}}"><img style="height: 60px" src="{{url('/images/logo-white.png')}}" class="mb-3 d-lg-none" alt=""></a>
                </div>

                <div class="text-center">
                    <h3 class="box-title" style="padding: 2%">Verify Your Email Address</h3>
                </div>
                <div class="register-card-body">

                    <div class="box">


                        <div class="box-body">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">A fresh verification link has been sent to
                                    your email address
                                </div>
                            @endif
                            <p>Before proceeding, please check your email for a verification link. If you did not receive
                                the email,</p>
                            <a class="btn btn-primary d-block mx-auto" style="width: 240px" href="#"
                               onclick="event.preventDefault(); document.getElementById('resend-form').submit();">
                                click here to request another
                            </a>
                            <form id="resend-form" action="{{ route('verification.resend') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.login-box -->
    </body>



    <style>

        body.login-page{
            background-color: #f6f6f6;
        }
        .pages-left {
            background-color: #fff;
        }

        .login-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 70px 50px 0;
            text-align: center;
        }

        .login-media img {
            width: 90%;
        }
        .login-media {
            margin-top: 80px;
        }

        .register-card-body{
            background-color: transparent !important;
        }

        .login-form {
            padding: 0 50px;
            max-width: 600px;
            margin: 0 auto;
        }
        .login-content img{
            height: 70px;
        }

        body{
            overflow: hidden;
        }
        .login-form .login-title {
            text-align: center;
            position: relative;
            margin-bottom: 48px;
            z-index: 1;
            display: flex;
            align-items: center;
        }
        @media (max-width: 600px){
            .login-content {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px 10px 0;
                text-align: center;
            }
            .login-form {
                padding: 0 10px;

            }
        }


    </style>
</x-laravel-ui-adminlte::adminlte-layout>
