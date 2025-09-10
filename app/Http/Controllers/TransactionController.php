<?php

namespace App\Http\Controllers;

use App\DataTables\TransactionDataTable;
use App\Enums\PayType;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\Company;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;

class TransactionController extends AppBaseController
{
    /** @var TransactionRepository $transactionRepository*/
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepo)
    {
        $this->transactionRepository = $transactionRepo;
    }

    /**
     * Display a listing of the Transaction.
     */
    public function index(TransactionDataTable $transactionDataTable)
    {

        $user = Auth::user();

        if($user->is_admin){
            $company = Company::where('id',">",0);
            $services = Client::where('user_id',">",0);
            $wallets = Wallet::where('id',">",0);

        }else{
            $company = Company::where("user_id",$user->id);
            $services = Client::where('user_id',$user->id);
            $wallets = $user->wallet();
        }

        $period = '';
        $type = '';
        $wallet_selected = '';
        $service_selected = '';
        $company_selected = "";

        $request = $transactionDataTable->request();

        if($request->input("period")!=null  ){

            $period = $request->input("period");

        }
        if($request->input("type")!=null  ){

            $type = $request->input("type");


        }
        if($request->input("company")!=null  ){

            $company_selected = $request->input("company");

            $companys = Company::where('id',$company_selected)->first();
            $wallets_all = $companys->wallets_nuage();

            $wallet_id= [];

            foreach ($wallets_all as $wallet){
                $wallet_id [] =$wallet->id;
            }


            $services = $services->where("company_id",$company_selected);
            $wallets =  $wallets->whereIn('id',$wallet_id);

        }

        if($request->input("service")!=null  ){

            $service_selected = $request->input("service");

            $service = Client::find($service_selected);
            //where('id',$service_selected)->first();

            $wallets_all = $service->wallets();

            $wallet_id= [];

            foreach ($wallets_all as $wallet){
                $wallet_id [] =$wallet->id;
            }

            $wallets =  $wallets->whereIn('id',$wallet_id);

        }

        if($request->input("wallet")!=null  ){

            $wallet_selected = $request->input("wallet");

        }

        $company=$company->get();
        $services =$services->get();
        $wallets = $wallets->get();


        return $transactionDataTable->render('transactions.index',compact('period',
            'type','company_selected','company',
            'service_selected',"services",'wallet_selected',
            'wallets'
        ));
    }

    /**
     * Show the form for creating a new Transaction.
     */
    public function create()
    {
        return view('transactions.create');
    }

    /**
     * Store a newly created Transaction in storage.
     */
    public function store(CreateTransactionRequest $request)
    {
        $input = $request->all();

        $transaction = $this->transactionRepository->create($input);

        Flash::success('Transaction saved successfully.');

        return redirect(route('transactions.index'));
    }

    /**
     * Display the specified Transaction.
     */
    public function show($id)
    {
        $transaction = $this->transactionRepository->find($id);

        if (empty($transaction)) {
            Flash::error('Transaction not found');

            return redirect(route('transactions.index'));
        }

        return view('transactions.show')->with('transaction', $transaction);
    }

    /**
     * Show the form for editing the specified Transaction.
     */
    public function edit($id)
    {
        $transaction = $this->transactionRepository->find($id);

        if (empty($transaction)) {
            Flash::error('Transaction not found');

            return redirect(route('transactions.index'));
        }

        return view('transactions.edit')->with('transaction', $transaction);
    }

    /**
     * Update the specified Transaction in storage.
     */
    public function update($id, UpdateTransactionRequest $request)
    {
        $transaction = $this->transactionRepository->find($id);

        if (empty($transaction)) {
            Flash::error('Transaction not found');

            return redirect(route('transactions.index'));
        }

        $transaction = $this->transactionRepository->update($request->all(), $id);

        Flash::success('Transaction updated successfully.');

        return redirect(route('transactions.index'));
    }

    /**
     * Remove the specified Transaction from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $transaction = $this->transactionRepository->find($id);

        if (empty($transaction)) {
            Flash::error('Transaction not found');

            return redirect(route('transactions.index'));
        }

        $this->transactionRepository->delete($id);

        Flash::success('Transaction deleted successfully.');

        return redirect(route('transactions.index'));
    }
}
