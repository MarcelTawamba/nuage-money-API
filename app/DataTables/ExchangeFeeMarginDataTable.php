<?php

namespace App\DataTables;

use App\Models\Client;
use App\Models\ExchangeFeeMargin;
use App\Models\ExchangeRequest;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class ExchangeFeeMarginDataTable extends DataTable
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

        $dataTable->editColumn('exchange_request', function ($data) {
            $request = ExchangeRequest::find($data->exchange_request);



            return Client::find($request->client_id)->user->name;

        });

        $dataTable->editColumn('created_at', function ($data) {



            return  $data->created_at->format('d M, Y') ;

        });

        return $dataTable->addColumn('action', 'exchange_fee_margins.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ExchangeFeeMargin $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ExchangeFeeMargin $model)
    {
        return $model->newQuery();
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
            ->addAction(['width' => '120px', 'printable' => false])
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
        return [
            'currency',
            'amount',
            ['data' => 'exchange_request', 'name' => 'exchange_request', 'title' => 'User'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'exchange_fee_margins_datatable_' . time();
    }
}
