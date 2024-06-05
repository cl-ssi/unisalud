<?php

namespace App\Filament\Imports;

use App\Models\Appointment;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

use App\Models\User;
use App\Models\Identifier;

class AppointmentImporter extends Importer
{
    protected static ?string $model = Appointment::class;

    public static function getColumns(): array
    {
        return [
            /*
            ImportColumn::make('appointment_id')
                ->numeric()
                ->rules(['integer']),
            */
            ImportColumn::make('status')
                ->label('Estado'),
            /*
            ImportColumn::make('cod_con_cancel_reason_id')
                ->numeric()
                ->rules(['integer']),
            */
            ImportColumn::make('cod_con_appointment_type_id')
                ->label('Tipo')
                ->numeric()
                ->rules(['integer']),
            /*
            ImportColumn::make('priority')
                ->numeric()
                ->rules(['integer']),
            */
            ImportColumn::make('description')
                ->label('Descripción')
                ->rules(['max:255']),
            ImportColumn::make('start')
                ->label('Fecha Inicio'),
                // ->rules(['datetime']),
            /*
            ImportColumn::make('end')
                ->rules(['datetime']),
            */
            ImportColumn::make('created')
                ->label('Fecha Creación'),
                // ->rules(['datetime']),
            ImportColumn::make('comment')
                ->label('Comentario')
                ->rules(['max:255']),
            ImportColumn::make('patient_instruction')
                ->label('Instrucciones de Paciente')
                ->rules(['max:255']),
                /*
            ImportColumn::make('mp_prog_prop_detail_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('mp_theoretical_programming_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('sent_to_hetg_at')
                ->rules(['datetime']),
            ImportColumn::make('run')
                ->label('RUN')
                ->rules(['max:255']),
            ImportColumn::make('dv')
                ->label('DV')
                ->rules(['max:255']),
            ImportColumn::make('given')
                ->label('Nombre')
                ->rules(['max:255']),
            ImportColumn::make('fathers_family')
                ->label('Apellido Paterno')
                ->rules(['max:255']),
            ImportColumn::make('mothers_family')
                ->label('Apellido Materno')
                ->rules(['max:255']),
            ImportColumn::make('sex')
                ->label('Sexo')
                ->rules(['max:255']),
            ImportColumn::make('gender')
                ->label('Género')
                ->rules(['max:255']),
            ImportColumn::make('birthday')
                ->label('Fecha Nacimiento'),
                // ->rules(['datetime']),
            ImportColumn::make('deceased_datatime')
                ->label('Fecha Deceso'),
                // ->rules(['datetime']),
            ImportColumn::make('deceased_datatime')
                ->label('Fecha Deceso'),
            ImportColumn::make('cod_con_marital_id')
                ->label('Estado Civil')
                ->rules(['max:255']),
            ImportColumn::make('nationality_id')
                ->label('Nacionalidad')
                ->rules(['max:255']),
            */
        ];
    }

    protected function beforeSave(): void
    {
        $data = $this->originalData;

        //PRIMERO VERIFICAR SI EXITE EL USER
        $user = User::whereHas('identifiers', function ($query) {
                $query->where('value', $this->originalData['run'])
                ->Where('cod_con_identifier_type_id', 1);
            })
            ->first();

        $userCreatedOrUpdated = User::updateOrCreate(
            [
                'id'    => $user ? $user->id : null
            ]
            ,
            [
                'active'                => 1,
                'text'                  => $this->originalData['given'].' '.$this->originalData['fathers_family'].' '.$this->originalData['mothers_family'],
                'given'                 => $this->originalData['given'],
                'fathers_family'        => $this->originalData['fathers_family'],
                'mothers_family'        => $this->originalData['mothers_family'],
                'sex'                   => $this->originalData['sex'],
                'gender'                => $this->originalData['gender'],
                'birthday'              => $this->originalData['birthday'],
                'deceased_datatime'     => $this->originalData['deceased_datatime'],
                'cod_con_marital_id'    => $this->originalData['cod_con_marital_id'],
                
            ]
        );

        if($user == null){
            $identifierCreate = Identifier::create(
                [
                    'user_id'                       => $userCreatedOrUpdated->id,
                    'use'                           => 'official',
                    'cod_con_identifier_type_id'    => 1,
                    'value'                         => $this->originalData['run'],
                    'dv'                            => $this->originalData['dv']
                ]
            );
        }

        //NO OLVIDAR HUMAN NAMES
    }

    public function resolveRecord(): ?Appointment
    {
        // return Appointment::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Appointment();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your appointment import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
