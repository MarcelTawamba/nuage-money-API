<?php

namespace App\Http\Controllers;

use App\DataTables\StartButtonBankDataTable;
use App\Http\Requests\CreateStartButtonBankRequest;
use App\Http\Requests\UpdateStartButtonBankRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\StartButtonBankRepository;
use Illuminate\Http\Request;
use Flash;

class StartButtonBankController extends AppBaseController
{
    /** @var StartButtonBankRepository $startButtonBankRepository*/
    private StartButtonBankRepository $startButtonBankRepository;

    public function __construct(StartButtonBankRepository $startButtonBankRepo)
    {
        $this->startButtonBankRepository = $startButtonBankRepo;
    }

    /**
     * Display a listing of the StartButtonBank.
     */
    public function index(StartButtonBankDataTable $startButtonBankDataTable)
    {
    return $startButtonBankDataTable->render('start_button_banks.index');
    }


    /**
     * Show the form for creating a new StartButtonBank.
     */
    public function create()
    {
        return view('start_button_banks.create');
    }

    /**
     * Store a newly created StartButtonBank in storage.
     */
    public function store(CreateStartButtonBankRequest $request)
    {
        $input = $request->all();

        $startButtonBank = $this->startButtonBankRepository->create($input);

        Flash::success('Start Button Bank saved successfully.');

        return redirect(route('start-button-banks.index'));
    }

    /**
     * Display the specified StartButtonBank.
     */
    public function show($id)
    {
        $startButtonBank = $this->startButtonBankRepository->find($id);

        if (empty($startButtonBank)) {
            Flash::error('Start Button Bank not found');

            return redirect(route('start-button-banks.index'));
        }

        return view('start_button_banks.show')->with('startButtonBank', $startButtonBank);
    }

    /**
     * Show the form for editing the specified StartButtonBank.
     */
    public function edit($id)
    {
        $startButtonBank = $this->startButtonBankRepository->find($id);

        if (empty($startButtonBank)) {
            Flash::error('Start Button Bank not found');

            return redirect(route('start-button-banks.index'));
        }

        return view('start_button_banks.edit')->with('startButtonBank', $startButtonBank);
    }

    /**
     * Update the specified StartButtonBank in storage.
     */
    public function update($id, UpdateStartButtonBankRequest $request)
    {
        $startButtonBank = $this->startButtonBankRepository->find($id);

        if (empty($startButtonBank)) {
            Flash::error('Start Button Bank not found');

            return redirect(route('start-button-banks.index'));
        }

        $startButtonBank = $this->startButtonBankRepository->update($request->all(), $id);

        Flash::success('Start Button Bank updated successfully.');

        return redirect(route('start-button-banks.index'));
    }

    /**
     * Remove the specified StartButtonBank from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $startButtonBank = $this->startButtonBankRepository->find($id);

        if (empty($startButtonBank)) {
            Flash::error('Start Button Bank not found');

            return redirect(route('start-button-banks.index'));
        }

        $this->startButtonBankRepository->delete($id);

        Flash::success('Start Button Bank deleted successfully.');

        return redirect(route('start-button-banks.index'));
    }
}
