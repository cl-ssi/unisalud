<?php

namespace App\Filament\Imports;

use App\Models\Address;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\HealthcareType;
use App\Models\HumanName;
use App\Models\Identifier;
use App\Models\MinsalSpecialty;
use App\Models\OdontologyHealthCareService;
use App\Models\OdontologySpeciality;
use App\Models\OdontologyWaitlist;
use App\Models\Organization;
use App\Models\Region;
use App\Models\Sex;
use App\Models\User;
use App\Models\WaitlistEntryType;
use App\Models\OdontologyMedicalBenefit;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class OdontologyWaitlistImporter extends Importer
{
    protected static ?string $model = OdontologyWaitlist::class;

    public static function getColumns(): array
    {
        return [
            //
        ];
    }

    public function resolveRecord(): ?OdontologyWaitlist
    {
        $text = strtolower(trim($this->originalData['PRESTA_EST']));
        $healthCareService = OdontologyHealthCareService::firstOrCreate(['text' => $text]);

        // CONSULTO SI EXISTE EL IDENTIFIER ESTA REGISTRADO
        $user = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['RUN'])
                ->Where('cod_con_identifier_type_id', 1);
        })
            ->first();

        // SEXO
        $sexValue = Sex::where('code', $this->originalData['SEXO'])->first()->value ?? null;

        $userCreatedOrUpdated = User::updateOrCreate(
            [
                'id'    => $user ? $user->id : null
            ],
            [
                'active'            => 1,
                'text'              => $this->originalData['NOMBRES'] . ' ' . $this->originalData['PRIMER_APELLIDO'] . ' ' . $this->originalData['SEGUNDO_APELLIDO'],
                'given'             => $this->originalData['NOMBRES'],
                'fathers_family'    => $this->originalData['PRIMER_APELLIDO'],
                'mothers_family'    => $this->originalData['SEGUNDO_APELLIDO'],
                'birthday'          => !empty($this->originalData['FECHA_NAC'])
                    ? \Carbon\Carbon::createFromFormat('d/m/Y', $this->originalData['FECHA_NAC'])->format('Y-m-d')
                    : null,
                'sex'               => $sexValue,
            ]
        );

        if ($user == null) {
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
        $commune = Commune::whereRaw('LOWER(code_deis) = ?', [strtolower($this->originalData['COMUNA'])])->first();
        $communeId = $commune ? $commune->id : null;
        //ESTABLECIMIENTO ORIGEN L.E.
        $organization = Organization::whereRaw('LOWER(code_deis) = ?', [strtolower($this->originalData['ESTAB_ORIG'])])->first()->id;

        //DIRECCIONES
        $addressExist = new Address();
        foreach ($userCreatedOrUpdated->addresses as $address) {
            if ($address->use->value == 'home') {
                $addressExist = $address;
            }
        }

        $newAddress = Address::updateOrCreate(
            [
                'id'    => $addressExist ? $addressExist->id : null
            ],
            [

                'user_id'       => $userCreatedOrUpdated->id,
                'use'           => 'home',
                'type'          => 'physical',
                'text'          => $this->originalData['NOM_CALLE'],
                'line'          => $this->originalData['NUM_DIRECCION'],
                'apartment'     => null,
                'suburb'        => $this->originalData['RESTO_DIRECCION'],
                'city'          => $this->originalData['CIUDAD'],
                'commune_id'    => $communeId,
                'postal_code'   => null,
                'region_id'     => null,
                'is_rural' => match ($this->originalData['COND_RURALIDAD'] ?? null) {
                    '1' => false,
                    '2' => true,
                    default => null,
                },
                'via' => match ($this->originalData['VIA_DIRECCION'] ?? null) {
                    '1' => 'calle',
                    '2' => 'pasaje',
                    '3' => 'avenida',
                    '4' => 'otro',
                    default => null,
                },
            ]
        );

        //CONTACT POINTS HOME
        $contactPointHome = $userCreatedOrUpdated->homeContactPoint;

        $contactPointHome = ContactPoint::updateOrCreate(
            [
                'id'    => $contactPointHome ? $contactPointHome->id : null
            ],
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
            ],
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
            ],
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

        // ESPECIALIDAD
        $textSpecialty = strtolower(trim($this->originalData['ESPECIALIDAD']));
        $specialty = OdontologySpeciality::firstOrCreate(['text' => $textSpecialty]);

        // ORGANIZACION DE ORIGEN
        $originOrganization = Organization::whereRaw('LOWER(code_deis) = ?', [strtolower($this->originalData['ESTAB_ORIG'])])->first()->id;

        // ORGANIZACION DE DESTINO 
        $destinyOrganization = Organization::whereRaw('LOWER(code_deis) = ?', [strtolower($this->originalData['ESTAB_DEST'])])->first()->id;

        // ESTABLECIMIENTO
        $alias = strtolower(trim($this->originalData['ESTABLECIMIENTO']));
        $organization = Organization::whereRaw("LOWER(alias) = ?", [$alias])->value('id');

        // PREVISION
        $prevision = strtolower(trim($this->originalData['PREVISION']));
        $previsionValue = HealthcareType::whereRaw("LOWER(code) = ?", [$prevision])->value('id');

        // PRESTA_MIN
        $minsalSpecialty = MinsalSpecialty::whereRaw('LOWER(code) = ?', [strtolower($this->originalData['PRESTA_MIN'])])->first()->id;

        // PRESTA_MIN_SALIDA
        $minsalExitSpecialty = !empty($this->originalData['PRESTA_MIN_SALIDA']) ? optional(
            MinsalSpecialty::whereRaw('LOWER(code) = ?', [strtolower(trim($this->originalData['PRESTA_MIN_SALIDA']))])->first()
        )->id : null;

        // PRESTA_EST
        $establishmentHealthCareService = OdontologyHealthCareService::whereRaw('LOWER(text) = ?', [strtolower($this->originalData['PRESTA_EST'])])->first()->id;

        // TIPO_PREST
        $entryType = WaitlistEntryType::whereRaw('LOWER(code) = ?', [strtolower($this->originalData['TIPO_PREST'])])->first()->id;

        // REGION
        $region = Region::whereRaw('LOWER(id) = ?', [strtolower($this->originalData['REGION'])])->first()->id;

        // RUN_PROF_SOL
        $profSol = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['RUN_PROF_SOL'])
                ->where('cod_con_identifier_type_id', 1);
        })
            ->first();

        if ($profSol) {
            $profSol->update(['active' => 1,]);

            $profSolCreatedOrUpdated = $profSol;
        } else {
            // Create new user
            $profSolCreatedOrUpdated = User::create([
                'active' => 1,
            ]);

            Identifier::create([
                'user_id'                    => $profSolCreatedOrUpdated->id,
                'use'                        => 'official',
                'cod_con_identifier_type_id' => 1,
                'value'                      => $this->originalData['RUN_PROF_SOL'],
                'dv'                         => $this->originalData['DV_PROF_SOL'],
            ]);
        }

        // RUN_PROF_RESOL
        $profResol = User::whereHas('identifiers', function ($query) {
            $query->where('value', $this->originalData['RUN_PROF_RESOL'])
                ->where('cod_con_identifier_type_id', 1);
        })
            ->first();

        if ($profResol) {
            $profResol->update(['active' => 1,]);

            $profResolCreatedOrUpdated = $profResol;
        } else {
            // Create new user
            $profResolCreatedOrUpdated = User::create([
                'active' => 1,
            ]);

            Identifier::create([
                'user_id'                    => $profResolCreatedOrUpdated->id,
                'use'                        => 'official',
                'cod_con_identifier_type_id' => 1,
                'value'                      => $this->originalData['RUN_PROF_RESOL'],
                'dv'                         => $this->originalData['DV_PROF_RESOL'],
            ]);
        }


        $waitlistCreatedOrUpdated = OdontologyWaitlist::updateOrCreate(
            [
                'user_id'                       => $userCreatedOrUpdated->id,
                'sigte_id'                      => $this->originalData['SIGTE_ID'],
            ],
            [
                'specialty_id'                  => $specialty->id,
                'origin_establishment_id'       => $originOrganization,
                'wait_health_care_service_id'   => $establishmentHealthCareService,
                'commune_id'                    => $communeId,
                'status'                        => ($this->originalData['ESTADO'] ?? null) ? strtolower(trim($this->originalData['ESTADO'])) : null,
                'destiny_establishment_id'      => $destinyOrganization,
                'suspected_diagnosis'           => strtolower(trim($this->originalData['SOSPECHA_DIAG'])),
                'establishment_id'              => $organization,
                'entry_date'                    => !empty($this->originalData['F_ENTRADA'])
                    ? \Carbon\Carbon::createFromFormat('d/m/Y', $this->originalData['F_ENTRADA'])->format('Y-m-d')
                    : null,
                'exit_date'                     => !empty($this->originalData['F_SALIDA'])
                    ? \Carbon\Carbon::createFromFormat('d/m/Y', $this->originalData['F_SALIDA'])->format('Y-m-d')
                    : null,
                'healthcare_type_id'            => $previsionValue,
                'minsal_specialty_id'           => $minsalSpecialty,
                'exit_minsal_specialty_id'      => $minsalExitSpecialty,
                'plano'                         => ($this->originalData['PLANO'] ?? null) ? strtolower(trim($this->originalData['PLANO'])) : null,
                'extremity'                     => ($this->originalData['EXTREMIDAD'] ?? null) ? strtolower(trim($this->originalData['EXTREMIDAD'])) : null,
                'prais'                         => strtolower(trim($this->originalData['PRAIS'])),
                'region_id'                     => $region,
                'pediatric'                     => $this->originalData['PEDIATRICO'],
                'lb'                            => $this->originalData['LB'],
                'requesting_professional_id'    => $profSolCreatedOrUpdated->id,
                'resolving_professional_id'     => $profResolCreatedOrUpdated->id,
                'waitlist_entry_type_id'        => $entryType,
                'local_id'                      => $this->originalData['ID_LOCAL'],
                'result'                        => $this->originalData['RESULTADO'],
                'waitlistAge'                   => $this->originalData['EDAD'] ? floatval(str_replace(',', '.', $this->originalData['EDAD'])) : null,
                'waitlistYear'                  => $this->originalData['AÑO'],
                'health_service_id'             => $this->originalData['SERV_SALUD'],
                'appointment_date'              => !empty($this->originalData['F_CITACION']) ? date("Y-m-d H:i:s", strtotime($this->originalData['F_CITACION'])) : null,
                'worker'                        => $this->originalData['FUNCIONARIO'] ?? null,
                'iqType'                        => $this->originalData['Tipo de IQ'] ?? null,
                'oncologic'                     => $this->originalData['Oncologico'] ?? null,
                'origin_commune_id'             => Commune::whereRaw('LOWER(name) = ?', [strtolower($this->originalData['Comuna Origen'])])->first()->id ?? null,
                'fonasa'                        => $this->originalData['FONASA'] ?? null,
                'praisUser'                     => $this->originalData['Usuario PRAIS'] ?? null,
                'lbPrais'                       => $this->originalData['LB PRAIS'] ?? null,
                'lbUrinary'                     => $this->originalData['LB INCONTINENCIA URINARIA'] ?? null,
                'exitError'                     => $this->originalData['Error Egreso'] ?? null,
                'lbIqOdonto'                    => $this->originalData['LB IQ ODONTO'] ?? null,
                'procedureType'                 => $this->originalData['Tipo Procedimiento'] ?? null,
                'sename'                        => $this->originalData['SENAME'] ?? null,
                'exit_code'                     => $this->originalData['C_SALIDA'] ?? null,
                'referring_specialty'           => $this->originalData['E_OTOR_AT'] ?? null,
                'wait_medical_benefit_id'       => OdontologyMedicalBenefit::whereRaw('LOWER(text) = ?', [strtolower($this->originalData['TIPO PRESTACION'])])->first()->id ?? null,
                'elapsed_days'                  => $this->originalData['DIAS_PASADOS'] ?? null,
                ]
        );

        // EVENTOS
        if ($waitlistCreatedOrUpdated->events->count() == 0) {
            $save = $waitlistCreatedOrUpdated->events()->create([
                'status'            => ($this->originalData['ESTADO'] ?? null) ? strtolower(trim($this->originalData['ESTADO'])) : null,
                'registered_at'     => now(),
                'text'              => 'Registrado a través de carga masiva',
                'discharge'         => strtolower(trim($this->originalData['CAUSAL_EGRESO'] ?? '')),
                'appointment_at'    => !empty($this->originalData['FECHA_ATENCION']) ? date("Y-m-d H:i:s", strtotime($this->originalData['FECHA_ATENCION'])) : null,
                'register_user_id'  => auth()->user()->id
            ]);
        }

        // CONTACTOS
        if ($waitlistCreatedOrUpdated->contacts->count() == 0) {
            if (in_array(strtolower(trim($this->originalData['ESTADO'])), ['citado', 'atendido', 'inasistente', 'egresado'])) {
                $statusContact = 'si';
            } else if (strtolower(trim($this->originalData['ESTADO'])) == 'incontactable') {
                $statusContact = 'no';
            } else {
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

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your waitlist import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
