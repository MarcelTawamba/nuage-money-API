
<!-- Currency Id Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('currency_id', 'Company  ID:') !!}
    <p>{{ $appFee->company_id }}</p>
</div>
<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('type', 'Company :') !!}
    <p>{{ $appFee->company->name }}</p>
</div>

<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('method_name', 'Method Name:') !!}
    <p>{{ $appFee->method->method_name}}</p>
</div>

<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('method_class', 'Method ID:') !!}
    <p>{{ $appFee->method_id }}</p>
</div>


<!-- Method Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('fee_type', 'Fee Type:') !!}
    <p>{{ $appFee->fee_type }}</p>
</div>

<!-- Fees Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('fees', 'Fees:') !!}
    <p>{{ $appFee->fee }}</p>
</div>


<!-- Created At Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $appFee->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-md-6 col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $appFee->updated_at }}</p>
</div>

