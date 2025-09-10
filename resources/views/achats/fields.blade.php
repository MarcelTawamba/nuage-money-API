<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Country Field -->
<div class="form-group col-sm-6">
    {!! Form::label('country', 'Country:') !!}
    {!! Form::text('country', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Currency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('currency', 'Currency:') !!}
    {!! Form::text('currency', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Ref Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ref_id', 'Ref Id:') !!}
    {!! Form::text('ref_id', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- User Ref Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_ref_id', 'User Ref Id:') !!}
    {!! Form::text('user_ref_id', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>