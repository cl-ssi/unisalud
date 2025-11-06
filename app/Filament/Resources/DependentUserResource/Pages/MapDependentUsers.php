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
use Filament\Forms\Get;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;

class MapDependentUsers extends Page
{

    protected static string $resource = DependentUserResource::class;

    protected static string $view = 'filament.resources.dependent-user-resource.pages.map-dependent-users';

    protected static ?string $title = 'Mapa de Pacientes con Condición';

    public $organizationTypes;
    public $conditionTypes;

    // public $data = [];


    // public $conditions_id = null;
    public $conditions_multiple = null;
    public $tipo = null;
    public $conditions = null;
    public $req_users_id = null;
    public $organizations_id = null;
    public $search = null;
    public $risks = null;



    public function mount(): void
    {
        // dd($this->getRecord()->children()->get());
        $this->conditionTypes = Condition::pluck('name', 'id')->toArray();
        $this->organizationTypes = Organization::whereHas('contactPoint', fn($query) => $query->whereNotNull('id'))
            ->with([
                'contactPoint' => fn($query) => $query->has('user')->whereNotNull('contactPoint.id'),
                'contactPoint.user' => fn($query) => $query->has('dependentUser')->whereNotNull('contactPoint.user.id')
            ])
            ->pluck('alias', 'id');

        //Obtener datos de solicitud con filtros y/o seleccion
        $conditions_multiple = request('conditions_multiple') ?? null;
        $this->tipo = $conditions_multiple['tipo'] ?? null;
        $this->conditions = $conditions_multiple['conditions'] ?? null;
        $this->req_users_id = request('users_id') ?? null;
        $this->organizations_id = request('organizations_id') ?? null;
        $this->risks = request('risks') ?? null;
        $this->form->fill([
            'tipo' => $this->tipo,
            'conditions' => $this->conditions,
            'organizations_id' => $this->organizations_id,
            'users_id' => $this->req_users_id,
            'risks' => $this->risks,
        ]);
    }



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('conditions_multiple')
                    ->label('')
                    ->schema([
                        Forms\Components\ToggleButtons::make('tipo')
                            ->boolean()
                            ->label('')
                            ->inline()
                            ->reactive()
                            ->options([
                                'u' => 'Unión',
                                'v' => 'Disyunción',
                            ])
                            ->grouped()
                            ->colors([
                                'false' => 'gray',
                                'true' => 'primary',
                            ])
                            ->live()
                            ->afterStateUpdated(fn($state) => $this->tipo = $state),
                        Forms\Components\Select::make('conditions')
                            // ->relationship('conditions', 'name')
                            ->placeholder('Seleccionar')
                            ->multiple()
                            ->reactive()
                            // ->live()
                            ->label('')
                            ->preload()
                            ->options(fn(Condition $query) => $query->orderByRaw('COALESCE(condition.parent_id, condition.id), condition.parent_id IS NOT NULL, condition.id')->pluck('name', 'id'))
                            // ->hidden(fn(Get $get) => $conditions_multiple['tipo'] ? false : true)                            
                            // ->getOptionLabelFromRecordUsing(fn(Model $record) => is_null($record->parent_id) ? Str::ucwords($record->name) : "——" . Str::ucwords($record->name))
                            ->afterStateUpdated(fn($state) => $this->conditions = $state),
                    ])
                    ->columnSpan(1)
                    ->reactive(),
                Forms\Components\Select::make('risks')
                    ->label('Riesgos')
                    ->options([
                        'Zona de Inundacion' => 'Zona de Inundación',
                        'Zona de Aluvion' => 'Zona de Aluvión'
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

        return [
            /*          
            Actions\Action::make('Volver')
                ->url(DependentUserResource::getUrl(
                    'index',
                    [
                        'conditions_id' => $this->conditions_id,
                        'risks' => $this->risks,
                        'organizations_id' => $this->organizations_id,
                    ]
                ))
                ->button()
                ->color('info'), 
            */];
    }

    protected function getFooterWidgets(): array
    {
        return [
            'map' => \App\Filament\Resources\DependentUserResource\Widgets\MapWidget::make([
                'tipo' => $this->tipo,
                'conditions' => $this->conditions,
                'organizations_id' => $this->organizations_id,
                'users_id' => $this->req_users_id,
                'risks' => $this->risks,
            ])
        ];
    }

    public function updated($name)
    {
        $this->dispatch('changeFilters', $this->tipo, $this->conditions, $this->organizations_id, $this->req_users_id, $this->risks);
    }

    public function goBack()
    {

        return redirect()->route('filament.admin.resources.dependent-users.index', [
            'tipo' => $this->tipo,
            'conditions' => $this->conditions,
            'organizations_id' => $this->organizations_id,
            'users_id' => $this->req_users_id,
            'risks' => $this->risks,
        ]);
    }
}
