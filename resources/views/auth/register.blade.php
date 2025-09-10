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
                    <p class="login-box-msg">Register a new membership in nuage pay</p>
                </div>
                <div class="register-card-body">


                    <form method="post" action="{{ route('register') }}">
                        @csrf
                        <div class="input-group  mb-4">
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
                            <div class="input-group col-sm-6 mb-4">
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

                            <div class="input-group col-sm-6 mb-4">

                                <select name="account_type" class="form-control @error('account_type') is-invalid @enderror cust"  required>
                                    <option disabled selected value="">Type Of Compte</option>
                                    <option @if(old('account_type') == "personnel") selected @endif value="personnel">Personnel</option>
                                    <option @if(old('account_type') == "company") selected @endif value="company">Company</option>
                                </select>

                                @error('account_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>


                        </div>


                        <div class="input-group mb-4">

                            <input  class="form-control" style="max-width: 100px;" name="country_code" list="countries" value="{{ old('country_code')  ? old('country_code') : '237' }}" required>
                            <datalist id="countries" >
                                @foreach($countries as $country)
                                    <option value="{{$country->international_phone}}" >{{$country->name}}</option>
                                @endforeach
                            </datalist>
                            <input required  type="tel" name="phone_number" value="{{ old('phone_number') }}"
                                   class="form-control @error('phone_number') is-invalid @enderror" placeholder="Phone Number">
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
                                <div class="input-group-text"><span style="opacity: 0" class="fas fa-phone"></span></div>
                            </div>
                            @error('company_name')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row cant d-none">
                            <div class="input-group col-sm-6 mb-4">
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


                            <div class="input-group col-sm-6 mb-4">
                                <select name="company_type"
                                       class="form-control @error('company_type') is-invalid @enderror" >
                                    @foreach(\App\Enums\BusinessType::asArray() as $name)
                                        <option value="{{$name}}" >{{$name}}</option>
                                    @endforeach

                                </select>

                                @error('company_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-sm-6 input-group mb-4">
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
                            <div class="col-sm-6 input-group mb-4">
                                <input required type="password" name="password_confirmation" class="form-control"
                                       placeholder="Retype password">
                                <div class="input-group-append">
                                    <div class="input-group-text"><span class="fas fa-lock"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-8">
                                <div class="icheck-primary">
                                    <input required type="checkbox" id="agreeTerms" name="terms" value="agree">
                                    <label for="agreeTerms">
                                        I agree to the <a href="#">terms</a>
                                    </label>
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-12 mt-3 mb-4">
                                <button style="border-radius: 1rem" type="submit" class="btn btn-primary btn-block">Register</button>
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

    <style>

        body.login-page{
            background-color: #f6f6f6;
        }
        .pages-left {
            background-color: #fff;
        }

        .login-content {

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

        .login-form {
            padding: 0 50px;

            margin: 0 auto;

        }
        .login-content img{
            height: 70px;
        }
        .input-group-append{
            height: 56px !important;
        }

        body{
            overflow-y: scroll;
            overflow-x: hidden;
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
