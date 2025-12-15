<?php

namespace App\DataTables;


use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class WalletDataTable extends DataTable
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

        return $dataTable
            ->addColumn('action', 'wallets.datatables_actions')
            ->editColumn('id', function($wallet) {
                return $wallet->id;
            })
            ->addColumn('owner', function($wallet) {
                $array = $wallet->toArray();
                return $array['owner'];
            })
            ->addColumn('company', function($wallet) {
                $array = $wallet->toArray();
                return $array['company'];
            })
            ->addColumn('user', function($wallet) {
                $array = $wallet->toArray();
                return $array['user'];
            })
            ->addColumn('currency', function($wallet) {
                return $wallet->currency->name;
            })
            ->editColumn('balance', function($wallet) {
                return $wallet->balance;
            })
            ->filterColumn('owner', function($query, $keyword) {
                // Don't let global search apply - we handle it manually
            })
            ->filterColumn('company', function($query, $keyword) {
                // Don't let global search apply - we handle it manually
            })
            ->filterColumn('user', function($query, $keyword) {
                // Don't let global search apply - we handle it manually
            })
            ->filterColumn('currency', function($query, $keyword) {
                // Don't let global search apply - we handle it manually  
            })
            ->filter(function ($query) {
                if (request()->has('search') && request('search.value') != '') {
                    $keyword = request('search.value');
                    
                    $query->where(function($q) use ($keyword) {
                        // Search by ID
                        $q->where('wallets_nuage.id', 'like', "%{$keyword}%")
                        // Search in User model (direct user relationship)
                        ->orWhere(function($subQ) use ($keyword) {
                            $subQ->where('wallets_nuage.user_type', 'App\\Models\\User')
                                 ->whereHasMorph('user', ['App\\Models\\User'], function($userQ) use ($keyword) {
                                     $userQ->where('name', 'like', "%{$keyword}%");
                                 });
                        })
                        // Search in ClientWallet->Client (oauth_clients.name)
                        ->orWhere(function($subQ) use ($keyword) {
                            $subQ->where('wallets_nuage.user_type', 'App\\Models\\ClientWallet')
                                 ->whereHasMorph('user', ['App\\Models\\ClientWallet'], function($cwQ) use ($keyword) {
                                     $cwQ->whereHas('client', function($clientQ) use ($keyword) {
                                         $clientQ->where('name', 'like', "%{$keyword}%");
                                     });
                                 });
                        })
                        // Search in currency/wallet type name
                        ->orWhereHas('currency', function($currQ) use ($keyword) {
                            $currQ->where('name', 'like', "%{$keyword}%");
                        });
                    });
                }
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ExchangeRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Wallet $model)
    {
        $user = Auth::user();
        if($user->is_admin){

            return $model->newQuery();

        }else{
            $wallets = $user->wallets_nuage();

            $wallet_id= [];

            foreach ($wallets as $wallet){
                $wallet_id [] =$wallet->id;
            }


            return  $model->whereIn("id",$wallet_id)->newQuery();



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
            ->addAction(['width' => '120px', 'printable' => false])
            ->parameters([
                'dom' => 'Bfrtip',
                'stateSave' => true,
                'order' => [[0, 'desc']],
                'buttons' => [
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
        if (Auth::user()->is_admin) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'ID', 'searchable' => true, 'orderable' => true],
                ['data' => 'owner', 'name' => 'owner', 'title' => 'Owner', 'searchable' => false, 'orderable' => false],
                ['data' => 'company', 'name' => 'company', 'title' => 'Company', 'searchable' => false, 'orderable' => false],
                ['data' => 'user', 'name' => 'user', 'title' => 'User', 'searchable' => false, 'orderable' => false],
                ['data' => 'currency', 'name' => 'currency', 'title' => 'Currency', 'searchable' => false, 'orderable' => false],
                ['data' => 'balance', 'name' => 'balance', 'title' => 'Balance', 'searchable' => false, 'orderable' => true],
            ];
        } else {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'ID', 'searchable' => true, 'orderable' => true],
                ['data' => 'owner', 'name' => 'owner', 'title' => 'Owner', 'searchable' => false, 'orderable' => false],
                ['data' => 'currency', 'name' => 'currency', 'title' => 'Currency', 'searchable' => false, 'orderable' => false],
                ['data' => 'balance', 'name' => 'balance', 'title' => 'Balance', 'searchable' => false, 'orderable' => true],
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
        return 'client_datatable_' . time();
    }
}
