<?php

namespace App\Http\Controllers;

use App\DataTables\CustomExchangeRateMarginDataTable;
use App\Http\Requests\CreateCustomExchangeRateMarginRequest;
use App\Http\Requests\UpdateCustomExchangeRateMarginRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Company;
use App\Models\CustomExchangeRateMargin;
use App\Models\ExchangeRateMargin;
use App\Repositories\CustomExchangeRateMarginRepository;
use Illuminate\Http\Request;
use Flash;

class CustomExchangeRateMarginController extends AppBaseController
{
    /** @var CustomExchangeRateMarginRepository $customExchangeRateMarginRepository*/
    private $customExchangeRateMarginRepository;

    public function __construct(CustomExchangeRateMarginRepository $customExchangeRateMarginRepo)
    {
        $this->customExchangeRateMarginRepository = $customExchangeRateMarginRepo;
    }

    /**
     * Display a listing of the CustomExchangeRateMargin.
     */
    public function index($id)
    {
        $company = Company::find($id);
        if (!$company instanceof Company) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }


        $margins = ExchangeRateMargin::all();

        $ma = [];

        foreach ($margins as $margin ){
            $custom_margin = CustomExchangeRateMargin::where("company_id",$id)->where("exchange_margin_id",$margin->id)->first();

            $mas = new \stdClass();
            if($custom_margin instanceof CustomExchangeRateMargin){

                $mas->id = $custom_margin->id ;
                $mas->margin = $custom_margin->margin;
            }else{
                $mas->id = 0 ;
                $mas->margin = $margin->margin;
            }
            $mas->from_currency = $margin->from_currency;
            $mas->to_currency = $margin->to_currency;
            $mas->rate_id = $margin->id;

            $ma[]=$mas;

        }

       return view('custom_exchange_rate_margins.index')->with("margins",$ma)->with("company",$company);
    }


    /**
     * Show the form for creating a new CustomExchangeRateMargin.
     */
    public function create($company_id,$id)
    {
        $company = Company::find($company_id);
        if (!$company instanceof Company) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        $exchange_rate = ExchangeRateMargin::find($id);
        if (!$exchange_rate instanceof ExchangeRateMargin) {
            Flash::error('Rate not found');

            return redirect(route('companies.index'));
        }

        $exchange_rates = [ $exchange_rate->id => $exchange_rate->from_currency . " => " . $exchange_rate->to_currency];



        return view('custom_exchange_rate_margins.create')->with("exchange_rates", $exchange_rates)->with("company",$company) ;
    }

    /**
     * Store a newly created CustomExchangeRateMargin in storage.
     */
    public function store(CreateCustomExchangeRateMarginRequest $request)
    {
        $input = $request->all();

        $customExchangeRateMargin = $this->customExchangeRateMarginRepository->create($input);

        Flash::success('Custom Exchange Rate Margin saved successfully.');

        return redirect(route('companies.show',$customExchangeRateMargin->company_id));
    }



    /**
     * Show the form for editing the specified CustomExchangeRateMargin.
     */
    public function edit($id)
    {
        $customExchangeRateMargin = $this->customExchangeRateMarginRepository->find($id);

        if (empty($customExchangeRateMargin)) {
            Flash::error('Custom Exchange Rate Margin not found');

            return redirect(route('custom-exchange-rate-margins.index', $customExchangeRateMargin->company_id));
        }
        $company = Company::find( $customExchangeRateMargin->company_id);
        $exchange_rate = ExchangeRateMargin::find($customExchangeRateMargin->exchange_margin_id);
        $exchange_rates = [];

        $exchange_rates[$exchange_rate->id] = $exchange_rate->from_currency . " => " . $exchange_rate->to_currency;
        return view('custom_exchange_rate_margins.edit')->with('customExchangeRateMargin', $customExchangeRateMargin)->with("exchange_rates", $exchange_rates)->with('company',$company) ;
    }

    /**
     * Update the specified CustomExchangeRateMargin in storage.
     */
    public function update($id, UpdateCustomExchangeRateMarginRequest $request)
    {
        $customExchangeRateMargin = $this->customExchangeRateMarginRepository->find($id);

        if (empty($customExchangeRateMargin)) {
            Flash::error('Custom Exchange Rate Margin not found');

            return redirect(route('custom-exchange-rate-margins.index',$request->input('company_id')));
        }

        $customExchangeRateMargin = $this->customExchangeRateMarginRepository->update($request->all(), $id);

        Flash::success('Custom Exchange Rate Margin updated successfully.');

        return redirect(route('companies.show',$customExchangeRateMargin->company_id));
    }


}
