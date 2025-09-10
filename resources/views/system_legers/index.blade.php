@extends('layouts.app')

@section('content')

    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>System Legers</h1>
                    </div>
                    <div class="col-sm-6">
                        <a class="btn btn-primary float-right"
                           href="{{ route('system-legers.create') }}">
                            Add New
                        </a>
                    </div>
                </div>
            </div>
        </section>

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="">
            @include('system_legers.table')
        </div>
    </div>

@endsection
