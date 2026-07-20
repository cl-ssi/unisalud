<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalEntryResource\Pages;

use App\Enums\Sex;
use App\Enums\SurgicalComplexity;
use App\Filament\Pages\Sigte\SigteBaseDeDatos;
use App\Filament\Resources\Sigte\SigteSurgicalEntryResource;
use App\Models\Address;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\HealthcareType;
use App\Models\Identifier;
use App\Models\Organization;
use App\Models\Region;
use App\Models\SigteSurgicalProcedureCode;
use App\Models\SigteSurgicalWaitlist;
use App\Models\User;
use App\Models\WaitlistEntryType;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class CreateSigteSurgicalEntry extends CreateRecord
{
    protected static string $resource = SigteSurgicalEntryResource::class;

    protected static ?string $title = 'Ingresar Paciente';

    public bool $searched = false;

    public bool $alreadyExists = false;

    public bool $leqxDuplicate = false;

    public ?User $foundUser = null;

    public ?SigteSurgicalWaitlist $existingEntry = null;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public function create(bool $another = false): void
    {
        if (! $this->searched || $this->alreadyExists) {
            return;
        }

        parent::create($another);
    }

    public function searchPatient(): void
    {
        $rut = trim((string) ($this->form->getRawState()['rut'] ?? ''));

        if ($rut === '' || ! ctype_digit($rut)) {
            Notification::make()
                ->title('Ingrese un RUN válido (solo números, sin DV).')
                ->danger()
                ->send();

            return;
        }

        $identifier = Identifier::where('value', $rut)
            ->where('cod_con_identifier_type_id', 1)
            ->first();

        $this->foundUser = $identifier?->user;
        $this->existingEntry = $this->foundUser
            ? SigteSurgicalWaitlist::where('user_id', $this->foundUser->id)->latest()->first()
            : null;
        $this->alreadyExists = (bool) $this->existingEntry;
        $this->leqxDuplicate = SigteBaseDeDatos::isRunInLeqxList($rut);
        $this->searched = true;

        if ($this->alreadyExists) {
            return;
        }

        $user = $this->foundUser;

        $address     = $user ? Address::where('user_id', $user->id)->where('use', 'home')->first() : null;
        $phoneHome   = $user ? ContactPoint::where('user_id', $user->id)->where('system', 'phone')->where('use', 'home')->first()?->value : null;
        $phoneMobile = $user ? ContactPoint::where('user_id', $user->id)->where('system', 'phone')->where('use', 'mobile')->first()?->value : null;
        $email       = $user ? ContactPoint::where('user_id', $user->id)->where('system', 'email')->where('use', 'work')->first()?->value : null;

        // When the RUN matches the uploaded "LE Quirúrgica" file, use that row
        // to pre-fill everything the form can use; data already on file for
        // this user (address, contact info, identity — e.g. from a prior "LE
        // CNE" upload) takes precedence.
        $leqxRow = $this->leqxDuplicate ? SigteBaseDeDatos::getLeqxRowData($rut) : null;

        $fillData = array_merge(
            $leqxRow ? $this->leqxFillData($leqxRow) : [],
            [
                'rut'            => $rut,
                'run'            => $identifier?->value ?? $rut,
            ],
            array_filter([
                'dv'             => $identifier?->dv,
                'given'          => $user?->given,
                'fathers_family' => $user?->fathers_family,
                'mothers_family' => $user?->mothers_family,
                'birthday'       => $user?->birthday?->format('Y-m-d'),
                'sex'            => $user?->sex?->value,
                'address_city'   => $address?->city,
                'is_rural'       => $address ? (bool) $address->is_rural : null,
                'via'            => $address?->via,
                'address_street' => $address?->text,
                'address_number' => $address?->line,
                'address_extra'  => $address?->suburb,
                'phone_home'     => $phoneHome,
                'phone_mobile'   => $phoneMobile,
                'email'          => $email,
            ], fn ($v) => $v !== null)
        );

        // Default to today only when the LE Quirúrgica row didn't supply
        // F_ENTRADA — otherwise that's the value "Fecha Entrada LE QX" should show.
        $fillData['entry_date'] ??= now()->format('Y-m-d');

        $this->form->fill($fillData);
    }

    private function leqxFillData(array $row): array
    {
        $val = fn (string $key) => trim((string) ($row[$key] ?? ''));

        $sexMap = ['1' => 'male', '2' => 'female', '3' => 'other', '9' => 'unknown'];
        $viaMap = ['1' => 'calle', '2' => 'pasaje', '3' => 'avenida', '4' => 'otro'];
        $complexityMap = [
            'cirugia mayor' => SurgicalComplexity::CirugiaMayor->value,
            'cirugia menor' => SurgicalComplexity::CirugiaMenor->value,
            'procedimiento' => SurgicalComplexity::Procedimiento->value,
        ];
        $normalize = fn (string $v) => strtr(strtolower($v), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        $parseDate = function (string $value): ?string {
            if ($value === '') {
                return null;
            }

            try {
                // The export stores dates as US-style m/d/Y, e.g. "1/25/2004".
                return Carbon::createFromFormat('n/j/Y', $value)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        };

        $procedureCode = $val('PRESTA_MIN') !== ''
            ? SigteSurgicalProcedureCode::whereRaw('LOWER(code) = ?', [strtolower($val('PRESTA_MIN'))])->first()
            : null;

        $healthcareType = $val('PREVISION') !== ''
            ? HealthcareType::whereRaw('LOWER(code) = ?', [strtolower($val('PREVISION'))])->first()
            : null;

        $originEstablishment = $this->findLeqxOrganization($val('ESTAB_ORIG'));
        $destinyEstablishment = $this->findLeqxOrganization($val('ESTAB_DEST'));

        // The export's REGION code is the MINSAL region number (1 = Tarapacá,
        // 2 = Antofagasta, ...), which matches this table's primary key order;
        // id_minsal isn't populated, so match against id directly.
        $region = ctype_digit($val('REGION')) ? Region::find((int) $val('REGION')) : null;

        $commune = $val('COMUNA') !== ''
            ? Commune::whereRaw('LOWER(code_deis) = ?', [strtolower($val('COMUNA'))])
                ->orWhereRaw('LOWER(name) = ?', [strtolower($val('COMUNA'))])
                ->first()
            : null;

        $findProfessional = function (string $run): ?User {
            $run = preg_replace('/[^0-9]/', '', $run);

            return $run
                ? User::whereHas(
                    'identifiers',
                    fn ($q) => $q->where('value', $run)->where('cod_con_identifier_type_id', 1)
                )->first()
                : null;
        };

        $requestingProfessional = $findProfessional($val('RUN_PROF_SOL'));
        $resolvingProfessional = $findProfessional($val('RUN_PROF_RESOL'));

        return array_filter([
            'dv'                               => $val('DV') ?: null,
            'given'                            => $val('NOMBRES') ?: null,
            'fathers_family'                   => $val('PRIMER_APELLIDO') ?: null,
            'mothers_family'                   => $val('SEGUNDO_APELLIDO') ?: null,
            'birthday'                         => $parseDate($val('FECHA_NAC')),
            'sex'                              => $sexMap[$val('SEXO')] ?? null,
            'health_service_id'                => $val('SERV_SALUD') ?: null,
            'healthcare_type_id'               => $healthcareType?->id,
            'complexity'                       => $complexityMap[$normalize($val('Tipo de IQ'))] ?? null,
            'sigte_surgical_procedure_code_id' => $procedureCode?->id,
            'plano'                            => $val('PLANO') ?: null,
            'extremity'                        => $val('EXTREMIDAD') ?: null,
            'entry_date'                       => $parseDate($val('F_ENTRADA')),
            'origin_establishment_id'          => $originEstablishment?->id,
            'destiny_establishment_id'         => $destinyEstablishment?->id,
            'referring_specialty'              => $val('E_OTOR_AT') ?: null,
            'suspected_diagnosis'              => $val('SOSPECHA_DIAG') ?: null,
            'confirmed_diagnosis'              => $val('CONFIR_DIAG') ?: null,
            'prais'                            => $val('PRAIS') === '2',
            'region_id'                        => $region?->id,
            'commune_id'                       => $commune?->id,
            'address_city'                     => $val('CIUDAD') ?: null,
            'is_rural'                         => $val('COND_RURALIDAD') === '2',
            'via'                              => $viaMap[$val('VIA_DIRECCION')] ?? null,
            'address_street'                   => $val('NOM_CALLE') ?: null,
            'address_number'                   => $val('NUM_DIRECCION') ?: null,
            'address_extra'                    => $val('RESTO_DIRECCION') ?: null,
            'phone_home'                       => $val('FONO_FIJO') ?: null,
            'phone_mobile'                     => $val('FONO_MOVIL') ?: null,
            'email'                            => $val('EMAIL') ?: null,
            'requesting_professional_id'       => $requestingProfessional?->id,
            'resolving_professional_id'        => $resolvingProfessional?->id,
            'sigte_id'                         => $val('SIGTE_ID') ?: null,
        ], fn ($v) => $v !== null);
    }

    private function findLeqxOrganization(string $code): ?Organization
    {
        if ($code === '') {
            return null;
        }

        return Organization::whereRaw('LOWER(code_deis) = ?', [strtolower($code)])
            ->orWhereRaw('LOWER(alias) = ?', [strtolower($code)])
            ->first();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buscar Paciente por RUN')
                    ->description('Los datos se autocompletarán si el paciente ya tiene un RUN registrado.')
                    ->schema([
                        Forms\Components\TextInput::make('rut')
                            ->label('RUN del Paciente (sin DV, sin puntos)')
                            ->numeric()
                            ->required()
                            ->dehydrated(false)
                            ->extraInputAttributes(['wire:keydown.enter.prevent' => 'searchPatient']),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('buscar')
                                ->label('Buscar')
                                ->icon('heroicon-o-magnifying-glass')
                                ->action('searchPatient'),
                        ]),
                    ]),

                Forms\Components\Placeholder::make('already_exists_alert')
                    ->label('')
                    ->content(function ($livewire) {
                        $entry = $livewire->existingEntry;

                        if (! $entry) {
                            return '';
                        }

                        $professional = $entry->requestingProfessional?->text
                            ?: trim(($entry->requestingProfessional?->given ?? '') . ' ' . ($entry->requestingProfessional?->fathers_family ?? ''));

                        return new HtmlString(
                            '⚠ Este paciente ya tiene un ingreso vigente en la Lista de Espera Quirúrgica (SIGTE LE QX).<br>'
                            . '<strong>Patología:</strong> ' . e($entry->suspected_diagnosis ?: '-') . '<br>'
                            . '<strong>Profesional:</strong> ' . e($professional ?: '-') . '<br>'
                            . '<strong>Fecha de solicitud:</strong> ' . e($entry->entry_date?->format('d-m-Y') ?? '-')
                        );
                    })
                    ->columnSpanFull()
                    ->visible(fn ($livewire) => $livewire->searched && $livewire->alreadyExists),

                Forms\Components\Placeholder::make('leqx_duplicate_alert')
                    ->label('')
                    ->content(function () {
                        $fecha = SigteBaseDeDatos::getLeqxUploadMeta()['fecha'] ?? null;

                        return new HtmlString(
                            '⚠ Este paciente figura en la Lista de Espera Quirúrgica de otra especialidad (LE Quirúrgica'
                            . ($fecha ? ', cargada el ' . e($fecha) : '')
                            . '). Verifique antes de continuar.'
                        );
                    })
                    ->columnSpanFull()
                    ->visible(fn ($livewire) => $livewire->searched && $livewire->leqxDuplicate && ! $livewire->alreadyExists),

                Forms\Components\Group::make([
                    Forms\Components\Section::make('Identificación del Paciente')
                        ->schema([
                            Forms\Components\TextInput::make('run')
                                ->label('RUN')
                                ->disabled(fn ($livewire) => $livewire->foundUser !== null)
                                ->dehydrated()
                                ->required(),
                            Forms\Components\TextInput::make('dv')
                                ->label('DV')
                                ->disabled(fn ($livewire) => $livewire->foundUser !== null)
                                ->dehydrated()
                                ->required(),
                            Forms\Components\TextInput::make('health_service_id')
                                ->label('Servicio de Salud'),
                            Forms\Components\TextInput::make('given')
                                ->label('Nombres')
                                ->disabled(fn ($livewire) => filled($livewire->foundUser?->given))
                                ->dehydrated()
                                ->required(),
                            Forms\Components\TextInput::make('fathers_family')
                                ->label('Apellido Paterno')
                                ->disabled(fn ($livewire) => filled($livewire->foundUser?->fathers_family))
                                ->dehydrated()
                                ->required(),
                            Forms\Components\TextInput::make('mothers_family')
                                ->label('Apellido Materno')
                                ->disabled(fn ($livewire) => filled($livewire->foundUser?->mothers_family))
                                ->dehydrated(),
                            Forms\Components\DatePicker::make('birthday')
                                ->label('Fecha Nacimiento')
                                ->disabled(fn ($livewire) => filled($livewire->foundUser?->birthday))
                                ->dehydrated()
                                ->required(),
                            Forms\Components\Select::make('sex')
                                ->label('Sexo')
                                ->placeholder('Seleccione sexo')
                                ->options(collect(Sex::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()]))
                                ->disabled(fn ($livewire) => filled($livewire->foundUser?->sex))
                                ->dehydrated()
                                ->required(),
                            Forms\Components\Select::make('healthcare_type_id')
                                ->label('Previsión')
                                ->placeholder('Seleccione previsión')
                                ->relationship('healthcareType', 'text'),
                        ])
                        ->columns(3),

                    ...SigteSurgicalEntryResource::getEntryFieldsSchema(),

                ])
                    ->columnSpanFull()
                    ->visible(fn ($livewire) => $livewire->searched && ! $livewire->alreadyExists),
            ]);
    }

    protected function getFormActions(): array
    {
        if (! $this->searched || $this->alreadyExists) {
            return [];
        }

        return parent::getFormActions();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->foundUser;

            $given         = $data['given'] ?? $this->foundUser?->given;
            $fathersFamily = $data['fathers_family'] ?? $this->foundUser?->fathers_family;
            $mothersFamily = $data['mothers_family'] ?? $this->foundUser?->mothers_family;
            $birthday      = $data['birthday'] ?? $this->foundUser?->birthday?->format('Y-m-d');
            $sex           = $data['sex'] ?? $this->foundUser?->sex?->value;

            if (! $user) {
                $user = User::create([
                    'active'         => 1,
                    'text'           => trim(($given ?? '') . ' ' . ($fathersFamily ?? '') . ' ' . ($mothersFamily ?? '')),
                    'given'          => $given,
                    'fathers_family' => $fathersFamily,
                    'mothers_family' => $mothersFamily,
                    'birthday'       => $birthday,
                    'sex'            => $sex,
                ]);

                Identifier::create([
                    'user_id'                    => $user->id,
                    'use'                        => 'official',
                    'cod_con_identifier_type_id' => 1,
                    'value'                      => $data['run'] ?? $data['rut'] ?? null,
                    'dv'                         => $data['dv'] ?? null,
                ]);
            } else {
                $user->fill(array_filter([
                    'given'          => $given,
                    'fathers_family' => $fathersFamily,
                    'mothers_family' => $mothersFamily,
                    'birthday'       => $birthday,
                    'sex'            => $sex,
                ], fn ($v) => $v !== null))->save();
            }

            Address::updateOrCreate(
                ['user_id' => $user->id, 'use' => 'home'],
                [
                    'type'       => 'physical',
                    'text'       => $data['address_street'] ?? null,
                    'line'       => $data['address_number'] ?? null,
                    'suburb'     => $data['address_extra'] ?? null,
                    'city'       => $data['address_city'] ?? null,
                    'commune_id' => $data['commune_id'] ?? null,
                    'is_rural'   => $data['is_rural'] ?? null,
                    'via'        => $data['via'] ?? null,
                ]
            );

            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'phone', 'use' => 'home'],
                ['value' => $data['phone_home'] ?? null]
            );

            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'phone', 'use' => 'mobile'],
                ['value' => $data['phone_mobile'] ?? null]
            );

            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'email', 'use' => 'work'],
                ['value' => $data['email'] ?? null]
            );

            $data['user_id'] = $user->id;
            $data['requesting_professional_id'] = $data['requesting_professional_id'] ?? auth()->id();
            $data['waitlist_entry_type_id'] = WaitlistEntryType::firstOrCreate(
                ['code' => '4'],
                ['text' => 'Lista de espera quirúrgica']
            )->id;

            return collect($data)->only((new SigteSurgicalWaitlist())->getFillable())->toArray();
        });
    }

    protected function afterCreate(): void
    {
        $this->record->update(['identifier' => $this->record->id]);
    }
}
