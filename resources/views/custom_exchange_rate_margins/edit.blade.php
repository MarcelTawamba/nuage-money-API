@extends('layouts.app')

@section('content')

    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Edit Custom Exchange Rate For {{$company->name}}
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::model($customExchangeRateMargin, ['route' => ['custom-exchange-rate-margins.update', $customExchangeRateMargin->id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('custom_exchange_rate_margins.fields')
                </div>
            </div>

            <div class="card-footer" style="background-color: white">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('companies.show',$customExchangeRateMargin->company_id) }}" class="btn btn-default"> Cancel </a>
            </div>
            {!! Form::close() !!}

        </div>
    </div>
@endsection
