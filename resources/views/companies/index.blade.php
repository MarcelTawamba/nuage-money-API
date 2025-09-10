@extends('layouts.app')

@section('content')
    <div class="content " style="background-color: white">
        <section class="content-header pb-0" >
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1>Companies</h1>
                    </div>

                </div>
            </div>
        </section>
        @include('flash::message')

        <div class="clearfix"></div>

        <div class="">
            @include('companies.table')
        </div>
    </div>

@endsection
