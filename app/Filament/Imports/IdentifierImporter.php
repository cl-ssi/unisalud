<?php

namespace App\Filament\Imports;

use App\Models\Identifier;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

use App\Models\User;

class IdentifierImporter extends Importer
{
    protected static ?string $model = Identifier::class;

    public static function getColumns(): array
    {
        return [
            /*
            ImportColumn::make('identifiers_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('user')
                ->relationship(),
            ImportColumn::make('use'),
            ImportColumn::make('codConIdentifierType')
                ->relationship(),
            ImportColumn::make('system')
                ->rules(['max:255']),
            */
            ImportColumn::make('value')
                ->rules(['max:255']),
            /*
            ImportColumn::make('dv')
                ->rules(['max:255']),
            ImportColumn::make('period_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('practitioner_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('organization_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('appointment_id')
                ->numeric()
                ->rules(['integer']),
            */
        ];
    }

    public function resolveRecord(): ?Identifier
    {
        /*
        return Identifier::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'email' => $this->data['email'],
        ]);
        */
        return new Identifier();
    }

    /*
    public function beforeSave(): void 
    {
        //PRIMERO VERIFICAR SI EXITE EL USER
        $user = User::whereHas('identifiers', function ($query) {
                    $query->where('value', $this->originalData['value'])
                        ->Where('cod_con_identifier_type_id', 1);
                    })
                    ->first();
        /*
        // Verifica si el usuario ya existe
        $user = User::where('email', $row['email'])->first();

        if ($user) {
            // Actualiza el usuario existente
            $user->update($row);
        } else {
            // Crea un nuevo usuario
            User::create($row);
        }
        
        // Retorna false para evitar la importación automática por Filament
    }
    */

    protected function afterSave(): void
    {
        //
        dd($this);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your identifier import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
