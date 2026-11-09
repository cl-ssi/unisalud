<?php

declare(strict_types=1);

namespace App\Filament\Resources\VentoxUserResource\Pages;

use App\Filament\Resources\VentoxUserResource;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class MapVentoxUsers extends Page
{
    protected static string $resource = VentoxUserResource::class;

    protected static string $view = 'filament.resources.ventox-user-resource.pages.map-ventox-users';

    protected static ?string $title = 'Mapa de Pacientes Ventox';

    public $organizationTypes;

    public $req_users_id = null;

    public $organizations_id = null;

    public $search = null;

    public $risks = null;

    public function mount(): void
    {
        $this->organizationTypes = Organization::whereHas('contactPoint', fn($query) => $query->whereNotNull('id'))
            ->with([
                'contactPoint' => fn($query) => $query->has('user')->whereNotNull('contactPoint.id'),
                'contactPoint.user' => fn($query) => $query->has('ventoxUser')->whereNotNull('contactPoint.user.id'),
            ])
            ->pluck('alias', 'id');

        $this->req_users_id = request('users_id') ?? null;
        $this->organizations_id = request('organizations_id') ?? null;
        $this->risks = request('risks') ?? null;

        $this->form->fill([
            'organizations_id' => $this->organizations_id,
            'users_id'         => $this->req_users_id,
            'risks'            => $this->risks,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('risks')
                    ->label('Riesgos')
                    ->options([
                        'Zona de Inundacion' => 'Zona de Inundación',
                        'Zona de Aluvion'    => 'Zona de Aluvión',
                    ])
                    ->multiple()
                    ->reactive()
                    ->columnSpan(1)
                    ->afterStateUpdated(fn($state) => $this->risks = $state),
                Forms\Components\Select::make('organizations_id')
                    ->label('Organización')
                    ->options($this->organizationTypes)
                    ->placeholder('Seleccione una organización')
                    ->multiple()
                    ->preload()
                    ->columnSpan(1)
                    ->reactive()
                    ->afterStateUpdated(fn($state) => $this->organizations_id = $state),
            ])->columns(3);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            'map' => \App\Filament\Resources\VentoxUserResource\Widgets\MapWidget::make([
                'organizations_id' => $this->organizations_id,
                'users_id'         => $this->req_users_id,
                'risks'            => $this->risks,
            ]),
        ];
    }

    public function updated($name)
    {
        $this->dispatch('changeFilters', $this->organizations_id, $this->req_users_id, $this->risks);
    }

    public function goBack()
    {
        return redirect()->to(VentoxUserResource::getUrl('index', [
            'organizations_id' => $this->organizations_id,
            'users_id'         => $this->req_users_id,
            'risks'            => $this->risks,
        ]));
    }
}
