@extends('layouts.app')

@section('content')

    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Create Exchange Fee Margins
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::open(['route' => 'exchange-fee-margins.store']) !!}

            <div class="card-body">

                <div class="row">
                    @include('exchange_fee_margins.fields')
                </div>

            </div>

            <div class="card-footer" style="background-color: white">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('exchange-fee-margins.index') }}" class="btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
