<!-- Name Field -->
<div class="col-12 col-sm-6 col-md-4">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $company->name }}</p>
</div>

<!-- Company Type Field -->
<div class="col-12 col-sm-6 col-md-4">
    {!! Form::label('company_type', 'Company Type:') !!}
    <p>{{ $company->company_type }}</p>
</div>

<!-- Address Field -->
<div class="col-12 col-sm-6 col-md-4">
    {!! Form::label('address', 'Address:') !!}
    <p>{{ $company->address }}</p>
</div>

<!-- Phone Number Field -->
<div class="col-12 col-sm-6 col-md-4">
    {!! Form::label('phone_number', 'Phone Number:') !!}
    <p>{{ $company->phone_number }}</p>
</div>

<!-- Created At Field -->
<div class="col-12 col-sm-6 col-md-4">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $company->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-12 col-sm-6 col-md-4">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $company->updated_at }}</p>
</div>

