<?php

namespace App\Http\Controllers;

use App\DataTables\ExchangeFeeMarginDataTable;
use App\Http\Requests\CreateExchangeFeeMarginRequest;
use App\Http\Requests\UpdateExchangeFeeMarginRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\WalletType;
use App\Repositories\ExchangeFeeMarginRepository;
use Illuminate\Http\Request;
use Flash;


class ExchangeFeeMarginController extends AppBaseController
{
    /** @var ExchangeFeeMarginRepository $exchangeFeeMarginRepository*/
    private $exchangeFeeMarginRepository;

    public function __construct(ExchangeFeeMarginRepository $exchangeFeeMarginRepo)
    {
        $this->exchangeFeeMarginRepository = $exchangeFeeMarginRepo;
    }

    /**
     * Display a listing of the ExchangeFeeMargin.
     */
    public function index(ExchangeFeeMarginDataTable $exchangeFeeMarginDataTable)
    {
    return $exchangeFeeMarginDataTable->render('exchange_fee_margins.index');
    }


    /**
     * Show the form for creating a new ExchangeFeeMargin.
     */
    public function create()
    {
        $currencies = WalletType::all();
        $currency = [];

        foreach ($currencies as $curr){
            $currency[$curr->name] = $curr->name;
        }
        return view('exchange_fee_margins.create')->with("currency",$currency);
    }

    /**
     * Store a newly created ExchangeFeeMargin in storage.
     */
    public function store(CreateExchangeFeeMarginRequest $request)
    {
        $input = $request->all();

        $exchangeFeeMargin = $this->exchangeFeeMarginRepository->create($input);

        Flash::success('Exchange Fee Margin saved successfully.');

        return redirect(route('exchange-fee-margins.index'));
    }

    /**
     * Display the specified ExchangeFeeMargin.
     */
    public function show($id)
    {
        $exchangeFeeMargin = $this->exchangeFeeMarginRepository->find($id);

        if (empty($exchangeFeeMargin)) {
            Flash::error('Exchange Fee Margin not found');

            return redirect(route('exchange-fee-margins.index'));
        }

        return view('exchange_fee_margins.show')->with('exchangeFeeMargin', $exchangeFeeMargin);
    }

    /**
     * Show the form for editing the specified ExchangeFeeMargin.
     */
    public function edit($id)
    {
        $exchangeFeeMargin = $this->exchangeFeeMarginRepository->find($id);

        if (empty($exchangeFeeMargin)) {
            Flash::error('Exchange Fee Margin not found');

            return redirect(route('exchange-fee-margins.index'));
        }
        $currencies = WalletType::all();
        $currency = [];

        foreach ($currencies as $curr){
            $currency[$curr->name] = $curr->name;
        }

        return view('exchange_fee_margins.edit')->with('exchangeFeeMargin', $exchangeFeeMargin)->with("currency",$currency);
    }

    /**
     * Update the specified ExchangeFeeMargin in storage.
     */
    public function update($id, UpdateExchangeFeeMarginRequest $request)
    {
        $exchangeFeeMargin = $this->exchangeFeeMarginRepository->find($id);

        if (empty($exchangeFeeMargin)) {
            Flash::error('Exchange Fee Margin not found');

            return redirect(route('exchange-fee-margins.index'));
        }

        $exchangeFeeMargin = $this->exchangeFeeMarginRepository->update($request->all(), $id);

        Flash::success('Exchange Fee Margin updated successfully.');

        return redirect(route('exchange-fee-margins.index'));
    }

    /**
     * Remove the specified ExchangeFeeMargin from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $exchangeFeeMargin = $this->exchangeFeeMarginRepository->find($id);

        if (empty($exchangeFeeMargin)) {
            Flash::error('Exchange Fee Margin not found');

            return redirect(route('exchange-fee-margins.index'));
        }

        $this->exchangeFeeMarginRepository->delete($id);

        Flash::success('Exchange Fee Margin deleted successfully.');

        return redirect(route('exchange-fee-margins.index'));
    }
}
