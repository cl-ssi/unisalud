<?php

namespace App\Imports;

use App\Enums\Sex;
use App\Models\Address;
use App\Models\Cie10;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\HealthcareType;
use App\Models\HumanName;
use App\Models\Identifier;
use App\Models\MinsalSpecialty;
use App\Models\DemandManagementHealthCareService;
use App\Models\DemandManagementMedicalBenefit;
use App\Models\DemandManagementSpeciality;
use App\Models\DemandManagementWaitlist;
use App\Models\GesHealthProblem;
use App\Models\Organization;
use App\Models\Region;
use App\Models\SigteProcedure;
use App\Models\User;
use App\Models\WaitlistEntryType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Row;
use OpenSpout\Reader\XLSX\Options as XlsxOptions;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Imports the SIGTE demand management waitlist template ("Libro1.xlsx").
 *
 * Run from App\Jobs\ImportWaitlistJob. The upload rules shown to users are in
 * resources/views/filament/resources/demand-management-waitlist-resource/import-rules.blade.php.
 */
class DemandManagementWaitlistImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    /**
     * SEXO in the SIGTE export uses the DEIS "sexo biológico" codification,
     * not free text — map it directly instead of resolving through the
     * sexes lookup table (whose autoincrement ids don't correspond to these codes).
     */
    public const SEX_CODE_MAP = [
        '1'  => Sex::Male->value,
        '2'  => Sex::Female->value,
        '3'  => Sex::Intersex->value,
        '93' => Sex::NotInformed->value,
        '99' => Sex::Unknown->value,
    ];

    /**
     * Fields reported when a re-upload changes an existing record, as
     * column => label. Lookup ids are shown through [model, attribute].
     */
    protected const USER_CHANGE_LABELS = [
        'given'          => 'Nombres',
        'fathers_family' => 'Primer apellido',
        'mothers_family' => 'Segundo apellido',
        'birthday'       => 'Fecha nacimiento',
        'sex'            => 'Sexo',
        'deceased_datetime' => 'Fecha defunción',
    ];

    protected const ADDRESS_CHANGE_LABELS = [
        'text'       => 'Calle',
        'line'       => 'Número dirección',
        'suburb'     => 'Resto dirección',
        'city'       => 'Ciudad',
        'commune_id' => 'Comuna dirección',
        'is_rural'   => 'Rural',
        'via'        => 'Vía dirección',
    ];

    protected const WAITLIST_CHANGE_LABELS = [
        'status'                      => 'Estado',
        'ges'                         => 'GES',
        'cie10_id'                    => 'CIE-10',
        'specialty_id'                => 'Especialidad',
        'minsal_specialty_id'         => 'Prestación MINSAL',
        'exit_minsal_specialty_id'    => 'Prestación MINSAL salida',
        'sigte_procedure_id'          => 'Procedimiento SIGTE',
        'exit_sigte_procedure_id'     => 'Procedimiento SIGTE salida',
        'wait_health_care_service_id' => 'Prestación establecimiento',
        'wait_medical_benefit_id'     => 'Tipo prestación',
        'waitlist_entry_type_id'      => 'Tipo prestación (código)',
        'origin_establishment_id'     => 'Establecimiento origen',
        'destiny_establishment_id'    => 'Establecimiento destino',
        'establishment_id'            => 'Establecimiento',
        'commune_id'                  => 'Comuna',
        'origin_commune_id'           => 'Comuna origen',
        'region_id'                   => 'Región',
        'healthcare_type_id'          => 'Previsión',
        'entry_date'                  => 'Fecha entrada',
        'exit_date'                   => 'Fecha salida',
        'exit_code'                   => 'Causal egreso',
        'appointment_date'            => 'Fecha citación',
        'suspected_diagnosis'         => 'Sospecha diagnóstica',
        'requesting_professional_id'  => 'Profesional solicitante',
        'resolving_professional_id'   => 'Profesional resolutor',
        'plano'                       => 'Plano',
        'extremity'                   => 'Extremidad',
        'prais'                       => 'PRAIS',
        'pediatric'                   => 'Pediátrico',
        'lb'                          => 'LB',
        'local_id'                    => 'ID local',
        'result'                      => 'Resultado',
        'waitlistAge'                 => 'Edad',
        'waitlistYear'                => 'Año',
        'health_service_id'           => 'Servicio de salud',
        'worker'                      => 'Funcionario',
        'iqType'                      => 'Tipo de IQ',
        'oncologic'                   => 'Oncológico',
        'fonasa'                      => 'Fonasa',
        'praisUser'                   => 'Usuario PRAIS',
        'lbPrais'                     => 'LB PRAIS',
        'lbUrinary'                   => 'LB incontinencia urinaria',
        'exitError'                   => 'Error egreso',
        'lbIqOdonto'                  => 'LB IQ odonto',
        'procedureType'               => 'Tipo procedimiento',
        'sename'                      => 'SENAME',
        'referring_specialty'         => 'E_OTOR_AT',
        'elapsed_days'                => 'Días pasados',
    ];

    protected const CHANGE_LOOKUPS = [
        'specialty_id'                => [DemandManagementSpeciality::class, 'text'],
        'minsal_specialty_id'         => [MinsalSpecialty::class, 'code'],
        'exit_minsal_specialty_id'    => [MinsalSpecialty::class, 'code'],
        'sigte_procedure_id'          => [SigteProcedure::class, 'code'],
        'exit_sigte_procedure_id'     => [SigteProcedure::class, 'code'],
        'wait_health_care_service_id' => [DemandManagementHealthCareService::class, 'text'],
        'wait_medical_benefit_id'     => [DemandManagementMedicalBenefit::class, 'text'],
        'waitlist_entry_type_id'      => [WaitlistEntryType::class, 'text'],
        'origin_establishment_id'     => [Organization::class, 'alias'],
        'destiny_establishment_id'    => [Organization::class, 'alias'],
        'establishment_id'            => [Organization::class, 'alias'],
        'commune_id'                  => [Commune::class, 'name'],
        'origin_commune_id'           => [Commune::class, 'name'],
        'region_id'                   => [Region::class, 'name'],
        'healthcare_type_id'          => [HealthcareType::class, 'text'],
        'cie10_id'                    => [Cie10::class, 'code'],
        'requesting_professional_id'  => [User::class, 'text'],
        'resolving_professional_id'   => [User::class, 'text'],
    ];

    protected static array $healthCareServiceCache = [];
    protected static array $specialtyCache = [];
    protected static array $medicalBenefitCache = [];
    protected static array $communeCodeCache = [];
    protected static array $organizationCodeCache = [];
    protected static array $organizationAliasCache = [];
    protected static array $healthcareTypeCache = [];
    protected static array $entryTypeCache = [];
    protected static array $regionCache = [];
    protected static array $originCommuneNameCache = [];
    /** normalized CIE-10 code ("N184") => cie10 id */
    protected static array $cie10Cache = [];
    /** Normalized CIE-10 name => cie10 id, or null when the name is ambiguous. */
    protected static array $cie10NameCache = [];
    /** Normalized CIE-10 names, sorted, for the cut-off name (prefix) search. */
    protected static array $cie10SortedNames = [];
    /** cie10 id => belongs to a GES health problem. */
    protected static array $cie10GesCache = [];
    protected static bool $initialized = false;

    /** Diagnosis of the last imported row, see lastRowDiagnosis(). */
    public const DIAGNOSIS_GES = 'ges';
    public const DIAGNOSIS_NON_GES = 'non_ges';
    public const DIAGNOSIS_NONE = 'none';

    /** A cut-off diagnosis text needs at least this many characters to be matched by prefix. */
    protected const MIN_PREFIX_LENGTH = 20;

    protected ?string $rowDiagnosis = null;
    /** Template header name => slugged key; Str::slug() is too slow to run ~70 times per row. */
    protected static array $slugs = [];

    /**
     * Rows committed together. Each row still runs in its own (nested)
     * transaction, so a failing row is rolled back alone, but committing
     * every row separately made large files exceed max_execution_time.
     */
    public const ROWS_PER_COMMIT = 500;

    public int $successfulRows = 0;
    public array $failedRows = [];

    /** Outcomes returned by importCsvRow(). */
    public const ROW_CREATED = 'created';
    public const ROW_UPDATED = 'updated';
    public const ROW_UNCHANGED = 'unchanged';

    public int $createdRows = 0;
    public int $unchangedRows = 0;
    /** @var array<int, array{row: int, run: ?string, created: bool, changes: string[]}> */
    public array $updatedRows = [];

    /**
     * Rows left out (warnings) or that failed, with the uploaded values, for
     * the rejected rows file.
     *
     * @var array<int, array{row: int, run: ?string, reasons: string[], values: array}>
     */
    public array $rejectedRows = [];

    /** The uploaded file's heading row, as written in the file. */
    public array $headings = [];

    /**
     * Headings (slugged) a sheet needs to be read as the SIGTE template.
     */
    public const REQUIRED_HEADINGS = ['run', 'dv', 'tipo_prest', 'presta_min', 'f_entrada'];

    /** Name of the .xlsx sheet that was imported. */
    public ?string $importedSheet = null;

    /** Other sheets of the .xlsx file, which were not imported. */
    public array $skippedSheets = [];

    /** Raw values of the row being imported, in file column order. */
    protected array $currentValues = [];

    /** "Label: old → new" lines for the row being imported. */
    protected array $rowChanges = [];
    /** Data-quality warnings (PRESTA_MIN code, patient age) for the row being imported. */
    protected array $rowWarnings = [];
    protected bool $rowCreated = false;

    protected static function ensureCachesAreLoaded(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$communeCodeCache = Commune::query()
            ->select('id', 'code_deis')
            ->get()
            ->pluck('id', 'code_deis')
            ->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])
            ->toArray();

        $organizations = Organization::query()
            ->select('id', 'code_deis', 'alias')
            ->get();

        self::$organizationCodeCache = $organizations->pluck('id', 'code_deis')
            ->filter()
            ->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])
            ->toArray();

        self::$organizationAliasCache = $organizations->pluck('id', 'alias')
            ->filter()
            ->mapWithKeys(fn($id, $alias) => [strtolower(trim($alias)) => $id])
            ->toArray();

        self::$healthCareServiceCache = DemandManagementHealthCareService::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        // Free-text ESPECIALIDAD values only; coded (Código MAI) rows are matched by PRESTA_MIN.
        self::$specialtyCache = DemandManagementSpeciality::whereNull('code')->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$medicalBenefitCache = DemandManagementMedicalBenefit::all()->pluck('id', 'text')->mapWithKeys(fn($id, $text) => [strtolower(trim($text)) => $id])->toArray();
        self::$healthcareTypeCache = HealthcareType::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$entryTypeCache = WaitlistEntryType::all()->pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        self::$regionCache = Region::all()->pluck('id', 'id')->mapWithKeys(fn($id, $regionId) => [strtolower(trim($regionId)) => $id])->toArray();

        $cie10 = Cie10::query()->get(['id', 'code', 'name']);

        self::$cie10Cache = $cie10
            ->mapWithKeys(fn($c) => [GesHealthProblem::normalizeCode($c->code) => $c->id])
            ->toArray();

        // Diagnosis texts that are a CIE-10 name. A name shared by several
        // codes is kept only when they are one category (the category code is
        // used, e.g. "COLELITIASIS" → K80); otherwise it is ambiguous.
        self::$cie10NameCache = $cie10
            ->groupBy(fn($c) => self::normalizeDiagnosisText($c->name))
            ->forget('')
            ->map(function ($codes) {
                if ($codes->count() === 1) {
                    return $codes->first()->id;
                }
                $normalized = $codes->map(fn($c) => GesHealthProblem::normalizeCode($c->code));
                if ($normalized->map(fn($code) => substr($code, 0, 3))->unique()->count() !== 1) {
                    return null;
                }

                return $codes->sortBy(fn($c) => strlen(GesHealthProblem::normalizeCode($c->code)))->first()->id;
            })
            ->toArray(); // null = ambiguous name, kept so a prefix matching it is ambiguous too
        self::$cie10SortedNames = array_keys(self::$cie10NameCache);
        sort(self::$cie10SortedNames, SORT_STRING);

        self::$cie10GesCache = [];

        self::$originCommuneNameCache = Commune::query()
            ->select('id', 'name')
            ->get()
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        self::$initialized = true;
    }

    /**
     * Entry point. .xlsx files are streamed with OpenSpout: maatwebsite
     * (PhpSpreadsheet) re-parses the whole file for every chunk, which alone
     * took ~10ms per row on large files. Other formats still go through
     * maatwebsite, which calls onRow() below.
     */
    public function import(string $path): void
    {
        // A queue worker runs many imports in one process: reload the lookups.
        self::$initialized = false;

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'xlsx') {
            Excel::import($this, $path);

            return;
        }

        $options = new XlsxOptions();
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;
        $reader = new XlsxReader($options);
        $reader->open($path);
        $rowsSinceCommit = 0;
        DB::beginTransaction();

        try {
            // Only the first sheet whose heading row is the SIGTE template:
            // SIGTE workbooks also carry summary sheets and older cuts of the
            // list, which must not be imported over the current one.
            foreach ($reader->getSheetIterator() as $sheet) {
                $headings = [];
                $rowNumber = 0;

                foreach ($sheet->getRowIterator() as $sheetRow) {
                    $values = array_map(fn($value) => self::cellToString($value), $sheetRow->toArray());

                    if (++$rowNumber === 1) {
                        $headings = HeadingRowFormatter::format($values);
                        if ($this->importedSheet !== null || array_diff(self::REQUIRED_HEADINGS, $headings)) {
                            $this->skippedSheets[] = $sheet->getName();
                            continue 2;
                        }
                        $this->importedSheet = $sheet->getName();
                        $this->headings = $sheetRow->toArray();
                        continue;
                    }

                    $data = [];
                    foreach ($values as $i => $value) {
                        if (array_key_exists($i, $headings)) {
                            $data[$headings[$i]] = $value;
                        } else {
                            $data[] = $value;
                        }
                    }

                    $this->currentValues = $sheetRow->toArray();
                    $this->handleRow($data, $rowNumber);

                    if (++$rowsSinceCommit === self::ROWS_PER_COMMIT) {
                        DB::commit();
                        DB::beginTransaction();
                        $rowsSinceCommit = 0;
                    }
                }
            }
        } finally {
            // Rows already handled are kept, as when each row was committed.
            DB::commit();
            $reader->close();
        }

        if ($this->importedSheet === null) {
            throw new \RuntimeException('El archivo no tiene una hoja con el formato SIGTE (columnas ' . strtoupper(implode(', ', self::REQUIRED_HEADINGS)) . ').');
        }
    }

    /**
     * Cell values as maatwebsite's read-only import yields them: plain
     * strings, with dates as Excel serial numbers (parseFlexibleDate and
     * parseDateTime handle those).
     */
    protected static function cellToString(mixed $value): ?string
    {
        return match (true) {
            $value === null, $value === '' => null,
            $value instanceof \DateTimeInterface => (string) Date::PHPToExcel($value),
            $value instanceof \DateInterval => $value->format('%H:%I:%S'),
            is_bool($value) => $value ? 'TRUE' : 'FALSE',
            default => (string) $value,
        };
    }

    public function onRow(Row $row): void
    {
        // maatwebsite only exposes the slugged headings.
        $data = $row->toArray();
        $this->headings = $this->headings ?: array_keys($data);
        $this->currentValues = array_values($data);

        $this->handleRow($data, $row->getIndex());
    }

    /** Lookups are reloaded on the next row (each queued chunk starts fresh). */
    public static function forgetCaches(): void
    {
        self::$initialized = false;
    }

    /**
     * One row of a CSV uploaded with Filament's Import action
     * (DemandManagementWaitlistImporter), keyed by the CSV headers as written.
     * Runs inside the transaction the Import action opens for each row.
     *
     * @return string|null what the row did: self::ROW_CREATED (new case),
     *         self::ROW_UPDATED (existing case or patient data changed),
     *         self::ROW_UNCHANGED (case already there with the same data, e.g. a
     *         row repeated in the file) or null for a fully empty row
     *
     * @throws RowRejectedException when the row is left out
     */
    public function importCsvRow(array $row): ?string
    {
        self::ensureCachesAreLoaded();

        $data = array_combine(HeadingRowFormatter::format(array_keys($row)), array_values($row));
        $data = array_map(fn($value) => is_string($value) ? trim($value) : $value, $data);

        if ($missing = array_diff(self::REQUIRED_HEADINGS, array_keys($data))) {
            throw new RowRejectedException(['El archivo no tiene el formato SIGTE: faltan las columnas ' . strtoupper(implode(', ', $missing)) . '.']);
        }

        // Fully empty rows (trailing formatted rows of the template).
        if (collect($data)->filter(fn($value) => filled($value))->isEmpty()) {
            return null;
        }

        $this->currentValues = array_values($row);
        $this->importRow($data);

        return match (true) {
            $this->rowCreated => self::ROW_CREATED,
            (bool) $this->rowChanges => self::ROW_UPDATED,
            default => self::ROW_UNCHANGED,
        };
    }

    protected function handleRow(array $data, int $rowIndex): void
    {
        self::ensureCachesAreLoaded();

        $data = array_map(fn($value) => is_string($value) ? trim($value) : $value, $data);

        // Skip fully empty rows (trailing formatted rows in the template).
        if (collect($data)->filter(fn($value) => filled($value))->isEmpty()) {
            return;
        }

        try {
            $this->importRow($data);
            $this->successfulRows++;

            $run = filled($data['run'] ?? null) ? $data['run'] . '-' . ($data['dv'] ?? '') : null;

            if ($this->rowCreated) {
                $this->createdRows++;
            }
            // A new case for an existing patient can still update the patient's data.
            if ($this->rowChanges) {
                $this->updatedRows[] = ['row' => $rowIndex, 'run' => $run, 'created' => $this->rowCreated, 'changes' => $this->rowChanges];
            } elseif (! $this->rowCreated) {
                $this->unchangedRows++;
            }
        } catch (RowRejectedException $e) {
            $this->rejectRow($data, $rowIndex, $e->reasons);
        } catch (\Throwable $e) {
            $this->failedRows[] = ['row' => $rowIndex, 'error' => $e->getMessage()];
            $this->rejectRow($data, $rowIndex, ['Error: ' . $e->getMessage()]);
            Log::error('DemandManagementWaitlistImport: error en fila ' . $rowIndex, [
                'sigte_id' => $data['sigte_id'] ?? null,
                'run'      => $data['run'] ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    protected function rejectRow(array $data, int $rowIndex, array $reasons): void
    {
        $this->rejectedRows[] = [
            'row'     => $rowIndex,
            'run'     => filled($data['run'] ?? null) ? $data['run'] . '-' . ($data['dv'] ?? '') : null,
            'reasons' => $reasons,
            'values'  => $this->currentValues,
        ];
    }

    protected function importRow(array $data): DemandManagementWaitlist
    {
        $this->rowChanges = [];
        $this->rowWarnings = [];
        $this->rowCreated = false;
        $this->rowDiagnosis = null;

        return DB::transaction(function () use ($data) {
            // Headings arrive slugged by WithHeadingRow ("TIPO PRESTACION" → "tipo_prestacion",
            // "AÑO" → "ano"), so look keys up by the template's original header name.
            $val = function ($key, $default = null) use ($data) {
                $value = $data[self::$slugs[$key] ??= Str::slug($key, '_')] ?? null;

                return ($value === null || $value === '') ? $default : $value;
            };

            // Checked before anything is written. PRESTA_MIN is a procedure
            // code when TIPO_PREST = 3, a Código MAI from the Gestión Demanda
            // specialty catalog when TIPO_PREST = 4, otherwise a specialty in the current
            // or the old codification; the patient's age at F_ENTRADA picks
            // between specialties sharing an old code and must fit the
            // specialty's range. Rows with any warning are left out.
            $age = MinsalSpecialty::ageInYears($this->parseFlexibleDate($val('FECHA_NAC')), $this->parseFlexibleDate($val('F_ENTRADA')));
            [$minsalSpecialty, $minsalExitSpecialty, $sigteProcedure, $exitSigteProcedure, $maiSpecialty, $exitMaiSpecialty] = $this->resolvePrestaMin($val('PRESTA_MIN'), $val('PRESTA_MIN_SALIDA'), $val('TIPO_PREST'), $age);
            // CIE-10 diagnosis and GES flag (see resolveCie10AndGes()).
            $cie10AndGes = $this->resolveCie10AndGes($data, $val);
            if ($this->rowWarnings) {
                throw new RowRejectedException($this->rowWarnings);
            }

            $text = strtolower(trim($val('PRESTA_EST', '')));
            $healthCareServiceId = self::$healthCareServiceCache[$text] ?? null;
            if (is_null($healthCareServiceId) && !empty($text)) {
                $healthCareService = DemandManagementHealthCareService::firstOrCreate(['text' => $text]);
                self::$healthCareServiceCache[$text] = $healthCareService->id;
                $healthCareServiceId = $healthCareService->id;
            }

            $runValue = $val('RUN');
            $user = null;
            if (!empty($runValue)) {
                $user = User::whereHas(
                    'identifiers',
                    fn($query) =>
                    $query->where('value', $runValue)->where('cod_con_identifier_type_id', 1)
                )->first();
            }

            $sexoRaw = trim((string) $val('SEXO', ''));
            $sexoCode = is_numeric($sexoRaw) ? (string) intval($sexoRaw) : $sexoRaw;
            $sexValue = self::SEX_CODE_MAP[$sexoCode] ?? null;
            $isNewUser = is_null($user);

            // fill()/save() on the already-loaded models below instead of
            // updateOrCreate(['id' => ...]), which re-selects them by id.
            $userCreatedOrUpdated = ($user ?? new User())->fill([
                'active'            => 1,
                'text'              => $val('NOMBRES') . ' ' . $val('PRIMER_APELLIDO') . ' ' . $val('SEGUNDO_APELLIDO'),
                'given'             => $val('NOMBRES'),
                'fathers_family'    => $val('PRIMER_APELLIDO'),
                'mothers_family'    => $val('SEGUNDO_APELLIDO'),
                'birthday'          => $this->parseFlexibleDate($val('FECHA_NAC')),
                'sex'               => $sexValue,
            ]);
            // F_DEFUNCION also marks the patient as deceased (never cleared by an upload).
            if ($deceasedAt = $this->parseFlexibleDate($val('F_DEFUNCION'))) {
                $userCreatedOrUpdated->deceased_datetime = $deceasedAt;
            }
            $userCreatedOrUpdated->save();
            if (! $isNewUser) {
                $this->recordChanges($userCreatedOrUpdated, self::USER_CHANGE_LABELS);
            }

            if ($isNewUser && !empty($runValue)) {
                // SE CREA IDENTIFIER
                Identifier::create([
                    'user_id'                 => $userCreatedOrUpdated->id,
                    'use'                     => 'official',
                    'cod_con_identifier_type_id'  => 1,
                    'value'                   => $runValue,
                    'dv'                      => $val('DV')
                ]);

                //SE CREA HUMAN NAME
                HumanName::create([
                    'use'              => 'official',
                    'given'            => $val('NOMBRES'),
                    'fathers_family'   => $val('PRIMER_APELLIDO'),
                    'mothers_family'   => $val('SEGUNDO_APELLIDO'),
                    'period_start'     => now(),
                    'user_id'          => $userCreatedOrUpdated->id
                ]);
            }

            $communeId = self::$communeCodeCache[strtolower(trim($val('COMUNA', '')))] ?? null;
            $originOrganizationId = self::$organizationCodeCache[strtolower(trim($val('ESTAB_ORIG', '')))] ?? null;
            $destinyOrganizationId = self::$organizationCodeCache[strtolower(trim($val('ESTAB_DEST', '')))] ?? null;

            $alias = strtolower(trim($val('ESTABLECIMIENTO', 'sin establecimiento')));
            $organizationId = self::$organizationAliasCache[$alias] ?? null;

            $previsionCode = strtolower(trim($val('PREVISION', '')));
            $previsionValue = self::$healthcareTypeCache[$previsionCode] ?? null;

            $entryType = self::$entryTypeCache[strtolower(trim($val('TIPO_PREST', '')))] ?? null;

            $region = self::$regionCache[strtolower(trim($val('REGION', '')))] ?? null;


            $address = $userCreatedOrUpdated->addresses()->where('use', 'home')->first()
                ?? new Address(['user_id' => $userCreatedOrUpdated->id]);

            $address->fill([
                    'use'           => 'home',
                    'type'          => 'physical',
                    'text'          => $val('NOM_CALLE'),
                    'line'          => $val('NUM_DIRECCION'),
                    'suburb'        => $val('RESTO_DIRECCION'),
                    'city'          => $val('CIUDAD'),
                    'commune_id'    => $communeId,
                    'is_rural'      => match ((string) $val('COND_RURALIDAD')) {
                        '1' => false,
                        '2' => true,
                        default => null,
                    },
                    'via'           => match ((string) $val('VIA_DIRECCION')) {
                        '1' => 'calle',
                        '2' => 'pasaje',
                        '3' => 'avenida',
                        '4' => 'otro',
                        default => null,
                    },
            ]);
            $address->save();
            if (! $isNewUser) {
                $this->recordChanges($address, self::ADDRESS_CHANGE_LABELS);
            }

            $contactPoints = $userCreatedOrUpdated->contactPoints()->whereIn('use', ['home', 'mobile', 'work'])->get();
            foreach ([
                ['phone', 'home', $val('FONO_FIJO'), 'Teléfono fijo'],
                ['phone', 'mobile', $val('FONO_MOVIL'), 'Teléfono móvil'],
                ['email', 'work', $val('EMAIL'), 'Email'],
            ] as [$system, $use, $value, $label]) {
                // The existing point for this use is only updated when its
                // system matches too; otherwise a new one is created.
                $contactPoint = $contactPoints->firstWhere('use', $use);
                if (! $contactPoint || $contactPoint->system !== $system) {
                    $contactPoint = new ContactPoint(['user_id' => $userCreatedOrUpdated->id, 'system' => $system, 'use' => $use]);
                }
                $oldValue = $contactPoint->value;
                $contactPoint->fill(['value' => $value, 'actually' => 0])->save();
                if (! $isNewUser && (string) $oldValue !== (string) $value) {
                    $this->rowChanges[] = $label . ': ' . $this->formatChangeValue('', $oldValue) . ' → ' . $this->formatChangeValue('', $value);
                }
            }


            // TIPO_PREST 4: the specialty is the PRESTA_MIN Código MAI;
            // otherwise the free-text ESPECIALIDAD column.
            $textSpecialty = strtolower(trim($val('ESPECIALIDAD', 'sin especialidad')));
            $specialtyId = $maiSpecialty ?? self::$specialtyCache[$textSpecialty] ?? null;
            if (is_null($specialtyId)) {
                $specialty = DemandManagementSpeciality::firstOrCreate(['text' => $textSpecialty, 'code' => null]);
                self::$specialtyCache[$textSpecialty] = $specialty->id;
                $specialtyId = $specialty->id;
            }

            $profSolCreatedOrUpdated = $this->findOrCreateProfessional($val('RUN_PROF_SOL'), $val('DV_PROF_SOL'));
            $profResolCreatedOrUpdated = $this->findOrCreateProfessional($val('RUN_PROF_RESOL'), $val('DV_PROF_RESOL'));


            $textMedicalBenefits = strtolower(trim($val('TIPO PRESTACION', '')));
            $medicalBenefitId = self::$medicalBenefitCache[$textMedicalBenefits] ?? null;
            if (is_null($medicalBenefitId)) {
                $medicalBenefits = DemandManagementMedicalBenefit::firstOrCreate(['text' => $textMedicalBenefits]);
                self::$medicalBenefitCache[$textMedicalBenefits] = $medicalBenefits->id;
                $medicalBenefitId = $medicalBenefits->id;
            }

            $originCommuneId = self::$originCommuneNameCache[strtolower(trim($val('Comuna Origen', '')))] ?? null;

            // Estado: the ESTADO column when the file has one; otherwise
            // "egresado" for a discharged case (F_SALIDA + C_SALIDA other than
            // 3 / 20, as the Cerrados tab), keeping the status set by hand for
            // open cases (an "egresado" case that is open again goes back to none).
            // A death date (F_DEFUNCION) always wins: the case is "fallecido".
            $exitDate = $this->parseFlexibleDate($val('F_SALIDA'));
            $exitCode = filled($val('C_SALIDA')) ? trim($val('C_SALIDA')) : null;
            $deathDate = $this->parseFlexibleDate($val('F_DEFUNCION'));
            if ($deathDate) {
                $status = ['status' => 'fallecido'];
            } elseif (array_key_exists('estado', $data)) {
                $status = ['status' => ($val('ESTADO') !== null) ? strtolower(trim($val('ESTADO'))) : null];
            } elseif (DemandManagementWaitlist::isDischarged($exitDate, $exitCode)) {
                $status = ['status' => 'egresado'];
            } else {
                $currentStatus = DemandManagementWaitlist::query()
                    ->where('user_id', $userCreatedOrUpdated->id)
                    ->where('sigte_id', $val('SIGTE_ID'))
                    ->value('status');
                $status = $currentStatus === 'egresado' ? ['status' => null] : [];
            }

            $waitlistCreatedOrUpdated = DemandManagementWaitlist::updateOrCreate(
                [
                    'user_id'                => $userCreatedOrUpdated->id,
                    'sigte_id'               => $val('SIGTE_ID'),
                ],
                [
                    'specialty_id'              => $specialtyId,
                    'exit_specialty_id'         => $exitMaiSpecialty,
                    'origin_establishment_id'   => $originOrganizationId,
                    'wait_health_care_service_id' => $healthCareServiceId,
                    'commune_id'                => $communeId,
                    'destiny_establishment_id'  => $destinyOrganizationId,
                    'suspected_diagnosis'       => ($val('SOSPECHA_DIAG') !== null) ? strtolower(trim($val('SOSPECHA_DIAG'))) : null,
                    'establishment_id'          => $organizationId,
                    'entry_date'                => $this->parseFlexibleDate($val('F_ENTRADA')),
                    'exit_date'                 => $exitDate,
                    'healthcare_type_id'        => $previsionValue,
                    'minsal_specialty_id'       => $minsalSpecialty,
                    'exit_minsal_specialty_id'  => $minsalExitSpecialty,
                    'sigte_procedure_id'        => $sigteProcedure,
                    'exit_sigte_procedure_id'   => $exitSigteProcedure,
                    'plano'                     => ($val('PLANO') !== null) ? strtolower(trim($val('PLANO'))) : null,
                    'extremity'                 => ($val('EXTREMIDAD') !== null) ? strtolower(trim($val('EXTREMIDAD'))) : null,
                    'prais'                     => ($val('PRAIS') !== null) ? strtolower(trim($val('PRAIS'))) : null,
                    'region_id'                 => $region,
                    'pediatric'                 => $val('PEDIATRICO'),
                    'lb'                        => ($val('LB') !== null) ? strtolower(trim($val('LB'))) : null,
                    'requesting_professional_id' => optional($profSolCreatedOrUpdated)->id,
                    'resolving_professional_id' => optional($profResolCreatedOrUpdated)->id,
                    'waitlist_entry_type_id'    => $entryType,
                    'local_id'                  => $val('ID_LOCAL'),
                    'result'                    => $val('RESULTADO'),
                    'waitlistAge'               => $val('EDAD') ? floatval(str_replace(',', '.', $val('EDAD'))) : null,
                    'waitlistYear'              => $val('AÑO'),
                    'health_service_id'         => $val('SERV_SALUD'),
                    'appointment_date'          => $this->parseDateTime($val('F_CITACION')),
                    'worker'                    => $val('FUNCIONARIO'),
                    'iqType'                    => $val('Tipo de IQ'),
                    'oncologic'                 => $val('Oncologico'),
                    'origin_commune_id'         => $originCommuneId,
                    'fonasa'                    => $val('FONASA'),
                    'praisUser'                 => $val('Usuario PRAIS'),
                    'lbPrais'                   => $val('LB PRAIS'),
                    'lbUrinary'                 => $val('LB INCONTINENCIA URINARIA'),
                    'exitError'                 => $val('Error Egreso'),
                    'lbIqOdonto'                => $val('LB IQ ODONTO'),
                    'procedureType'             => $val('Tipo Procedimiento'),
                    'sename'                    => $val('SENAME'),
                    'exit_code'                 => $exitCode,
                    'referring_specialty'       => $val('E_OTOR_AT'),
                    'wait_medical_benefit_id'   => $medicalBenefitId,
                    'elapsed_days'              => $val('DIAS_PASADOS'),
                ]
                + $status
                + $cie10AndGes
            );

            $isNewWaitlist = $waitlistCreatedOrUpdated->wasRecentlyCreated;
            $this->rowCreated = $isNewWaitlist;
            if (! $isNewWaitlist) {
                $this->recordChanges($waitlistCreatedOrUpdated, self::WAITLIST_CHANGE_LABELS);
            }

            // The upload is logged under "Cargas" only, never as a contact.
            $text = null;
            if ($isNewWaitlist || ! $waitlistCreatedOrUpdated->events()->exists()) {
                $text = 'Registrado a través de carga masiva'
                    . ($this->rowChanges ? "\nDatos actualizados:\n- " . implode("\n- ", $this->rowChanges) : '');
            } elseif ($this->rowChanges) {
                $text = "Actualizado a través de carga masiva:\n- " . implode("\n- ", $this->rowChanges);
            }

            if ($text) {
                $waitlistCreatedOrUpdated->events()->create([
                    'status'            => ($val('ESTADO') ?? null) ? strtolower(trim($val('ESTADO'))) : null,
                    'registered_at'     => now(),
                    'text'              => $text,
                    'discharge'         => strtolower(trim($val('CAUSAL_EGRESO') ?? '')),
                    'appointment_at'    => $this->parseDateTime($val('FECHA_ATENCION')),
                    'register_user_id'  => auth()->id() ?? User::first()?->id // Fallback if no user is authenticated
                ]);
            }

            return $waitlistCreatedOrUpdated;
        });
    }

    protected function findOrCreateProfessional($run, $dv): ?User
    {
        $run = trim((string) $run);
        if (empty($run)) {
            return null;
        }

        $user = User::whereHas(
            'identifiers',
            fn($query) =>
            $query->where('value', $run)->where('cod_con_identifier_type_id', 1)
        )->first();

        if ($user) {
            $user->update(['active' => 1]);
        } else {
            $user = User::create(['active' => 1]);
            Identifier::create([
                'user_id'                  => $user->id,
                'use'                      => 'official',
                'cod_con_identifier_type_id' => 1,
                'value'                    => $run,
                'dv'                       => $dv,
            ]);
        }

        return $user;
    }

    /**
     * cie10_id / ges attributes for the row, adding a row warning when an
     * explicit code isn't a CIE-10 code or GES = Sí doesn't fit it.
     *
     * The CIE-10 comes from the "CIE-10" column or a CONFIR_DIAG /
     * SOSPECHA_DIAG cell that is just a code (an unknown code rejects the
     * row), or else from the diagnosis text (CONFIR_DIAG first), which never
     * rejects (see cie10FromDiagnosisText()).
     *
     * GES comes from the GES column when the file has one; otherwise it
     * follows the diagnosis (Sí when the CIE-10 belongs to a GES health
     * problem). The GES problem's age and clinical criteria are not checked.
     * Neither field is overwritten when the file doesn't provide it.
     */
    protected function resolveCie10AndGes(array $data, \Closure $val): array
    {
        $attributes = [];
        $cie10Id = null;

        $code = $val('CIE-10');
        if ($code === null) {
            $code = collect([$val('CONFIR_DIAG'), $val('SOSPECHA_DIAG')])
                ->first(fn ($text) => preg_match('/^[A-Z]\d{2}(\.?[0-9X]{1,2})?$/i', trim((string) $text)));
        }

        if (filled($code)) {
            $cie10Id = self::$cie10Cache[GesHealthProblem::normalizeCode($code)] ?? null;
            if (! $cie10Id) {
                $this->rowWarnings[] = 'CIE-10: ' . trim($code) . ' no existe en el catálogo CIE-10.';
            }
        } else {
            $cie10Id = self::cie10FromDiagnosisText($val('CONFIR_DIAG')) ?? self::cie10FromDiagnosisText($val('SOSPECHA_DIAG'));
        }

        $isGesDiagnosis = $cie10Id ? self::isGesDiagnosis($cie10Id) : null;
        if ($cie10Id) {
            $attributes['cie10_id'] = $cie10Id;
        }
        $this->rowDiagnosis = match ($isGesDiagnosis) {
            true => self::DIAGNOSIS_GES,
            false => self::DIAGNOSIS_NON_GES,
            null => self::DIAGNOSIS_NONE,
        };

        if (array_key_exists('ges', $data)) {
            $attributes['ges'] = $this->parseYesNo($val('GES'));
            if ($attributes['ges'] && $isGesDiagnosis === false) {
                $this->rowWarnings[] = 'GES = Sí, pero el CIE-10 ' . Cie10::find($cie10Id)?->code . ' no pertenece a ningún problema de salud GES.';
            }
        } elseif ($isGesDiagnosis !== null) {
            // No GES column: GES follows the diagnosis.
            $attributes['ges'] = $isGesDiagnosis;
        }

        return $attributes;
    }

    /**
     * CIE-10 id from a free-text diagnosis, or null. In order:
     *  1. a code at the start ("H11.0 PTERIGION" → H11.0), if in the catalog;
     *  2. the exact name of a CIE-10 code ("COLELITIASIS" → K80);
     *  3. a cut-off name (SIGTE truncates the text) that starts exactly one
     *     CIE-10 name, with at least MIN_PREFIX_LENGTH characters.
     * Accents, case and punctuation are ignored. Texts that name a
     * prestación instead of a diagnosis ("ECOGRAFIA RENAL") get none.
     */
    public static function cie10FromDiagnosisText(?string $text): ?int
    {
        self::ensureCachesAreLoaded();

        $text = trim((string) $text);
        if ($text === '' || $text === '-' || $text === "'-") {
            return null;
        }

        if (preg_match('/^([A-Z]\d{2}(?:\.?[0-9X]{1,2})?)\b/i', $text, $m)
            && ($id = self::$cie10Cache[GesHealthProblem::normalizeCode($m[1])] ?? null)) {
            return $id;
        }

        $name = self::normalizeDiagnosisText($text);
        if ($name === '') {
            return null;
        }
        if (array_key_exists($name, self::$cie10NameCache)) {
            return self::$cie10NameCache[$name]; // null when ambiguous
        }
        if (strlen($name) < self::MIN_PREFIX_LENGTH) {
            return null;
        }

        // Names starting with $name: a contiguous range of the sorted list.
        $names = self::$cie10SortedNames;
        $low = 0;
        $high = count($names);
        while ($low < $high) {
            $mid = intdiv($low + $high, 2);
            $names[$mid] < $name ? $low = $mid + 1 : $high = $mid;
        }
        $first = $names[$low] ?? null;
        if ($first === null || ! str_starts_with($first, $name)) {
            return null;
        }
        $second = $names[$low + 1] ?? null;
        if ($second !== null && str_starts_with($second, $name)) {
            return null; // several names start like this
        }

        return self::$cie10NameCache[$first];
    }

    public static function isGesDiagnosis(int $cie10Id): bool
    {
        return self::$cie10GesCache[$cie10Id] ??= GesHealthProblem::forCie10Code(
            array_search($cie10Id, self::$cie10Cache, true) ?: null
        )->isNotEmpty();
    }

    protected static function normalizeDiagnosisText(?string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]+/', ' ', Str::lower(Str::ascii((string) $text)))));
    }

    /**
     * self::DIAGNOSIS_GES / DIAGNOSIS_NON_GES / DIAGNOSIS_NONE for the last
     * row imported by importCsvRow().
     */
    public function lastRowDiagnosis(): ?string
    {
        return $this->rowDiagnosis;
    }

    /**
     * PRESTA_MIN / PRESTA_MIN_SALIDA ids. With TIPO_PREST = 3 both are
     * procedure codes (SIGTE procedure catalog); with TIPO_PREST = 4 both
     * are Códigos MAI (Gestión Demanda specialty catalog); otherwise MINSAL
     * specialties.
     * Adds a row warning for unknown codes, ambiguous old codes, and a
     * patient age outside PRESTA_MIN's specialty range.
     *
     * @return array{0: ?int, 1: ?int, 2: ?int, 3: ?int, 4: ?int, 5: ?int} specialty, exit specialty, procedure, exit procedure, MAI specialty, exit MAI specialty
     */
    protected function resolvePrestaMin(?string $code, ?string $exitCode, ?string $entryTypeCode, ?float $age): array
    {
        $isProcedure = trim((string) $entryTypeCode) === SigteProcedure::ENTRY_TYPE_CODE;
        $isMai = trim((string) $entryTypeCode) === DemandManagementSpeciality::ENTRY_TYPE_CODE;
        $specialtyIds = [];
        $procedureIds = [];
        $maiIds = [];
        foreach (['PRESTA_MIN' => $code, 'PRESTA_MIN_SALIDA' => $exitCode] as $column => $value) {
            if ($isMai) {
                $mai = DemandManagementSpeciality::forCode($value);
                if (filled($value) && ! $mai) {
                    $this->rowWarnings[] = "{$column}: " . trim($value) . ' no existe en el catálogo de códigos MAI (TIPO_PREST 4).';
                }
                $specialtyIds[] = null;
                $procedureIds[] = null;
                $maiIds[] = $mai?->id;
                continue;
            }
            $maiIds[] = null;

            if ($isProcedure) {
                $procedure = SigteProcedure::forCode($value);
                if (filled($value) && ! $procedure) {
                    $this->rowWarnings[] = "{$column}: " . trim($value) . ' no existe en el catálogo de procedimientos SIGTE (TIPO_PREST 3).';
                }
                $specialtyIds[] = null;
                $procedureIds[] = $procedure?->id;
                continue;
            }

            [$specialty, $warning] = MinsalSpecialty::resolve($value, $age);
            if (filled($value) && ! $specialty) {
                $warning = trim($value) . match (true) {
                    SigteProcedure::forCode($value) !== null => ' es un código de procedimiento; para procedimientos TIPO_PREST debe ser 3.',
                    DemandManagementSpeciality::forCode($value) !== null => ' es un código MAI de intervención quirúrgica; para estas TIPO_PREST debe ser 4.',
                    default => ' no existe en el catálogo de especialidades.',
                };
            }
            if ($warning) {
                $this->rowWarnings[] = "{$column}: {$warning}";
            }
            if ($column === 'PRESTA_MIN' && ($ageWarning = $specialty?->ageWarning($age))) {
                $this->rowWarnings[] = $ageWarning;
            }
            $specialtyIds[] = $specialty?->id;
            $procedureIds[] = null;
        }

        return [...$specialtyIds, ...$procedureIds, ...$maiIds];
    }

    /**
     * Adds a "Label: old → new" line for each labeled attribute the last
     * save() changed. A model that save() just created counts every filled
     * attribute as changed from empty.
     */
    protected function recordChanges(Model $model, array $labels): void
    {
        $changes = $model->wasRecentlyCreated
            ? array_filter($model->getAttributes(), fn ($value) => filled($value))
            : $model->getChanges();
        $previous = $model->wasRecentlyCreated ? [] : $model->getPrevious();

        foreach ($labels as $key => $label) {
            if (! array_key_exists($key, $changes)) {
                continue;
            }
            $old = $this->formatChangeValue($key, $previous[$key] ?? null);
            $new = $this->formatChangeValue($key, $changes[$key]);
            if ($old !== $new) {
                $this->rowChanges[] = "$label: $old → $new";
            }
        }
    }

    /**
     * Raw attribute value as shown in the change list: lookup ids by name,
     * dates as d-m-Y, flags as Sí/No.
     */
    protected function formatChangeValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '(vacío)';
        }

        if (isset(self::CHANGE_LOOKUPS[$key])) {
            [$class, $attribute] = self::CHANGE_LOOKUPS[$key];

            return (string) ($class::find($value)?->{$attribute} ?? $value);
        }

        return match (true) {
            in_array($key, ['ges', 'is_rural'], true) => $value ? 'Sí' : 'No',
            $key === 'sex' => Sex::tryFrom($value)?->getLabel() ?? (string) $value,
            $key === 'exit_code' => $value . ' - ' . (DemandManagementWaitlist::EXIT_CODE_LABELS[$value] ?? '?'),
            in_array($key, ['birthday', 'deceased_datetime', 'entry_date', 'exit_date', 'appointment_date'], true) => Carbon::parse($value)->format('d-m-Y'),
            default => (string) $value,
        };
    }

    /**
     * "Sí"/"Si"/"S"/"1"/"TRUE"/"X" (any case) count as yes; anything else,
     * including an empty cell, as no.
     */
    protected function parseYesNo($value): bool
    {
        $value = Str::lower(Str::ascii(trim((string) $value)));

        return in_array($value, ['si', 's', '1', 'true', 'x', 'yes'], true);
    }

    /**
     * SIGTE exports have used both "d/m/Y" and "d-m-Y" for date columns
     * depending on the source, and .xlsx files may hold real date cells
     * (Excel serial numbers). Return null instead of throwing on an
     * unparseable value so one bad date doesn't fail the whole row.
     */
    protected function parseFlexibleDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
        }

        $value = trim((string) $value);
        $format = str_contains($value, '/') ? 'd/m/Y' : 'd-m-Y';

        try {
            return Carbon::createFromFormat($format, $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseDateTime($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d H:i:s');
        }

        $timestamp = strtotime((string) $value);

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
