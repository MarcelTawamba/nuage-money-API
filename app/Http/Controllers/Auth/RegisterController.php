<?php

namespace App\Http\Controllers\Auth;

use App\Classes\ToupesuPhoneNumber;
use App\Enums\BusinessType;
use App\Http\Controllers\Controller;
use App\Models\ClientWallet;
use App\Models\Company;
use App\Models\CountryAvaillable;
use App\Models\Operator;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use http\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\ClientRepository;
use libphonenumber\NumberParseException;
use Lwwcas\LaravelCountries\Models\Country;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     * @throws NumberParseException
     */
    protected function validator(array $data)
    {
        $list = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'account_type' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if($data["account_type"] == "company"){
            $list[ 'company_name' ]= ["required", 'string', 'max:255'];
            $list ['company_type'] = ["required", 'string', 'max:255'];
            $list ['address'] = ["required", 'string', 'max:255'];
        }

        $validator = Validator::make($data,$list );

        try {
            $phone = new ToupesuPhoneNumber("+". $data['country_code'].$data['phone_number']);



            $validator->after(function ($validator)use($phone) {
                if (!$phone->IsValidNumber()) {
                    $validator->errors()->add(
                        'phone_number', 'This number is in valid! (ex for cmr code : 237, number : 653253215)'
                    );
                }
            });
        }catch (\Exception $e){


            $validator->after(function ($validator) {

                $validator->errors()->add(
                    'phone_number', 'This number is in valid!'
                );
            });

        }




        return $validator;



    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $phone = "+". $data['country_code'].$data['phone_number'];

        $country = Country::where('international_phone',$data['country_code'])->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_admin' => false,
            'phone_number'=> $data['phone_number'],
            'account_type'=> $data['account_type'],
            'country_code'=> strtolower($country->iso_alpha_3),
            'password' => Hash::make($data['password']),
        ]);

        if($data['account_type'] == "company"){
            $company = new Company();
            $company->name = $data["company_name"];
            $company->address = $data["address"];
            $company->company_type = $data["company_type"];
            $company->phone_number = $phone;
            $company->user_id = $user->id;

            $company->save();
        }else{
            $company = new Company();
            $company->name =$data['name'];
            $company->address = "none";
            $company->company_type = BusinessType::OTHER;
            $company->phone_number = $phone;
            $company->user_id = $user->id;

            $company->save();
        }


        $client_repo = new ClientRepository();

        $country_available = CountryAvaillable::whereCode($user->country_code)->first();

        if($country_available instanceof CountryAvaillable){
            $pod = Operator::whereCountryId($country_available->id)->first();
            $currency  = WalletType::find($pod->currency_id);
        }else{
            $currency = WalletType::whereName('XAF')->first();
        }

        $client =  $client_repo->create(
            $user->id, $company->name, "",
            null, false, false, true
        );
        $client->main_wallet = $currency->name;
        $client->company_id = $company->id;
        $client->save();


        $client_wallet = new ClientWallet();

        $client_wallet->client_id = $client->id;

        $client_wallet->save();

        $wallet = new Wallet();

        $wallet->user()->associate($client_wallet);
        $wallet->wallet_type_id = $currency->id;
        $wallet->raw_balance = 0;
        $wallet->save();

        event(new Registered($user));

        return  $user;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm(): \Illuminate\View\View
    {
        $country = Country::all();

        return view('auth.register')->with("countries",$country);
    }

}
