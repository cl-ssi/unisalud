<?php

namespace App\Filament\Imports;

use App\Models\Waitlist;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

use App\Models\User;
use App\Models\Identifier;
use App\Models\HumanName;
use App\Models\HealthCareService;
use App\Models\Cie10;
use App\Models\WaitlistMedicalBenefit;
use App\Models\WaitlistSpecialty;
use App\Models\Commune;
use App\Models\Organization;
use App\Models\Address;
use App\Models\ContactPoint;

use Filament\Notifications\Notification;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;

class WaitlistImporter extends Importer
{
    protected static ?string $model = Waitlist::class;

    public static function getColumns(): array
    {
        return [
            //
        ];
    }
    
    public function resolveRecord(): ?Waitlist
    {
        $text = strtolower(trim($this->originalData['PRESTA_EST']));
        $healthCareService = HealthCareService::firstOrCreate(['text' => $text]);
        
        // CONSULTO SI EXISTE EL IDENTIFIER ESTA REGISTRADO
        $user = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['RUN'])
                ->Where('cod_con_identifier_type_id', 1);
            })
            ->first();

        $userCreatedOrUpdated = User::updateOrCreate(
            [
                'id'    => $user ? $user->id : null
            ]
            ,
            [
                'active'            => 1,
                'text'              => $this->originalData['NOMBRES'].' '.$this->originalData['PRIMER_APELLIDO'].' '.$this->originalData['SEGUNDO_APELLIDO'],
                'given'             => $this->originalData['NOMBRES'],
                'fathers_family'    => $this->originalData['PRIMER_APELLIDO'],
                'mothers_family'    => $this->originalData['SEGUNDO_APELLIDO'],
                //'sex'             => $sexValue,
                //'gender'          => $sexGender,
                'birthday'          => !empty($this->originalData['FECHA_NAC']) 
                                        ? date("Y-m-d", strtotime($this->originalData['FECHA_NAC'])) 
                                        : null,
            ]
        );

        if($user == null){
            // SE CREA IDENTIFIER
            $identifierCreate = Identifier::create(
                [
                    'user_id'                       => $userCreatedOrUpdated->id,
                    'use'                           => 'official',
                    'cod_con_identifier_type_id'    => 1,
                    'value'                         => $this->originalData['RUN'],
                    'dv'                            => $this->originalData['DV']
                ]
            );

            //SE CREA HUMAN NAME
            $humanName = HumanName::create(
                [
                    'use'               => 'official',
                    'given'             => $this->originalData['NOMBRES'],
                    'fathers_family'    => $this->originalData['PRIMER_APELLIDO'],
                    'mothers_family'    => $this->originalData['SEGUNDO_APELLIDO'],
                    'period_start'      => now(),
                    'user_id'           => $userCreatedOrUpdated->id
                ]
            );
        }

        //COMUNA DE ORIGEN L.E.
        $commune = Commune::whereRaw('LOWER(name) = ?', [strtolower($this->originalData['COMUNA_ORIGEN'])])->first();
        $communeId = $commune ? $commune->id : null;
        //ESTABLECIMIENTO ORIGEN L.E.
        $organization = Organization::whereRaw('LOWER(name) = ?', [strtolower($this->originalData['ESTABLECIMIENTO'])])->first()->id;

        //DIRECCIONES
        $addressExist = new Address();
        foreach($userCreatedOrUpdated->addresses as $address){
            if($address->use->value == 'home'){
                $addressExist = $address;
            }
        }

        $newAddress = Address::updateOrCreate(
            [
                'id'    => $addressExist ? $addressExist->id : null
            ]
            ,
            [

                'user_id'       => $userCreatedOrUpdated->id,
                'use'           => 'home',
                'type'          => 'physical',
                'text'          => $this->originalData['NOM_CALLE'],
                'line'          => $this->originalData['NUM_DIRECCION'],
                'apartment'     => null,
                'suburb'        => $this->originalData['RESTO_DIRECCION'],
                'city'          => null,
                'commune_id'    => $communeId,
                'postal_code'   => null,
                'region_id'     => null,
            ]
        );

        //CONTACT POINTS HOME
        $contactPointHome = $userCreatedOrUpdated->homeContactPoint;

        $contactPointHome = ContactPoint::updateOrCreate(
            [
                'id'    => $contactPointHome ? $contactPointHome->id : null
            ]
            ,
            [

                'system'                => 'phone',
                'contact_point_id'      => null,
                'user_id'               => $userCreatedOrUpdated->id,
                'location_id'           => null,
                'emergency_contact_id'  => null,
                'value'                 => $this->originalData['FONO_FIJO'], 	
                'organization_id'       => null,
                'use'                   => 'home',
                'rank'                  => null,
                'actually'              => 0,
            ]
        );

        //CONTACT POINTS MOBILE
        $contactPointMobile = $userCreatedOrUpdated->mobileContactPoint;

        $contactPointMobile = ContactPoint::updateOrCreate(
            [
                'id'    => $contactPointMobile ? $contactPointMobile->id : null
            ]
            ,
            [
                'system'                => 'phone',
                'contact_point_id'      => null,
                'user_id'               => $userCreatedOrUpdated->id,
                'location_id'           => null,
                'emergency_contact_id'  => null,
                'value'                 => $this->originalData['FONO_MOVIL'], 	
                'organization_id'       => null,
                'use'                   => 'mobile',
                'rank'                  => null,
                'actually'              => 0,
            ]
        );

        //CONTACT POINTS MAIL
        $contactPointEmail = $userCreatedOrUpdated->emailContactPoint;
        $contactPointEmail = ContactPoint::updateOrCreate(
            [
                'id'    => $contactPointEmail ? $contactPointEmail->id : null
            ]
            ,
            [
                'system'                => 'email',
                'contact_point_id'      => null,
                'user_id'               => $userCreatedOrUpdated->id,
                'location_id'           => null,
                'emergency_contact_id'  => null,
                'value'                 => $this->originalData['EMAIL'], 	
                'organization_id'       => null,
                'use'                   => 'work',
                'rank'                  => null,
                'actually'              => 0,
            ]
        );

        // CIE10
        $code = preg_match('/^[A-Z0-9\.]+/', strtoupper($this->originalData['CONFIR_DIAG']), $matches) ? $matches[0] : null;
        if ($code) {
            $cie10 = Cie10::whereRaw('BINARY code = ?', [$code])->first();
        }
        else
        {
            $cie10 = null;
        }

        // TIPO PRESTACION
        $textMedicalBenefits = strtolower(trim($this->originalData['TIPO_PRESTACION']));
        $medicalBenefits = WaitlistMedicalBenefit::firstOrCreate(['text' => $textMedicalBenefits]);

        // ESPECIALIDAD
        $textSpecialty = strtolower(trim($this->originalData['ESPECIALIDAD']));
        $specialty = WaitlistSpecialty::firstOrCreate(['text' => $textSpecialty]);

        // ORGANIZACION DE DESTINO 
        $alias = strtolower(trim($this->originalData['ESTAB_PRESTADOR']));
        $organizationId = Organization::whereRaw("LOWER(alias) = ?", [$alias])->value('id');

        $waitlist = $userCreatedOrUpdated->waitlists->where('wait_health_care_service_id', $healthCareService->id)->first();
        $waitlistCreatedOrUpdated = Waitlist::updateOrCreate(
            [
                'id'                            => optional($waitlist)->id
            ]
            ,
            [
                'user_id'                       => $userCreatedOrUpdated->id,
                'wait_health_care_service_id'   => $healthCareService->id,
                'cie10_id'                      => ($cie10 != null) ? $cie10->id : null,
                'sigte_id'                      => $this->originalData['SIGTE_ID'],
                'wait_medical_benefit_id'       => $medicalBenefits->id,
                'wait_specialty_id'             => $specialty->id,
                'organization_id'               => $organization,
                'commune_id'                    => $communeId,
                'status'                        => strtolower(trim($this->originalData['ESTADO'])),
                'destiny_organization_id'       => $organizationId,
                'extremity_id'	                => $this->originalData['EXTREMIDAD'],
            ]
        );

        // EVENTOS
        if($waitlistCreatedOrUpdated->events->count() == 0){
            $save = $waitlistCreatedOrUpdated->events()->create([
                'status'            => strtolower(trim($this->originalData['ESTADO'])),
                'registered_at'     => now(),
                'text'              => 'Registrado a través de carga masiva',
                'discharge'         => strtolower(trim($this->originalData['CAUSAL_EGRESO'] ?? '')),
                'appointment_at'    => !empty($this->originalData['FECHA_ATENCION']) ? date("Y-m-d H:i:s", strtotime($this->originalData['FECHA_ATENCION'])) : null,
                'register_user_id'  => auth()->user()->id
            ]);
        }
        
        // CONTACTOS
        if($waitlistCreatedOrUpdated->contacts->count() == 0){
            if(in_array(strtolower(trim($this->originalData['ESTADO'])), ['citado', 'atendido', 'inasistente', 'egresado'])){
                $statusContact = 'si';
            }
            else if(strtolower(trim($this->originalData['ESTADO'])) == 'incontactable'){
                $statusContact = 'no';
            }
            else{
                $statusContact = null;
            }

            $waitlistCreatedOrUpdated->contacts()->create([
                'type'              => 'telefonico',
                'status'            => $statusContact,
                'contacted_at'      => now(),
                'text'              => 'Registrado a través de carga masiva',
                'register_user_id'  => auth()->user()->id,
            ]);
        }
    
        return $waitlistCreatedOrUpdated;
    }

    protected function afterSave(): void
    {
        //PRIMERO VERIFICAR SI EXITE EL USER
        /*
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
        */
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your waitlist import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
