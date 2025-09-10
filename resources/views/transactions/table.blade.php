{{--<div class="card-body p-0">--}}
{{--    <form method="GET" id="search-form" action="{{route('transactions.index')}}">--}}
{{--        <div class="row pb-2">--}}
{{--            <div class="col-sm-6 col-md-3">--}}
{{--                <div class="dataTables_length" id="datatables-reponsive_length">--}}
{{--                    <label style="display: flex;align-items: center"--}}
{{--                    >Show&nbsp;--}}
{{--                        <select--}}
{{--                            name="limit"--}}
{{--                            onchange="document.querySelector('#search-form').submit()"--}}
{{--                            style="width: 70px;padding-left: 5px;padding-right: 5px"--}}
{{--                            aria-controls="datatables-reponsive"--}}
{{--                            class="form-select form-select-sm limit"--}}
{{--                        >--}}
{{--                            <option value="3" @if($limit == 3) selected @endif>3</option>--}}
{{--                            <option value="10" @if($limit == 10) selected @endif>10</option>--}}
{{--                            <option value="25" @if($limit == 25) selected @endif>25</option>--}}
{{--                            <option value="50" @if($limit == 50) selected @endif>50</option>--}}
{{--                            <option value="100" @if($limit == 100) selected @endif>100</option>--}}
{{--                        </select>--}}
{{--                        &nbsp;entries</label--}}
{{--                    >--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-sm-6 col-md-3">--}}
{{--                <div class="dataTables_length" id="datatables-reponsive_length">--}}
{{--                    <label style="display: flex;align-items: center"--}}
{{--                    >Period&nbsp;--}}
{{--                        <select--}}
{{--                            name="period"--}}
{{--                            onchange="document.querySelector('#search-form').submit()"--}}
{{--                            style="width: 150px;padding-left: 5px;padding-right: 5px"--}}
{{--                            aria-controls="datatables-reponsive"--}}
{{--                            class="form-select form-select-sm "--}}
{{--                        >--}}
{{--                            <option value="" @if($period == '') selected @endif>ALL</option>--}}
{{--                            <option value="day" @if($period == 'day') selected @endif>Day</option>--}}
{{--                            <option value="week" @if($period == 'week') selected @endif>Week</option>--}}
{{--                            <option value="month" @if($period == 'month') selected @endif>Month</option>--}}
{{--                            <option value="semester" @if($period == 'semester') selected @endif>Semester</option>--}}
{{--                            <option value="year" @if($period == 'year') selected @endif>Year</option>--}}
{{--                        </select>--}}
{{--                    </label--}}
{{--                    >--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-sm-6 col-md-3">--}}
{{--                <div class="dataTables_length" id="datatables-reponsive_length">--}}
{{--                    <label style="display: flex;align-items: center"--}}
{{--                    >Type&nbsp;--}}
{{--                        <select--}}
{{--                            name="type"--}}
{{--                            onchange="document.querySelector('#search-form').submit()"--}}
{{--                            style="width: 200px;padding-left: 5px;padding-right: 5px"--}}
{{--                            aria-controls="datatables-reponsive"--}}
{{--                            class="form-select form-select-sm limit"--}}
{{--                        >--}}
{{--                            <option value="" @if($type == '') selected @endif>ALL</option>--}}
{{--                            <option value="pay_in" @if($type == "pay_in") selected @endif>Pay In</option>--}}
{{--                            <option value="pay_out" @if($type == "pay_out") selected @endif>PayOut</option>--}}

{{--                        </select>--}}
{{--                    </label--}}
{{--                    >--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-sm-6 col-md-3">--}}
{{--                <div class="dataTables_length" id="datatables-reponsive_length">--}}
{{--                    <label style="display: flex;align-items: center"--}}
{{--                    >Company&nbsp;--}}
{{--                        <select--}}
{{--                            name="company"--}}
{{--                            onchange="document.querySelector('#search-form').submit()"--}}
{{--                            style="width: 200px;padding-left: 5px;padding-right: 5px"--}}
{{--                            aria-controls="datatables-reponsive"--}}
{{--                            class="form-select form-select-sm limit"--}}
{{--                        >--}}
{{--                            <option value="" @if($company_selected == '') selected @endif>ALL</option>--}}
{{--                            @foreach( $company as $comp)--}}
{{--                                <option value={{$comp->id}} @if($company_selected == $comp->id) selected @endif>{{$comp->name}}</option>--}}
{{--                            @endforeach--}}



{{--                        </select>--}}
{{--                    </label--}}
{{--                    >--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-sm-6 col-md-3">--}}
{{--                <div class="dataTables_length" id="datatables-reponsive_length">--}}
{{--                    <label style="display: flex;align-items: center"--}}
{{--                    >Services&nbsp;--}}
{{--                        <select--}}
{{--                            name="service"--}}
{{--                            onchange="document.querySelector('#search-form').submit()"--}}
{{--                            style="width: 200px;padding-left: 5px;padding-right: 5px"--}}
{{--                            aria-controls="datatables-reponsive"--}}
{{--                            class="form-select form-select-sm limit"--}}
{{--                        >--}}
{{--                            <option value="" @if($service_selected  == '') selected @endif >ALL</option>--}}
{{--                            @foreach( $services as $comp)--}}
{{--                                <option value={{$comp->id}} @if($service_selected == $comp->id) selected @endif >{{$comp->name}}</option>--}}
{{--                            @endforeach--}}

{{--                        </select>--}}
{{--                    </label--}}
{{--                    >--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-sm-6 col-md-3">--}}
{{--                <div class="dataTables_length" id="datatables-reponsive_length">--}}
{{--                    <label style="display: flex;align-items: center"--}}
{{--                    >Wallets&nbsp;--}}
{{--                        <select--}}
{{--                            name="wallet"--}}
{{--                            onchange="document.querySelector('#search-form').submit()"--}}
{{--                            style="width: 200px;padding-left: 5px;padding-right: 5px"--}}

{{--                            aria-controls="datatables-reponsive"--}}
{{--                            class="form-select form-select-sm limit"--}}
{{--                        >--}}
{{--                            <option value="" @if($wallet_selected == '') selected @endif>ALL</option>--}}
{{--                            @foreach( $wallets as $comp)--}}
{{--                                <option value={{$comp->id}} @if($wallet_selected == $comp->id) selected @endif>{{$comp->currency->name}}</option>--}}
{{--                            @endforeach--}}

{{--                        </select>--}}
{{--                    </label--}}
{{--                    >--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </form>--}}
{{--         <div class="table-responsive">--}}
{{--        <table class="table" id="transactions-table">--}}
{{--            <thead>--}}
{{--       <tr>--}}
{{--                <th>Reference</th>--}}
{{--                <th>Wallet</th>--}}
{{--                <th>Amount</th>--}}
{{--                <th>Balance Before</th>--}}
{{--                <th>Balance After</th>--}}
{{--                <th>Refund</th>--}}
{{--                <th>Request Type</th>--}}
{{--                <th>Request Id</th>--}}
{{--                <th colspan="3">Action</th>--}}
{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach($transactions as $transaction)--}}
{{--                <tr>--}}
{{--                    <td>{{ $transaction->reference }}</td>--}}
{{--                    <td>   @if($transaction->wallet->user->name !=null)--}}
{{--                            {{ $transaction->wallet->user->name}}--}}
{{--                        @else--}}
{{--                            {{ $transaction->wallet->user->client_id}}--}}
{{--                        @endif--}}
{{--                        ({{ $transaction->wallet->currency->name }})</td>--}}
{{--                    <td>{{ $transaction->amount }}</td>--}}
{{--                    <td>{{ $transaction->balance_before }}</td>--}}
{{--                    <td>{{ $transaction->balance_after }}</td>--}}
{{--                    <td>{{ $transaction->refund }}</td>--}}
{{--                    <td>{{ $transaction->achatable_type }}</td>--}}
{{--                    <td>{{ $transaction->achatable_id }}</td>--}}
{{--                    <td  style="width: 120px">--}}
{{--                        <div class='btn-group'>--}}
{{--                            <a href="{{ route('transactions.show', [$transaction->id]) }}"--}}
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
{{--            @include('adminlte-templates::common.paginate', ['records' => $transactions])--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}


@push('third_party_stylesheets')
    @include('layouts.datatables_css')
@endpush

<div class="card-body px-0 px-md-4 ">
        <form method="GET" id="search-form" action="{{route('transactions.index')}}">
            <div class="row pb-2">

                <div class="col-sm-6 col-md-4 col-lg-3">

                        <label style="display: flex;align-items: center"
                        >Period&nbsp;
                            <select
                                name="period"
                                onchange="document.querySelector('#search-form').submit()"
                                style="width: 150px;padding-left: 5px;padding-right: 5px"
                                aria-controls="datatables-reponsive"
                                class="form-select form-select-sm "
                            >
                                <option value="" @if($period == '') selected @endif>ALL</option>
                                <option value="day" @if($period == 'day') selected @endif>Day</option>
                                <option value="week" @if($period == 'week') selected @endif>Week</option>
                                <option value="month" @if($period == 'month') selected @endif>Month</option>
                                <option value="semester" @if($period == 'semester') selected @endif>Semester</option>
                                <option value="year" @if($period == 'year') selected @endif>Year</option>
                            </select>
                        </label
                        >

                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="dataTables_length" id="datatables-reponsive_length">
                        <label style="display: flex;align-items: center"
                        >Type&nbsp;
                            <select
                                name="type"
                                onchange="document.querySelector('#search-form').submit()"
                                style="width: 200px;padding-left: 5px;padding-right: 5px"
                                aria-controls="datatables-reponsive"
                                class="form-select form-select-sm limit"
                            >
                                <option value="" @if($type == '') selected @endif>ALL</option>
                                <option value="pay_in" @if($type == "pay_in") selected @endif>Pay In</option>
                                <option value="pay_out" @if($type == "pay_out") selected @endif>PayOut</option>

                            </select>
                        </label
                        >
                    </div>
                </div>
{{--                <div class="col-sm-6 col-md-4 col-lg-3">--}}
{{--                    <div class="dataTables_length" id="datatables-reponsive_length">--}}
{{--                        <label style="display: flex;align-items: center"--}}
{{--                        >Company&nbsp;--}}
{{--                            <select--}}
{{--                                name="company"--}}
{{--                                onchange="document.querySelector('#search-form').submit()"--}}
{{--                                style="width: 200px;padding-left: 5px;padding-right: 5px"--}}
{{--                                aria-controls="datatables-reponsive"--}}
{{--                                class="form-select form-select-sm limit"--}}
{{--                            >--}}
{{--                                <option value="" @if($company_selected == '') selected @endif>ALL</option>--}}
{{--                                @foreach( $company as $comp)--}}
{{--                                    <option value={{$comp->id}} @if($company_selected == $comp->id) selected @endif>{{$comp->name}}</option>--}}
{{--                                @endforeach--}}



{{--                            </select>--}}
{{--                        </label--}}
{{--                        >--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-sm-6 col-md-4 col-lg-3">--}}
{{--                        <label style="display: flex;align-items: center"--}}
{{--                        >Services&nbsp;--}}
{{--                            <select--}}
{{--                                name="service"--}}
{{--                                onchange="document.querySelector('#search-form').submit()"--}}
{{--                                style="width: 200px;padding-left: 5px;padding-right: 5px"--}}
{{--                                aria-controls="datatables-reponsive"--}}
{{--                                class="form-select form-select-sm limit"--}}
{{--                            >--}}
{{--                                <option value="" @if($service_selected  == '') selected @endif >ALL</option>--}}
{{--                                @foreach( $services as $comp)--}}
{{--                                    <option value={{$comp->id}} @if($service_selected == $comp->id) selected @endif >{{$comp->name}}</option>--}}
{{--                                @endforeach--}}

{{--                            </select>--}}
{{--                        </label--}}
{{--                        >--}}
{{--                </div>--}}
                <div class="col-sm-6 col-md-4 col-lg-3">

                        <label style="display: flex;align-items: center"
                        >Wallets&nbsp;
                            <select
                                name="wallet"
                                onchange="document.querySelector('#search-form').submit()"
                                style="width: 200px;padding-left: 5px;padding-right: 5px"

                                aria-controls="datatables-reponsive"
                                class="form-select form-select-sm limit"
                            >
                                <option value="" @if($wallet_selected == '') selected @endif>ALL</option>
                                @foreach( $wallets as $comp)
                                    <option value={{$comp->id}} @if($wallet_selected == $comp->id) selected @endif>{{$comp->user->client == null ?$comp->user->name : $comp->user->client->name }} ({{$comp->currency->name}})</option>
                                @endforeach

                            </select>
                        </label
                        >

                </div>
            </div>
        </form>
    {!! $dataTable->table(['width' => '100%', 'class' => 'table table-striped table-bordered']) !!}
</div>

@push('third_party_scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
@endpush
