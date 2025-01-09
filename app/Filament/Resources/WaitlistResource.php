<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaitlistResource\Pages;
use App\Filament\Resources\WaitlistResource\RelationManagers;
use App\Models\Waitlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\IconColumn;

class WaitlistResource extends Resource
{
    protected static ?string $model = Waitlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Lista De Espera'; // Grupo de navegación

    protected static ?string $navigationLabel = 'Listado de Pacientes';

    protected static ?string $label = 'Lista de Espera de Pacientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Relacion con OfficialIdentifier
                
                Forms\Components\TextInput::make('run')
                    ->label('RUN')
                    ->disabled() // Deshabilita el campo
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Cargar el valor de RUN desde la relación `officialIdentifier`
                        if ($record && $record->user && $record->user->officialIdentifier) {
                            $component->state($record->user->officialIdentifier->value);
                        }
                    }),
                    
                Forms\Components\TextInput::make('dv')
                    ->label('DV')
                    ->disabled() // Deshabilita el campo
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Cargar el valor de DV desde la relación `officialIdentifier`
                        if ($record && $record->user && $record->user->officialIdentifier) {
                            $component->state($record->user->officialIdentifier->dv);
                        }
                    }),

                Forms\Components\TextInput::make('user_text')
                ->label('Usuario')
                ->disabled() // Deshabilita el campo
                ->afterStateHydrated(function ($component, $state, $record) {
                    // Cargar el valor de 'text' desde la relación 'user'
                    if ($record && $record->user) {
                        $component->state($record->user->text);
                    }
                }),

                Forms\Components\DatePicker::make('birthday')
                    ->label('Fecha de Nacimiento')
                    ->disabled() // Deshabilitar si no quieres que se pueda editar
                    ->format('d-m-Y') // Formato de la fecha (opcional)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Cargar la fecha de nacimiento desde la relación 'user'
                        if ($record && $record->user) {
                            $component->state($record->user->birthday);
                        }
                    }),

                Forms\Components\TextInput::make('age')
                    ->label('Edad')
                    ->disabled() // No editable
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user && $record->user->birthday) {
                            $birthday = \Carbon\Carbon::parse($record->user->birthday);
                            $age = $birthday->age; // Calcula la edad a partir de la fecha de nacimiento
                            $component->state($age); // Asigna la edad calculada al campo
                        }
                    }),

                Forms\Components\Select::make('wait_health_care_service_id')
                    ->label('Lista de Espera')
                    ->options(\App\Models\HealthCareService::all()->pluck('text', 'id')) // Mostrar las primeras 10 opciones
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('sigte_id')
                    ->label('ID SIGTE'),

                Forms\Components\Select::make('cie10_id')
                    ->label('CIE10')
                    ->options(\App\Models\Cie10::all()->mapWithKeys(function ($cie10) {
                        return [$cie10->id => "{$cie10->code} - {$cie10->name}"];
                    })) // Concatenar code y name
                    ->searchable() // Permite la búsqueda
                    ->required(), // Si es un campo obligatorio
                
                Forms\Components\Select::make('wait_medical_benefit_id')
                    ->label('Tipo de Prestación')
                    ->options(\App\Models\WaitlistMedicalBenefit::all()->pluck('text', 'id')) // Mostrar todas las opciones de WaitlistMedicalBenefit
                    ->searchable() // Permitir la búsqueda
                    ->required(), // Si es un campo obligatorio
                
                Forms\Components\Select::make('wait_specialty_id')
                    ->label('Especialidad')
                    ->options(\App\Models\WaitlistSpecialty::all()->pluck('text', 'id')) // Mostrar todas las opciones de WaitlistMedicalBenefit
                    ->searchable() // Permitir la búsqueda
                    ->required(), // Si es un campo obligatorio

                Forms\Components\Select::make('commune_id')
                    ->label('Comuna Origen')
                    ->relationship('commune', 'name')
                    ->required(),

                Forms\Components\Select::make('organization_id')
                    ->label('Establecimiento')
                    ->relationship('organization', 'alias')
                    ->required(),

                Forms\Components\TextInput::make('full_address')
                    ->label('Dirección Completa')
                    ->disabled() // Deshabilitar si no se debe editar
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Concatenar `address.text`, `address.line`, y `address.suburb` desde la relación `user.address`
                        if ($record && $record->user && $record->user->address) {
                            $addressText = optional($record->user->address)->text;
                            $addressLine = optional($record->user->address)->line;
                            $addressSuburb = optional($record->user->address)->suburb;
                
                            // Concatenar los valores en una dirección completa
                            $fullAddress = $addressText . ' ' . $addressLine . ' ' . $addressSuburb;
                            $component->state($fullAddress); // Asignar la dirección concatenada al campo
                        }
                    }),
                Forms\Components\TextInput::make('home_phone')
                    ->label('Teléfono Fijo')
                    ->tel()
                    ->disabled() // Deshabilitar si no se debe editar
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Cargar el valor del teléfono fijo desde la relación `homeContactPoint`
                        if ($record && $record->user && $record->user->homeContactPoint) {
                            $component->state($record->user->homeContactPoint->value);
                        }
                    }),
                Forms\Components\TextInput::make('mobile_phone')
                    ->label('Teléfono Móvil')
                    ->tel()
                    ->disabled() // Deshabilitar si no se debe editar
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Cargar el valor del teléfono móvil desde la relación `mobileContactPoint`
                        if ($record && $record->user && $record->user->mobileContactPoint) {
                            $component->state($record->user->mobileContactPoint->value);
                        }
                    })
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('latest_contact_status')
                    ->label('Contactado')
                    ->getStateUsing(function ($record) {
                        // Obtener el último contacto asociado, ordenado por la fecha de contacto
                        $lastContact = $record->contacts()->latest('id')->first();
                        // Retornar el estado del último contacto, o null si no hay contacto
                        return $lastContact ? $lastContact->status : null;
                    })
                    ->icon(function ($state) {
                        if (in_array($state, ['si'])) {
                            return 'heroicon-o-check-circle'; // Ícono de check para estos valores
                        }
                        if (in_array($state, ['no'])) {
                            return 'heroicon-o-x-circle'; // Ícono de "x" para estos valores
                        }
                        return 'heroicon-o-clock'; // Ícono de reloj si no hay estado o es otro valor
                    })
                    ->color(function ($state) {
                        if (in_array($state, ['si'])) {
                            return 'success'; // Verde si el estado es "si"
                        }
                        if (in_array($state, ['no'])) {
                            return 'danger'; // Rojo si el estado es "no primera" o "no segunda"
                        }
                        return 'gray'; // Gris si no hay contacto
                    })
                    ->default('heroicon-o-clock'), // Asegurar que el ícono del reloj sea el valor predeterminado
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->default('pendiente')
                    // NO DERIVADO - DERIVADO - CITADO - ATENDIDO - INASISTENTE - INCONTACTABLE - EGRESADO
                    ->colors([
                        'success'   => 'atendido',
                        'primary'   => 'citado',
                        'gray'      => 'derivado',
                        'info'      => 'egresado',
                        'danger'    => 'inasistente', 
                        'warning'   => 'incontactable',
                    ])
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state); // Formatear el estado para que se muestre con la primera letra en mayúscula
                    })
                    ->sortable(), // Si quieres que sea ordenable
                Tables\Columns\BadgeColumn::make('is_deceased')
                    ->label('Causal')
                    ->getStateUsing(function ($record) {
                        // Verificar si el paciente está en estado "egresado"
                        if ($record->status === 'egresado') {
                            // Obtener el último evento registrado
                            $lastEvent = $record->events()->latest('registered_at')->first();
                            // Verificar si el último evento tiene la causa "fallecimiento"
                            if ($lastEvent && $lastEvent->discharge === 'fallecimiento') {
                                return 'Fallecido';
                            }
                            if ($lastEvent && $lastEvent->discharge === 'atencion realizada') {
                                return 'Atención Realizada';
                            }
                            return 'Egresado';
                        }
                        else{
                            return null;
                        }
                    })
                    ->colors([
                        'danger' => 'Fallecido',   // Rojo para Fallecido
                        'success' => 'Atención Realizada',    // Verde para Activo
                        'secondary' => 'Egresado' // Gris para Egresado
                    ])
                    ->sortable(), // Permitir ordenamiento por esta columna
                Tables\Columns\TextColumn::make('user.officialIdentifier.value')
                    ->label('RUN')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.officialIdentifier.dv')
                    ->label('DV'),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.birthday')
                    ->label('Fecha Nacimiento')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)->format('d-m-Y');
                    }),
                Tables\Columns\TextColumn::make('age')
                    ->label('Edad')
                    ->getStateUsing(function ($record) {
                        // Accede a la fecha de nacimiento y calcula la edad
                        $birthday = ($record->user) ? $record->user->birthday : null;
                        return $birthday ? \Carbon\Carbon::parse($birthday)->age : '-';
                    }),
                
                Tables\Columns\TextColumn::make('healthCareService.text')
                    ->label('Lista de Espera')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cie10')
                    ->label('CIE10')
                    ->getStateUsing(function ($record) {
                        if ($record->cie10) {
                            return "{$record->cie10->code} - {$record->cie10->name}";
                        }
                        return '-'; // En caso de que no tenga relación
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('sigte_id')
                    ->label('ID SIGTE'),

                Tables\Columns\TextColumn::make('medicalBenefit.text')
                    ->label('Tipo Prestación')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('waitlistSpecialty.text')
                    ->label('Especialidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commune.name')
                    ->label('Comuna Origen')
                    ->sortable(),
                Tables\Columns\TextColumn::make('organization.alias')
                    ->label('Establecimiento')
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Dirección Completa')
                    ->getStateUsing(function ($record) {
                        // Concatenar `address.text` y `address.line`
                        $addressText = optional($record->user->address)->text;
                        $addressLine = optional($record->user->address)->line;
                        $addressSuburb = optional($record->user->address)->suburb;
    
                        return $addressText . ' ' . $addressLine. ' ' . $addressSuburb;
                    }),
                Tables\Columns\TextColumn::make('user.homeContactPoint.value')
                    ->label('Teléfono Fijo'),
                Tables\Columns\TextColumn::make('user.mobileContactPoint.value')
                    ->label('Teléfono Móvil'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contact_status')
                ->label('Estado del Último Contacto')
                ->options([
                    'si' => 'Contactado - Sí',
                    'no' => 'Contactado - No',
                ])
                ->default(null)  // No seleccionar nada por defecto, mostrar todos los registros
                ->placeholder('Todos')  // Opción inicial para seleccionar el filtro
                ->query(function (Builder $query, array $data) {
                    if (empty($data['value'])) {
                        // Si no hay ningún filtro seleccionado, mostrar todos los registros
                        return $query;
                    }

                    if ($data['value'] === 'sin contacto') {
                        // Filtro para registros sin ningún contacto
                        return $query->whereDoesntHave('contacts');
                    }

                    // Filtro para registros con el último estado de contacto específico
                    return $query->whereHas('contacts', function ($q) use ($data) {
                        // Obtener el último contacto y verificar su estado
                        $q->orderBy('contacted_at', 'desc')
                          ->where('status', $data['value'])
                          ->limit(1);
                    });
                }),
                Tables\Filters\SelectFilter::make('health_care_service')
                    ->label('Lista de Espera')
                    ->relationship('healthCareService', 'text')
                    ->placeholder('Seleccionar'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'no derivado'       => 'No derivado',
                        'derivado'          => 'Derivado',
                        'citado'            => 'Citado',
                        'atendido'          => 'Atendido',
                        'inasistente'       => 'Inasistente',
                        'incontactable'     => 'Incontactable',
                        'egresado'          => 'Egresado',
                    ])
                    ->placeholder('Seleccionar'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('reporte')
                    ->label('Reporte')
                    ->color('primary')
                    ->icon('heroicon-o-chart-bar')
                    ->action('exportReport') // Llama al método `exportReport` en la página
                    ->requiresConfirmation() // Opcional, para mostrar una confirmación
                    ->modalHeading('Generar Reporte')
                    ->modalButton('Descargar')
                    ->modalDescription('¿Estás seguro de que deseas generar este reporte?'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // Acción personalizada para ver el UserResource
                /*
                Tables\Actions\Action::make('view_user')
                    ->label('Ver Usuario')
                    ->url(fn($record) => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->icon('heroicon-o-user')
                    ->openUrlInNewTab(), // Opcional, abre el recurso en una nueva pestaña
                
                Tables\Actions\Action::make('estado')
                    ->label('Estado')
                    ->modal('waitlist-status-modal') // Abre el modal especificado
                    ->form(function ($record) {
                        return [
                            Forms\Components\Select::make('status')
                                ->label('Estado')
                                ->placeholder('Selecciona una opción')
                                ->options([
                                    'no derivado'       => 'No derivado',
                                    'derivado'          => 'Derivado',
                                    'citado'            => 'Citado',
                                    'atendido'          => 'Atendido',
                                    'inasistente'       => 'Inasistente',
                                    'incontactable'     => 'Incontactable',
                                    'egresado'          => 'Egresado',
                                ])
                                ->required()
                                ->reactive(), // Escuchar cambios para hacer que el select "discharge" sea visible
    
                            Forms\Components\Select::make('discharge')
                                ->label('Causal de Egreso')
                                ->placeholder('Selecciona una opción')
                                ->options([
                                    'atencion realizada'                => 'Atención Realizada',
                                    'rechazo'                           => 'Rechazo',
                                    'inasistencia'                      => 'Inasistencia',
                                    'fallecimiento'                     => 'Fallecimiento',
                                    'contacto no corresponde'           => 'Contacto no corresponde',
                                    'atencion otorgada en extrasistema' => 'Atencion otorgada en extrasistema',
                                ])
                                ->required(function ($get) {
                                    // Hacer que el campo sea requerido solo si el status es EGRESADO
                                    return $get('status') === 'egresado';
                                })
                                ->visible(function ($get) {
                                    // Mostrar el campo discharge solo si el status es EGRESADO
                                    return $get('status') === 'egresado';
                                }),
    
                            Forms\Components\DatePicker::make('appointment_datetime')
                                ->label('Fecha de Cita')
                                ->required(),
                        ];
                    })
                    ->action(function ($record, $data) {
                        // Guardar los cambios en los campos del modal
                        $record->update([
                            'status' => $data['status'],
                            'discharge' => $data['discharge'] ?? null, // Solo actualizar si hay valor en discharge
                            'appointment_datetime' => $data['appointment_datetime'],
                        ]);
                    }),
                */
                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([50]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContactsRelationManager::class,
            RelationManagers\WaitlistEventRelationManager::class,
            RelationManagers\FileRelationManager::class,
            RelationManagers\WaitlistMessageRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaitlists::route('/'),
            'create' => Pages\CreateWaitlist::route('/create'),
            'edit' => Pages\EditWaitlist::route('/{record}/edit'),
        ];
    }

    protected static function getTableQuery(): Builder
    {
        $userOrganizations = auth()->user()->organizations;

        // Si no hay organizaciones vinculadas, devuelve una consulta vacía
        if ($userOrganizations->isEmpty()) {
            return Waitlist::query()->whereRaw('1 = 0'); // Condición que nunca se cumple
        }

        // Si el usuario tiene organizaciones, retorna la consulta base sin filtros adicionales
        return Waitlist::query();
    }
}
