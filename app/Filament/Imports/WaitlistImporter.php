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
        
        $user = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['RUN'])
                ->Where('cod_con_identifier_type_id', 1);
            })
            ->first();
        
        /*
        $sexValue = ClassSex::where('text', $this->originalData['sexo'])->first()->value;
        $sexGender = ClassGender::where('text', $this->originalData['genero'])->first()->value;
        $nationality = Country::where('name', $this->originalData['nacionalidad'])->first()->id;
        */

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
                'birthday'          => date("Y-m-d", strtotime($this->originalData['FECHA_NAC'])),
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
        $commune = Commune::whereRaw('LOWER(name) = ?', [strtolower($this->originalData['COMUNA_ORIGEN'])])->first()->id;
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
                'commune_id'    => $commune,
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

        // CIE10
        $code = preg_match('/^[A-Z0-9\.]+/', strtoupper($this->originalData['CONFIR_DIAG']), $matches) ? $matches[0] : null;
        if ($code) {
            $cie10 = Cie10::whereRaw('BINARY code = ?', [$code])->first();
        }

        // TIPO PRESTACION
        $textMedicalBenefits = strtolower(trim($this->originalData['TIPO_PRESTACION']));
        $medicalBenefits = WaitlistMedicalBenefit::firstOrCreate(['text' => $textMedicalBenefits]);

        // ESPECIALIDAD
        $textSpecialty = strtolower(trim($this->originalData['ESPECIALIDAD']));
        $specialty = WaitlistSpecialty::firstOrCreate(['text' => $textSpecialty]);

        $waitlist = $userCreatedOrUpdated->waitlists->where('wait_health_care_service_id', $healthCareService->id)->first();
        $waitlistCreatedOrUpdated = Waitlist::updateOrCreate(
            [
                'id'                            => optional($waitlist)->id
            ]
            ,
            [
                'user_id'                       => $userCreatedOrUpdated->id,
                'wait_health_care_service_id'   => $healthCareService->id,
                'cie10_id'                      => $cie10->id,
                'organization_id'               => $organization,
                'commune_id'                    => $commune,
                'sigte_id'                      => $this->originalData['SIGTE_ID'],
                'wait_medical_benefit_id'       => $medicalBenefits->id, //corregir singularspecialty
                'wait_specialty_id'             => $specialty->id,
            ]
        );

        
    
        return $waitlistCreatedOrUpdated;
        /*
        $user = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['RUN'])
                ->Where('cod_con_identifier_type_id', 1);
            })
            ->firstOrNew();

        // Actualizamos o asignamos campos
        if (!$user) {
            // $sexValue = ClassSex::where('text', $this->originalData['sexo'])->first()->value;
            // $sexGender = ClassGender::where('text', $this->originalData['genero'])->first()->value;
            // $nationality = Country::where('name', $this->originalData['nacionalidad'])->first()->id;

            $user->active           = 1;
            $user->text             = $this->originalData['NOMBRES'];
            $user->given            = $this->originalData['PRIMER_APELLIDO'];
            $user->fathers_family   = $this->originalData['SEGUNDO_APELLIDO'];
        } else {
            // $sexValue = ClassSex::where('text', $this->originalData['sexo'])->first()->value;
            // $sexGender = ClassGender::where('text', $this->originalData['genero'])->first()->value;
            // $nationality = Country::where('name', $this->originalData['nacionalidad'])->first()->id;
            
            $user->fill([
                'active'            => 1,
                'text'              => $this->originalData['NOMBRES'].' '.$this->originalData['PRIMER_APELLIDO'].' '.$this->originalData['SEGUNDO_APELLIDO'],
                'given'             => $this->originalData['NOMBRES'],
                'fathers_family'    => $this->originalData['PRIMER_APELLIDO'],
                'mothers_family'    => $this->originalData['SEGUNDO_APELLIDO'],
                'sex'               => null, // NO SE ECUENTRA INFO 
                'gender'            => null, // NO SE ECUENTRA INFO
                'birthday'          => $this->originalData['FECHA_NAC'],
                // Otros campos
            ]);
        }

        // Guardar el registro
        $user->save();
        // Aquí es donde puedes ejecutar acciones "after save"
        // $this->afterSave($user);

        // Verificar si el valor PRESTA_EST no existe en la tabla
        if (!HealthCareService::where('text', $this->originalData['PRESTA_EST'])->exists()) {
            // Pasar el mensaje de error a la sesión usando flash
            // session()->flash('error', 'El valor ' . $this->originalData['PRESTA_EST'] . ' no existe en la base de datos.');

            Notification::make()
                ->title('Error')
                ->body('El valor ' . $this->originalData['PRESTA_EST'] . ' no existe en la base de datos.')
                ->danger()
                ->send();

            // Redirigir a la página anterior (la pantalla de la tabla de registros)
            return redirect()->intended('/admin/users');
        }

        /*
        $user = Waitlist::firstOrNew(
            [
                'user_id' => $user->,
                'PRESTA_EST'
            ]
        )
        // PRESTA_EST
        // Fondo de Ojo
        */

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
