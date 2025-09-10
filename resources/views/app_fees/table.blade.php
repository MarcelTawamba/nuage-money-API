<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-bordered dataTable " id="app-fees-table">
            <thead>
            <tr>

                <th>Method</th>
                <th>Payment Type</th>
                <th>Currency</th>
                <th>Fee Type</th>
                <th>Fee</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($fees as $appFee)
                <tr>

                    <td>{{ $appFee->name }}</td>
                    <td>{{ $appFee->type }}</td>
                    <td>{{ $appFee->currency }}</td>
                    <td>{{ $appFee->fee_type }}</td>
                    <td>{{ $appFee->fee }}</td>
                    <td  style="width: 120px">
                         <div class='btn-group'>
                             @if($appFee->id > 0)
                                 <a href="{{ route('custom-fees.edit', [$appFee->id]) }}"
                                    class='btn btn-default btn-xs'>
                                     <i class="far fa-edit"></i>
                                 </a>
                             @else
                                 <a href="{{ route('custom-fees.create', [$company->id,$appFee->operator_id]) }}"
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
