<!-- Currency Field -->
<div class="col-sm-12">
    {!! Form::label('currency', 'Currency:') !!}
    <p>{{ $exchangeFeeMargin->currency }}</p>
</div>

<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Amount:') !!}
    <p>{{ $exchangeFeeMargin->amount }}</p>
</div>

<!-- Exchange Request Field -->
<div class="col-sm-12">
    {!! Form::label('exchange_request', 'Exchange Request:') !!}
    <p>{{ $exchangeFeeMargin->exchange_request }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $exchangeFeeMargin->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $exchangeFeeMargin->updated_at }}</p>
</div>

