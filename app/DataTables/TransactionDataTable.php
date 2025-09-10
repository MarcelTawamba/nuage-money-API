<?php

namespace App\DataTables;

use App\Enums\PayType;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class TransactionDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        $dataTable->editColumn('wallet_id', function ($data) {

           $wallet = Wallet::whereId($data->wallet_id)->first();

           return $wallet->currency->name;

        });

        $dataTable->editColumn('wallet_id', function ($data) {

            $wallet = Wallet::whereId($data->wallet_id)->first();
            if($wallet->user->client == null){
                return  $wallet->user->name . " ( " .$wallet->currency->name ." )";
            }else{
                return  $wallet->user->client->user->name . " ( " .$wallet->currency->name ." )";
            }



        });

        $dataTable->editColumn('created_at', function ($data) {



            return  $data->created_at->format('d M, Y') ;

        });

        return $dataTable->addColumn('action', 'transactions.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ExchangeRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Transaction $model)
    {
        $user = Auth::user();


        if($user->is_admin){

            $transactions = $model->where("id",">",0);

        }else{

            $wallets = $user->wallets_nuage();

            $wallet_id= [];

            foreach ($wallets as $wallet){
                $wallet_id [] =$wallet->id;
            }

            $transactions = $model->whereIn("wallet_id",$wallet_id)->where("refund",false);
        }

        $request = $this->request();

//
//        if($request->input("company")!=null  ){
//
//            $company_selected = $request->input("company");
//
//            $companys = Company::where('id',$company_selected)->first();
//            $wallets = $companys->wallets_nuage();
//
//            $wallet_id= [];
//
//            foreach ($wallets as $wallet){
//                $wallet_id [] =$wallet->id;
//            }
//
//            $transactions = Transaction::whereIn("wallet_id",$wallet_id);
//
//            $transactions = $transactions->whereId('wallet_id',$company_selected);
//
//
//        }
//
//        if($request->input("service")!=null  ){
//
//            $service_selected = $request->input("service");
//
//            $service = Client::find($service_selected);
//            //where('id',$service_selected)->first();
//
//            $wallets = $service->wallets();
//
//            $wallet_id= [];
//
//            foreach ($wallets as $wallet){
//                $wallet_id [] =$wallet->id;
//            }
//
//            $transactions = Transaction::whereIn("wallet_id",$wallet_id);
//
//
//
//        }

        if($request->input("wallet")!=null  ){

            $wallet_selected = $request->input("wallet");
            $transactions = $transactions->where('wallet_id','=',$wallet_selected);

        }

        if($request->input("period")!=null  ){

            $period = $request->input("period");
            $date = Carbon::now();
            if($period == "day"){

                $transactions = $transactions->whereDate('created_at', $date);

            }elseif ($period == "week"){


                $transactions = $transactions->whereBetween('created_at',  [Carbon::parse($date)->startOfWeek(), Carbon::parse($date)->endOfWeek()]);

            }elseif ($period == "month"){

                $transactions = $transactions->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year);

            }elseif ($period == "year"){

                $transactions = $transactions->whereYear('created_at',$date->year);

            }elseif ($period == "semester"){
                $date_debut = $date->subMonth(3);
                $transactions = $transactions->whereBetween('created_at',[Carbon::parse($date_debut->startOfMonth()),Carbon::parse($date)]);
            }

        }
        if($request->input("type")!=null  ){

            $type = $request->input("type");

            if($type == PayType::PAY_IN){
                $transactions = $transactions->where('amount','>',0);
            }else{
                $transactions = $transactions->where('amount','<',0);
            }

        }


        return    $transactions->where("amount",'!=',0)->newQuery();


    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'dom' => 'Bfrtip',
                'stateSave' => true,
                'order' => [[0, 'desc']],
                'buttons' => [
                    // Enable Buttons as per your need
                    ['extend' => 'export', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
                ],
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {

        if (Auth::user()->is_admin) {

            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                ['data' => 'reference', 'name' => 'reference', 'title' => 'reference'],
                ['data' => 'wallet_id', 'name' => 'wallet_id', 'title' => 'Wallet'],
                ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                ['data' => 'balance_before', 'name' => 'balance_before', 'title' => 'Balance Before'],
                ['data' => 'balance_after', 'name' => 'balance_after', 'title' => 'Balance After'],
                ['data' => 'refund', 'name' => 'refund', 'title' => 'Refund'],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date'],
            ];
        } else {

            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                ['data' => 'reference', 'name' => 'reference', 'title' => 'reference'],
                ['data' => 'wallet_id', 'name' => 'wallet_id', 'title' => 'Wallet'],
                ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                ['data' => 'balance_before', 'name' => 'balance_before', 'title' => 'Balance Before'],
                ['data' => 'balance_after', 'name' => 'balance_after', 'title' => 'Balance After'],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date'],
            ];
        }

    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'transaction_datatable_' . time();
    }


}
