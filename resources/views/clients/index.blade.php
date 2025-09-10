@extends('layouts.app')

@section('content')


    <div class="content px-2 px-md-3 " style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Apps</h1>
                    </div>
{{--                    @if(!Auth::user()->is_admin)--}}
{{--                        <div class="col-sm-6">--}}
{{--                            <a class="btn btn-primary float-right"--}}
{{--                               href="{{ route('apps.create') }}">--}}
{{--                                Add New--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    @endif--}}

                </div>
            </div>
        </section>
        @include('flash::message')

        <div class="clearfix"></div>

        <div class="">
            @include('clients.table')
        </div>
    </div>

@endsection
