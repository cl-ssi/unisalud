<?php

namespace App\Filament\Resources\DependentUserResource\Pages;

use App\Filament\Resources\DependentUserResource;
use App\Models\Condition;
use App\Models\DependentUser;
use App\Models\Organization;
use App\Models\User;

use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

use Illuminate\Database\Eloquent\Builder;

class MapDependentUsers extends Page
{

    protected static string $resource = DependentUserResource::class;

    protected static string $view = 'filament.resources.dependent-user-resource.pages.map-dependent-users';

    protected static ?string $title = 'Mapa de Pacientes con Condición';

    public $organizationTypes;
    public $conditionTypes;


    public $conditions_id = null;
    public $req_users_id = null;
    public $organizations_id = null;
    public $search = null;
    public $risks = null;



    public function mount(): void
    {
        $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        $this->organizationTypes = Organization::whereHas('contactPoint', fn($query) => $query->whereNotNull('id'))
            ->with([
                'contactPoint' => fn($query) => $query->has('user')->whereNotNull('contactPoint.id'),
                'contactPoint.user' => fn($query) => $query->has('dependentUser')->whereNotNull('contactPoint.user.id')
            ])
            ->pluck('alias', 'id');


        $this->conditions_id = request('conditions_id') ?? null;
        $this->req_users_id = request('users_id') ?? null;
        $this->organizations_id = request('organizations_id') ?? null;
        $this->risks = request('risks') ?? null;

        $this->form->fill([
            'conditions_id' => $this->conditions_id,
            'organizations_id' => $this->organizations_id,
            'users_id' => $this->req_users_id,
            'risks' => $this->risks,
        ]);
    }



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('conditions_id')
                    ->label('Condición')
                    ->options($this->conditionTypes)
                    ->placeholder('Seleccione una condición')
                    ->preload()
                    ->multiple()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => $this->conditions_id = $state),
                Forms\Components\Select::make('organizations_id')
                    ->label('Organización')
                    ->options($this->organizationTypes)
                    ->placeholder('Seleccione una organización')
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => $this->organizations_id = $state),
                Forms\Components\Select::make('risks')
                    ->label('Riesgos')
                    ->options([
                        'Zona de Inundacion' => 'Zona de Inundación',
                        'Zona de Aluvion' => 'Zona de Aluvión'
                    ])
                    ->multiple()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => $this->risks = $state),
            ])->columns(3);
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

    protected function getFooterWidgets(): array
    {
        return [
            'map' => \App\Filament\Resources\DependentUserResource\Widgets\MapWidget::make([
                'conditions_id' => $this->conditions_id,
                'organizations_id' => $this->organizations_id,
                'users_id' => $this->req_users_id,
                'risks' => $this->risks,
            ])

        ];
    }

    public function updated($name)
    {
        $this->dispatch('changeFilters', $this->conditions_id, $this->organizations_id, $this->req_users_id, $this->risks);
    }
}
