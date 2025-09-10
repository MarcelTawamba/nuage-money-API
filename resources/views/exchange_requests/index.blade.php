@extends('layouts.app')

@section('content')
    @include('flash_message')


    <div class="content px-3" style="background-color: white">

        <section class="content-header pb-0">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1>Convert Requests</h1>
                    </div>

                </div>
            </div>
        </section>

        <div class="clearfix"></div>

        <div class="">
            @include('exchange_requests.table')
        </div>
    </div>


@endsection
