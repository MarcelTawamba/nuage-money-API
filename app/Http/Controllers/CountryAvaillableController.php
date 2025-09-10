<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCountryAvaillableRequest;
use App\Http\Requests\UpdateCountryAvaillableRequest;
use  Lwwcas\LaravelCountries\Models\Country;
use App\Repositories\CountryAvaillableRepository;
use Illuminate\Http\Request;
use Flash;

class CountryAvaillableController extends AppBaseController
{
    /** @var CountryAvaillableRepository $countryAvaillableRepository*/
    private $countryAvaillableRepository;

    public function __construct(CountryAvaillableRepository $countryAvaillableRepo)
    {
        $this->countryAvaillableRepository = $countryAvaillableRepo;
    }

    /**
     * Display a listing of the CountryAvaillable.
     */
    public function index(Request $request)
    {
        $countryAvaillables = $this->countryAvaillableRepository->paginate(10);

        return view('country_availlables.index')
            ->with('countryAvaillables', $countryAvaillables);
    }

    /**
     * Show the form for creating a new CountryAvaillable.
     */
    public function create()
    {
        $countries = Country::all();
        return view('country_availlables.create')->with("countries",$countries);
    }

    /**
     * Store a newly created CountryAvaillable in storage.
     */
    public function store(CreateCountryAvaillableRequest $request)
    {
        $input = $request->all();

        $input["name"] = explode("-",$input["name"])[0];

        $countryAvaillable = $this->countryAvaillableRepository->create($input);

        Flash::success('Country Availlable saved successfully.');

        return redirect(route('country-availlables.index'));
    }

    /**
     * Display the specified CountryAvaillable.
     */
    public function show($id)
    {
        $countryAvaillable = $this->countryAvaillableRepository->find($id);

        if (empty($countryAvaillable)) {
            Flash::error('Country Availlable not found');

            return redirect(route('country-availlables.index'));
        }

        return view('country_availlables.show')->with('countryAvaillable', $countryAvaillable);
    }

    /**
     * Show the form for editing the specified CountryAvaillable.
     */
    public function edit($id)
    {
        $countryAvaillable = $this->countryAvaillableRepository->find($id);

        if (empty($countryAvaillable)) {
            Flash::error('Country Availlable not found');

            return redirect(route('country-availlables.index'));
        }

        return view('country_availlables.edit')->with('countryAvaillable', $countryAvaillable);
    }

    /**
     * Update the specified CountryAvaillable in storage.
     */
    public function update($id, UpdateCountryAvaillableRequest $request)
    {
        $countryAvaillable = $this->countryAvaillableRepository->find($id);

        if (empty($countryAvaillable)) {
            Flash::error('Country Availlable not found');

            return redirect(route('country-availlables.index'));
        }

        $countryAvaillable = $this->countryAvaillableRepository->update($request->all(), $id);

        Flash::success('Country Availlable updated successfully.');

        return redirect(route('country-availlables.index'));
    }

    /**
     * Remove the specified CountryAvaillable from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $countryAvaillable = $this->countryAvaillableRepository->find($id);

        if (empty($countryAvaillable)) {
            Flash::error('Country Availlable not found');

            return redirect(route('country-availlables.index'));
        }

        $this->countryAvaillableRepository->delete($id);

        Flash::success('Country Availlable deleted successfully.');

        return redirect(route('country-availlables.index'));
    }
}
