<?php

namespace App\Exports;

use App\Models\Lead;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class LeadsFromView implements ShouldAutoSize, WithMapping, WithEvents, WithHeadings, FromQuery
{
    use Exportable;
    private $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function query()
    {
        $query = Lead::query()
        ->when(array_key_exists('lead', $this->params), function ($q){
            return $q->where(function ($quer) {
                return $quer->where('lead.name', 'LIKE', "%{$this->params['lead']}%");
            });
        })
        ->when(array_key_exists('status', $this->params), function ($query) {
            return $query->where('lead.status', $this->params['status']);
        })
        ->orderBy('name');

        return $query;
    }

    public function map($lead): array
    {
        return [
            $lead->name ?? 'Não Informado',
            $lead->email ?? 'Não Informado',
            $lead->cellphone ?? 'Não Informado',

        ];
    }

    public function headings(): array
    {
        return [
            'Nome',
            'Email',
            'Celular',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:P1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            },
        ];
    }


}
