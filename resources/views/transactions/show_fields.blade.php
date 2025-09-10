<!-- Reference Field -->
<div class="col-sm-12">
    {!! Form::label('reference', 'Reference:') !!}
    <p>{{ $transaction->reference }}</p>
</div>

<!-- Wallet Id Field -->
<div class="col-sm-12">
    {!! Form::label('wallet_id', 'Wallet Id:') !!}
    <p>{{ $transaction->wallet_id }}</p>
</div>

<!-- Wallet Balance Field -->
<div class="col-sm-12">
    {!! Form::label('wallet_balance', 'Wallet Balance:') !!}
    <p>{{ $transaction->wallet_balance }}</p>
</div>

<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Amount:') !!}
    <p>{{ $transaction->amount }}</p>
</div>

<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Fees :') !!}
    <p>{{ $transaction->fees }}</p>
</div>
<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Operator fees:') !!}
    <p>{{ $transaction->operator_fees }}</p>
</div>

<!-- Request Type Field -->
<div class="col-sm-12">
    {!! Form::label('request_type', 'Request Type:') !!}
    <p>{{ $transaction->requestable_type }}</p>
</div>

<!-- Request Id Field -->
<div class="col-sm-12">
    {!! Form::label('request_id', 'Request Id:') !!}
    <p>{{ $transaction->requestable_id }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $transaction->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $transaction->updated_at }}</p>
</div>

