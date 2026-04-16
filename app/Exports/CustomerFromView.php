<?php

namespace App\Exports;

use App\Models\Customer;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class CustomerFromView implements ShouldAutoSize, WithMapping, WithEvents, WithHeadings, FromQuery
{

    use Exportable;
    private $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function query()
    {
        $query = Customer::query()
        ->person()
        ->when(array_key_exists('customer', $this->params), function ($q){
            return $q->where(function ($quer) {
                return $quer->where('peoples.name', 'LIKE', "%{$this->params['customer']}%")
                ->orWhereRaw('(replace(replace(replace(peoples.nif, ".",""),"/",""),"-","") like "$'. clean($this->params['customer']). '%")');

            });
        })
        ->when(array_key_exists('city_id', $this->params), function ($query) {
            return $query->where('peoples.city_id', '=', $this->params['city_id']);
        })
        ->when(array_key_exists('status', $this->params), function ($query) {
            return $query->where('customers.status', $this->params['status']);
        })
        ->when(array_key_exists('type', $this->params), function ($query) {
            return $query->where('type', $this->params['type']);
        })
        ->orderBy('name');

        return $query;
    }

    public function map($customer): array
    {
        return [
            $customer->name ?? 'Não Informado',
            $customer->nif ?? 'Não Informado',
            $customer->email ?? 'Não Informado',
            $customer->address ?? 'Não Informado',
            $customer->district ?? 'Não Informado',
            $customer->zip_code ?? 'Não Informado',
            $customer->city ?? 'Não informado',
            $customer->phone ?? 'Não Informado',
            Carbon::parse($customer->birth_day)->format('d/m/Y'),

        ];
    }

    public function headings(): array
    {
        return [
            'Nome',
            'Cpf',
            'Email',
            'Endereço',
            'Bairro',
            'CEP',
            'Cidade',
            'Telefone',
            'Data de Nascimento',
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
    public function getQuery($param)
    {
        return $this->allQueries()[$param];
    }
    public static function allQueries()
    {
        return [
            'customer' => 'customer',
            'city_id' => 'city_id',
            'status' => 'status',
            'created' => 'created',
            'type' => 'type',
        ];
    }

}
