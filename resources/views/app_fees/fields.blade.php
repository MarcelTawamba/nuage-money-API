<!-- Country Id Field -->

@isset($company)
    <input type="hidden" name="company_id" value="{{$company->id}}">
@else
    <input type="hidden" name="company_id" value="{{$fees->company_id}}">

@endif
<!-- Currency Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('method_id', 'Method:') !!}
    {!! Form::select('method_id', $methods , null, ['class' => 'form-control custom-select']) !!}
</div>


<div class="form-group col-sm-6">
    {!! Form::label('fee_type', 'Fee type:') !!}
    {!! Form::select('fee_type', ["percentage"=>"Percentage","value"=>"Value"] , isset($fees)? $fees->fee_type : null, ['class' => 'form-control custom-select']) !!}
</div>
<!-- Fees Field -->
<div class="form-group col-sm-6">
    {!! Form::label('fees', 'Fees:') !!}
    {!! Form::number('fee', isset($fees) ? $fees->fees : null, ['class' => 'form-control',"step"=>0.1]) !!}
</div>


