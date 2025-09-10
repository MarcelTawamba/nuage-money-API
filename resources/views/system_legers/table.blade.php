<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-bordered dataTable "   id="system-legers-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($systemLegers as $systemLeger)
                <tr>
                    <td>{{ $systemLeger->name }}</td>
                    <td>{{ $systemLeger->description }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['system-legers.destroy', $systemLeger->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>

                            <a href="{{ route('system-legers.edit', [$systemLeger->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
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
            @include('adminlte-templates::common.paginate', ['records' => $systemLegers])
        </div>
    </div>
</div>
