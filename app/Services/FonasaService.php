<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FONASA "Certificador Previsional" lookups through the WSSSI proxy (the same
 * service ionline uses): GET {WSSSI_URL}/fonasa?run=X&dv=Y returns JSON.
 */
class FonasaService
{
    /** Keys the proxy may use for FONASA's beneficiarioTO.fechaFallecimiento. */
    private const DEATH_DATE_KEYS = ['fechaFallecimiento', 'fecha_fallecimiento', 'deceased_datetime', 'deceased_at'];

    public static function isConfigured(): bool
    {
        return filled(config('services.wsssi.url'));
    }

    /**
     * The beneficiary as returned by the proxy, or ['error' => ...].
     */
    public function find(string|int $run, ?string $dv = null): array
    {
        $run = (int) preg_replace('/\D/', '', (string) $run);
        $dv = strtoupper(trim((string) $dv)) ?: self::dv($run);

        if ($run <= 0 || $dv !== self::dv($run)) {
            return ['error' => 'Run inválido'];
        }
        if (! self::isConfigured()) {
            return ['error' => 'WSSSI_URL no está configurado'];
        }

        try {
            $response = Http::timeout((int) config('services.wsssi.timeout', 15))
                ->retry(2, 500, throw: false)
                ->get(rtrim(config('services.wsssi.url'), '/') . '/fonasa', ['run' => $run, 'dv' => $dv]);
        } catch (\Throwable $e) {
            Log::warning('FONASA lookup failed', ['run' => $run, 'error' => $e->getMessage()]);

            return ['error' => 'No se pudo conectar a FONASA'];
        }

        $data = $response->json();
        if (! $response->successful() || ! is_array($data)) {
            return ['error' => 'Respuesta inválida de FONASA (HTTP ' . $response->status() . ')'];
        }
        if (isset($data['message']) && ! isset($data['run'])) {
            return ['error' => (string) $data['message']];
        }

        return $data;
    }

    /** Whether the proxy's answer carries a death-date field at all (filled or not). */
    public static function hasDeathDateField(array $beneficiary): bool
    {
        foreach (self::DEATH_DATE_KEYS as $key) {
            if (array_key_exists($key, $beneficiary)) {
                return true;
            }
        }

        return false;
    }

    /** The death date (Y-m-d) in a proxy answer, or null. */
    public static function deathDate(array $beneficiary): ?string
    {
        foreach (self::DEATH_DATE_KEYS as $key) {
            $value = trim((string) ($beneficiary[$key] ?? ''));
            if ($value === '') {
                continue;
            }
            $format = match (true) {
                (bool) preg_match('/^\d{4}-\d{2}-\d{2}/', $value) => ['!Y-m-d', 10],
                (bool) preg_match('/^\d{2}-\d{2}-\d{4}/', $value) => ['!d-m-Y', 10],
                (bool) preg_match('/^\d{2}\/\d{2}\/\d{4}/', $value) => ['!d/m/Y', 10],
                (bool) preg_match('/^\d{8}$/', $value) => ['!Ymd', 8],
                default => null,
            };
            try {
                $date = $format
                    ? Carbon::createFromFormat($format[0], substr($value, 0, $format[1]))
                    : Carbon::parse($value);
                // FONASA sends empty dates as zeros (e.g. 0000-00-00).
                if ($date && $date->year > 1900 && $date->lte(now())) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
            }
        }

        return null;
    }

    public static function dv(int $run): string
    {
        $s = 1;
        for ($m = 0; $run !== 0; $run = intdiv($run, 10)) {
            $s = ($s + $run % 10 * (9 - $m++ % 6)) % 11;
        }

        return $s ? (string) ($s - 1) : 'K';
    }
}
