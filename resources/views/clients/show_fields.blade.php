<!-- User Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $client->user_id }}</p>
</div>

<div>
    {!! Form::label('company', 'Company:') !!}
    <p> @if($client->company != null)
            {{ $client->company->name }}
        @else
            None
        @endif</p>


</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $client->name }}</p>
</div>

<!-- Secret Field -->
<div class="col-sm-12">
    {!! Form::label('secret', 'Secret:') !!}
    <p>{{ $client->secret }}</p>
</div>

<!-- Redirect Field -->
<div class="col-sm-12">
    {!! Form::label('redirect', 'Redirect:') !!}
    <p>{{ $client->redirect }}</p>
</div>







