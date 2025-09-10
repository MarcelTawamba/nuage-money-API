 <!-- Country Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('country_id', 'Country :') !!}
    {!! Form::select('country_id',$country , null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Currency Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('currency_id', 'Currency:') !!}
    {!! Form::select('currency_id', $currency , null, ['class' => 'form-control custom-select']) !!}
</div>
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    {!! Form::select('type', ["pay_in"=>"Payin","pay_out"=>"Payout"] ,  isset($fees) ? $fees->type : null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Method Field -->
<div class="form-group col-sm-6">
    {!! Form::label('method_class', 'Method Class:') !!}
    {!! Form::select('method_class',\App\Enums\PaymentMethod::asArray() , null, ['class' => 'form-control custom-select']) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('method_type', 'Method type:') !!}
    {!! Form::select('method_type', \App\Enums\MethodType::asArray() , isset($fees)? $fees->method_type : null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Method Field -->
<div class="form-group col-sm-6">
    {!! Form::label('method_name', 'Method name:') !!}
    {!! Form::text('method_name',null,['class' => 'form-control ']) !!}
</div>


<div class="form-group col-sm-6">
    {!! Form::label('fee_type', 'Fee type:') !!}
    {!! Form::select('fee_type', ["percentage"=>"Percentage","value"=>"Value"] , isset($fees)? $fees->fee_type : null, ['class' => 'form-control custom-select']) !!}
</div>
<!-- Fees Field -->
<div class="form-group col-sm-6">
    {!! Form::label('fees', 'Fees:') !!}
    {!! Form::number('fees', isset($fees) ? $fees->fees : null, ['class' => 'form-control',"step"=>0.0001]) !!}
</div>


<div class="form-group col-sm-6">
    {!! Form::label('operator_fee_type', 'Operator fee type:') !!}
    {!! Form::select('operator_fee_type', ["percentage"=>"Percentage","value"=>"Value"] , isset($fees)? $fees->operator_fee_type : null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Fees Field -->
<div class="form-group col-sm-6">
    {!! Form::label('operator_fees', 'Operator fees:') !!}
    {!! Form::number('operator_fees', isset($fees) ? $fees->operator_fees : null, ['class' => 'form-control',"step"=>0.001]) !!}
</div>
