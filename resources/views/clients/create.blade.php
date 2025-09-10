@extends('layouts.app')

@section('content')

    <div class="content px-0 px-md-3 " style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Create App
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::open(['route' => 'apps.store']) !!}

            <div class="card-body">

                <div class="row">
                    @include('clients.fields')
                </div>

            </div>

            <div class="ml-3">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('apps.index') }}" class=" ml-2btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
        <br/>
    </div>
@endsection
