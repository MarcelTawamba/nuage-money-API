<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAppFeeRequest;
use App\Http\Requests\UpdateAppFeeRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Company;
use App\Models\CustomFee;
use App\Models\ExchangeRateMargin;
use App\Models\Operator;
use App\Repositories\AppFeeRepository;
use Illuminate\Http\Request;
use Flash;

class AppFeeController extends AppBaseController
{
    /** @var AppFeeRepository $appFeeRepository*/
    private $appFeeRepository;

    public function __construct(AppFeeRepository $appFeeRepo)
    {
        $this->appFeeRepository = $appFeeRepo;
    }

    /**
     * Display a listing of the AppFee.
     */
    public function index(Request $request)
    {
        $appFees = $this->appFeeRepository->paginate(10);

        return view('app_fees.index')
            ->with('appFees', $appFees);
    }

    /**
     * Show the form for creating a new AppFee.
     */
    public function create($company_id,$id)
    {
        $company = Company::find($company_id);
        if (!$company instanceof Company) {
            Flash::error('Company not found');

            return redirect(route('companies.index'));
        }

        $fee = Operator::find($id);
        if (!$fee instanceof Operator) {
            Flash::error('operator not found');

            return redirect(route('companies.show',$company->id));
        }



        $methods[$fee->id] = $fee->method_name . " ( ". $fee->type .")";

        return view('app_fees.create')->with("methods",$methods)->with("company",$company);
    }

    /**
     * Store a newly created AppFee in storage.
     */
    public function store(CreateAppFeeRequest $request)
    {
        $input = $request->all();

        $appFee = $this->appFeeRepository->create($input);

        Flash::success('App Fee saved successfully.');

        return redirect(route('companies.show',$appFee->company_id));
    }

    /**
     * Display the specified AppFee.
     */
    public function show($id)
    {
        $appFee = $this->appFeeRepository->find($id);

        if (empty($appFee)) {
            Flash::error('App Fee not found');

            return redirect(route('companies.index'));
        }

        return view('app_fees.show')->with('appFee', $appFee);
    }

    /**
     * Show the form for editing the specified AppFee.
     */
    public function edit($id)
    {
        $appFee = $this->appFeeRepository->find($id);

        if (empty($appFee)) {
            Flash::error('App Fee not found');

            return redirect(route('companies.index'));
        }
        $company = Company::find($appFee->company_id);

        $methods[$appFee->method->id] = $appFee->method->method_name . " ( ". $appFee->method->type .")";

        return view('app_fees.edit')->with('appFee', $appFee)->with("methods",$methods)->with("company",$company);
    }

    /**
     * Update the specified AppFee in storage.
     */
    public function update($id, UpdateAppFeeRequest $request)
    {
        $appFee = $this->appFeeRepository->find($id);

        if (empty($appFee)) {
            Flash::error('App Fee not found');

            return redirect(route('companies.index'));
        }

        $appFee = $this->appFeeRepository->update($request->all(), $id);

        Flash::success('App Fee updated successfully.');

        return redirect(route('companies.show',$appFee->company_id));
    }

    /**
     * Remove the specified AppFee from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $appFee = $this->appFeeRepository->find($id);

        if (empty($appFee)) {
            Flash::error('App Fee not found');

            return redirect(route('companies.index'));
        }

        $this->appFeeRepository->delete($id);

        Flash::success('App Fee deleted successfully.');

        return redirect(route('companies.show',$appFee->company_id));
    }
}
