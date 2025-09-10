<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client :') !!}
    {!! Form::select('client_id', $client , null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Currency Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('currency_id', 'Currency :') !!}
    {!! Form::select('currency_id', $currencies , null, ['class' => 'form-control custom-select']) !!}
</div>

