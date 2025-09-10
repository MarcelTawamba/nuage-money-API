<div class="form-group col-sm-12">
    {!! Form::label('company_id', 'Company:') !!}
    {!! Form::select('company_id', $company , null, ['class' => 'form-control custom-select']) !!}
</div>
<!-- Name Field -->
<div class="form-group col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>



<!-- Redirect Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('redirect', 'Redirect:') !!}
    {!! Form::text('redirect', null, ['class' => 'form-control', 'required', 'maxlength' => 65535, 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>





