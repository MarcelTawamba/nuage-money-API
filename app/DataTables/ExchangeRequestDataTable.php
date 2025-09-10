<?php

namespace App\DataTables;

use App\Models\Client;
use App\Models\ExchangeRequest;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class ExchangeRequestDataTable extends DataTable
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

        $dataTable->editColumn('created_at', function ($data) {

            return  $data->created_at->format('d M, Y') ;

        });

        return $dataTable->addColumn('action', 'exchange_requests.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ExchangeRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ExchangeRequest $model)
    {
        if(Auth::user()->is_admin){
            return $model->newQuery();
        }else{
            $services = Client::where('user_id',Auth::user()->id)->get();

            $client_id = [];
            foreach ($services as $service){
                $client_id []= $service->id;
            }


            return $model->whereIn("client_id",$client_id)->newQuery();
        }

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
                'dom'       => 'Bfrtip',
                'stateSave' => true,
                'order'     => [[0, 'desc']],
                'buttons'   => [
                    // Enable Buttons as per your need
//                    ['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner',],
//                    ['extend' => 'export', 'className' => 'btn btn-default btn-sm no-corner',],
//                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
//                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
//                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
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
        if(Auth::user()->is_admin){
            return [
                'from_currency',
                'to_currency',
                "client_id",
                'amount',
                'market_rate',
                'rate',
                'status',
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date'],
            ];
        }else{
            return [
                'from_currency',
                'to_currency',
                'amount',
                'rate',
                'status',
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
        return 'exchange_requests_datatable_' . time();
    }
}
