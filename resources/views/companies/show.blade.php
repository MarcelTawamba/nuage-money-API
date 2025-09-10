@extends('layouts.app')

@section('content')

    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>
                            Company Details
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <a class="btn btn-default float-right"
                           href="{{ route('companies.index') }}">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="">
            <div class="card-body">
                <div class="row">
                    @include('companies.show_fields')
                </div>
            </div>
        </div>
        <div>
            <h5>
                Exchange Fees
            </h5>
            @include('custom_exchange_rate_margins.table')
        </div>
        <br/>
        <div>
            <h5>
                Operator Fees
            </h5>
            @include('app_fees.table')
        </div>
    </div>


@endsection
