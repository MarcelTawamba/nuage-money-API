<!-- Client Id Field -->
<div class="col-sm-12">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $wallet->client_id }}</p>
</div>

<!-- Currency Id Field -->
<div class="col-sm-12">
    {!! Form::label('currency_id', 'Currency Id:') !!}
    <p>{{ $wallet->currency_id }}</p>
</div>

<!-- Balance Field -->
<div class="col-sm-12">
    {!! Form::label('balance', 'Balance:') !!}
    <p>{{ $wallet->balance }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $wallet->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $wallet->updated_at }}</p>
</div>

