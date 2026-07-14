<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalEntryResource\Pages;

use App\Enums\Sex;
use App\Filament\Pages\Sigte\SigteBaseDeDatos;
use App\Filament\Resources\Sigte\SigteSurgicalEntryResource;
use App\Models\Address;
use App\Models\ContactPoint;
use App\Models\Identifier;
use App\Models\SigteSurgicalWaitlist;
use App\Models\User;
use App\Models\WaitlistEntryType;
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
        if (! $this->searched || $this->alreadyExists || $this->leqxDuplicate) {
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

        $this->form->fill([
            'rut'            => $rut,
            'run'            => $identifier?->value ?? $rut,
            'dv'             => $identifier?->dv,
            'given'          => $user?->given,
            'fathers_family' => $user?->fathers_family,
            'mothers_family' => $user?->mothers_family,
            'birthday'       => $user?->birthday?->format('Y-m-d'),
            'sex'            => $user?->sex?->value,
            'entry_date'     => now()->format('Y-m-d'),
            'address_city'   => $address?->city,
            'is_rural'       => (bool) ($address?->is_rural),
            'via'            => $address?->via,
            'address_street' => $address?->text,
            'address_number' => $address?->line,
            'address_extra'  => $address?->suburb,
            'phone_home'     => $phoneHome,
            'phone_mobile'   => $phoneMobile,
            'email'          => $email,
        ]);
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
                    ->visible(fn ($livewire) => $livewire->searched && ! $livewire->alreadyExists && ! $livewire->leqxDuplicate),
            ]);
    }

    protected function getFormActions(): array
    {
        if (! $this->searched || $this->alreadyExists || $this->leqxDuplicate) {
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
