<!-- Currency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('currency', 'Currency:') !!}
    {!! Form::text('currency', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', 'Amount:') !!}
    {!! Form::number('amount', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Exchange Request Field -->
<div class="form-group col-sm-6">
    {!! Form::label('exchange_request', 'Exchange Request:') !!}
    {!! Form::text('exchange_request', null, ['class' => 'form-control', 'required']) !!}
</div>