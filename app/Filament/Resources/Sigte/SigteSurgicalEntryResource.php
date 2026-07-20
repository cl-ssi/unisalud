<?php

namespace App\Filament\Resources\Sigte;

use App\Enums\SurgicalComplexity;
use App\Filament\Resources\Sigte\SigteSurgicalEntryResource\Pages;
use App\Models\SigteSurgicalProcedureCode;
use App\Models\SigteSurgicalWaitlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SigteSurgicalEntryResource extends Resource
{
    protected static ?string $model = SigteSurgicalWaitlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'SIGTE';

    protected static ?string $navigationLabel = 'Mis Ingresos';

    protected static ?string $label = 'Ingreso Lista de Espera Quirúrgica';

    protected static ?string $pluralLabel = 'Lista de Espera Quirúrgica';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('SIGTE LE QX: listado')
            || auth()->user()?->can('be god');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificación del Paciente')
                    ->schema([
                        Forms\Components\TextInput::make('run')
                            ->label('RUN')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->user?->officialIdentifier?->value)
                            ),
                        Forms\Components\TextInput::make('dv')
                            ->label('DV')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->user?->officialIdentifier?->dv)
                            ),
                        Forms\Components\TextInput::make('user_text')
                            ->label('Paciente')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->user?->text)
                            ),
                        Forms\Components\TextInput::make('health_service_id')
                            ->label('Servicio de Salud'),
                        Forms\Components\Select::make('healthcare_type_id')
                            ->label('Previsión')
                            ->placeholder('Seleccione previsión')
                            ->relationship('healthcareType', 'text'),
                    ])
                    ->columns(3),

                ...static::getEntryFieldsSchema(),
            ]);
    }

    public static function getEntryFieldsSchema(): array
    {
        return [
            Forms\Components\Section::make('Prestación Quirúrgica')
                ->schema([
                    Forms\Components\Placeholder::make('tipo_prestacion')
                        ->label('Tipo Prestación')
                        ->content('4 – Lista de espera quirúrgica'),
                    Forms\Components\Select::make('complexity')
                        ->label('Complejidad')
                        ->placeholder('Seleccione complejidad')
                        ->options(collect(SurgicalComplexity::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()]))
                        ->live()
                        ->required(),
                    Forms\Components\Select::make('sigte_surgical_procedure_code_id')
                        ->label('Código FONASA')
                        ->placeholder('Seleccione código')
                        ->options(fn (Get $get) => SigteSurgicalProcedureCode::query()
                            ->when($get('complexity'), fn ($q, $c) => $q->where('complexity', $c))
                            ->get()
                            ->mapWithKeys(fn ($code) => [$code->id => "{$code->code} - {$code->text}"]))
                        ->searchable()
                        ->disabled(fn (Get $get) => blank($get('complexity')))
                        ->live()
                        ->required(),
                    Forms\Components\Placeholder::make('procedure_name')
                        ->label('Nombre Prestación')
                        ->content(fn (Get $get) => SigteSurgicalProcedureCode::find($get('sigte_surgical_procedure_code_id'))?->text ?? 'Se completa al seleccionar código')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('plano')
                        ->label('Plano (si corresponde)'),
                    Forms\Components\TextInput::make('extremity')
                        ->label('Extremidad (si corresponde)'),
                    Forms\Components\DatePicker::make('entry_date')
                        ->label('Fecha Entrada LE QX')
                        ->default(now())
                        ->required(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Establecimiento')
                ->schema([
                    Forms\Components\Select::make('origin_establishment_id')
                        ->label('Estab. Origen')
                        ->placeholder('Seleccione establecimiento')
                        ->relationship('originEstablishment', 'alias')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->alias ?: $record->name ?: "#{$record->id}")
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('destiny_establishment_id')
                        ->label('Estab. Destino')
                        ->placeholder('Seleccione establecimiento')
                        ->relationship('destinyEstablishment', 'alias')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->alias ?: $record->name ?: "#{$record->id}")
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('referring_specialty')
                        ->label('E. Otorga Atención'),
                ])
                ->columns(3),

            Forms\Components\Section::make('Diagnóstico')
                ->schema([
                    Forms\Components\TextInput::make('suspected_diagnosis')
                        ->label('Sospecha Diagnóstica')
                        ->required(),
                    Forms\Components\TextInput::make('confirmed_diagnosis')
                        ->label('Diagnóstico Confirmado'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Ubicación y Localidad')
                ->schema([
                    Forms\Components\Toggle::make('prais')
                        ->label('PRAIS'),
                    Forms\Components\Select::make('region_id')
                        ->label('Región')
                        ->placeholder('Seleccione región')
                        ->relationship('region', 'name')
                        ->live()
                        ->required(),
                    Forms\Components\Select::make('commune_id')
                        ->label('Comuna')
                        ->placeholder('Seleccione comuna')
                        ->options(fn (Get $get) => \App\Models\Commune::where('region_id', $get('region_id'))->pluck('name', 'id'))
                        ->disabled(fn (Get $get) => blank($get('region_id')))
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('address_city')
                        ->label('Ciudad'),
                    Forms\Components\Toggle::make('is_rural')
                        ->label('Ruralidad (Rural)'),
                    Forms\Components\Select::make('via')
                        ->label('Vía Dirección')
                        ->placeholder('Seleccione vía')
                        ->options(collect(\App\Enums\AddressVia::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])),
                    Forms\Components\TextInput::make('address_street')
                        ->label('Nombre Calle'),
                    Forms\Components\TextInput::make('address_number')
                        ->label('Número'),
                    Forms\Components\TextInput::make('address_extra')
                        ->label('Resto Dirección'),
                ])
                ->columns(3),

            Forms\Components\Section::make('Contacto')
                ->schema([
                    Forms\Components\TextInput::make('phone_home')
                        ->label('Teléfono Fijo')
                        ->tel(),
                    Forms\Components\TextInput::make('phone_mobile')
                        ->label('Teléfono Móvil')
                        ->tel(),
                    Forms\Components\TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Profesional')
                ->schema([
                    Forms\Components\Select::make('requesting_professional_id')
                        ->label('Prof. Solicitante')
                        ->placeholder('Seleccione profesional')
                        ->relationship('requestingProfessional', 'text')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->text ?: trim("{$record->given} {$record->fathers_family}"))
                        ->default(fn () => auth()->id())
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('resolving_professional_id')
                        ->label('Prof. Resuelve')
                        ->placeholder('Seleccione profesional')
                        ->relationship('resolvingProfessional', 'text')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->text ?: trim("{$record->given} {$record->fathers_family}"))
                        ->searchable(),
                    Forms\Components\TextInput::make('identifier')
                        ->label('ID Local')
                        ->disabled(),
                    Forms\Components\TextInput::make('sigte_id')
                        ->label('SIGTE ID'),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->placeholder('Seleccione estado')
                        ->options([
                            'completo'  => 'Completo',
                            'incompleto' => 'Incompleto',
                        ])
                        ->default('incompleto')
                        ->required(),
                ])
                ->columns(3),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identifier')
                    ->label('ID Local')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.officialIdentifier.value')
                    ->label('RUT')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('procedureCode.code')
                    ->label('Cód. FONASA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('procedureCode.text')
                    ->label('Prestación')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('F. Entrada')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completo'   => 'Completo',
                        'incompleto' => 'Incompleto',
                        default      => '-',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completo' => 'success',
                        default    => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(
                ! auth()->user()->can('be god'),
                fn ($query) => $query->where('requesting_professional_id', auth()->id())
            );
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSigteSurgicalEntries::route('/'),
            'create' => Pages\CreateSigteSurgicalEntry::route('/create'),
            'edit'   => Pages\EditSigteSurgicalEntry::route('/{record}/edit'),
        ];
    }
}
