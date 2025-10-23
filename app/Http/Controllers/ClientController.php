<?php

namespace App\Http\Controllers;

use App\Classes\ExchangeHelper;
use App\Classes\GeneralPaymentHelper;
use App\Classes\StartButtonAfricaPaymentHelper;
use App\Classes\ToupesuGeneralPaymentHelpers;
use App\Classes\ToupesuPhoneNumber;
use App\Enums\MethodType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PayType;
use App\Events\PayInSuccessEvent;
use App\Http\Requests\CreateClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Achat;
use App\Models\AdminDepositeRequest;
use App\Models\ClientWallet;
use App\Models\Company;
use App\Models\CountryAvaillable;
use App\Models\CustomFee;
use App\Models\Operator;
use App\Models\StartButtonBank;
use App\Models\ToupesuPaymentRequest;
use App\Models\Wallet;
use App\Models\WalletType;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Passport\ClientRepository;
use App\Models\Client;
use Illuminate\Http\Request;
use Flash;
use Laravel\Passport\Http\Rules\RedirectRule;
use function Termwind\render;

class ClientController extends AppBaseController
{
    /** @var ClientRepository $clientRepository*/
    private $clientRepository;

    /**
     * The redirect validation rule.
     *
     * @var \Laravel\Passport\Http\Rules\RedirectRule
     */
    protected $redirectRule;

    /**
     * The validation factory implementation.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validation;

    public function __construct(ClientRepository $clientRepo,ValidationFactory $validation, RedirectRule $redirectRule)
    {
        $this->clientRepository = $clientRepo;
        $this->validation = $validation;
        $this->redirectRule = $redirectRule;
    }

    /**
     * Display a listing of the Client.
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        if($user->is_admin){
            $clients = Client::paginate(10);
        }else{
            $clients = Client::where("user_id",$user->id)->paginate(10);
        }

        return view('clients.index')
            ->with('clients', $clients);
    }

    /**
     * Show the form for creating a new Client.
     */
    public function create()
    {
        $user = Auth::user();

        if($user->is_admin){
            $company = Company::all();
        }else{
            $company = Company::where('user_id',$user->id)->get();
        }

        $companies = [];
        foreach ($company as $com){
            $companies[$com->id]=$com->name;
        }


        return view('clients.create')->with('company',$companies);
    }

    /**
     * Store a newly created Client in storage.
     */
    public function store(CreateClientRequest $request)
    {
        $input = $request->all();

        $this->validation->make($request->all(), [
            'name' => Rule::unique('oauth_clients')->where(fn ($query) => $query->where('user_id', \Auth::user()->id)),
            'redirect' => ['required', $this->redirectRule],
            'company_id'=>"required|string|exists:companies,id",
            'confidential' => 'boolean',
        ])->validate();


        $client = $this->clientRepository->create(
            \Auth::user()->id, $request->name, $request->redirect,
            null, false, false, (bool) $request->input('confidential', true)
        );
        $client->company_id = $request->company_id;
        $client->save();

        Flash::success('App saved successfully.');

        return redirect(route('home'));
    }

    /**
     * Store a newly created Client in storage.
     */
    public function regenerate($id,Request $request)
    {

        $user = Auth::user();
        $client = Client::where("id",$id)->where("user_id",$user->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $client->secret = Str::random(40);
        $client->save();


        Flash::success('secret regenerated successfully.');

        return redirect(route('home'));
    }

    /**
     * Store a newly created Client in storage.
     */
    public function changeWallet(Request $request)
    {

        $user = Auth::user();

        $compnay=  Company::whereUserId($user->id)->first();
        $client = Client::whereUserId($user->id)->where("company_id",$compnay->id)->first();

       $currency = WalletType::find($request->input("main_wallet"));

       $curr = WalletType::whereName($client->main_wallet)->first();

       if(!$currency instanceof  WalletType){
           Flash::error('Currency not found');
           return redirect(route('home'));
       }
       $wallet = Wallet::whereUserType(ClientWallet::class)
           ->where("user_id",$client->wallet->id)
           ->where("wallet_type_id",$curr->id)->first();

        if(!$wallet instanceof  Wallet){
           $wallet = new Wallet();
           $wallet->user_id = $client->wallet->id;
           $wallet->user_type = ClientWallet::class;
           $wallet->wallet_type_id = $curr->id;
           $wallet->raw_balance = 0;

           $wallet->save();

        }
       if ($wallet->balance > 0){
           Flash::error($curr->name .' balance most be << 0 >> for you to change the main wallet');
           return redirect(route('home'));
       }

       $client->main_wallet = $currency->name;
       $client->save();

       Flash::success('Main wallet change successfully');

       return redirect(route('home'));

    }



    /**
     * Store a newly created Client in storage.
     */
    public function showSecret($id,Request $request)
    {

        $user = Auth::user();
        $client = Client::where("id",$id)->where("user_id",$user->id)->first();

        $input = $request->all();

        $password = "";
        if(isset($input['pass'])){
            $password = $input['pass'];
        }



        if( Hash::check($password,$user->password)){
            session(['secret' => $client->secret]);

        }else{
            Flash::error('Password not correct');
        }
        return redirect(route('home'));


    }
    /**
     * Display the specified Client.
     */
    public function show($id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        return view('clients.show')->with('client', $client);
    }

    /**
     * Show the form for editing the specified Client.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $client = Client::where("id",$id)->where("user_id",$user->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        if($user->is_admin){
            $company = Company::all();
        }else{
            $company = Company::where('user_id',$user->id)->get();
        }

        $companies = [];
        foreach ($company as $com){
            $companies[$com->id]=$com->name;
        }



        return view('clients.edit')->with('client', $client)->with('company',$companies);
    }

    /**
     * Update the specified Client in storage.
     */
    public function update($id, UpdateClientRequest $request)
    {
        $client = Client::find($id);

        $user_id = \Auth::user()->id;

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $data = $request->all();


        $this->validation->make($request->all(), [
            'name' => Rule::unique('App\Models\Client')->ignore($client->id),
            'redirect' => ['required', $this->redirectRule],
        ])->validate();

        $client = $this->clientRepository->update(
            $client, $data['name'], $data['redirect']
        );

        Flash::success('App updated successfully.');

        return redirect(route('home'));
    }

    /**
     * Remove the specified Client from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }



        $this->clientRepository->delete($client);
        $client->delete();
        Flash::success('App deleted successfully.');

        return redirect(route('home'));
    }

    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function fundWalletView( $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $currencies = WalletType::whereName($client->main_wallet)->get();
        $currency = [];

        foreach ( $currencies as $cur){
            $currency[$cur->id] = $cur->name;
        }
        $methods = Operator::where("type",PayType::PAY_IN)->where("currency_id",$currencies[0]->id)->get();

        $countries = CountryAvaillable::all();

        $country = [];

        foreach ($countries as $count){
            $country[$count->id] = $count->name;
        }

        return view('clients.fund_wallet')->with('client', $client)->with("currency",$currency)->with("method",$methods);

    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function fundWallet(Request $request, $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $input = $request->all();

        $method = Operator::whereId($input["method"])->where('currency_id',$client->mainCurrency()->id)->first();
        if($method instanceof Operator){
            if( strtolower($method->method_class) == strtolower(PaymentMethod::TOUPESU_MOBILE)){
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "user-". GeneralPaymentHelper::UUID(),
                    "msidn"=> $input["msidn"]
                ];
                $result = ToupesuGeneralPaymentHelpers::initPayment($inputs);
                $result = json_decode($result->content());
                if( ! $result->success){
                    return redirect(route('apps.fund_wallet'))
                        ->withErrors(["errors"=>"An error has occur during the request"])
                        ->withInput();
                }

                Flash::success('Mobile request send, please validate on your phone');


            }elseif(strtolower($method->method_class) == strtolower(PaymentMethod::START_BUTTON_BANK)){
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "user-". GeneralPaymentHelper::UUID(),
                    "email"=> $input["email"]
                ];
                $result = StartButtonAfricaPaymentHelper::initPayment($inputs);
                $result = json_decode($result->content());
                if($result->success){
                    return redirect($result->payment_link);
                }else{
                    return redirect(route('apps.fund_wallet'))
                        ->withErrors(["errors"=>"An error has occur during the request"])
                        ->withInput();
                }
            }else{
                return redirect(route('apps.fund_wallet'))
                    ->withErrors(["errors"=>"Payment method not found"])
                    ->withInput();
            }
        }else{
            return redirect(route('apps.fund_wallet'))
                ->withErrors(["errors"=>"Payment method not found"])
                ->withInput();
        }


        return redirect(route('home'));
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function withdrawView( $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $currencies = WalletType::where("name","!=",$client->main_wallet)->get();
        $currency = [];

        foreach ( $currencies as $cur){
            $currency[$cur->id] = $cur->name;
        }
        $methods = Operator::where("type",PayType::PAY_OUT)->where("currency_id","!=",$client->mainCurrency()->id)->get();

        $countries = CountryAvaillable::all();

        $country = [];

        foreach ($countries as $count){
            $country[$count->id] = $count->name;
        }

        $bank_code = StartButtonBank::all();

        $bank_codes = [];

        foreach ($bank_code as $bank){
            $bank_codes[$bank->code] = $bank->currency . "-{$bank->code}";
        }

        return view('clients.send_fund')->with('client', $client)->with("currency",$currency)->with("method",$methods)->with("bank_codes",$bank_codes);

    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     * @throws GuzzleException
     */
    public function withdraw(Request $request, $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $input = $request->all();

        $method = Operator::whereId($input["method"])->where('currency_id',"!=",$client->mainCurrency()->id)->first();


        $currency= $method->currency;

        if( !($client->wallet instanceof ClientWallet)){

            return  back()
                ->withErrors(["message"=>"Solde insuffisant"])
                ->withInput();
        }

        $wallet = Wallet::where("user_type",ClientWallet::class)->where('user_id', $client->wallet->id)->where("wallet_type_id",$currency->id)->first();

        if( !($wallet instanceof Wallet)){

            return  back()
                ->withErrors(["message"=>"Solde insuffisant"])
                ->withInput();
        }

        $fees = $method;
        $custom_fee = CustomFee::where("company_id",$client->company_id)->where("method_id",$method->id)->first();

        if($custom_fee instanceof  CustomFee){
            $fees = $custom_fee;
        }

        $total_fee_amount = $fees->fee_type == "percentage" ?  -1 * $input["amount"]* ( $fees->fees / 100 ) : $fees->fees;

        if( $wallet->balance < ($input["amount"] +  $total_fee_amount) ){
            $input["amount_to"] = ($input["amount"] +  $total_fee_amount);
            $result =  ExchangeHelper::paymentDiffCurrency($input,$client,$currency);

            if(!$result["success"]){
                return  back()
                    ->withErrors(["message"=>$result["message"]])
                    ->withInput();
            }
        }





        if($method instanceof Operator){
            if( strtolower($method->method_class) == strtolower(PaymentMethod::TOUPESU_MOBILE)){
                $phone = new  ToupesuPhoneNumber($input["msidn"]);

                if( !$phone->IsValidNumber()){
                    back()
                        ->withErrors(["message"=> "Provide a valid mobile number"])
                        ->withInput();
                }
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "User-". GeneralPaymentHelper::UUID(),
                    "msidn"=> $input["msidn"]
                ];
                $result = ToupesuGeneralPaymentHelpers::initPayout($inputs);
                $result = json_decode($result->content());
                info("result",["data"=>$result]);
                if( ! $result->success){
                    return  back()
                        ->withErrors(["message"=>$result->message])
                        ->withInput();
                }


            }elseif(strtolower($method->method_class) == strtolower(PaymentMethod::START_BUTTON_BANK)){
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "User-". GeneralPaymentHelper::UUID(),
                    "bank_code"=> $input["bank_code"],
                    "account_name"=> $input["account_name"],
                    "account_number"=> $input["account_number"]
                ];
                $result = StartButtonAfricaPaymentHelper::initPayout($inputs);
                $result = json_decode($result->content());

                if( !$result->success){

                    return  back()
                        ->withErrors(["message"=>$result->message])
                        ->withInput();
                }
            }else{
                return  back()
                    ->withErrors(["message"=>"Payment method not found"])
                    ->withInput();
            }

        }else{

            return back()->withErrors(["message"=>"Payment method not found"])
                ->withInput();
        }

        Flash::success('Votre demande a été initialisée');

        return redirect(route('home'));
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function adminFundWalletView( $id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('apps.index'));
        }
        $currencies = WalletType::all();
        $currency = [];

        foreach ( $currencies as $cur){
            $currency[$cur->name] = $cur->name;
        }

        $countries = CountryAvaillable::all();

        $country = [];

        foreach ($countries as $count){
            $country[$count->code] = $count->name;
        }

        return view('clients.fund_wallet_admin')->with('client', $client)->with("currency",$currency)->with("country",$country);
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function adminFundWallet(Request $request, $id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('apps.index'));
        }
        $input = $request->all();

        $new_achat = new  Achat();
        $new_achat->client_id = $client->id;
        $new_achat->amount = $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["reference"];
        $new_achat->ref_id = "Admin-".GeneralPaymentHelper::UUID();
        $new_admin_deposit_request = new AdminDepositeRequest();
        $new_admin_deposit_request->description = $input['description'];
        $new_admin_deposit_request->status = PaymentStatus::SUCCESSFUL;

        $new_admin_deposit_request->save();

        $new_achat->requestable()->associate( $new_admin_deposit_request);
        $new_achat->status= PaymentStatus::SUCCESSFUL;
        $new_achat->save();
        PayInSuccessEvent::dispatch($new_achat);
        Flash::success('Deposit has been made');

        return redirect(route('home'));
    }

}
