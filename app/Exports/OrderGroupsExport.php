<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrderGroupsExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $orderGroups;

    public function __construct($orderGroups)
    {
        $this->orderGroups = $orderGroups;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->orderGroups as $group) {
            foreach ($group->orders as $order) {
                $data[] = [
                    $group->user->unique_id ?? '-',
                    $group->user->name ?? '-',
                    $group->unique_id,
                    $order->unique_id,
                    $order->total_price,
                    $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '-',
                    $order->kg ?? '-',
                    $group->user->phone ?? '-',
                    $order->getStatus?->name_uz ?? '-',
                    $order->delivery_china ?? '-',
                    $order->delivery_type ?? "-",
                ];
            }

            $data[] = ['', '', '', '', '', '', '', '', '', ''];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'USER ID',
            'Ism',
            'GROUP ID',
            'ORDER ID',
            'SUMMA',
            'SANA',
            'KG',
            'MOBILE',
            'STATUS',
            "Xitoy ichki yo'lkiro",
            "Hisoblash turi",
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }
}
