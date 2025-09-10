@extends('layouts.app')

@section('content')


    <div class="content px-md-3" style="background-color: white">
        <section class="content-header pb-0">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Wallets</h1>
                    </div>
                    @if(!Auth::user()->is_admin)
                        <div class="col-sm-6">
                            <a class="btn btn-primary float-right"
                               href="{{ route('wallets.create') }}">
                                Add New
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @include('flash::message')

        <div class="clearfix"></div>

        <div class="">
            @include('wallets.table')
        </div>
    </div>

@endsection
