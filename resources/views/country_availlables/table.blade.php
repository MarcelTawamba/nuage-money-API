<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-bordered dataTable "  id="country-availlables-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($countryAvaillables as $countryAvaillable)
                <tr>
                    <td>{{ $countryAvaillable->name }}</td>
                    <td>{{ $countryAvaillable->code }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['country-availlables.destroy', $countryAvaillable->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>

                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix" style="background-color: white">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $countryAvaillables])
        </div>
    </div>
</div>
