<!-- Exchange Margin Id Field -->
<div class="col-sm-12">
    {!! Form::label('exchange_margin_id', 'Exchange Margin Id:') !!}
    <p>{{ $customExchangeRateMargin->exchange_margin_id }}</p>
</div>

<!-- Margin Field -->
<div class="col-sm-12">
    {!! Form::label('margin', 'Margin:') !!}
    <p>{{ $customExchangeRateMargin->margin }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $customExchangeRateMargin->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $customExchangeRateMargin->updated_at }}</p>
</div>

