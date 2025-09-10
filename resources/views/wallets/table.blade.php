{{--<div class="card-body p-0">--}}
{{--    <div class="table-responsive">--}}
{{--        <table class="table" id="wallets-table">--}}
{{--            <thead>--}}
{{--            <tr>--}}
{{--                <th>Owner</th>--}}
{{--                <th>Currency</th>--}}
{{--                <th>Balance</th>--}}
{{--                <th colspan="3">Action</th>--}}
{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach($wallets as $wallet)--}}
{{--                <tr>--}}
{{--                    <td>--}}
{{--                        @if($wallet->user->name !=null)--}}
{{--                            {{ $wallet->user->name}}--}}
{{--                        @else--}}
{{--                            {{ $wallet->user->client_id}}--}}
{{--                        @endif--}}
{{--                    </td>--}}
{{--                    <td>{{ $wallet->currency->name }}</td>--}}
{{--                    <td>{{ $wallet->balance }}</td>--}}
{{--                    <td  style="width: 120px">--}}

{{--                        <div class='btn-group'>--}}
{{--                            <a href="{{ route('wallets.show', [$wallet->id]) }}"--}}
{{--                               class='btn btn-default btn-xs'>--}}
{{--                                <i class="far fa-eye"></i>--}}
{{--                            </a>--}}
{{--                        </div>--}}

{{--                    </td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
{{--        </table>--}}
{{--    </div>--}}

{{--    <div class="card-footer clearfix">--}}
{{--        <div class="float-right">--}}
{{--            @include('adminlte-templates::common.paginate', ['records' => $wallets])--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}


@push('third_party_stylesheets')
    @include('layouts.datatables_css')
@endpush

<div class="card-body px-0 px-md-4 ">
    {!! $dataTable->table(['width' => '100%', 'class' => 'table table-striped table-bordered']) !!}
</div>

@push('third_party_scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
@endpush
