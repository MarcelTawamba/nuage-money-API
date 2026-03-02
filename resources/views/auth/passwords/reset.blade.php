


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
                    <h3 class="title ">Reset Password</h3>
                    <p class="login-box-msg">You are only one step a way from your new password, recover your password
                        now.</p>
                </div>

                <div class=" login-card-body">


                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf

                        @php
                            if (!isset($token)) {
                                $token = \Request::route('token');
                            }
                        @endphp

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="input-group mb-4">
                            <input type="email" name="email"
                                   class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                   placeholder="Email">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                            </div>
                            @if ($errors->has('email'))
                                <span class="error invalid-feedback">{{ $errors->first('email') }}</span>
                            @endif
                        </div>

                        <div class="input-group mb-4">
                            <input type="password" name="password"
                                   class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                   placeholder="Password">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                            @if ($errors->has('password'))
                                <span class="error invalid-feedback">{{ $errors->first('password') }}</span>
                            @endif
                        </div>

                        <div class="input-group mb-4">
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="Confirm Password">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                            @if ($errors->has('password_confirmation'))
                                <span
                                    class="error invalid-feedback">{{ $errors->first('password_confirmation') }}</span>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button style="border-radius: 1rem" type="submit" class="btn btn-primary btn-block">Reset Password</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>

                    <p class="mt-3 mb-1">
                        <a href="{{ route('login') }}">Login</a>
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

        @media (max-width: 991.98px) {
            .login-form {
                padding: 1.5rem;
            }
        }
    </style>
</x-laravel-ui-adminlte::adminlte-layout>
