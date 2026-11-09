<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Address;
use App\Models\Commune;
use App\Models\ContactPoint;
use App\Models\Country;
use App\Models\Gender;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Sex;
use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

abstract class BasePatientImport implements ToModel, WithHeadingRow, WithChunkReading, WithEvents, ShouldQueue
{
    protected string $date_format = 'Y-m-d';

    protected static ?array $sexCache = null;
    protected static ?array $genderCache = null;
    protected static ?array $countriesCache = null;
    protected static $communesCache = null;
    protected static ?array $organizationsCache = null;

    protected static int $insertedCount = 0;
    protected static int $updatedCount = 0;
    protected static int $skippedCount = 0;
    protected static int $errorCount = 0;
    protected static int $currentRow = 0;

    public function ensureBaseCachesAreLoaded(): void
    {
        if (self::$sexCache !== null) {
            return;
        }

        Log::info('Cargando cachés base en el Worker (' . static::class . ')...');
        self::$sexCache           = Sex::pluck('value', 'text')->toArray();
        self::$genderCache        = Gender::pluck('value', 'text')->toArray();
        self::$countriesCache     = Country::pluck('id', 'name')->toArray();
        self::$communesCache      = Commune::get()->keyBy('name');
        self::$organizationsCache = Organization::pluck('id', 'code_deis')->toArray();
    }

    public function getAddress(array $row, User $user): Address
    {
        $calle        = $row['calle'];
        $numero       = $row['numero'];
        $departamento = $row['departamento'];
        $comuna       = ucfirst((string) $row['comuna']);

        $addressExist = $user->addresses()->where('use', 'home')->first();

        $communeData = self::$communesCache[$comuna] ?? null;

        $address = Address::updateOrCreate(
            ['id' => $addressExist?->id],
            [
                'user_id'    => $user->id,
                'use'        => 'home',
                'type'       => 'physical',
                'text'       => $calle,
                'line'       => $numero,
                'apartment'  => $departamento,
                'commune_id' => $communeData?->id,
                'region_id'  => $communeData?->region_id,
            ]
        );

        if ($calle && $numero && $comuna && !$address->location) {
            try {
                $geocodingService = app(GeocodingService::class);
                $coordinates = $geocodingService->getCoordinates($calle . '+' . $numero . '+' . $comuna);

                if (!empty($coordinates['lat']) && !empty($coordinates['lng'])) {
                    Location::create([
                        'address_id' => $address->id,
                        'longitude'  => $coordinates['lng'],
                        'latitude'   => $coordinates['lat'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Error obteniendo coordenadas', ['address' => $address->id]);
            }
        }

        $this->getContactPoint($row, $user, $address);

        return $address;
    }

    public function getContactPoint(array $row, User $user, ?Address $address = null): ContactPoint
    {
        $telefono        = $row['telefono'];
        $establecimiento = $row['establecimiento'];

        $organization_id = preg_replace("/[^0-9]/", '', (string) $establecimiento);
        $organization_id = self::$organizationsCache[$organization_id] ?? null;

        $contactPoint = $user->contactPoints()->latest()->first();

        return ContactPoint::updateOrCreate(
            ['id' => $contactPoint?->id],
            [
                'system'          => 'phone',
                'user_id'         => $user->id,
                'location_id'     => $address?->location?->id,
                'value'           => $telefono,
                'organization_id' => $organization_id,
                'use'             => 'mobile',
                'actually'        => 0,
            ]
        );
    }

    public function formatField($value, string $type)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        switch ($type) {
            case 'boolean':
                $value = strtolower(trim((string) $value));
                return $value === 'si' || $value === 'ok' ? true : ($value === 'no' || $value === 'p' ? false : null);

            case 'integer':
                $clean = intval(trim((string) $value));
                return $clean != 0 ? strval($clean) : null;

            case 'date':
                try {
                    $value = intval(trim((string) $value));
                    if ($value == 0) {
                        return null;
                    }
                    return Date::excelToDateTimeObject($value)->format($this->date_format);
                } catch (\Exception $e) {
                    Log::warning('Error parseando fecha', ['value' => $value]);
                    return null;
                }

            case 'text':
            default:
                return trim((string) $value);
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }
}
