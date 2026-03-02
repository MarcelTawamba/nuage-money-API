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
                        <p>Sign in to your account to start using Nuage Money</p>
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



@include('layouts.coinflow_design')

<style>
    body.login-page {
        overflow-y: auto;
    }

        .pages-left {
            background-color: var(--bg-input);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-content {
            max-width: 500px;
            padding: 2rem;
        }

        .login-content p {
            font-family: 'Red Hat Display', sans-serif;
            color: var(--text-body);
            line-height: 1.6;
        }

        .login-media img {
            max-width: 80%;
            height: auto;
        }

        .login-form {
            padding: 3rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .btn-primary {
            height: 3.5rem;
            font-size: 1.1rem;
        }

        a {
            color: var(--primary-lilac);
            font-weight: 500;
        }

        a:hover {
            color: var(--primary-lilac-hover);
        }

        .icheck-primary > input:first-child:checked + label::before {
            background-color: var(--primary-lilac);
            border-color: var(--primary-lilac);
        }

        @media (max-width: 991.98px) {
            .login-form {
                padding: 1.5rem;
            }
        }
    </style>
</x-laravel-ui-adminlte::adminlte-layout>
