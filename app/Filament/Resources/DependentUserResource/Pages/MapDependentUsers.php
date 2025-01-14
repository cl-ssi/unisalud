<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

// use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Cheesegrits\FilamentGoogleMaps\Columns\MapColumn;
use App\Filament\Resources\DependentUserResource;
use App\Models\DependentUser;


use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class MapDependentUsers extends ListRecords{

    protected static string $resource = DependentUserResource::class;

    protected static ?string $title = 'Mapa Usuarios Dependiente';

    public $dependent_user = null;
    public $user_id = null;
    public $users = [];
    public $conditionTypes = [];

    public function table(Table $table): Table
    {
        return $table
            ->query(DependentUser::query())
            ->columns([
                MapColumn::make('user.address.location.location')
            ]);
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Volver')
                ->url(DependentUserResource::getUrl())
                ->button()
                ->color('info'),
        ];
    }
}
