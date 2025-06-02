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

class MapDependentUsers extends Page
{


    protected static string $resource = DependentUserResource::class;

    protected static string $view = 'filament.resources.dependent-user-resource.pages.map-dependent-users';

    protected static ?string $title = 'Mapa de Pacientes con Condición';

    public $users = [];

    public $organizationTypes;
    public $conditionTypes;

    public $search;
    public $conditions_id = null;
    public $organization_id = null;
    public $user_id = null;


    // New way to handle filters
    public $req_conditions_id = null;
    public $req_users_id = null;
    public $req_organization_id = null;
    public $req_search = null;



    public function mount(): void
    {
        $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        $this->organizationTypes = Organization::whereHas('contactPoint', function ($query){
            $query->whereNotNull('id');
        })->with(['contactPoint' => function($query){
            $query->has('user')->whereNotNull('contactPoint.id');
        }, 'contactPoint.user' => function($query){
            $query->has('dependentUser')->whereNotNull('contactPoint.user.id');
        }])->pluck('alias', 'id');

        $this->req_conditions_id = request('conditions_id') ?? null;
        // $this->req_users_id = request('users_id') ?? null;
        $this->req_organization_id = request('organization_id') ?? null;
        // $this->req_search = request('search') ?? null;

        $this->form->fill([
            'conditions_id' => $this->req_conditions_id,
            'organization_id' => $this->req_organization_id,
            // 'users_id' => $this->req_users_id,
            // 'search' => $this->req_search,
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
                    ->required()
                    ->multiple()
                    ->reactive() // Hacer que el select sea reactivo
                    ->afterStateUpdated(fn ($state) => $this->req_conditions_id =$state), // Llamar a un método cuando se actualice
                Forms\Components\Select::make('organization_id')
                    ->label('Organización')
                    ->options($this->organizationTypes)
                    ->placeholder('Seleccione una organización')
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->req_organization_id = $state),
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

    protected function getFooterWidgets(): array
    {
        return [
            'map' => \App\Filament\Resources\DependentUserResource\Widgets\MapWidget::make([
                'conditions_id' => $this->req_conditions_id,
                'organization_id' => $this->req_organization_id,
                'user_id' => $this->user_id,
            ])

        ];
    }

    public function updated($property = null)
    {                
        $this->dispatch('changeFilters', $this->req_conditions_id, $this->user_id);
    }
}
