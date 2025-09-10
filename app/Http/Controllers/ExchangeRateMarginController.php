<?php

namespace App\Http\Controllers;

use App\DataTables\ExchangeRateMarginDataTable;
use App\Http\Requests\CreateExchangeRateMarginRequest;
use App\Http\Requests\UpdateExchangeRateMarginRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\WalletType;
use App\Repositories\ExchangeRateMarginRepository;
use Illuminate\Http\Request;
use Flash;

class ExchangeRateMarginController extends AppBaseController
{
    /** @var ExchangeRateMarginRepository $exchangeRateMarginRepository*/
    private $exchangeRateMarginRepository;

    public function __construct(ExchangeRateMarginRepository $exchangeRateMarginRepo)
    {
        $this->exchangeRateMarginRepository = $exchangeRateMarginRepo;
    }

    /**
     * Display a listing of the ExchangeRateMargin.
     */
    public function index(ExchangeRateMarginDataTable $exchangeRateMarginDataTable)
    {
    return $exchangeRateMarginDataTable->render('exchange_rate_margins.index');
    }


    /**
     * Show the form for creating a new ExchangeRateMargin.
     */
    public function create()
    {
        $currencies = WalletType::all();
        $currency = [];

        foreach ($currencies as $curr){
            $currency[$curr->name] = $curr->name;
        }
        return view('exchange_rate_margins.create')->with("currency",$currency);
    }

    /**
     * Store a newly created ExchangeRateMargin in storage.
     */
    public function store(CreateExchangeRateMarginRequest $request)
    {
        $input = $request->all();

        $exchangeRateMargin = $this->exchangeRateMarginRepository->create($input);

        Flash::success('Exchange Rate Margin saved successfully.');

        return redirect(route('exchange-rate-margins.index'));
    }

    /**
     * Display the specified ExchangeRateMargin.
     */
    public function show($id)
    {
        $exchangeRateMargin = $this->exchangeRateMarginRepository->find($id);

        if (empty($exchangeRateMargin)) {
            Flash::error('Exchange Rate Margin not found');

            return redirect(route('exchange-rate-margins.index'));
        }

        return view('exchange_rate_margins.show')->with('exchangeRateMargin', $exchangeRateMargin);
    }

    /**
     * Show the form for editing the specified ExchangeRateMargin.
     */
    public function edit($id)
    {
        $exchangeRateMargin = $this->exchangeRateMarginRepository->find($id);

        if (empty($exchangeRateMargin)) {
            Flash::error('Exchange Rate Margin not found');

            return redirect(route('exchange-rate-margins.index'));
        }

        $currencies = WalletType::all();
        $currency = [];

        foreach ($currencies as $curr){
            $currency[$curr->name] = $curr->name;
        }

        return view('exchange_rate_margins.edit')->with('exchangeRateMargin', $exchangeRateMargin)->with("currency",$currency);
    }

    /**
     * Update the specified ExchangeRateMargin in storage.
     */
    public function update($id, UpdateExchangeRateMarginRequest $request)
    {
        $exchangeRateMargin = $this->exchangeRateMarginRepository->find($id);

        if (empty($exchangeRateMargin)) {
            Flash::error('Exchange Rate Margin not found');

            return redirect(route('exchange-rate-margins.index'));
        }

        $exchangeRateMargin = $this->exchangeRateMarginRepository->update($request->all(), $id);

        Flash::success('Exchange Rate Margin updated successfully.');

        return redirect(route('exchange-rate-margins.index'));
    }

    /**
     * Remove the specified ExchangeRateMargin from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $exchangeRateMargin = $this->exchangeRateMarginRepository->find($id);

        if (empty($exchangeRateMargin)) {
            Flash::error('Exchange Rate Margin not found');

            return redirect(route('exchange-rate-margins.index'));
        }

        $this->exchangeRateMarginRepository->delete($id);

        Flash::success('Exchange Rate Margin deleted successfully.');

        return redirect(route('exchange-rate-margins.index'));
    }
}
