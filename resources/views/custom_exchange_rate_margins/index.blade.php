@extends('layouts.app')

@section('content')

    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Custom Exchange Rate Margins</h1>
                    </div>

                </div>
            </div>
        </section>

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="">
            @include('custom_exchange_rate_margins.table')
        </div>
    </div>

@endsection
