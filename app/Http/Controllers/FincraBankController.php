<?php

namespace App\Http\Controllers;

use App\DataTables\FincraBankDataTable;
use App\Http\Requests\CreateFincraBankRequest;
use App\Http\Requests\UpdateFincraBankRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\FincraBankRepository;
use Illuminate\Http\Request;
use Flash;

class FincraBankController extends AppBaseController
{
    /** @var FincraBankRepository $fincraBankRepository*/
    private $fincraBankRepository;

    public function __construct(FincraBankRepository $fincraBankRepo)
    {
        $this->fincraBankRepository = $fincraBankRepo;
    }

    /**
     * Display a listing of the FincraBank.
     */
    public function index(FincraBankDataTable $fincraBankDataTable)
    {
    return $fincraBankDataTable->render('fincra_banks.index');
    }


    /**
     * Show the form for creating a new FincraBank.
     */
    public function create()
    {
        return view('fincra_banks.create');
    }

    /**
     * Store a newly created FincraBank in storage.
     */
    public function store(CreateFincraBankRequest $request)
    {
        $input = $request->all();

        $fincraBank = $this->fincraBankRepository->create($input);

        Flash::success('Fincra Bank saved successfully.');

        return redirect(route('fincra-banks.index'));
    }

    /**
     * Display the specified FincraBank.
     */
    public function show($id)
    {
        $fincraBank = $this->fincraBankRepository->find($id);

        if (empty($fincraBank)) {
            Flash::error('Fincra Bank not found');

            return redirect(route('fincra-banks.index'));
        }

        return view('fincra_banks.show')->with('fincraBank', $fincraBank);
    }

    /**
     * Show the form for editing the specified FincraBank.
     */
    public function edit($id)
    {
        $fincraBank = $this->fincraBankRepository->find($id);

        if (empty($fincraBank)) {
            Flash::error('Fincra Bank not found');

            return redirect(route('fincra-banks.index'));
        }

        return view('fincra_banks.edit')->with('fincraBank', $fincraBank);
    }

    /**
     * Update the specified FincraBank in storage.
     */
    public function update($id, UpdateFincraBankRequest $request)
    {
        $fincraBank = $this->fincraBankRepository->find($id);

        if (empty($fincraBank)) {
            Flash::error('Fincra Bank not found');

            return redirect(route('fincra-banks.index'));
        }

        $fincraBank = $this->fincraBankRepository->update($request->all(), $id);

        Flash::success('Fincra Bank updated successfully.');

        return redirect(route('fincra-banks.index'));
    }

    /**
     * Remove the specified FincraBank from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $fincraBank = $this->fincraBankRepository->find($id);

        if (empty($fincraBank)) {
            Flash::error('Fincra Bank not found');

            return redirect(route('fincra-banks.index'));
        }

        $this->fincraBankRepository->delete($id);

        Flash::success('Fincra Bank deleted successfully.');

        return redirect(route('fincra-banks.index'));
    }
}
