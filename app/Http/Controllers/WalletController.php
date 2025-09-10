<?php

namespace App\Http\Controllers;

use App\DataTables\WalletDataTable;
use App\Http\Requests\CreateWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\WalletType;
use App\Repositories\WalletRepository;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Flash;

class WalletController extends AppBaseController
{
    /** @var WalletRepository $walletRepository*/
    private $walletRepository;

    public function __construct(WalletRepository $walletRepo)
    {
        $this->walletRepository = $walletRepo;
    }

    /**
     * Display a listing of the Wallet.
     */
    public function index(WalletDataTable $dataTable)
    {


        return $dataTable->render('wallets.index');
    }

    /**
     * Show the form for creating a new Wallet.
     */
    public function create()
    {
        $client = \Auth::user()->clients;

        $clients = [];
        foreach ( $client as $can){
            $clients["$can->id"] = $can->name;
        }

        $currency = WalletType::all();
        $currencies = [];

        foreach ($currency as $can){
            $currencies["$can->id"] = $can->name;
        }


        return view('wallets.create')->with("client",$clients)->with("currencies",$currencies);
    }

    /**
     * Store a newly created Wallet in storage.
     */
    public function store(CreateWalletRequest $request)
    {
        $input = $request->all();

        $client = Client::find($input["client_id"]);
        $client_wallet = $client->wallet;
        if(!$client_wallet instanceof  ClientWallet){
            $client_wallet = new  ClientWallet();
            $client_wallet->client_id = $client->id;
            $client_wallet->save();
        }

        $currency = WalletType::find($input["currency_id"]);


        if($client_wallet->wallet( $currency->name) == null){
            $client_wallet->wallets()->create(['wallet_type_id' => $input["currency_id"]]);
        }



        Flash::success('Wallet saved successfully.');

        return redirect(route('wallets.index'));
    }

    /**
     * Display the specified Wallet.
     */
    public function show($id)
    {
        $wallet = $this->walletRepository->find($id);

        if (empty($wallet)) {
            Flash::error('Wallet not found');

            return redirect(route('wallets.index'));
        }

        return view('wallets.show')->with('wallet', $wallet);
    }

    /**
     * Show the form for editing the specified Wallet.
     */
    public function edit($id)
    {
        $wallet = $this->walletRepository->find($id);

        if (empty($wallet)) {
            Flash::error('Wallet not found');

            return redirect(route('wallets.index'));
        }

        return view('wallets.edit')->with('wallet', $wallet);
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $wallet = $this->walletRepository->find($id);

        if (empty($wallet)) {
            Flash::error('Wallet not found');

            return redirect(route('wallets.index'));
        }

        $this->walletRepository->delete($id);

        Flash::success('Wallet deleted successfully.');

        return redirect(route('wallets.index'));
    }






}
