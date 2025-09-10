@extends('layouts.app')

@section('content')

    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Custom Fees</h1>
                    </div>
                    <div class="col-sm-6">
                        <a class="btn btn-primary float-right"
                           href="{{ route('custom-fees.create') }}">
                            Add New
                        </a>
                    </div>
                </div>
            </div>
        </section>

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="">
            @include('app_fees.table')
        </div>
    </div>

@endsection
