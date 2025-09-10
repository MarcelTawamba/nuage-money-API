@extends('layouts.app')

@section('content')

    <div class="content px-0 px-md-3 " style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Fund wallet
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::open(['route' => ['apps.fund_wallet_post',$client->id]]) !!}

            <div class="card-body">

                <div class="row">
                    <div class="form-group col-sm-6">
                        {!! Form::label('currency', 'Currency') !!}
                        {!! Form::select('currency', $currency , null, ['class' => 'form-control custom-select']) !!}
                    </div>

                    <!-- Name Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('method', 'Method') !!}
                        <select name="method" class="form-control custom-select" id="method">
                            @foreach($method as $meth)
                                <option value="{{$meth->id}}"  data-type="{{strtolower($meth->method_type)}}" data-currency="{{$meth->currency->name}}" data-country="{{$meth->country->code}}">{{$meth->methodName()}} - {{$meth->country->code}}</option>
                            @endforeach
                        </select>

                    </div>
                    <!-- Code Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('amount', 'Amount:') !!}
                        {!! Form::number('amount', null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Code Field -->
                    <div class="form-group col-sm-6 phone">
                        {!! Form::label('msidn', 'Mobile number:') !!}
                        {!! Form::tel('msidn', null, ['class' => 'form-control', ]) !!}
                    </div>
                    <!-- Code Field -->
                    <div class="form-group col-sm-6 d-none email">
                        {!! Form::label('email', 'email:') !!}
                        {!! Form::email('email', null, ['class' => 'form-control', ]) !!}
                    </div>

                </div>

            </div>

            <div class="ml-4 mb-4">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('home') }}" class="btn btn-default ml-2"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
        <br/>

    </div>

    <script>

        method_input = document.querySelector("#method")
        phone = document.querySelector(".phone")
        email = document.querySelector(".email")
        method_input.addEventListener('change',(e)=>{
            if(method_input.options[method_input.selectedIndex].getAttribute('data-type') === "mobile"){
                 phone.classList.remove("d-none")
                 email.classList.add("d-none")
            }else{
                phone.classList.add("d-none")
                email.classList.remove("d-none")
            }
        })

        if(method_input.options[method_input.selectedIndex].getAttribute('data-type') === "mobile"){
            phone.classList.remove("d-none")
            email.classList.add("d-none")
        }else{
            phone.classList.add("d-none")
            email.classList.remove("d-none")
        }

    </script>
@endsection
