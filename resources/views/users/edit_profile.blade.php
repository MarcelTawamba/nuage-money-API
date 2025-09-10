@extends('layouts.app')

@section('content')
    <div style="background-color: white">
        <section class="content-header" style="max-width: 1200px">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="text-center">
                            Profile
                        </h1>
                    </div>
                </div>
            </div>
        </section>
        @include('adminlte-templates::common.errors')
        <div class="content px-3" style="max-width: 1200px ;">
            <div class="">
                {!! Form::model($user, ['route' => ['users.profile.update'], 'method' => 'post']) !!}
                <div class="card-body" >
                    <div class="row">
                        <!-- Name Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('name', 'Name:') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'required', 'minlength' => 3]) !!}
                        </div>


                        <!-- Email Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('email', 'Email:') !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'required','disabled']) !!}
                        </div>

                        <!-- Country Code Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('country_code', 'Country Code:') !!}
                            {!! Form::select('country_code', $pays, null, ['class' => 'form-control custom-select']) !!}
                        </div>

                        <!-- Phone Number Field -->
                        <div class="form-group col-sm-6 " >
                            {!! Form::label('phone_number', 'Phone Number:') !!}
                            <div class="row px-2">
                                <div class="col-sm-3 m-0 p-0">
                                    <input style="border-bottom-right-radius: 0;border-top-right-radius: 0" class="form-control m-0 " name="phone_code" list="countries" value="237" required>
                                    <datalist id="countries" >
                                        @foreach($countries as $country)
                                            <option value="{{$country->international_phone}}" >{{$country->name}}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="col-sm-9 m-0 p-0">
                                    {!! Form::text('phone_number', null, ['class' => 'form-control m-0', 'required', 'minlength' => 8 ,"style"=>"border-bottom-left-radius: 0;border-top-left-radius: 0" ]) !!}
                                </div>
                            </div>


                        </div>


                    </div>
                </div>
                <div class="ml-4" >
                    <button href="" class="btn btn-primary">
                        Update profile
                    </button>

                </div>

                {!! Form::close() !!}
            </div>
        </div>

        @if(\Illuminate\Support\Facades\Auth::user()->account_type =="company")
            <section class="content-header mt-3">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h1 class="">
                                Company
                            </h1>
                        </div>
                    </div>
                </div>
            </section>

            <div class="content px-3" style="max-width: 1200px ;">
                <div class="">
                    {!! Form::model($company, ['route' => ['companies.update', $company->id], 'method' => 'patch']) !!}
                    <div class="card-body" >
                        <div class="row">
                            <!-- Name Field -->
                            <div class="form-group col-sm-6">
                                {!! Form::label('name', 'Name:') !!}
                                {!! Form::text('name', null, ['class' => 'form-control', 'required', 'minlength' => 4]) !!}
                            </div>

                            <!-- Company Type Field -->
                            <div class="form-group col-sm-6">
                                {!! Form::label('company_type', 'Company Type:') !!}
                                {!! Form::select('company_type', \App\Enums\BusinessType::asArray(), null, ['class' => 'form-control custom-select']) !!}
                            </div>

                            <!-- Address Field -->
                            <div class="form-group col-sm-6">
                                {!! Form::label('address', 'Address:') !!}
                                {!! Form::text('address', null, ['class' => 'form-control', 'required']) !!}
                            </div>

                            <!-- Phone Number Field -->
                            <div class="form-group col-sm-6">
                                {!! Form::label('phone_number', 'Phone Number:') !!}
                                {!! Form::text('phone_number', null, ['class' => 'form-control']) !!}
                            </div>


                        </div>
                    </div>
                    <div class="ml-4" >
                        <button href="" class="btn btn-primary">
                            Update Company
                        </button>

                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        @endif


        <section class="content-header mt-3">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="">
                            Password
                        </h1>
                    </div>
                </div>
            </div>
        </section>
        <div class="content px-3 " style="max-width: 1200px">

            <div>
                {!! Form::model($user, ['route' => ['users.change.password'], 'method' => 'post']) !!}
                <div class="card-body" >
                    <div class="row">


                        <!-- password Field -->
                        <div class="form-group col-sm-12">
                            {!! Form::label('old_password', 'Old Password:') !!}
                            {!! Form::password('old_password', ['class' => 'form-control', 'required']) !!}
                        </div>
                        <!-- password Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('password', 'New Password:') !!}
                            {!! Form::password('new_password', ['class' => 'form-control', 'required', 'minlength' => 4]) !!}
                        </div>
                        <!-- password Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('password_confirmation', 'Comfirm Password:') !!}
                            {!! Form::password('new_password_confirmation', ['class' => 'form-control', 'required', 'minlength' => 4]) !!}
                        </div>

                    </div>
                </div>
                <div class="ml-4">
                    <button class="btn btn-primary">
                        Change Password
                    </button>

                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <br/>
    </div>

@endsection
