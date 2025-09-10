@extends('layouts.app')

@section('content')

    <div class="content px-0 px-md-3 " style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Send Fund
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::open(['route' => ['apps.withdraw_post',$client->id]]) !!}

            <div class="card-body">

                <div class="row">
                    <div class="form-group col-sm-6">
                        {!! Form::label('currency', 'Currency') !!}
                        {!! Form::select('currency', $currency , null, ['class' => 'form-control custom-select currency']) !!}
                    </div>

                    <!-- Name Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('method', 'Method') !!}


                        <select name="method" class="form-control custom-select" id="method">
                            <option  value="" data-type="" data-currency="" data-country="" >Choose the Method</option>
                            @foreach($method as $meth)
                                <option @if(old("method") == $meth->id) selected @endif value="{{$meth->id}}"  data-type="{{strtolower($meth->method_type)}}" data-currency="{{$meth->currency->name}}" data-country="{{$meth->country->code}}">{{$meth->methodName()}} - {{$meth->country->code}}</option>
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
                        {!! Form::label('bank_code', 'Bank code:') !!}

                        {!! Form::select('bank_code', $bank_codes , null, ['class' => 'form-control custom-select']) !!}
                    </div>
                    <!-- Code Field -->
                    <div class="form-group col-sm-6 d-none email">
                        {!! Form::label('account_name', 'Account Name:') !!}
                        {!! Form::text('account_name', null, ['class' => 'form-control', ]) !!}
                    </div>
                    <!-- Code Field -->
                    <div class="form-group col-sm-6 d-none email">
                        {!! Form::label('account_number', 'Account Number:') !!}
                        {!! Form::text('account_number', null, ['class' => 'form-control', ]) !!}
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

        currency = document.querySelector(".currency")

        method_input = document.querySelector("#method")
        phones = document.querySelectorAll(".phone")
        emails = document.querySelectorAll(".email")
        method_input.addEventListener('change',(e)=>{
            if(method.options[method.selectedIndex].getAttribute('data-type') === "mobile"){
                phones.forEach(phone=>{
                    phone.classList.remove("d-none")
                })
                emails.forEach(email=>{
                    email.classList.add("d-none")
                })

            }else{
                phones.forEach(phone=>{
                    phone.classList.add("d-none")
                })
                emails.forEach(email=>{
                    email.classList.remove("d-none")
                })

            }
        })



        currency.addEventListener('change',(e)=>{

            for(let i =0 ; i<method_input.children.length ; i++ ) {

                if(method_input.children[i].dataset.currency.toLowerCase() === currency.options[currency.selectedIndex].text.toLowerCase() || method_input.children[i].dataset.currency.toLowerCase() === "" ){

                    method_input.children[i].classList.remove("d-none")
                }else{
                    method_input.children[i].classList.add("d-none")
                }
            }

        })



        for(let i =0 ; i<method_input.children.length ; i++ ) {


            if (method_input.children[i].dataset.currency.toLowerCase() === currency.options[currency.selectedIndex].text.toLowerCase() || method_input.children[i].dataset.currency.toLowerCase() === ""  ){

                method_input.children[i].classList.remove("d-none")
            }else{
                method_input.children[i].classList.add("d-none")
            }
        }
        if(method.options[method.selectedIndex].getAttribute('data-type') === "mobile"){
            phones.forEach(phone=>{
                phone.classList.remove("d-none")
            })
            emails.forEach(email=>{
                email.classList.add("d-none")
            })


        }else{
            phones.forEach(phone=>{
                phone.classList.add("d-none")
            })
            emails.forEach(email=>{
                email.classList.remove("d-none")
            })
        }

    </script>
@endsection
