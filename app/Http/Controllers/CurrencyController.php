<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatecurrencyRequest;
use App\Http\Requests\UpdatecurrencyRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\Currency;
use App\Models\Wallet;
use App\Repositories\CurrencyRepository;
use CoreProc\WalletPlus\Models\WalletType;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Validation\Rule;

class CurrencyController extends AppBaseController
{
    /** @var CurrencyRepository $currencyRepository*/
    private $currencyRepository;
    private ValidationFactory $validation;

    public function __construct(CurrencyRepository $currencyRepo,ValidationFactory $validation)
    {
        $this->currencyRepository = $currencyRepo;
        $this->validation = $validation;
    }

    /**
     * Display a listing of the currency.
     */
    public function index(Request $request)
    {
        $currencies = $this->currencyRepository->paginate(10);

        return view('currencies.index')
            ->with('currencies', $currencies);
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create()
    {
        return view('currencies.create');
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(CreatecurrencyRequest $request)
    {
        $input = $request->all();

        $currency = $this->currencyRepository->create($input);

        if($currency instanceof WalletType) {
            //let's create the currency type
            $this->createLaravelWalletPlus($currency);
        }

        Flash::success('Currency saved successfully.');

        return redirect(route('currencies.index'));
    }

    /**
     * Display the specified currency.
     */
    public function show($id)
    {
        $currency = $this->currencyRepository->find($id);

        if (empty($currency)) {
            Flash::error('Currency not found');

            return redirect(route('currencies.index'));
        }

        return view('currencies.show')->with('currency', $currency);
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit($id)
    {
        $currency = $this->currencyRepository->find($id);

        if (empty($currency)) {
            Flash::error('Currency not found');

            return redirect(route('currencies.index'));
        }

        return view('currencies.edit')->with('currency', $currency);
    }

    /**
     * Update the specified currency in storage.
     */
    public function update($id, Request $request)
    {
        $currency = $this->currencyRepository->find($id);

        $this->validation->make($request->all(), [

            'decimals' => ["required","numeric"],

        ])->validate();

        if (empty($currency)) {
            Flash::error('Currency not found');

            return redirect(route('currencies.index'));
        }

        $currency = $this->currencyRepository->update(["decimals"=>$request->all()["decimals"]], $id);


        Flash::success('Currency updated successfully.');

        return redirect(route('currencies.index'));
    }

    /**
     * Remove the specified currency from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $currency = $this->currencyRepository->find($id);

        if (empty($currency)) {
            Flash::error('Currency not found');

            return redirect(route('currencies.index'));
        }

        $this->currencyRepository->delete($id);

        Flash::success('Currency deleted successfully.');

        return redirect(route('currencies.index'));
    }

    /**
     * @param WalletType $currency
     */
    private function createLaravelWalletPlus(WalletType $currency)
    {

        $clients = Client::all();
        foreach ($clients as $client) {
            $wa = $client->wallet;
            if($wa == null){
                $wa = new ClientWallet();
                $wa->client_id = $client->id;
                $wa->save();
            }

            $wallet = new Wallet();
            $wallet->user_id = $wa->id;
            $wallet->user_type = ClientWallet::class;
            $wallet->wallet_type_id = $currency->id;
            $wallet->raw_balance = 0;
            $wallet->save();

        }


    }
}
