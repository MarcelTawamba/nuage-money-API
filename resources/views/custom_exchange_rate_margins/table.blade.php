
<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-bordered dataTable" id="companies-table">
            <thead>
            <tr>
                <th>From Currency</th>
                <th>To Currency</th>
                <th>margin</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($margins as $margin)
                <tr>
                    <td>{{ $margin->from_currency }}</td>
                    <td>{{ $margin->to_currency }}</td>
                    <td>{{ $margin->margin }}</td>

                    <td  style="width: 120px">

                        <div class='btn-group'>
                            @if($margin->id > 0)
                                <a href="{{ route('custom-exchange-rate-margins.edit', [$margin->id]) }}"
                                   class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                </a>
                            @else
                                <a href="{{ route('custom-exchange-rate-margins.create', [$company->id,$margin->rate_id]) }}"
                                   class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                </a>
                            @endif
                        </div>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>

{{--@push('third_party_stylesheets')--}}
{{--    @include('layouts.datatables_css')--}}
{{--@endpush--}}

{{--<div class="card-body px-4">--}}
{{--    {!! $dataTable->table(['width' => '100%', 'class' => 'table table-striped table-bordered']) !!}--}}
{{--</div>--}}

{{--@push('third_party_scripts')--}}
{{--    @include('layouts.datatables_js')--}}
{{--    {!! $dataTable->scripts() !!}--}}
{{--@endpush--}}
