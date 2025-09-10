@extends('layouts.app')

@section('content')


    <div class="content px-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            Edit System Leger
                        </h1>
                    </div>
                </div>
            </div>
        </section>
        @include('adminlte-templates::common.errors')

        <div class="">

            {!! Form::model($systemLeger, ['route' => ['system-legers.update', $systemLeger->id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('system_legers.fields')
                </div>
            </div>

            <div class="card-footer" style="background-color: white">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('system-legers.index') }}" class="btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
