<?php

namespace App\DataTables;


use App\Models\Achat;
use App\Models\Client;
use App\Models\ExchangeRequest;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;

use Yajra\DataTables\Services\DataTable;

class AchatDataTable extends DataTable
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

        $dataTable->editColumn('requestable_id', function ($data) {

            $request = $data->requestable_type::find($data->requestable_id);
            if( $data->requestable_type == ExchangeRequest::class ){
                return json_encode([
                    'from' => $request->from_currency,
                    'to' => $request->to_currency,
                    'amount' => $request->amount,
                ]);
            }else{
                return json_encode($request->toArray());
            }


        });
        $dataTable->editColumn('amount', function ($data) {

            return "$data->amount $data->currency";

        });

        $dataTable->editColumn('client_id', function ($data) {

            return Client::find($data->client_id)->user->name;


        });
        $dataTable->editColumn('created_at', function ($data) {



            return  $data->created_at->format('d M, Y') ;

        });


        return $dataTable;

    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Achat $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(Achat $model)
    {


        $user = \Auth::user();
        if ($user->is_admin) {

            $result =  $model->where("id",">",0);

        } else {

            $wallets = $user->clients;

            $wallet_id= [];

            foreach ($wallets as $wallet){
                $wallet_id [] =$wallet->id;
            }

            $result =  $model->whereIn("client_id",$wallet_id);

        }

        return $result->newQuery();

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
                ['data' => 'ref_id', 'name' => 'ref_id', 'title' => 'Reference'],
                ['data' => 'user_ref_id', 'name' => 'user_ref_id', 'title' => 'User Reference'],
                ['data' => 'client_id', 'name' => 'client_id', 'title' => 'User'],
                ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                ['data' => 'requestable_id', 'name' => 'requestable_id', 'title' => 'Detail'],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date'],
            ];
        } else {

            return [
                ['data' => 'ref_id', 'name' => 'ref_id', 'title' => 'Reference'],

                ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                ['data' => 'requestable_id', 'name' => 'requestable_id', 'title' => 'Detail'],
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
        return 'Achat_' . time();
    }


}

