<?php

namespace App\Filament\Resources\DemandManagementWaitlistResource\Pages;

use App\Filament\Resources\DemandManagementWaitlistResource;
use App\Imports\DemandManagementSpecialityCatalogImport;
use App\Models\DemandManagementSpeciality;
use App\Services\SigteImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Gestión Demanda Código MAI catalog, every Tipo of
 * Prestaciones_c-fechavigencia.xlsx (hoja "prest total"); the Tipo 4 rows
 * are the PRESTA_MIN values accepted for TIPO_PREST 4.
 * The catalog is loaded with the "Importar Excel" header action.
 *
 * Shown as the "Códigos Especialidad" tab of Gestión Demanda
 * (DemandManagementWaitlistResource::getSectionSubNavigation()).
 */
class DemandManagementSpecialityCodes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = DemandManagementWaitlistResource::class;

    protected static ?string $title = 'Códigos Especialidad Gestión Demanda';

    protected static ?string $navigationLabel = 'Códigos Especialidad';

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static string $view = 'filament.resources.demand-management-waitlist-resource.pages.speciality-codes';

    public static function canAccess(array $parameters = []): bool
    {
        return (bool) auth()->user()?->can('DemandManagementWaitlist: View');
    }

    public function getSubNavigation(): array
    {
        return static::getResource()::getSectionSubNavigation();
    }

    public function getBreadcrumbs(): array
    {
        return [
            static::getResource()::getUrl() => static::getResource()::getBreadcrumb(),
            static::getNavigationLabel(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importCatalog')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->modalHeading('Importar catálogo de Códigos MAI')
                ->modalDescription('Suba "Prestaciones_c-fechavigencia.xlsx". Se lee la hoja "prest total" (o la primera hoja con las columnas Código MAI, Glosa y Especialidad). Se cargan todos los Tipos (si no hay columna Tipo, se toman como Tipo 4); solo los Tipo 4 se validan contra TIPO_PREST 4 en la lista de espera. Los códigos nuevos se agregan y los existentes se actualizan; los que no vienen en el archivo se mantienen.')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->disk(SigteImportService::uploadsDisk())
                        ->directory('demand-management-uploads')
                        ->visibility('private')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Importar')
                ->visible(fn () => auth()->user()?->can('DemandManagementWaitlist: Edit'))
                ->action(fn (array $data) => $this->importCatalog($data['file'])),
        ];
    }

    protected function importCatalog(string $file): void
    {
        $disk = Storage::disk(SigteImportService::uploadsDisk());
        $tempPath = tempnam(sys_get_temp_dir(), 'mai_');

        try {
            $stream = $disk->readStream($file);
            file_put_contents($tempPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $s = (new DemandManagementSpecialityCatalogImport($tempPath))->run();

            $n = fn (int $v) => number_format($v, 0, ',', '.');
            $body = "Hoja \"{$s['sheet']}\": {$n($s['created'])} nuevos, {$n($s['updated'])} actualizados"
                . ($s['restored'] ? ", {$n($s['restored'])} restaurados" : '')
                . ", {$n($s['unchanged'])} sin cambios.";

            ksort($s['by_type']);
            $body .= ' Por Tipo: ' . collect($s['by_type'])->map(fn ($count, $type) => "Tipo {$type}: {$n($count)}")->implode(', ') . '.';

            $skipped = array_filter([
                $s['empty'] ? "{$n($s['empty'])} sin Código MAI" : null,
                $s['duplicate'] ? "{$n($s['duplicate'])} códigos repetidos" : null,
            ]);
            if ($skipped) {
                $body .= ' Omitidas: ' . implode(', ', $skipped) . '.';
            }

            Notification::make()
                ->title('Catálogo de Códigos MAI importado')
                ->body($body)
                ->success()
                ->persistent()
                ->send();
        } catch (\RuntimeException $e) {
            Notification::make()->title('No se pudo importar el archivo')->body($e->getMessage())->danger()->persistent()->send();
        } catch (\Throwable $e) {
            Log::error('DemandManagementSpecialityCodes: error al importar catálogo', ['file' => $file, 'error' => $e->getMessage()]);
            Notification::make()->title('Error al importar el catálogo')->body($e->getMessage())->danger()->persistent()->send();
        } finally {
            @unlink($tempPath);
            $disk->delete($file);
        }

        $this->resetTable();
    }

    /**
     * SQL counting the active waitlist rows that use a catalog row's code,
     * through the link the waitlist import sets for its Tipo:
     * Tipo 4 → specialty_id; Tipo 3 → sigte_procedure_id (procedure code);
     * Tipo 1/2 → minsal_specialty_id (MINSAL specialty code).
     */
    protected static function patientsCountSql(): string
    {
        $s = 'demand_management_specialities';

        return "CASE {$s}.type
            WHEN '4' THEN (SELECT COUNT(*) FROM demand_management_waitlists w
                WHERE w.specialty_id = {$s}.id AND w.deleted_at IS NULL)
            WHEN '3' THEN (SELECT COUNT(*) FROM demand_management_waitlists w
                JOIN sigte_procedures p ON p.id = w.sigte_procedure_id
                WHERE p.code = {$s}.code AND w.deleted_at IS NULL)
            WHEN '1' THEN (SELECT COUNT(*) FROM demand_management_waitlists w
                JOIN minsal_specialties m ON m.id = w.minsal_specialty_id
                WHERE m.code = {$s}.code AND w.deleted_at IS NULL)
            WHEN '2' THEN (SELECT COUNT(*) FROM demand_management_waitlists w
                JOIN minsal_specialties m ON m.id = w.minsal_specialty_id
                WHERE m.code = {$s}.code AND w.deleted_at IS NULL)
            ELSE 0 END";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DemandManagementSpeciality::query()
                    ->whereNotNull('code')
                    ->select('demand_management_specialities.*')
                    ->selectRaw(static::patientsCountSql() . ' AS waitlists_count')
            )
            ->defaultSort('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código MAI')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (?string $state) => $state === DemandManagementSpeciality::ENTRY_TYPE_CODE ? 'success' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Glosa')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Especialidad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waitlists_count')
                    ->label('Pacientes en lista')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(fn () => DemandManagementSpeciality::query()
                        ->whereNotNull('code')
                        ->whereNotNull('type')
                        ->distinct()
                        ->orderBy('type')
                        ->pluck('type', 'type')
                        ->map(fn ($type) => "Tipo {$type}")),
                Tables\Filters\SelectFilter::make('text')
                    ->label('Especialidad')
                    ->options(fn () => DemandManagementSpeciality::query()
                        ->whereNotNull('code')
                        ->distinct()
                        ->orderBy('text')
                        ->pluck('text', 'text'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('in_use')
                    ->label('Con pacientes')
                    ->placeholder('Todos')
                    ->trueLabel('Con pacientes')
                    ->falseLabel('Sin pacientes')
                    ->queries(
                        true: fn (Builder $query) => $query->whereRaw('(' . static::patientsCountSql() . ') > 0'),
                        false: fn (Builder $query) => $query->whereRaw('(' . static::patientsCountSql() . ') = 0'),
                    ),
            ])
            ->paginated([25, 50, 100]);
    }
}
