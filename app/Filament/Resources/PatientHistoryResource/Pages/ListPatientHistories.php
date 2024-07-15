<?php

namespace App\Filament\Resources\PatientHistoryResource\Pages;

use App\Filament\Resources\PatientHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use App\Models\PatientHistory;

class ListPatientHistories extends ListRecords
{
    protected static string $resource = PatientHistoryResource::class;

    public static function getPatientHistory(string $run)
    {
        PatientHistoryResource::searchPatientHistory($run);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test')->action(fn () => PatientHistory::searchPatientHistory()),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PatientHistoryResource\Widgets\ListPatientHistoryWidget::class,
        ];
    }
}
