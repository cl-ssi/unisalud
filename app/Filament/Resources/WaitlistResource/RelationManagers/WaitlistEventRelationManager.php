<?php

namespace App\Filament\Resources\WaitlistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaitlistEventRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'Historial Paciente';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->placeholder('Selecciona una opción')
                    ->options(function () {
                        // Obtener el último contacto relacionado con el registro de la lista de espera
                        $lastContact = $this->ownerRecord->contacts()->latest('contacted_at')->first();
                
                        // Definir opciones de estado
                        $options = [
                            'no derivado'   => 'No Derivado',
                            'derivado'      => 'Derivado',
                            'citado'        => 'Citado',
                            'atendido'      => 'Atendido',
                            'inasistente'   => 'Inasistente',
                            'incontactable' => 'Incontactable',
                            'egresado'      => 'Egresado',
                        ];
                
                        // Verificar si el último contacto tiene estado "sí"
                        if ($lastContact && $lastContact->status === 'si') {
                            // Eliminar la opción "incontactable" si el último contacto es "sí"
                            unset($options['incontactable']);
                        }

                        // Obtener el último evento relacionado con el registro de la lista de espera
                        $lastEvent = $this->ownerRecord->events()->latest('registered_at')->first();
                         // Si hay un último evento registrado, inhabilitar la opción de estado igual al último evento
                        if ($lastEvent) {
                            unset($options[$lastEvent->status]);
                        }
                
                        return $options;
                    })
                ->required()
                ->reactive(),

                Forms\Components\DateTimePicker::make('registered_at')
                    ->label('Fecha/Hora de Registro')         
                    ->required(), // Formato de la fecha (opcional)

                Forms\Components\Textarea::make('text')
                    ->label('Observación') 
                    ->columnSpan('full') 
                    ->required(),
                
                Forms\Components\Select::make('discharge')
                    ->label('Causal Egreso')
                    ->placeholder('Selecciona una opción')
                    ->options(fn ($get) => $get('status') === 'egresado'
                        ? [
                            'atencion realizada'         => 'Atención Realizada',
                            'rechazo'                   => 'Rechazo',
                            'inasistencia'              => 'Inasistencia',
                            'fallecimiento'             => 'Fallecimiento',
                            'contacto no corresponde'   => 'Contacto no Corresponde',
                            'atencion otorgada en extrasistema' => 'Atencion Otorgada en Extrasistema',
                        ]
                        : []
                    )
                    ->visible(fn ($get) => $get('status') === 'egresado') // Mostrar solo si el estado es 'egresado'
                    ->required(fn ($get) => $get('status') === 'egresado')
                    ->reactive(),

                Forms\Components\DateTimePicker::make('appointment_datetime')
                    ->label('Fecha/Hora de Citación')         
                    ->required(), // Formato de la fecha (opcional)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'no derivado',
                        'success'   => 'derivado',
                        'success'   => 'citado',
                        'success'   => 'atendido',
                        'danger'    => 'inasistente',
                        'danger'    => 'incontactable',
                        'success'   => 'egresado',
                    ])
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state); // Formatear el estado para que se muestre con la primera letra en mayúscula
                    })
                    ->sortable(), // Si quieres que sea ordenable
                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Fecha Registro')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)->format('d-m-Y');
                    }),
                Tables\Columns\TextColumn::make('text')
                    ->label('Observaciones')
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Registrado Por'),
                Tables\Columns\BadgeColumn::make('discharge')
                    ->label('Causal Egreso')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state); // Formatear el estado para que se muestre con la primera letra en mayúscula
                    })
                    ->sortable(), // Si quieres que sea ordenable
                Tables\Columns\TextColumn::make('appointment_at')
                    ->label('Fecha/Hora Citación')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)->format('d-m-Y');
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                /*
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Evento')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Crear Nuevo Evento'),
                */
                Tables\Actions\CreateAction::make('add_event')
                    ->label('Nuevo Evento')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Crear Nuevo Evento')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Aquí puedes agregar o modificar los datos antes de crear el registro
                        $data['register_user_id'] = auth()->user()->id; // Guardar el ID del usuario autenticado
                        // $data['organization_user_id'] = 123; // Otro dato que no proviene del formulario
                        
                        return $data;
                    })
                    ->after(function (array $data, RelationManager $livewire): void {
                        if ($data['status'] === 'egresado') {
                             // Obtener la instancia de Waitlist a través de $livewire
                            $waitlist = $livewire->ownerRecord;

                            // Verificar que exista el registro antes de actualizar
                            if ($waitlist) {
                                $waitlist->update([
                                    'status' => 'egresado',
                                ]);
                            }
                        }
                        if ($data['status'] === 'citado') {
                            // Obtener la instancia de Waitlist a través de $livewire
                           $waitlist = $livewire->ownerRecord;

                           // Verificar que exista el registro antes de actualizar
                           if ($waitlist) {
                               $waitlist->update([
                                   'status' => 'citado',
                               ]);
                           }
                       }
                    })
                    /*
                    ->disabled(fn() => $this->ownerRecord->status === 'incontactable'
                        ->where('type', 'visita domiciliaria')
                        ->where('status', 'si')
                        ->exists()) // Deshabilitar si el estado es "incontactable" y no existe un contacto válido
                    ->disabled(fn() => $this->ownerRecord->status === 'egresado'), // Deshabilitar si el estado es "egresado"
                    */
                    ->disabled(function () {
                        $waitlist = $this->ownerRecord;
                
                        // Condición 1: Estado es "incontactable" y no existe un contacto válido
                        if ($waitlist->status === 'incontactable') {
                            $hasValidContact = $waitlist->contacts()
                                ->where('type', 'visita domiciliaria')
                                ->where('status', 'si')
                                ->exists();
                
                            if (!$hasValidContact) {
                                return true; // Deshabilitar si no hay contacto válido
                            }
                        }
                
                        // Condición 2: Estado es "egresado"
                        if ($waitlist->status === 'egresado') {
                            return true; // Deshabilitar si el estado es "egresado"
                        }
                
                        return false; // Permitir el botón en otros casos
                    }),
                // Agregar acción para el botón "Historial"
                Tables\Actions\Action::make('view_history')
                    ->label('Historial')
                    ->icon('heroicon-o-calendar')
                    ->color('success') // Color verde para el botón
                    ->modalHeading('Historial de Eventos') // Título del modal
                    ->modalContent(function ($record) {
                        // Crear una línea de tiempo con los eventos del registro
                        $events = $this->ownerRecord->events()->orderBy('registered_at', 'desc')->get(); // Obtener todos los eventos ordenados

                        return view('filament.components.event-timeline', ['events' => $events]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTabLabel(): ?string
    {
        return 'Eventos del Paciente'; // Personaliza el título del tab
    }
}
