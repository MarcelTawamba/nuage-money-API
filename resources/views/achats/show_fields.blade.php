<!-- Client Id Field -->
<div class="col-sm-12">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $achat->client_id }}</p>
</div>

<!-- Country Field -->
<div class="col-sm-12">
    {!! Form::label('country', 'Country:') !!}
    <p>{{ $achat->country }}</p>
</div>

<!-- Currency Field -->
<div class="col-sm-12">
    {!! Form::label('currency', 'Currency:') !!}
    <p>{{ $achat->currency }}</p>
</div>

<!-- Ref Id Field -->
<div class="col-sm-12">
    {!! Form::label('ref_id', 'Ref Id:') !!}
    <p>{{ $achat->ref_id }}</p>
</div>

<!-- User Ref Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_ref_id', 'User Ref Id:') !!}
    <p>{{ $achat->user_ref_id }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $achat->status }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $achat->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $achat->updated_at }}</p>
</div>

