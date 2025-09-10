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
                        <h3 class="title ">Sign In</h3>
                        <p>Sign in to your account to start using Nuage Pay</p>
                    </div>
                    <div class=" login-card-body">

                        <form method="post" action="{{ url('/login') }}">
                            @csrf

                            <div class="input-group mb-4">
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email"
                                       class="form-control @error('email') is-invalid @enderror">
                                <div class="input-group-append">
                                    <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                                </div>
                                @error('email')
                                <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="input-group mb-5">
                                <input type="password" name="password" placeholder="Password"
                                       class="form-control @error('password') is-invalid @enderror">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-lock"></span>
                                    </div>
                                </div>
                                @error('password')
                                <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror

                            </div>

                            <div class="row">
                                <div class="col-6 mt-2">
                                    <div class="icheck-primary">
                                        <input type="checkbox" id="remember">
                                        <label for="remember">Remember Me</label>
                                    </div>
                                </div>

                                <div class="col-6 mt-2">
                                    <p class="mb-1">
                                        <a style="text-align:right;display: block" href="{{ route('password.request') }}">I forgot my password</a>
                                    </p>

                                </div>
                                <div class="col-12 mt-3">
                                    <button style="border-radius: 1rem" type="submit" class="btn btn-primary btn-block">Sign In</button>
                                </div>

                            </div>

                        </form>

                        <p class="mt-5">
                            <span>Not registered?</span> <a href="{{ route('register') }}" class="text-center">Register now</a>
                        </p>


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

        .login-card-body{
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
        .form-control {
            background: #fff;
            border: 0.0625rem solid #e6e6e6;
            padding: 0.3125rem 1.25rem;
            color: #6e6e6e;
            height: 3.5rem;
            border-radius: 1rem;
        }
        .input-group-append{
            background-color: white;
            border-top-right-radius: 1rem !important;
            border-bottom-right-radius: 1rem !important;
        }
        .input-group-text{
            background-color: white;
            border: 0.0625rem solid #e6e6e6;

            border-top-right-radius: 1rem !important;
            border-bottom-right-radius: 1rem !important;
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


    </style>
</x-laravel-ui-adminlte::adminlte-layout>
