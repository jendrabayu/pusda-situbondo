<?php

namespace App\DataTables;

use App\Models\Statistic;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class StatisticsDataTable extends DataTable
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
            ->editColumn('created_at', function ($statistic) {
                return $statistic->created_at ? with(new Carbon($statistic->created_at))->diffForHumans() : '';
            })
            ->addIndexColumn();
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Statistic $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Statistic $model)
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
            ->parameters([
                'language' => [
                    'url' => url('https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json')
                ],
            ])
            ->setTableId('statistics-table')
            ->columns($this->getColumns())
            ->languageEmptyTable('Data pengunjung belum tersedia')
            ->minifiedAjax()
            ->orderBy(1);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::computed('DT_RowIndex', '#'),
            Column::make('ip')->title('Alamat IP'),
            Column::make('os')->title('Sistem Operasi'),
            Column::make('browser')->title('Browser'),
            Column::make('created_at')->title('Waktu')->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Statistics_' . date('YmdHis');
    }
}
