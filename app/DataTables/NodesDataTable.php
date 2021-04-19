<?php

namespace App\DataTables;

use App\Models\Node;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class NodesDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('status', function ($item) {
                if ($item->status == 'OFFLINE') {
                    $class = 'danger';
                } elseif ($item->status == 'GENERATE_ID') {
                    $class = 'info';
                } elseif ($item->status == 'PRUNING_DB') {
                    $class = 'secondary';
                } elseif ($item->status == 'WAIT_FOR_SYNCING') {
                    $class = 'warning';
                } elseif ($item->status == 'SYNC_STARTED') {
                    $class = 'primary';
                } elseif ($item->status == 'PERSIST_FINISHED') {
                    $class = 'success';
                } else {
                    $class = 'light';
                }

                return '<span class="badge badge-' . $class . '">' . $item->status . '</span>';
            })->rawColumns(['status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Node $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Node $model)
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
            ->setTableId('nodes-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
//            ->orderBy([1, 'desc'])
            ->buttons(
                Button::make('create'),
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('host'),
            Column::make('country'),
            Column::make('region'),
            Column::make('city'),
            Column::make('status'),
            Column::make('version'),
            Column::make('height'),
            Column::make('uptime'),
            Column::make('proposals'),
            Column::make('speed'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Nodes_' . date('YmdHis');
    }
}
