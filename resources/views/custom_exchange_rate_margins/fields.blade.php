<!-- Exchange Margin Id Field -->
@isset($company)
    <input type="hidden" name="company_id" value="{{$company->id}}">
@else
    <input type="hidden" name="company_id" value="{{$customExchangeRateMargin->company_id}}">

@endif

<div class="form-group col-sm-6">
    {!! Form::label('exchange_margin_id', 'Currency:') !!}

    {!! Form::select('exchange_margin_id', $exchange_rates , null, ['class' => 'form-control custom-select']) !!}

</div>

<!-- Margin Field -->
<div class="form-group col-sm-6">
    {!! Form::label('margin', 'Margin:') !!}
    {!! Form::number('margin', null, ['class' => 'form-control', 'required',"step"=>0.01,'max'=>100,"min"=>0]) !!}
</div>
