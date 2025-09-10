
<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Country:') !!}
    <input class="form-control country" name="name" list="countries" placeholder="Start typing..." required>
    <datalist id="countries" >
        @foreach($countries as $country)
            <option value="{{$country->name}}-{{$country->iso_alpha_3}}">{{$country->name}}</option>
        @endforeach
    </datalist>
</div>

<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:') !!}
    {!! Form::text('code', null, ['class' => 'form-control code', 'required',"disable"]) !!}
</div>




<script>
    let country = document.querySelector(".country");
    let code = document.querySelector(".code");

    country.addEventListener("change",function (){
        code.value = country.value.split("-")[1]
    });
</script>

