@extends('layouts.app')

@section('content')

    <div class="content px-0 px-md-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Create Companies
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::open(['route' => 'companies.store']) !!}

            <div class="card-body">

                <div class="row">
                    @include('companies.fields')
                </div>

            </div>

            <div class="ml-3">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('companies.index') }}" class="btn btn-default ml-2"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
        <br/>
    </div>
@endsection
