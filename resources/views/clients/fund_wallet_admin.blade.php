@extends('layouts.app')

@section('content')


    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Fund Wallet
                        </h1>
                    </div>
                </div>
            </div>
        </section>
        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::open(['route' => ['apps.fund_wallet_admin_post',$client->id]]) !!}

            <div class="card-body">

                <div class="row">
                    <div class="form-group col-sm-6">
                        {!! Form::label('currency', 'Currency') !!}
                        {!! Form::select('currency', $currency , null, ['class' => 'form-control custom-select']) !!}
                    </div>

                    <!-- Code Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('country', 'Country:') !!}
                        {!! Form::select('country', $country , null, ['class' => 'form-control custom-select']) !!}
                    </div>

                    <!-- Code Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('amount', 'Amount:') !!}
                        {!! Form::number('amount', null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Code Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('reference', 'Reference:') !!}
                        {!! Form::text('reference', null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Code Field -->
                    <div class="form-group col-sm-12">
                        {!! Form::label('description', 'Description:') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'required']) !!}
                    </div>

                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('apps.index') }}" class="btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
