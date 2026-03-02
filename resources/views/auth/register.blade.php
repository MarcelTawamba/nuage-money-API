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
                    <h3 class="title ">Sign Up</h3>
                    <p class="login-box-msg">Create your Nuage Money account</p>
                </div>
                <div class="register-card-body">


                    <form method="post" action="{{ route('register') }}">
                        @csrf
                        <div class="input-group mb-4">
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                   placeholder="Full name" required>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-user"></span></div>
                            </div>
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="input-group">
                                    <input type="email" name="email" value="{{ old('email') }}"
                                           class="form-control @error('email') is-invalid @enderror" placeholder="Email" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                                    </div>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="input-group">
                                    <select name="account_type" class="form-control @error('account_type') is-invalid @enderror cust"  required>
                                        <option disabled selected value="">Type Of Account</option>
                                        <option @if(old('account_type') == "personal") selected @endif value="personal">Personal</option>
                                        <option @if(old('account_type') == "company") selected @endif value="company">Company</option>
                                    </select>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="fas fa-briefcase"></span></div>
                                    </div>
                                </div>
                                @error('account_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="input-group mb-4">
                            <div class="input-group-prepend">
                                <input class="form-control" style="width: 80px; border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;" name="country_code" list="countries" value="{{ old('country_code')  ? old('country_code') : '237' }}" required>
                                <datalist id="countries" >
                                    @foreach($countries as $country)
                                        <option value="{{$country->international_phone}}" >{{$country->name}}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <input required  type="tel" name="phone_number" value="{{ old('phone_number') }}"
                                   class="form-control @error('phone_number') is-invalid @enderror" placeholder="Phone Number" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-phone"></span></div>
                            </div>
                            @error('phone_number')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="input-group mb-4 cant d-none">
                            <input  type="text" name="company_name" value="{{ old('company_name') }}"
                                   class="form-control @error('company_name') is-invalid @enderror" placeholder="Company Name">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-building"></span></div>
                            </div>
                            @error('company_name')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row cant d-none">
                            <div class="col-md-6 mb-4">
                                <div class="input-group">
                                    <input type="text" name="address" value="{{ old('address') }}"
                                           class="form-control @error('address') is-invalid @enderror" placeholder="Company Address">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="fas fa-map-marker"></span></div>
                                    </div>
                                    @error('address')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="input-group">
                                    <select name="company_type"
                                           class="form-control @error('company_type') is-invalid @enderror" >
                                        @foreach(\App\Enums\BusinessType::asArray() as $name)
                                            <option value="{{$name}}" >{{$name}}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="fas fa-tag"></span></div>
                                    </div>
                                </div>
                                @error('company_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="input-group">
                                    <input required type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror" placeholder="Password">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                                    </div>
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="input-group">
                                    <input required type="password" name="password_confirmation" class="form-control"
                                           placeholder="Retype password">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="icheck-primary">
                                    <input required type="checkbox" id="agreeTerms" name="terms" value="agree">
                                    <label for="agreeTerms">
                                        I agree to the <a href="#">terms</a>
                                    </label>
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-12 mt-2 mb-4">
                                <button type="submit" class="btn btn-primary btn-block">Register</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>

                    <a href="{{ route('login') }}" class="text-center">I already have a membership</a>
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
            padding: 2rem;
            max-width: 700px;
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

    <script>

        let input = document.querySelector(".cust");

        let values = document.querySelectorAll('.cant')

        input.addEventListener("change",()=>{

            if(input.value === "personnel"){

                values.forEach(elt =>{
                    console.log(elt);
                    elt.classList.add("d-none")
                });
            }else{
                values.forEach(elt =>{
                    elt.classList.remove("d-none")
                });
            }

        });


    </script>
</x-laravel-ui-adminlte::adminlte-layout>
