@extends('layouts.app')

@section('content')


    <div class="content px-2 px-lg-3 " style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row ">
                    <div class="col-sm-6">
                        <h1>Transactions</h1>
                    </div>

                </div>
            </div>
        </section>
        @include('flash::message')



        <div class="">
            @include('transactions.table')
        </div>
    </div>

@endsection
