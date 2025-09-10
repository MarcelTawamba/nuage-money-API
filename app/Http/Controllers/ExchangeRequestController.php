<?php

namespace App\Http\Controllers;

use App\Classes\ExchangeHelper;
use App\DataTables\ExchangeRequestDataTable;
use App\Http\Requests\CreateExchangeRequestRequest;
use App\Http\Requests\UpdateExchangeRequestRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Repositories\ExchangeRequestRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Flash;

class ExchangeRequestController extends AppBaseController
{
    /** @var ExchangeRequestRepository $exchangeRequestRepository*/
    private $exchangeRequestRepository;

    public function __construct(ExchangeRequestRepository $exchangeRequestRepo)
    {
        $this->exchangeRequestRepository = $exchangeRequestRepo;
    }

    /**
     * Display a listing of the ExchangeRequest.
     */
    public function index(ExchangeRequestDataTable $exchangeRequestDataTable)
    {
       return $exchangeRequestDataTable->render('exchange_requests.index');
    }


    /**
     * Show the form for creating a new ExchangeRequest.
     */
    public function create($id)
    {
        $client = Client::find($id);

        if(!$client instanceof  Client){
            Flash::error('Client not found');

            return redirect(route('apps.index'));
        }
        $currencies = WalletType::where("name",'!=',$client->main_wallet)->get();
        $currency = [];

        foreach ($currencies as $curr){
            $currency[$curr->name] = $curr->name;
        }


        $wallets = Wallet::where('user_type',ClientWallet::class)->where('user_id',$client->wallet->id)->get();
        return view('exchange_requests.create')->with("client",$client)->with("wallets",$wallets)->with("currency",$currency);
    }

    /**
     * Store a newly created ExchangeRequest in storage.
     * @throws GuzzleException
     */
    public function store(CreateExchangeRequestRequest $request)
    {
        $input = $request->all();

        $result = ExchangeHelper::exchange( $input);

        if(!$result["success"]){
            return redirect(route('exchange-request.create', [$input["service"]]))
                ->withErrors(["errors"=> $result["message"]])
                ->withInput();
        }

        Flash::success('Exchange made successfully.');

        return redirect(route('exchange-requests.index'));
    }

    /**
     * Display the specified ExchangeRequest.
     */
    public function show($id)
    {
        $exchangeRequest = $this->exchangeRequestRepository->find($id);

        if (empty($exchangeRequest)) {
            Flash::error('Exchange Request not found');

            return redirect(route('exchange-requests.index'));
        }

        return view('exchange_requests.show')->with('exchangeRequest', $exchangeRequest);
    }

    /**
     * Show the form for editing the specified ExchangeRequest.
     */
    public function edit($id)
    {
        $exchangeRequest = $this->exchangeRequestRepository->find($id);

        if (empty($exchangeRequest)) {
            Flash::error('Exchange Request not found');

            return redirect(route('exchange-requests.index'));
        }

        return view('exchange_requests.edit')->with('exchangeRequest', $exchangeRequest);
    }

    /**
     * Update the specified ExchangeRequest in storage.
     */
    public function update($id, UpdateExchangeRequestRequest $request)
    {
        $exchangeRequest = $this->exchangeRequestRepository->find($id);

        if (empty($exchangeRequest)) {
            Flash::error('Exchange Request not found');

            return redirect(route('exchange-requests.index'));
        }

        $exchangeRequest = $this->exchangeRequestRepository->update($request->all(), $id);

        Flash::success('Exchange Request updated successfully.');

        return redirect(route('exchange-requests.index'));
    }

    /**
     * Remove the specified ExchangeRequest from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $exchangeRequest = $this->exchangeRequestRepository->find($id);

        if (empty($exchangeRequest)) {
            Flash::error('Exchange Request not found');

            return redirect(route('exchange-requests.index'));
        }

        $this->exchangeRequestRepository->delete($id);

        Flash::success('Exchange Request deleted successfully.');

        return redirect(route('exchange-requests.index'));
    }
}
