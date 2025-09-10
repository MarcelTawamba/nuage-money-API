<div class="card-body p-0">

    <div class="table-responsive">
        <table class="table table-striped table-bordered dataTable "  id="clients-table">
            <thead class="bg-custom-blue">
            <tr>
                <th>User</th>
                <th>Company</th>
                <th>Name</th>
                <th>Client Id</th>
                <th>Redirect</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $client->user_id }}</td>
                    <td> @if($client->company != null)
                            {{ $client->company->name }}
                        @else
                            None
                        @endif
                    </td>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->id }}</td>
                    <td>{{ $client->redirect }}</td>
                    <td  style="width: 120px">
{{--                        {!! Form::open(['route' => ['apps.destroy', $client->id], 'method' => 'delete']) !!}--}}
                        <div class='btn-group'>
                            @if(Auth::user()->is_admin)
                                <a href="{{ route('apps.fund_wallet_admin', [$client->id]) }}"
                                   class='btn btn-default btn-xs'>
                                    <i class="far fa-credit-card"></i>
                                </a>
                            @else
                                <a href="{{ route('exchange-request.create', [$client->id]) }}"
                                   class='btn btn-default btn-xs'>
                                    <i class="fas fa-sync"></i>
                                </a>
                                <a href="{{ route('apps.fund_wallet', [$client->id]) }}"
                                   class='btn btn-default btn-xs'>
                                    <i class="far fa-credit-card"></i>
                                </a>
                            @endif
                            <a href="{{ route('apps.show', [$client->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('apps.edit', [$client->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
{{--                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}--}}
                        </div>
{{--                        {!! Form::close() !!}--}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class=" clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $clients])
        </div>
    </div>
    <br/>
</div>
