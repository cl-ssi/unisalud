<?php

namespace App\Filament\Imports;

use DateTime;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

use App\Models\Condition;
use App\Models\DependentUser;
use App\Models\DependentCaregiver;

use App\Models\User;
use App\Models\Identifier;
use App\Models\HumanName;
use App\Models\Address;
use App\Services\GeocodingService;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\Location;

// use App\Models\Coding;

use App\Models\Sex as ClassSex;
use App\Models\Gender as ClassGender;
use App\Models\Country;
use App\Models\Organization;
use Carbon\Carbon;

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
        //COALESCE ARREGLO
        $this->originalData['establecimiento'] = $this->originalData['establecimiento'] ?? '';

        $this->originalData['nombre'] = $this->originalData['nombre']?? '';
        $this->originalData['apellido_paterno'] = $this->originalData['apellido_paterno'] ?? '';
        $this->originalData['apellido_materno'] = $this->originalData['apellido_materno'] ?? '';
        $this->originalData['run'] = $this->originalData['run']?? '';
        //dv
        $this->originalData['prevision'] = $this->originalData['prevision']?? '';
        $this->originalData['sexo'] = $this->originalData['sexo']?? '';
        $this->originalData['genero'] = $this->originalData['genero']?? '';
        
        $this->originalData['fecha_nacimiento'] = $this->originalData['fecha_nacimiento']??null;
        $this->originalData['nacionalidad'] = $this->originalData['nacionalidad']?? '';
        $this->originalData['comuna'] = $this->originalData['comuna'] ?? '';
        $this->originalData['calle'] = $this->originalData['calle'] ?? '';
        $this->originalData['departamento'] = $this->originalData['departamento'] ?? '';
        $this->originalData['diagnostico'] = $this->originalData['diagnostico'] ?? '';
        $this->originalData['fecha_ingreso'] = $this->originalData['fecha_ingreso'] ?? null;
        $this->originalData['fecha_egreso'] = $this->originalData['fecha_egreso'] ?? null;
        $this->originalData['visitas_integrales'] = $this->originalData['visitas_integrales'] ?? null;
        $this->originalData['visitas_tratamiento'] = $this->originalData['visitas_tratamiento'] ?? null;
        $this->originalData['emp_empam'] = $this->originalData['emp_empam'] ?? null;
        $this->originalData['eleam'] = $this->originalData['eleam'] ?? null;
        $this->originalData['upp'] = $this->originalData['upp'] ?? null;
        $this->originalData['plan_elaborado'] = $this->originalData['plan_elaborado'] ?? null;
        $this->originalData['plan_evaluado'] = $this->originalData['plan_evaluado'] ?? null;
        $this->originalData['neumo'] = $this->originalData['neumo'] ?? null;
        $this->originalData['influenza'] = $this->originalData['influenza'] ?? null;
        $this->originalData['covid_19'] = $this->originalData['covid_19'] ?? null;
        $this->originalData['extra_info'] = $this->originalData['extra_info'] ?? null;
        $this->originalData['ayuda_tecnica'] = $this->originalData['ayuda_tecnica'] ?? null;
        $this->originalData['ayuda_tecnica_fecha'] = $this->originalData['ayuda_tecnica_fecha'] ?? '';
        $this->originalData['entrega_alimentacion'] = $this->originalData['entrega_alimentacion'] ?? null;
        $this->originalData['entrega_alimentacion_fecha'] =$this->originalData['entrega_alimentacion_fecha'] ?? '';
        $this->originalData['sonda_sng'] = $this->originalData['sonda_sng']?? '';
        $this->originalData['sonda_urinaria'] = $this->originalData['sonda_urinaria']?? '';
        
        $this->originalData['prevision_cuidador'] = $this->originalData['prevision_cuidador']?? '';
        $this->originalData['talla_panal'] = $this->originalData['talla_panal']?? '';
        $this->originalData['nombre_cuidador'] = $this->originalData['nombre_cuidador'] ?? '';
        $this->originalData['apellido_paterno_cuidador'] = $this->originalData['apellido_paterno_cuidador'] ?? '';
        $this->originalData['apellido_paterno_cuidador'] = $this->originalData['apellido_materno_cuidador'] ?? '';
        $this->originalData['fecha_nacimiento_cuidador'] = $this->originalData['fecha_nacimiento_cuidador']?? null;
        $this->originalData['run_cuidador'] = $this->originalData['run_cuidador'] ?? '';
        $this->originalData['dv_cuidador'] = $this->originalData['dv_cuidador'] ?? '';
        $this->originalData['sexo_cuidador'] = $this->originalData['sexo_cuidador'] ?? '';
        $this->originalData['genero_cuidador'] = $this->originalData['genero_cuidador'] ?? '';
        $this->originalData['nacionalidad_cuidador'] = $this->originalData['nacionalidad_cuidador'] ?? '';
        $this->originalData['parentesco_cuidador'] = $this->originalData['parentesco_cuidador'] ?? '';
        $this->originalData['empam_cuidador'] = $this->originalData['empam_cuidador'] ?? '';
        $this->originalData['zarit_cuidador'] = $this->originalData['zarit_cuidador'] ?? '';
        $this->originalData['inmunizaciones_cuidador'] = $this->originalData['inmunizaciones_cuidador'] ?? '';
        $this->originalData['plan_elaborado_cuidador'] = $this->originalData['plan_elaborado_cuidador'] ?? '';
        $this->originalData['plan_evaluado_cuidador'] = $this->originalData['plan_evaluado_cuidador'] ?? '';
        $this->originalData['capacitacion_cuidador'] = $this->originalData['capacitacion_cuidador'] ?? '';
        $this->originalData['estipendio_cuidador'] = $this->originalData['estipendio_cuidador'] ?? '';

        //PRIMERO VERIFICAR SI EXITE EL USER
        $user = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['run'])->Where('cod_con_identifier_type_id', 1);
        })->first();
        
        $sexValue = ClassSex::where('text', $this->originalData['sexo'])->first()->value??null;
        $sexGender = ClassGender::where('text', $this->originalData['genero'])->first()->value??null;
        $nationality = Country::where('name', $this->originalData['nacionalidad'])->first()->id??null;

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
                'birthday'              => date('Y-m-d', Carbon::createFromFormat('d/m/Y', $this->originalData['fecha_nacimiento'])->getTimestamp()),
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

        $commune = Commune::where('name', $this->originalData['comuna'])->first()->id??null;

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
                'apartment'     => $this->originalData['departamento'] ?? null,
                'suburb'        => null,
                'city'          => null,
                'commune_id'    => $commune,
                'postal_code'   => null,
                'region_id'     => null,
            ]
        );

        //LOCATION
        $street    = $this->originalData['calle'];
        $number     = $this->originalData['numero'];
        $commune    = $this->originalData['comuna'];

        if ($street && $number && $commune ) {

            $geocodingService = app(GeocodingService::class);
            $coordinates = $geocodingService->getCoordinates($street.'+'.$number.'+'.$commune);

            if ($coordinates) {
                $latitude   = $coordinates['lat'];
                $longitude  = $coordinates['lng'];
            } else {
                $latitude   = null;
                $longitude  = null;
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
        }

        // Crear o Actualizar contactPoint
        $organization_id = preg_replace("/[^0-9]/", '', $this->originalData['establecimiento']);
        $organization_id = Organization::where('code_deis', '=', $organization_id)->first()->id ?? null;
        $contactPoint = ContactPoint::where('user_id', $userCreatedOrUpdated->id)->latest()->first();
        $contactPoint_upsert = ContactPoint::updateOrCreate(
            [
                'id'    => $contactPoint ? $contactPoint->id : null
            ],
            [
                'system'            => 'phone',
                'user_id'           => $userCreatedOrUpdated->id, 
                'location_id'       => $newLocation->id ?? null,
                'value'             => $this->originalData['telefono'],
                'organization_id'   => $organization_id,
                'use'               => 'mobile',
                'actually'          => 0, // TODO: vaya agregando si cambia
            ]
        );



        /*
        *
        * Creator Importer cuidador
        *
        */

        if($this->originalData['run_cuidador'] != '')
        {
            $user_caregiver = User::whereHas('identifiers', function ($query) {
                $query->where('value', $this->originalData['run_cuidador'])
                    ->Where('cod_con_identifier_type_id', 1);
                })
                ->first();

            $sexValue_caregiver = ClassSex::where('text', $this->originalData['sexo_cuidador'])->first()->value ?? null;
            $sexGender_caregiver = ClassGender::where('text', $this->originalData['genero_cuidador'])->first()->value ?? null;
            $nationality_caregiver = Country::where('name', $this->originalData['nacionalidad_cuidador'])->first()->id ?? null;

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
                    'birthday'              => date('Y-m-d', Carbon::createFromFormat('d/m/Y', $this->originalData['fecha_nacimiento_cuidador'])->getTimestamp()),
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
                    'apartment'     => $this->originalData['departamento'] ?? null,
                    'suburb'        => null,
                    'city'          => null,
                    'commune_id'    => $communeCaregiver,
                    'postal_code'   => null,
                    'region_id'     => null,
                ]
            );

            //LOCATION CAREGIVER
            $caregiverStreet    = $this->originalData['calle'];
            $caregiverNumber     = $this->originalData['numero'];
            $caregiverCommune    = $this->originalData['comuna'];
            if ($caregiverStreet && $caregiverNumber && $caregiverCommune ) {

                $geocodingService = app(GeocodingService::class);
                $caregiverCordinates = $geocodingService->getCoordinates($caregiverStreet.'+'.$caregiverNumber.'+'.$caregiverCommune);

                if ($coordinates) {
                    $caregiverLatitude   = $caregiverCordinates['lat'];
                    $caregiverLongitude  = $caregiverCordinates['lng'];
                } else {
                    $caregiverLatitude   = null;
                    $caregiverLongitude  = null;
                }

                $newCaregiverLocation = Location::updateOrCreate(
                    [
                        'id'    => $newAddressCaregiver->location ? $newAddressCaregiver->location->id : null
                    ]
                    ,
                    [
                        'address_id'        => $newAddressCaregiver->id,
                        'longitude'         => $longitude,
                        'latitude'          => $latitude
                    ]
                );
            }

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
                    'healthcare_type'       => $this->validateHealthcareType($this->originalData['prevision_cuidador']),
                    'empam'                 => $this->validateBool($this->originalData['empam_cuidador']),
                    'zarit'                 => $this->validateBool($this->originalData['zarit_cuidador']),
                    'immunizations'         => $this->originalData['inmunizaciones_cuidador'],
                    'elaborated_plan'       => $this->validateBool($this->originalData['plan_elaborado_cuidador']),
                    'evaluated_plan'        => $this->validateBool($this->originalData['plan_evaluado_cuidador']),
                    'trained'               => $this->validateBool($this->originalData['capacitacion_cuidador']),
                    'stipend'               => $this->validateBool($this->originalData['estipendio_cuidador']),
                ]
            );


            // Crear o Actualizar contactPoint del cuidador
            $caregiverContactPoint = ContactPoint::where('user_id', $user_caregiver_upsert->id)->latest()->first();
            $caregiverContactPoint_upsert = ContactPoint::updateOrCreate(
                [
                    'id'    => $caregiverContactPoint ? $caregiverContactPoint->id : null
                ],
                [
                    'system'            => 'phone',
                    'user_id'           => $user_caregiver_upsert->id,
                    'location_id'       => $newCaregiverLocation->id ?? null,
                    'value'             => $this->originalData['telefono'],
                    'organization_id'   => $organization_id,
                    'use'               => 'mobile',
                    'actually'          => 0, // TODO: vaya agregando si cambia
                ]
            );
        }

        //  Asociar Condiciones
        if($this->validateBool($this->originalData['electrodependencia'])){
            $this->record->conditions()->attach(1);
        }
        if($this->validateBool($this->originalData['movilidad_reducida'])){
            $this->record->conditions()->attach(2);
        }
        if($this->validateBool($this->originalData['oxigeno_dependiente'])){
            $this->record->conditions()->attach(3);            
        }
        if($this->validateBool($this->originalData['alimentacion_enteral'])){
            $this->record->conditions()->attach(4);
        }
        if($this->validateBool($this->originalData['oncologicos'])){
            $this->record->conditions()->attach(5);
        }
        if($this->validateBool($this->originalData['cuidados_paliativos_universales'])){
            $this->record->conditions()->attach(6);
        }
        if($this->validateBool($this->originalData['naneas'])){
            $this->record->conditions()->attach(7);
        }
        

        // SE AGREGA EL 'user_id' A $this->record, que corresponde al user recien creado o ya creado.
        $this->record->user_id  = $userCreatedOrUpdated->id;
        $this->record->diagnosis = $this->originalData['diagnostico'];
        $this->record->healthcare_type = $this->validateHealthcareType($this->originalData['prevision']);
        $this->record->check_in_date = $this->validateDate($this->originalData['fecha_ingreso']);
        $this->record->check_out_date = $this->validateDate($this->originalData['fecha_egreso']);
        $this->record->integral_visits = $this->validateInt($this->originalData['visitas_integrales']);
        $this->record->treatment_visits = $this->validateInt($this->originalData['visitas_tratamiento']);
        $this->record->last_integral_visit = $this->validateDate($this->originalData['fecha_visita_integral']);
        $this->record->last_treatment_visit =  $this->validateDate($this->originalData['fecha_visita_tratamiento']);
        $this->record->barthel = $this->validateBarthel($this->originalData['barthel']);
        $this->record->empam = $this->validateBool($this->originalData['emp_empam']);
        $this->record->eleam = $this->validateBool($this->originalData['eleam']);
        $this->record->upp = $this->validateBool($this->originalData['upp']);
        $this->record->elaborated_plan = $this->validateBool($this->originalData['plan_elaborado']);
        $this->record->evaluated_plan = $this->validateBool($this->originalData['plan_evaluado']);
        $this->record->diapers_size = $this->originalData['talla_panal'];
        $this->record->pneumonia = $this->validateDate($this->originalData['neumo']);
        $this->record->influenza = $this->validateDate($this->originalData['influenza']);
        $this->record->covid_19 = $this->validateDate($this->originalData['covid_19']);
        $this->record->extra_info = $this->originalData['extra_info'];
        $this->record->tech_aid = $this->validateBool($this->originalData['ayuda_tecnica']);
        $this->record->tech_aid_date = $this->validateDate($this->originalData['ayuda_tecnica_fecha']);
        $this->record->nutrition_assistance = $this->validateBool($this->originalData['entrega_alimentacion']);
        $this->record->nutrition_assistance_date = $this->validateDate($this->originalData['entrega_alimentacion_fecha']);
        $this->record->nasogastric_catheter = $this->validateInt(intval(trim($this->originalData['sonda_sng'])));
        $this->record->urinary_catheter = $this->validateInt(intval(trim($this->originalData['sonda_urinaria'])));
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

    public function validateInt($val)
    {
        $out = null;
        $clean = intval(trim($val));
        if($clean != 0){
            $out = strval($clean);
        }
        return $out;
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

    public function validateHealthcareType($text){
        $out = null;
        $words = explode(' ', $text);
        $text = (count($words) > 1)?array_pop($words):$text;
        $text = strtolower(trim($text));
        switch($text){
            case 'a':
                $out = 'FONASA A';break;
            case 'b':
                $out = 'FONASA B';break;
            case 'c':
                $out = 'FONASA C';break;
            case 'd':
                $out = 'FONASA D';break;
            case 'isapre':
                $out = 'ISAPRE';break;
            case 'prais':
                $out = 'PRAIS';break;
        }
        return $out;
    }
}   
