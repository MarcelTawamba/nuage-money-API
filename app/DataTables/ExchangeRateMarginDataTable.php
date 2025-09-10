<?php

namespace App\DataTables;

use App\Models\ExchangeRateMargin;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class ExchangeRateMarginDataTable extends DataTable
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

        $dataTable->editColumn('updated_at', function ($data) {



            return  $data->created_at->format('d M, Y') ;

        });
        return $dataTable->addColumn('action', 'exchange_rate_margins.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ExchangeRateMargin $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ExchangeRateMargin $model)
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
            'from_currency',
            'to_currency',
            ['data' => 'rate', 'name' => 'rate', 'title' => 'Daily rate'],
            'margin',
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Date'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'exchange_rate_margins_datatable_' . time();
    }
}
