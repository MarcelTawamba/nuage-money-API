<!-- From Currency Field -->
<div class="col-sm-12">
    {!! Form::label('from_currency', 'From Currency:') !!}
    <p>{{ $exchangeRateMargin->from_currency }}</p>
</div>

<!-- To Currency Field -->
<div class="col-sm-12">
    {!! Form::label('to_currency', 'To Currency:') !!}
    <p>{{ $exchangeRateMargin->to_currency }}</p>
</div>

<!-- Margin Field -->
<div class="col-sm-12">
    {!! Form::label('margin', 'Margin:') !!}
    <p>{{ $exchangeRateMargin->margin }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $exchangeRateMargin->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $exchangeRateMargin->updated_at }}</p>
</div>

