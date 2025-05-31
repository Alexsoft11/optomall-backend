<?php

namespace App\Filament\Resources\OrderGroupResource\Pages;

use App\Exports\OrderGroupsExport;
use App\Filament\Resources\OrderGroupResource;
use App\Models\OrderGroup;
use App\Models\OrderStatus;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListOrderGroups extends ListRecords
{
    protected static string $resource = OrderGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make('export')
                ->label('Buyurtma Export')
                ->color('success')
                ->modalHeading('Export Orders')
                ->form([
                    Select::make('status')
                        ->label('Buyurtma Status')
                        ->options(OrderStatus::pluck('name_uz', 'id')->toArray()),
                    DatePicker::make('date_from')->label('Boshlanish sanasi'),
                    DatePicker::make('date_to')->label('Tugash sanasi'),
                ])
                ->action(function (array $data) {
                    $query = OrderGroup::with(['orders', 'user'])->orderBy('created_at', 'asc');

                    if (!empty($data['status'])) {
                        $query->where('status', $data['status']);
                    }

                    if (!empty($data['date_from'])) {
                        $query->whereDate('created_at', '>=', $data['date_from']);
                    }

                    if (!empty($data['date_to'])) {
                        $query->whereDate('created_at', '<=', $data['date_to']);
                    }

                    $orderGroups = $query->get();

                    return self::exportToWord($orderGroups, $data['status'], $data['date_from'], $data['date_to']);
                }),

            Action::make('exportExcel')
                ->label('Excel Export')
                ->color('primary')
                ->icon('heroicon-o-document-arrow-down')
                ->modalHeading('Export Orders to Excel')
                ->form([
                    Select::make('status')
                        ->label('Buyurtma Status')
                        ->options(OrderStatus::pluck('name_uz', 'id')->toArray()),
                    DatePicker::make('date_from')->label('Boshlanish sanasi'),
                    DatePicker::make('date_to')->label('Tugash sanasi'),
                ])
                ->action(function (array $data) {
                    $query = OrderGroup::with(['orders', 'user'])->orderBy('created_at', 'asc');

                    if (!empty($data['status'])) {
                        $query->where('status', $data['status']);
                    }

                    if (!empty($data['date_from'])) {
                        $query->whereDate('created_at', '>=', $data['date_from']);
                    }

                    if (!empty($data['date_to'])) {
                        $query->whereDate('created_at', '<=', $data['date_to']);
                    }

                    $orderGroups = $query->get();

                    return self::exportToExcel($orderGroups, $data['status'], $data['date_from'], $data['date_to']);
                }),
        ];
    }

    protected static function exportToWord($orderGroups, $status, $dateFrom, $dateTo)
    {
        return response()->streamDownload(function () use ($orderGroups) {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection([
                'orientation' => 'landscape',
                'marginLeft' => 600,
                'marginRight' => 600,
                'marginTop' => 600,
                'marginBottom' => 600,
            ]);

            $tableStyle = [
                'borderSize' => 6,
                'borderColor' => '000000',
                'cellMargin' => 50,
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                'width' => 100 * 50,
            ];

            $cellStyle = ['valign' => 'center'];
            $headerStyle = ['bold' => true, 'size' => 12];

            foreach ($orderGroups as $group) {
                $section->addText("ORDER GROUP: {$group->unique_id}", ['bold' => true, 'size' => 14]);
                $section->addTextBreak(1);

                $table = $section->addTable($tableStyle);

                $table->addRow();
                $table->addCell(2000, $cellStyle)->addText("USER ID", $headerStyle);
                $table->addCell(2000, $cellStyle)->addText("Ism", $headerStyle);
                $table->addCell(2000, $cellStyle)->addText("GROUP ID", $headerStyle);
                $table->addCell(2000, $cellStyle)->addText("ORDER ID", $headerStyle);
                $table->addCell(2000, $cellStyle)->addText("SUMMA", $headerStyle);
                $table->addCell(2000, $cellStyle)->addText("SANA", $headerStyle);
                $table->addCell(2000, $cellStyle)->addText("KG", $headerStyle);
                $table->addCell(2000, $cellStyle)->addText("MOBILE", $headerStyle);

                foreach ($group->orders as $order) {
                    $table->addRow();
                    $table->addCell(2000, $cellStyle)->addText($group->user->unique_id ?? '-');
                    $table->addCell(2000, $cellStyle)->addText($group->user->name ?? '-');
                    $table->addCell(2000, $cellStyle)->addText($group->unique_id);
                    $table->addCell(2000, $cellStyle)->addText($order->unique_id);
                    $table->addCell(2000, $cellStyle)->addText($order->total_price);
                    $table->addCell(2000, $cellStyle)->addText($order->created_at);
                    $table->addCell(2000, $cellStyle)->addText($order->kg ?? '-');
                    $table->addCell(2000, $cellStyle)->addText($group->user->phone ?? '-');
                }

                $section->addTextBreak(2);
            }

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
        }, self::generateFileName($status, $dateFrom, $dateTo));
    }

    protected static function exportToExcel($orderGroups, $status, $dateFrom, $dateTo)
    {
        $fileName = self::generateExcelFileName($status, $dateFrom, $dateTo);
        return Excel::download(new OrderGroupsExport($orderGroups), $fileName);
    }

    protected static function generateFileName($status, $dateFrom, $dateTo)
    {
        $statusName = OrderStatus::find($status)?->name_uz ?? 'All';
        $dateFrom = $dateFrom ? date('Y-m-d', strtotime($dateFrom)) : 'All';
        $dateTo = $dateTo ? date('Y-m-d', strtotime($dateTo)) : 'All';

        return "Buyurtmalar_{$statusName}_{$dateFrom}_{$dateTo}.docx";
    }

    protected static function generateExcelFileName($status, $dateFrom, $dateTo)
    {
        $statusName = OrderStatus::find($status)?->name_uz ?? 'All';
        $dateFrom = $dateFrom ? date('Y-m-d', strtotime($dateFrom)) : 'All';
        $dateTo = $dateTo ? date('Y-m-d', strtotime($dateTo)) : 'All';

        return "Buyurtmalar_{$statusName}_{$dateFrom}_{$dateTo}.xlsx";
    }
}