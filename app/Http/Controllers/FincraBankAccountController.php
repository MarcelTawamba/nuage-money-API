<?php

namespace App\Http\Controllers;

use App\DataTables\FincraBankAccountDataTable;
use App\Http\Requests\CreateFincraBankAccountRequest;
use App\Http\Requests\UpdateFincraBankAccountRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\FincraBankAccountRepository;
use Illuminate\Http\Request;
use Flash;

class FincraBankAccountController extends AppBaseController
{
    /** @var FincraBankAccountRepository $fincraBankAccountRepository*/
    private $fincraBankAccountRepository;

    public function __construct(FincraBankAccountRepository $fincraBankAccountRepo)
    {
        $this->fincraBankAccountRepository = $fincraBankAccountRepo;
    }

    /**
     * Display a listing of the FincraBankAccount.
     */
    public function index(FincraBankAccountDataTable $fincraBankAccountDataTable)
    {
    return $fincraBankAccountDataTable->render('fincra_bank_accounts.index');
    }


    /**
     * Show the form for creating a new FincraBankAccount.
     */
    public function create()
    {
        return view('fincra_bank_accounts.create');
    }

    /**
     * Store a newly created FincraBankAccount in storage.
     */
    public function store(CreateFincraBankAccountRequest $request)
    {
        $input = $request->all();

        $fincraBankAccount = $this->fincraBankAccountRepository->create($input);

        Flash::success('Fincra Bank Account saved successfully.');

        return redirect(route('fincra-bank-accounts.index'));
    }

    /**
     * Display the specified FincraBankAccount.
     */
    public function show($id)
    {
        $fincraBankAccount = $this->fincraBankAccountRepository->find($id);

        if (empty($fincraBankAccount)) {
            Flash::error('Fincra Bank Account not found');

            return redirect(route('fincra-bank-accounts.index'));
        }

        return view('fincra_bank_accounts.show')->with('fincraBankAccount', $fincraBankAccount);
    }

    /**
     * Show the form for editing the specified FincraBankAccount.
     */
    public function edit($id)
    {
        $fincraBankAccount = $this->fincraBankAccountRepository->find($id);

        if (empty($fincraBankAccount)) {
            Flash::error('Fincra Bank Account not found');

            return redirect(route('fincra-bank-accounts.index'));
        }

        return view('fincra_bank_accounts.edit')->with('fincraBankAccount', $fincraBankAccount);
    }

    /**
     * Update the specified FincraBankAccount in storage.
     */
    public function update($id, UpdateFincraBankAccountRequest $request)
    {
        $fincraBankAccount = $this->fincraBankAccountRepository->find($id);

        if (empty($fincraBankAccount)) {
            Flash::error('Fincra Bank Account not found');

            return redirect(route('fincra-bank-accounts.index'));
        }

        $fincraBankAccount = $this->fincraBankAccountRepository->update($request->all(), $id);

        Flash::success('Fincra Bank Account updated successfully.');

        return redirect(route('fincra-bank-accounts.index'));
    }

    /**
     * Remove the specified FincraBankAccount from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $fincraBankAccount = $this->fincraBankAccountRepository->find($id);

        if (empty($fincraBankAccount)) {
            Flash::error('Fincra Bank Account not found');

            return redirect(route('fincra-bank-accounts.index'));
        }

        $this->fincraBankAccountRepository->delete($id);

        Flash::success('Fincra Bank Account deleted successfully.');

        return redirect(route('fincra-bank-accounts.index'));
    }
}
