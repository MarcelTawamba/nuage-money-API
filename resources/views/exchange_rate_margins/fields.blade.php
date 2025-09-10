<!-- From Currency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('from_currency', 'From Currency:') !!}
    {!! Form::select('from_currency', $currency , null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- To Currency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('to_currency', 'To Currency:') !!}

    {!! Form::select('to_currency', $currency , null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Margin Field -->
<div class="form-group col-sm-6">
    {!! Form::label('margin', 'Margin:') !!}
    {!! Form::number('margin', null, ['class' => 'form-control', 'required',"step"=>0.0001,'max'=>100,"min"=>0]) !!}
</div>

<!-- Margin Field -->
<div class="form-group col-sm-6">
    {!! Form::label('rate', 'Daily Rate:') !!}
    {!! Form::number('rate', null, ['class' => 'form-control', 'required',"step"=>0.0001,'max'=>1000,"min"=>0]) !!}
</div>
