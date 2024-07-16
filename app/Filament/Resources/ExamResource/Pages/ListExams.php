<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Actions;
use App\Models\Exam;
use Filament\Resources\Pages\ListRecords;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /*
            Actions\Action::make('test')->action(
                function ()
                {
                    dd('test');
                }
            ),
            */
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExamResource\Widgets\SearchExamWidget::class,
        ];
    }
}
