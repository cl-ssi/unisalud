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
    
    public $req_conditions_id = null;
    public $req_users_id = null;
    public $req_organizations_id = null;
    public $req_search = null;



    public function mount(): void
    {
        $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        $this->organizationTypes = Organization::whereHas('contactPoint', fn($query) => $query->whereNotNull('id'))
        ->with([
            'contactPoint' => fn($query) => $query->has('user')->whereNotNull('contactPoint.id'),
            'contactPoint.user' => fn($query) => $query->has('dependentUser')->whereNotNull('contactPoint.user.id')
            ])
        ->pluck('alias', 'id');

        $this->req_conditions_id = request('conditions_id') ?? null;
        $this->req_users_id = request('users_id') ?? null;
        $this->req_organizations_id = request('organizations_id') ?? null;

        $this->form->fill([
            'conditions_id' => $this->req_conditions_id,
            'organizations_id' => $this->req_organizations_id,
            'users_id' => $this->req_users_id,
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
                    ->afterStateUpdated(fn ($state) => $this->req_conditions_id =$state),
                Forms\Components\Select::make('organizations_id')
                    ->label('Organización')
                    ->options($this->organizationTypes)
                    ->placeholder('Seleccione una organización')
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->req_organizations_id = $state),
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
                'organizations_id' => $this->req_organizations_id,
                'users_id' => $this->req_users_id,
            ])

        ];
    }

    public function updated($property = null)
    {                
        $this->dispatch('changeFilters', $this->req_conditions_id, $this->req_organizations_id, $this->req_users_id);
    }
}
