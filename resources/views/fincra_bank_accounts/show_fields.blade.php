<!-- Account Number Field -->
<div class="col-sm-12">
    {!! Form::label('account_number', 'Account Number:') !!}
    <p>{{ $fincraBankAccount->account_number }}</p>
</div>

<!-- Account Name Field -->
<div class="col-sm-12">
    {!! Form::label('account_name', 'Account Name:') !!}
    <p>{{ $fincraBankAccount->account_name }}</p>
</div>

<!-- Bank Code Field -->
<div class="col-sm-12">
    {!! Form::label('bank_code', 'Bank Code:') !!}
    <p>{{ $fincraBankAccount->bank_code }}</p>
</div>

