
<div class="col-sm-12">
    <h3 class="mb-2">Wallet</h3>

    <div class="d-flex">
        @foreach($wallets as $wallet)
            <div class="mx-2 p-2 card">{{$wallet->currency->name }} {{$wallet->balance }} </div>
        @endforeach

    </div>

</div>

<input type="hidden" name="service" value="{{$client->id}}">
<!-- From Currency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('from_currency', 'From Currency:') !!}

    <select name="from_currency" class='form-control currency' required>
        <option value="" disabled selected>Choose currency</option>
        @foreach($wallets as $wallet)
            @if($wallet->balance >0 && $wallet->currency->name == $client->main_wallet  )
                <option value="{{$wallet->currency->name }}" data-balance = {{$wallet->balance}}>{{$wallet->currency->name }}</option>
            @endif
        @endforeach
    </select>
</div>

<!-- To Currency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('to_currency', 'To Currency:') !!}
    {!! Form::select('to_currency', $currency , null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Amount Field -->
<div class="form-group col-sm-12">
    {!! Form::label('amount', 'Amount:') !!}
    {!! Form::number('amount', null, ['class' => 'form-control amount', 'required',"step"=>0.01]) !!}
</div>

<script>
    currency = document.querySelector(".currency")

    amount = document.querySelector(".amount")
    currency.addEventListener('change',(e)=>{
        amount.max =  currency.options[currency.selectedIndex].getAttribute('data-balance')
    })

</script>
