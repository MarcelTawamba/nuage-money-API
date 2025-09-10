@extends('layouts.app')

@section('content')

    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>
                            Start Button Bank Details
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <a class="btn btn-default float-right"
                           href="{{ route('start-button-banks.index') }}">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="">
            <div class="card-body">
                <div class="row">
                    @include('start_button_banks.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection
