<!-- From Currency Field -->
<div class="col-sm-12">
    {!! Form::label('from_currency', 'From Currency:') !!}
    <p>{{ $exchangeRequest->from_currency }}</p>
</div>

<!-- To Currency Field -->
<div class="col-sm-12">
    {!! Form::label('to_currency', 'To Currency:') !!}
    <p>{{ $exchangeRequest->to_currency }}</p>
</div>

<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Amount:') !!}
    <p>{{ $exchangeRequest->amount }}</p>
</div>

<!-- Market Rate Field -->
<div class="col-sm-12">
    {!! Form::label('market_rate', 'Market Rate:') !!}
    <p>{{ $exchangeRequest->market_rate }}</p>
</div>

<!-- Rate Field -->
<div class="col-sm-12">
    {!! Form::label('rate', 'Rate:') !!}
    <p>{{ $exchangeRequest->rate }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $exchangeRequest->status }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $exchangeRequest->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $exchangeRequest->updated_at }}</p>
</div>

