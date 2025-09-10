@extends('layouts.app')

@section('content')


    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Create Wallets
                        </h1>
                    </div>
                </div>
            </div>
        </section>
        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::open(['route' => 'wallets.store']) !!}

            <div class="card-body">

                <div class="row">
                    @include('wallets.fields')
                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('wallets.index') }}" class="btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
