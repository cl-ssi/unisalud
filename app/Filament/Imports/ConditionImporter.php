<?php

namespace App\Filament\Imports;

use App\Models\Condition;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

use App\Models\User;
use App\Models\Identifier;
use App\Models\HumanName;
use App\Models\Address;
use App\Services\GeocodingService;
use App\Models\Commune;
use App\Models\Location;
use App\Models\Coding;

use App\Models\Sex as ClassSex;
use App\Models\Gender as ClassGender;
use App\Models\Country;

class ConditionImporter extends Importer
{
    protected static ?string $model = Condition::class;

    public $condition = null;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('coding')
                ->label('condicion')
                ->relationship(resolveUsing: ['display'])
        ];
    }

    public function resolveRecord(): ?Condition
    {
        return new Condition();
    }

    protected function afterSave(): void
    {
        //PRIMERO VERIFICAR SI EXITE EL USER
        $user = User::whereHas('identifiers', function ($query) {
                    $query->where('value', $this->originalData['run'])
                        ->Where('cod_con_identifier_type_id', 1);
                    })
                    ->first();
        
        $sexValue = ClassSex::where('text', $this->originalData['sexo'])->first()->value;
        $sexGender = ClassGender::where('text', $this->originalData['genero'])->first()->value;
        $nationality = Country::where('name', $this->originalData['nacionalidad'])->first()->id;
 
        $userCreatedOrUpdated = User::updateOrCreate(
            [
                'id'    => $user ? $user->id : null
            ]
            ,
            [
                'active'                => 1,
                'text'                  => $this->originalData['nombre'].' '.$this->originalData['apellido_paterno'].' '.$this->originalData['apellido_materno'],
                'given'                 => $this->originalData['nombre'],
                'fathers_family'        => $this->originalData['apellido_paterno'],
                'mothers_family'        => $this->originalData['apellido_materno'],
                'sex'                   => $sexValue,
                'gender'                => $sexGender,
                'birthday'              => $this->originalData['fecha_nacimiento'],
                // 'cod_con_marital_id'    => $this->originalData['estado_civil'],
                'nationality_id'        => $nationality,
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
            $humanName = HumanName::create(
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

        //ADDRESS
        $addressExist = new Address();
        foreach($userCreatedOrUpdated->addresses as $address){
            if($address->use->value == 'home'){
                $addressExist = $address;
            }    
        }

        $commune = Commune::where('name', $this->originalData['comuna'])->first()->id;
        
        $newAddress = Address::updateOrCreate(
            [
                'id'    => $addressExist ? $addressExist->id : null
            ]
            ,
            [

                'user_id'       => $userCreatedOrUpdated->id,
                'use'           => 'home',
                'type'          => 'physical',
                'text'          => $this->originalData['calle'],
                'line'          => $this->originalData['numero'],
                'apartment'     => $this->originalData['departamento'],
                'suburb'        => null,
                'city'          => null,
                'commune_id'    => $commune,
                'postal_code'   => null,
                'region_id'     => null,
            ]
        );

        //LOCATION
        $locationExist = new Location();
        $locationExist = $newAddress->location ? $newAddress->location : null;

        //LOCATION
        $address    = $this->originalData['calle'];
        $number     = $this->originalData['numero'];
        $commune    = $this->originalData['comuna'];

        if ($address && $number && $commune ) {

            $geocodingService = app(GeocodingService::class);
            $coordinates = $geocodingService->getCoordinates($address.'+'.$number.'+'.$commune);

            if ($coordinates) {
                $latitude   = $coordinates['lat'];
                $longitude  = $coordinates['lng'];
            } else {
                $latitude   = null;
                $longitude  = null;
            }
        }

        $newLocation = Location::updateOrCreate(
            [
                'id'    => $newAddress->location ? $newAddress->location->id : null
            ]
            ,
            [
                'address_id'        => $newAddress->id,
                'longitude'         => $longitude,
                'latitude'          => $latitude
            ]
        );

        // SE AGREGA EL 'user_id' A $this->record, que corresponde al user recien creado o ya creado.

        $this->record->user_id                      = $userCreatedOrUpdated->id;
        $this->record->cod_con_clinical_status      = 'active';
        $this->record->cod_con_verification_status  = 'confirmed';
    
        //JSON PARA GUARDAR INFO EXTRA
        $extra_info_json = [
            'fecha_ingreso' => $this->originalData['fecha_ingreso'],
            'diagnostico'   => $this->originalData['diagnostico'],
        ];
        $this->record->extra_info                   = json_encode($extra_info_json);
        //
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
