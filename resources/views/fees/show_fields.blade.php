<!-- Country Id Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('country_id', 'Country :') !!}
    <p>{{ $fees->country->name }}</p>
</div>

<!-- Currency Id Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('currency_id', 'Currency :') !!}
    <p>{{ $fees->currency->name }}</p>
</div>
<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ $fees->type }}</p>
</div>

<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('method_name', 'Method Name:') !!}
    <p>{{ $fees->method_name }}</p>
</div>

<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('method_class', 'Method class:') !!}
    <p>{{ $fees->method_class }}</p>
</div>

<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('method_type', 'Method Type:') !!}
    <p>{{ $fees->method_type }}</p>
</div>

<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('fee_type', 'Fee Type:') !!}
    <p>{{ $fees->fee_type }}</p>
</div>

<!-- Fees Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('fees', 'Fees:') !!}
    <p>{{ $fees->fees }}</p>
</div>

<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('fee_type', 'Operator Fee Type:') !!}
    <p>{{ $fees->operator_fee_type }}</p>
</div>

<!-- Fees Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('fees', 'Operator Fees:') !!}
    <p>{{ $fees->operator_fees }}</p>
</div>

<!-- Created At Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $fees->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $fees->updated_at }}</p>
</div>

