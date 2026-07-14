<?php

namespace App\Filament\Resources\Sigte;

use App\Enums\SurgicalComplexity;
use App\Filament\Resources\Sigte\SigteSurgicalAdminResource\Pages;
use App\Filament\Widgets\SigteSurgicalStatsWidget;
use App\Models\SigteSurgicalWaitlist;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SigteSurgicalAdminResource extends Resource
{
    protected static ?string $model = SigteSurgicalWaitlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'SIGTE';

    protected static ?string $navigationLabel = 'Todos los Ingresos';

    protected static ?string $label = 'Ingreso';

    protected static ?string $pluralLabel = 'Todos los Ingresos';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->can('SIGTE LE QX: administrador')
            || auth()->user()->can('be god');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            ...SigteSurgicalEntryResource::getEntryFieldsSchema(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identifier')
                    ->label('ID Local')
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.officialIdentifier.value')
                    ->label('RUT')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.text')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('procedureCode.code')
                    ->label('Cód. FONASA')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('F. Entrada')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('requestingProfessional.text')
                    ->label('Cirujano')
                    ->getStateUsing(fn ($record) => $record->requestingProfessional?->text
                        ?: trim(($record->requestingProfessional?->given ?? '') . ' ' . ($record->requestingProfessional?->fathers_family ?? ''))
                        ?: '-')
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHas(
                        'requestingProfessional',
                        fn ($q) => $q->where('text', 'like', "%{$search}%")
                            ->orWhere('given', 'like', "%{$search}%")
                            ->orWhere('fathers_family', 'like', "%{$search}%")
                    ))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->leftJoin(
                        'users as rp', 'rp.id', '=', 'sigte_surgical_waitlists.requesting_professional_id'
                    )->orderBy('rp.text', $direction))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(),
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
                Tables\Columns\IconColumn::make('exported_at')
                    ->label('Exportado')
                    ->boolean()
                    ->tooltip(fn ($record) => $record->exported_at?->format('d-m-Y H:i')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->options([
                        'completo'   => 'Completo',
                        'incompleto' => 'Incompleto',
                    ]),
                SelectFilter::make('complexity')
                    ->label('Complejidad')
                    ->placeholder('Todas')
                    ->options(
                        collect(SurgicalComplexity::cases())
                            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
                    ),
                SelectFilter::make('requesting_professional_id')
                    ->label('Cirujano')
                    ->placeholder('Todos')
                    ->options(fn () => User::whereIn(
                        'id',
                        SigteSurgicalWaitlist::distinct()->pluck('requesting_professional_id')
                    )->get()->mapWithKeys(fn ($u) => [
                        $u->id => $u->text ?: trim("{$u->given} {$u->fathers_family}"),
                    ])),
                SelectFilter::make('destiny_establishment_id')
                    ->label('Estab. Destino')
                    ->placeholder('Todos')
                    ->relationship('destinyEstablishment', 'alias')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->alias ?: $record->name ?: "#{$record->id}")
                    ->searchable(),
                TernaryFilter::make('exported_at')
                    ->label('Exportado')
                    ->placeholder('Todos')
                    ->trueLabel('Exportado')
                    ->falseLabel('No exportado')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('exported_at'),
                        false: fn (Builder $query) => $query->whereNull('exported_at'),
                    ),
                Filter::make('entry_date')
                    ->label('F. Entrada')
                    ->columnSpan(2)
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['desde'] ?? null, fn ($q, $v) => $q->where('entry_date', '>=', $v))
                        ->when($data['hasta'] ?? null, fn ($q, $v) => $q->where('entry_date', '<=', $v)))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde'] ?? null) {
                            $indicators[] = 'Desde ' . Carbon::parse($data['desde'])->format('d-m-Y');
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators[] = 'Hasta ' . Carbon::parse($data['hasta'])->format('d-m-Y');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSigteSurgicalAdminEntries::route('/'),
            'edit'  => Pages\EditSigteSurgicalAdminEntry::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SigteSurgicalStatsWidget::class,
        ];
    }
}
