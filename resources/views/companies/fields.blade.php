<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'minlength' => 4]) !!}
</div>

<!-- Company Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('company_type', 'Company Type:') !!}
    {!! Form::select('company_type', \App\Enums\BusinessType::asArray(), null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::text('address', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Phone Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone_number', 'Phone Number:') !!}
    {!! Form::text('phone_number', null, ['class' => 'form-control']) !!}
</div>
