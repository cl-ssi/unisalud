<?php

namespace App\Filament\Imports;

use App\Models\Condition;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

use App\Models\User;
use App\Models\Identifier;
use App\Models\HumanName;

class ConditionImporter extends Importer
{
    protected static ?string $model = Condition::class;

    public static function getColumns(): array
    {
        return [
            /*
            ImportColumn::make('identifier')
                ->rules(['max:255']),
            ImportColumn::make('cod_con_clinical_status'),
            ImportColumn::make('cod_con_verification_status'),
            */
            ImportColumn::make('cod_con_code_id')
                ->label('condicion')
                ->numeric()
            /*
            ImportColumn::make('user')
                ->relationship(),
            */
        ];
    }

    public function resolveRecord(): ?Condition
    {
        // return Condition::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Condition();
    }

    protected function beforeSave(): void
    {
        /*
        $hn = $this->originalData['nombre'].' '.$this->originalData['apellido_paterno'].' '.$this->originalData['apellido_materno'];
        dd($hn, $this->record);
        */
    }

    protected function afterSave(): void
    {
        //PRIMERO VERIFICAR SI EXITE EL USER
        $user = User::whereHas('identifiers', function ($query) {
                    $query->where('value', $this->originalData['run'])
                        ->Where('cod_con_identifier_type_id', 1);
                    })
                    ->first();
        
        $hn = $this->originalData['nombre'].' '.$this->originalData['apellido_paterno'].' '.$this->originalData['apellido_materno'];

        $userCreatedOrUpdated = User::updateOrCreate(
            [
                'id'    => $user ? $user->id : null
            ]
            ,
            [
                'active'                => 1,
                'text'                  => $hn,
                'given'                 => $this->originalData['nombre'],
                'fathers_family'        => $this->originalData['apellido_paterno'],
                'mothers_family'        => $this->originalData['apellido_materno'],
                'sex'                   => $this->originalData['sexo'],
                'gender'                => $this->originalData['genero'],
                'birthday'              => $this->originalData['fecha_nacimiento'],
                'cod_con_marital_id'    => $this->originalData['estado_civil'],
                'nacionality_id'        => $this->originalData['nacionalidad'],
            ]
        );

        if($user == null){
            // SE CREA IDENTIFIER
            $identifierCreate = Identifier::create(
                [
                    'user_id'                       => $userCreatedOrUpdated->id,
                    'use'                           => 'official',
                    'cod_con_identifier_type_id'    => 1,
                    'value'                         => $this->originalData['run'],
                    'dv'                            => $this->originalData['dv']
                ]
            );

            //SE CREA HUMAN NAME
            $identifierCreate = HumanName::create(
                [
                    'use'               => 'official',
                    'given'             => $this->originalData['nombre'],
                    'fathers_family'    => $this->originalData['apellido_paterno'],
                    'mothers_family'    => $this->originalData['apellido_materno'],
                    'period_start'      => now(),
                    'user_id'           => $userCreatedOrUpdated->id
                ]
            );

        }

        //ADDRESS con Location

        

        // SE AGREGA EL 'user_id' A $this->record, que corresponde al user recien creado o ya creado.

        $this->record->user_id                      = $userCreatedOrUpdated->id;
        $this->record->cod_con_clinical_status      = 'active';
        $this->record->cod_con_verification_status  = 'confirmed';
        $this->record->save();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your condition import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
