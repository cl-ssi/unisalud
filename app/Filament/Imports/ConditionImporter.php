<?php

namespace App\Filament\Imports;

use DateTime;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

// use App\Models\Condition;
use App\Models\DependentUser;
use App\Models\DependentCaregiver;
use App\Models\DependentConditions;
use App\Models\Condition;

use App\Models\User;
use App\Models\Identifier;
use App\Models\HumanName;
use App\Models\Address;
use App\Services\GeocodingService;
use App\Models\Commune;
use App\Models\Location;

// use App\Models\Coding;

use App\Models\Sex as ClassSex;
use App\Models\Gender as ClassGender;
use App\Models\Country;

class ConditionImporter extends Importer
{
    // protected static ?string $model = Condition::class;
    protected static ?string $model = DependentUser::class;

    // public $condition = null;
    public $dependent_user = null;

    public static function getColumns(): array
    {
        return [];
    }

    public function resolveRecord(): ?DependentUser
    {
        $user = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['run'])
                ->where('cod_con_identifier_type_id', 1);
            })
            ->first();
        $user_id = $user->id??null;
        return DependentUser::firstOrCreate(['user_id' => $user_id ]);
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
                'birthday'              => date("Y-m-d", strtotime($this->originalData['fecha_nacimiento'])),
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

        /*
        * Creator Importer cuidador
        */
        $user_caregiver = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['run_cuidador'])
                ->Where('cod_con_identifier_type_id', 1);
            })
            ->first();

        $sexValue_caregiver = ClassSex::where('text', $this->originalData['sexo_cuidador'])->first()->value;
        $sexGender_caregiver = ClassGender::where('text', $this->originalData['genero_cuidador'])->first()->value;
        $nationality_caregiver = Country::where('name', $this->originalData['nacionalidad_cuidador'])->first()->id;

        $user_caregiver_upsert = User::updateOrCreate(
            [
                'id'    => $user_caregiver ? $user_caregiver->id : null
            ]
            ,
            [
                'active'                => 1,
                'text'                  => $this->originalData['nombre_cuidador'].' '.$this->originalData['apellido_paterno_cuidador'].' '.$this->originalData['apellido_materno_cuidador'],
                'given'                 => $this->originalData['nombre_cuidador'],
                'fathers_family'        => $this->originalData['apellido_paterno_cuidador'],
                'mothers_family'        => $this->originalData['apellido_materno_cuidador'],
                'sex'                   => $sexValue_caregiver,
                'gender'                => $sexGender_caregiver,
                'birthday'              => date("Y-m-d", strtotime($this->originalData['fecha_nacimiento_cuidador'])),
                // 'cod_con_marital_id'    => $this->originalData['estado_civil'],
                'nationality_id'        => $nationality_caregiver,
            ]
        );

        if($user_caregiver == null){
            // SE CREA IDENTIFIER
            $identifier_caregiver_create = Identifier::create(
                [
                    'user_id'                       => $user_caregiver_upsert->id,
                    'use'                           => 'official',
                    'cod_con_identifier_type_id'    => 1,
                    'value'                         => $this->originalData['run_cuidador'],
                    'dv'                            => $this->originalData['dv_cuidador']
                ]
            );

            //SE CREA HUMAN NAME
            $humanNameCaregiver = HumanName::create(
                [
                    'use'               => 'official',
                    'given'             => $this->originalData['nombre_cuidador'],
                    'fathers_family'    => $this->originalData['apellido_paterno_cuidador'],
                    'mothers_family'    => $this->originalData['apellido_materno_cuidador'],
                    'period_start'      => now(),
                    'user_id'           => $user_caregiver_upsert->id
                ]
            );
        }

        //ADDRESS
        $addressCaregiverExist = new Address();
        foreach($user_caregiver_upsert->addresses as $address){
            if($address->use->value == 'home'){
                $addressCaregiverExist = $address;
            }
        }

        $communeCaregiver = Commune::where('name', $this->originalData['comuna'])->first()->id;

        $newAddressCaregiver = Address::updateOrCreate(
            [
                'id'    => $addressCaregiverExist ? $addressCaregiverExist->id : null
            ]
            ,
            [

                'user_id'       => $user_caregiver_upsert->id,
                'use'           => 'home',
                'type'          => 'physical',
                'text'          => $this->originalData['calle'],
                'line'          => $this->originalData['numero'],
                'apartment'     => $this->originalData['departamento'],
                'suburb'        => null,
                'city'          => null,
                'commune_id'    => $communeCaregiver,
                'postal_code'   => null,
                'region_id'     => null,
            ]
        );

        // Verificar que no exista ya un caregiver y si existe actualizar
        $caregiver = DependentCaregiver::whereHas('user', 
            function ($query) use($user_caregiver_upsert) {
                $query->where('id', $user_caregiver_upsert->id);
            }
        )->first();

        $caregiver_upsert = DependentCaregiver::updateOrCreate(
            [
                'id'    => $caregiver ? $caregiver->id : null
            ]
            ,
            [
                'dependent_user_id'     => $this->record->id,
                'user_id'               => $user_caregiver_upsert->id,
                'relative'              => $this->originalData['parentesco_cuidador'],
                'empam'                 => $this->validateBool($this->originalData['empam_cuidador']),
                'zarit'                 => $this->validateBool($this->originalData['zarit_cuidador']),
                'immunizations'         => $this->originalData['inmunizaciones_cuidador'],
                'elaborated_plan'       => $this->validateBool($this->originalData['plan_elaborado_cuidador']),
                'evaluated_plan'        => $this->validateBool($this->originalData['plan_evaluado_cuidador']),
                'trained'               => $this->validateBool($this->originalData['capacitacion_cuidador']),
                'stipend'               => $this->validateBool($this->originalData['estipendio_cuidador']),
            ]
        );

        //  Asociar Condiciones
        if($this->validateBool($this->originalData['electrodependencia'])){
            DependentConditions::firstOrCreate(['dependent_user_id' => $this->record->id, 'condition_id' => 1]);
        }
        if($this->validateBool($this->originalData['movilidad_reducida'])){
            DependentConditions::firstOrCreate(['dependent_user_id' => $this->record->id, 'condition_id' => 2]);
        }
        if($this->validateBool($this->originalData['oxigeno_dependiente'])){
            DependentConditions::firstOrCreate(['dependent_user_id' => $this->record->id, 'condition_id' => 3]);
        }
        if($this->validateBool($this->originalData['alimentacion_enteral'])){
            DependentConditions::firstOrCreate(['dependent_user_id' => $this->record->id, 'condition_id' => 4]);
        }
        if($this->validateBool($this->originalData['oncologicos'])){
            DependentConditions::firstOrCreate(['dependent_user_id' => $this->record->id, 'condition_id' => 5]);
        }
        if($this->validateBool($this->originalData['cuidados_paliativos_universales'])){
            DependentConditions::firstOrCreate(['dependent_user_id' => $this->record->id, 'condition_id' => 6]);
        }
        if($this->validateBool($this->originalData['naneas'])){
            DependentConditions::firstOrCreate(['dependent_user_id' => $this->record->id, 'condition_id' => 7]);
        }


        // SE AGREGA EL 'user_id' A $this->record, que corresponde al user recien creado o ya creado.
        $this->record->user_id  = $userCreatedOrUpdated->id;
        $this->record->diagnosis = $this->originalData['diagnostico'];
        $this->record->check_in_date = $this->validateDate($this->originalData['fecha_ingreso']);
        $this->record->check_out_date = $this->validateDate($this->originalData['fecha_egreso']);
        $this->record->integral_visits = $this->originalData['visitas_integrales'];
        $this->record->treatment_visits = $this->originalData['visitas_tratamiento'];
        $this->record->last_integral_visit = $this->validateDate($this->originalData['fecha_visita_integral']);
        $this->record->last_treatment_visit =  $this->validateDate($this->originalData['fecha_visita_tratamiento']);
        $this->record->barthel = $this->validateBarthel($this->originalData['barthel']);
        $this->record->empam = $this->validateBool($this->originalData['emp_empam']);
        $this->record->eleam = $this->validateBool($this->originalData['eleam']);
        $this->record->upp = $this->validateBool($this->originalData['upp']);
        $this->record->elaborated_plan = $this->validateBool($this->originalData['plan_elaborado']);
        $this->record->evaluated_plan = $this->validateBool($this->originalData['plan_evaluado']);
        $this->record->pneumonia = $this->validateDate($this->originalData['neumo']);
        $this->record->influenza = $this->validateDate($this->originalData['influenza']);
        $this->record->covid_19 = $this->validateDate($this->originalData['covid_19']);
        $this->record->extra_info = $this->originalData['extra_info'];
        $this->record->tech_aid = $this->validateBool($this->originalData['ayuda_tecnica']);
        $this->record->tech_aid_date = $this->validateDate($this->originalData['ayuda_tecnica_fecha']);
        $this->record->nutrition_assistance = $this->validateBool($this->originalData['entrega_alimentacion']);
        $this->record->nutrition_assistance_date = $this->validateDate($this->originalData['entrega_alimentacion_fecha']);
        $this->record->flood_zone = $this->validateBool($this->originalData['zona_inundabilidad']);
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

    public function validateBool($text)
    {
        $out = null;
        $text = strtolower(trim($text));
        if($text == 'si' || $text == 'ok'){
            $out = true;
        } else if($text == 'no' || $text == 'p'){
            $out = false;
        }
        return $out;
    }

    public function validateDate($text)
    {
        $out = null;
        if($text != ''){
            $date_str = DateTime::createFromFormat('d/m/Y', $text);
            if($date_str != false){
                $out = $date_str->format('Y-m-d');
            }
        }
        return $out;
    }

    public function validateBarthel($text){
        $out = null;
        $text = strtolower(trim($text));
        switch($text){
            case 'independiente':
                $out = 'independent';break;
            case 'leve':
                $out = 'slight';break;
            case 'moderado':
                $out = 'moderate';break;
            case 'grave':
                $out = 'severe';break;
            case 'total':
                $out = 'total';break;
        }
        return $out;
    }
}
