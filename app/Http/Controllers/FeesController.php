<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFeesRequest;
use App\Http\Requests\UpdateFeesRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\CountryAvaillable;
use App\Models\Currency;
use App\Repositories\FeesRepository;
use CoreProc\WalletPlus\Models\WalletType;
use Illuminate\Http\Request;
use Flash;

class FeesController extends AppBaseController
{
    /** @var FeesRepository $feesRepository*/
    private $feesRepository;

    public function __construct(FeesRepository $feesRepo)
    {
        $this->feesRepository = $feesRepo;
    }

    /**
     * Display a listing of the Fees.
     */
    public function index(Request $request)
    {
        $fees = $this->feesRepository->paginate(10);

        return view('fees.index')
            ->with('fees', $fees);
    }

    /**
     * Show the form for creating a new Fees.
     */
    public function create()
    {
        $country = CountryAvaillable::all();
        $countries = [];
        foreach ($country as $can){
            $countries["$can->id"] = $can->name;
        }

        $currency = WalletType::all();
        $currencies = [];
        foreach ($currency as $can){
            $currencies["$can->id"] = $can->name;
        }
        $method = $this->getAllModels();

        $methods=[];

        foreach ($method as $meth){
            $methods["$meth"]=$meth;
        }





        return view('fees.create')->with("country",$countries)->with("currency",$currencies)->with("methods",$methods );
    }

    /**
     * Store a newly created Fees in storage.
     */
    public function store(CreateFeesRequest $request)
    {
        $input = $request->all();

        $input["method_class"] = strtolower($input["method_class"]) ;



        $fees = $this->feesRepository->create($input);

        Flash::success('Fees saved successfully.');

        return redirect(route('fees.index'));
    }

    /**
     * Display the specified Fees.
     */
    public function show($id)
    {
        $fees = $this->feesRepository->find($id);

        if (empty($fees)) {
            Flash::error('Fees not found');

            return redirect(route('fees.index'));
        }

        return view('fees.show')->with('fees', $fees);
    }

    /**
     * Show the form for editing the specified Fees.
     */
    public function edit($id)
    {
        $fees = $this->feesRepository->find($id);

        if (empty($fees)) {
            Flash::error('Fees not found');

            return redirect(route('fees.index'));
        }

        $country = CountryAvaillable::all();
        $countries = [];
        foreach ($country as $can){
            $countries["$can->id"] = $can->name;
        }

        $currency = WalletType::all();
        $currencies = [];
        foreach ($currency as $can){
            $currencies["$can->id"] = $can->name;
        }
        $method = $this->getAllModels();

        $methods=[];

        foreach ($method as $meth){
            $methods["$meth"]=$meth;
        }



        return view('fees.edit')->with('fees', $fees)->with("country",$countries)->with("currency",$currencies)->with("methods", $methods);
    }

    /**
     * Update the specified Fees in storage.
     */
    public function update($id, UpdateFeesRequest $request)
    {
        $fees = $this->feesRepository->find($id);

        if (empty($fees)) {
            Flash::error('Fees not found');

            return redirect(route('fees.index'));
        }
        $input = $request->all();

        $input["method_class"] = strtolower($input["method_class"]) ;
        info("Method",['data'=>$input]);


        $fees = $this->feesRepository->update($input, $id);
        info("Method",['data'=>$fees]);
        Flash::success('Fees updated successfully.');

        return redirect(route('fees.index'));
    }

    /**
     * Remove the specified Fees from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $fees = $this->feesRepository->find($id);

        if (empty($fees)) {
            Flash::error('Fees not found');

            return redirect(route('fees.index'));
        }

        $this->feesRepository->delete($id);

        Flash::success('Fees deleted successfully.');

        return redirect(route('fees.index'));
    }

    public function getAllModels()
    {

        $path = app_path() . "/Models";

        return $this->getModels($path);
    }

    public function getModels($path )
    {
        $modelList = [];

        $results = scandir($path);

        foreach ($results as $result) {
            if ($result === '.' or $result === '..') continue;
            $filename = $result;

            if (is_dir($filename)) {
                $modelList = array_merge($modelList, $this->getModels($filename));
            }else{
                if(str_contains($filename,"Client") || str_contains($filename,"Client") ){
                    $modelList[] = substr($filename,0,-4);
                }

            }
        }

        return $modelList;
    }


}
