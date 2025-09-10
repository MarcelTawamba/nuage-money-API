@extends('layouts.app')

@section('content')
    <section class="content-header">
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

    <div class="content px-3">
        @include('flash::message')
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Name Field -->
                    <div class="col-sm-12">
                        {!! Form::label('name', 'Name:') !!}
                        <p>{{ $user->name }}</p>
                    </div>



                    <!-- Email Field -->
                    <div class="col-sm-12">
                        {!! Form::label('email', 'Email:') !!}
                        <p>{{ $user->email }}</p>
                    </div>

                    <!-- Country Code Field -->
                    <div class="col-sm-12">
                        {!! Form::label('country_code', 'Country Code:') !!}
                        <p>{{ $user->country_code }}</p>
                    </div>

                    <!-- Phone Number Field -->
                    <div class="col-sm-12">
                        {!! Form::label('phone_number', 'Phone Number:') !!}
                        <p>{{ $user->phone_number }}</p>
                    </div>

                </div>
            </div>
            <div class="card-footer">
                <a href="{{route('users.profile.edit')}}" class="btn btn-primary">
                    Update profile
                </a>

            </div>
        </div>
    </div>
@endsection
