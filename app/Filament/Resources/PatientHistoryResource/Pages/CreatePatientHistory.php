<?php

namespace App\Filament\Resources\PatientHistoryResource\Pages;

use App\Filament\Resources\PatientHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePatientHistory extends CreateRecord
{
    protected static string $resource = PatientHistoryResource::class;
}
