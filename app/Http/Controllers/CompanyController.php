<?php

namespace App\Http\Controllers;

use App\DataTables\CompanyDataTable;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Client;
use App\Models\Company;
use App\Models\CustomExchangeRateMargin;
use App\Models\CustomFee;
use App\Models\ExchangeRateMargin;
use App\Models\Operator;
use App\Repositories\CompanyRepository;
use Illuminate\Http\Request;
use Flash;

class CompanyController extends AppBaseController
{
    /** @var CompanyRepository $companyRepository*/
    private $companyRepository;

    public function __construct(CompanyRepository $companyRepo)
    {
        $this->companyRepository = $companyRepo;
    }

    /**
     * Display a listing of the Company.
     */
    public function index(CompanyDataTable $dataTable)
    {

       return  $dataTable->render('companies.index');
    }

    /**
     * Show the form for creating a new Company.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created Company in storage.
     */
    public function store(CreateCompanyRequest $request)
    {
        $user = \Auth::user();
        $input = $request->all();
        if(!$user->is_admin){
            $input['user_id'] = $user->id;
        }

        $company = $this->companyRepository->create($input);

        Flash::success('Company saved successfully.');

        return redirect(route('companies.index'));
    }

    /**
     * Display the specified Company.
     */
    public function show($id)
    {
        $user = \Auth::user();
        if($user->is_admin){
            $company = $this->companyRepository->find($id);
        }else{
            $company = Company::where("id",$id)->where("user_id",$user->id)->first();
        }

        if (empty($company)) {
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

        $fees = Operator::all();

        $custom_fees = [];

        foreach ($fees as $fee ){
            $custom_fee = CustomFee::where("company_id",$id)->where("method_id",$fee->id)->first();

            $mas = new \stdClass();
            if($custom_fee instanceof CustomFee){

                $mas->id = $custom_fee->id ;
                $mas->fee = $custom_fee->fee;
                $mas->fee_type = $custom_fee->fee_type;
            }else{
                $mas->id = 0 ;
                $mas->fee = $fee->fees;
                $mas->fee_type = $fee->fee_type;
            }
            $mas->name = $fee->method_name;
            $mas->type = $fee->type;
            $mas->currency = $fee->currency->name;
            $mas->operator_id = $fee->id;


            $custom_fees[]=$mas;

        }



        return view('companies.show')->with('company', $company)->with("margins",$ma)->with("fees",$custom_fees);
    }

    /**
     * Show the form for editing the specified Company.
     */
    public function edit($id)
    {
        $user = \Auth::user();
        if($user->is_admin){
            $company = $this->companyRepository->find($id);
        }else{
            $company = Company::where("id",$id)->where("user_id",$user->id)->first();
        }

        if (empty($company)) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        return view('companies.edit')->with('company', $company);
    }

    /**
     * Update the specified Company in storage.
     */
    public function update($id, UpdateCompanyRequest $request)
    {


        $user = \Auth::user();
        if($user->is_admin){
            $company = $this->companyRepository->find($id);
        }else{
            $company = Company::where("id",$id)->where("user_id",$user->id)->first();
        }
        if (empty($company)) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        $company = $this->companyRepository->update($request->all(), $id);

        Flash::success('Company updated successfully.');

        if($user->is_admin){
            return redirect(route('companies.index'));

        }else{
            return redirect(route('users.profile.edit'));
        }

    }

    /**
     * Remove the specified Company from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $user = \Auth::user();
        if($user->is_admin){
            $company = $this->companyRepository->find($id);
        }else{
            $company = Company::where("id",$id)->where("user_id",$user->id)->first();
        }

        if (empty($company)) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        $this->companyRepository->delete($id);

        Flash::success('Company deleted successfully.');

        return redirect(route('companies.index'));
    }
}
