<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-bordered dataTable " id="fees-table">
            <thead>
            <tr>
                <th>Country</th>
                <th>Currency</th>
                <th>Method name</th>
                <th>Fees</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($fees as $fee)
                <tr>
                    <td>{{ $fee->country->name }}</td>
                    <td>{{ $fee->currency->name }}</td>
                    <td>{{ $fee->method_name }}</td>
                    <td>{{ $fee->fees }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['fees.destroy', $fee->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('fees.show', [$fee->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('fees.edit', [$fee->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $fees])
        </div>
    </div>
</div>
