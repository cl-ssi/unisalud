<?php

namespace App\Filament\Resources;

use App\Filament\Imports\OdontologyWaitlistImporter;
use App\Filament\Exports\OdontologyWaitlistExporter;
use App\Filament\Resources\OdontologyWaitlistResource\Pages;
use App\Filament\Resources\OdontologyWaitlistResource\RelationManagers;
use App\Models\OdontologyWaitlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\Sex;
use Filament\Actions\Action;


class OdontologyWaitlistResource extends Resource
{
    protected static ?string $model = OdontologyWaitlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Lista De Espera'; // Grupo de navegación

    protected static ?string $navigationLabel = 'Odontología';

    protected static ?string $label = 'Lista de Espera Odontología';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('run')
                    ->label('RUN')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user && $record->user->officialIdentifier) {
                            $component->state($record->user->officialIdentifier->value);
                        }
                    }),
                Forms\Components\TextInput::make('dv')
                    ->label('DV')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user && $record->user->officialIdentifier) {
                            $component->state($record->user->officialIdentifier->dv);
                        }
                    }),
                Forms\Components\TextInput::make('user_text')
                    ->label('Usuario')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user) {
                            $component->state($record->user->text);
                        }
                    }),
                Forms\Components\TextInput::make('birthday')
                    ->label('Fecha de Nacimiento')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user && $record->user->birthday) {
                            $component->state($record->user->birthday->format('d-m-Y'));
                        }
                    }),
                Forms\Components\TextInput::make('age')
                    ->label('Edad')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user && $record->user->birthday) {
                            $birthday = \Carbon\Carbon::parse($record->user->birthday);
                            $age = $birthday->age;
                            $component->state($age);
                        }
                    }),
                Forms\Components\TextInput::make('sex')
                    ->label('Sexo')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user->sex->getLabel() && $record->user->sex->getLabel()) {
                            $component->state($record->user->sex->getLabel());
                        }
                    }),
                Forms\Components\TextInput::make('healthcare_type')
                    ->label('Previsión')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->healthcareType?->text ?? null);
                    }),
                Forms\Components\TextInput::make('entryType')
                    ->label('Tipo Prestación')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->entryType?->text ?? null);
                    }),
                Forms\Components\TextInput::make('minsalSpecialty')
                    ->label('Prestación MINSAL')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->minsalSpecialty?->text ?? null);
                    }),
                Forms\Components\TextInput::make('plano')
                    ->label('Plano')
                    ->disabled(),
                Forms\Components\TextInput::make('extremity')
                    ->label('Extremidad')
                    ->disabled(),
                Forms\Components\TextInput::make('establishmentHealthCareService')
                    ->label('Prestación Establecimiento')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->establishmentHealthCareService?->text ?? null);
                    }),
                Forms\Components\DatePicker::make('entry_date')
                    ->label('Fecha Entrada')
                    ->disabled(),
                Forms\Components\TextInput::make('origin_establishment')
                    ->label('Establecimiento Origen')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->originEstablishment->alias ?? null);
                    }),
                Forms\Components\TextInput::make('destiny_establishment')
                    ->label('Establecimiento Destino')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->destinyEstablishment->alias ?? null);
                    }),
                Forms\Components\DatePicker::make('exit_date')
                    ->label('Fecha Salida')
                    ->disabled(),
                Forms\Components\TextInput::make('referring_specialty')
                    ->label('E OTOR AT')
                    ->disabled(),
                Forms\Components\TextInput::make('exit_code')
                    ->label('Codigo Salida')
                    ->disabled(),
                Forms\Components\TextInput::make('minsalExitSpecialty')
                    ->label('Prestación MINSAL Salida')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->minsalExitSpecialty?->text ?? null);
                    }),
                Forms\Components\TextInput::make('prais')
                    ->label('PRAIS')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state) {
                        if ($state == 1) {
                            $component->state('Si');
                        } elseif ($state == 2) {
                            $component->state('No');
                        } else {
                            $component->state(null);
                        }
                    }),
                Forms\Components\TextInput::make('region_id')
                    ->label('Región')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->region->name ?? null);
                    }),
                Forms\Components\TextInput::make('commune_id')
                    ->label('Comuna')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->commune->name ?? null);
                    }),
                Forms\Components\TextInput::make('suspected_diagnosis')
                    ->label('Sospecha Diagnostico')
                    ->disabled(),
                Forms\Components\TextInput::make('confirmed_diagnosis')
                    ->label('Confirmación Diagnostico')
                    ->disabled(),

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
                    }),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->user && $record->user->contactPointEmail) {
                            $component->state($record->user->contactPointEmail->value);
                        }
                    }),
                Forms\Components\TextInput::make('local_id')
                    ->label('ID Local')
                    ->disabled(),
                Forms\Components\TextInput::make('result')
                    ->label('Resultado')
                    ->disabled(),
                Forms\Components\TextInput::make('sigte_id')
                    ->label('ID SIGTE')
                    ->disabled(),
                Forms\Components\TextInput::make('waitlistAge')
                    ->label('Edad')
                    ->disabled(),
                Forms\Components\TextInput::make('waitlistYear')
                    ->label('Año')
                    ->disabled(),

                Forms\Components\TextInput::make('specialty_id')
                    ->label('Especialidad')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->waitlistSpecialty->text ?? null);
                    }),
                Forms\Components\TextInput::make('establishment_id')
                    ->label('Establecimiento')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->establishment->alias ?? null);
                    }),
                Forms\Components\TextInput::make('pediatric')
                    ->label('Pediatrico')
                    ->disabled(),
                Forms\Components\TextInput::make('lb')
                    ->label('LB')
                    ->disabled(),

                Forms\Components\DatePicker::make('appointment_date')
                    ->label('Fecha Citación')
                    ->disabled(),
                Forms\Components\TextInput::make('requesting_professional_id')
                    ->label('RUN Profesional Solicitante')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->requestingProfessional && $record->requestingProfessional->officialIdentifier) {
                            $identifier = $record->requestingProfessional->officialIdentifier;
                            $component->state("{$identifier->value}-{$identifier->dv}");
                        }
                    }),
                Forms\Components\TextInput::make('resolving_professional_id')
                    ->label('RUN Profesional Resolutor')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->resolvingProfessional && $record->resolvingProfessional->officialIdentifier) {
                            $identifier = $record->resolvingProfessional->officialIdentifier;
                            $component->state("{$identifier->value}-{$identifier->dv}");
                        }
                    }),
                Forms\Components\TextInput::make('wait_medical_benefit_id')
                    ->label('Tipo de Prestación')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->medicalBenefit->text ?? null);
                    }),

                Forms\Components\TextInput::make('health_service_id')
                    ->label('Servicio de Salud')
                    ->disabled(),

                Forms\Components\DatePicker::make('appointment_date')
                    ->label('Fecha Citación')
                    ->disabled(),

                Forms\Components\TextInput::make('worker')
                    ->label('Funcionario')
                    ->disabled(),

                Forms\Components\TextInput::make('iqType')
                    ->label('Tipo de IQ')
                    ->disabled(),

                Forms\Components\TextInput::make('oncologic')
                    ->label('Oncológico')
                    ->disabled(),

                Forms\Components\TextInput::make('origin_commune_id')
                    ->label('Comuna de Origen')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->originCommune->name ?? null);
                    }),

                Forms\Components\TextInput::make('fonasa')
                    ->label('Fonasa')
                    ->disabled(),

                Forms\Components\TextInput::make('praisUser')
                    ->label('Usuario PRAIS')
                    ->disabled(),

                Forms\Components\TextInput::make('lbPrais')
                    ->label('LB PRAIS')
                    ->disabled(),

                Forms\Components\TextInput::make('lbUrinary')
                    ->label('LB Incontinencia Urinaria')
                    ->disabled(),

                Forms\Components\TextInput::make('exitError')
                    ->label('Error Egreso')
                    ->disabled(),

                Forms\Components\TextInput::make('lbIqOdonto')
                    ->label('LB IQ Odonto')
                    ->disabled(),

                Forms\Components\TextInput::make('procedureType')
                    ->label('Tipo de Procedimiento')
                    ->disabled(),

                Forms\Components\TextInput::make('sename')
                    ->label('SENAME')
                    ->disabled(),
                Forms\Components\TextInput::make('elapsed_days')
                    ->label('Días Pasados')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(OdontologyWaitlistImporter::class)
                    ->label('Importar Lista de Espera')
                    ->modalHeading('Importar Lista de Espera')
                    ->modalSubmitActionLabel('Importar'),
                Tables\Actions\ExportAction::make()
                    ->exporter(OdontologyWaitlistExporter::class)
                    ->label('Exportar')
                    ->columnMapping(false),
                Tables\Actions\Action::make('stats')
                    ->label('Estadísticas')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn() => OdontologyWaitlistResource::getUrl('stats'))
                    ->openUrlInNewTab(),
            ])
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->default('pendiente')
                    // NO DERIVADO - DERIVADO - CITADO - ATENDIDO - INASISTENTE - INCONTACTABLE - EGRESADO
                    ->colors([
                        'success'   => 'primer llamado',
                        'primary'   => 'segundo llamado',
                        'gray'      => 'tercer llamado',
                        'info'      => 'en visita domiciliaria',
                        'danger'    => 'citado',
                    ])
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
                    })
                    ->sortable(),
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
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Fecha Entrada')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)->format('d-m-Y');
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('originEstablishment.alias')
                    ->label('Establecimiento Origen')
                    ->sortable(),
                Tables\Columns\TextColumn::make('destinyEstablishment.alias')
                    ->label('Establecimiento Destino')
                    ->sortable(),
                Tables\Columns\TextColumn::make('suspected_diagnosis')
                    ->label('Sospecha Diagnostico')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sigte_id')
                    ->label('ID SIGTE')
                    ->searchable(),
                Tables\Columns\TextColumn::make('waitlistSpecialty.text')
                    ->label('Especialidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('establishment.alias')
                    ->label('Establecimiento')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('waitlistSpecialty')
                    ->label('Especialidad')
                    ->relationship('waitlistSpecialty', 'text')
                    ->searchable()
                    ->placeholder('Todas'),
                Tables\Filters\SelectFilter::make('originEstablishment')
                    ->label('Establecimiento Origen')
                    ->relationship('originEstablishment', 'alias')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas'),
                Tables\Filters\SelectFilter::make('destinyEstablishment')
                    ->label('Establecimiento Destino')
                    ->relationship('destinyEstablishment', 'alias')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'primer llamado'         => 'Primer Llamado',
                        'segundo llamado'        => 'Segundo Llamado',
                        'tercer llamado'         => 'Tercer Llamado',
                        'en visita domiciliaria' => 'En Visita Domiciliaria',
                        'citado'                 => 'Citado',

                        'atendido praps'         => 'Atendido PRAPS',
                        'atendido hetg'          => 'Atendido HETG',
                        'atendido hah'           => 'Atendido HAH',
                        'fallecido'              => 'Fallecido',
                    ])
                    ->placeholder('Todos')
                    ->searchable()
            ],  layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WaitlistEventRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOdontologyWaitlists::route('/'),
            'create' => Pages\CreateOdontologyWaitlist::route('/create'),
            'edit' => Pages\EditOdontologyWaitlist::route('/{record}/edit'),
            'stats'  => Pages\OdontologyStats::route('/stats'),
        ];
    }
}
