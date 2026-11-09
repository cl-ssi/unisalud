<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutValido implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Limpiar el RUT (quitar puntos y guiones)
        $rut = preg_replace('/[^kK0-9]/', '', $value);

        if (strlen($rut) < 8) {
            $fail('El RUT no es válido.');
            return;
        }

        $body = substr($rut, 0, -1);
        $vDV = strtoupper(substr($rut, -1));

        // Calcular dígito verificador
        $sum = 0;
        $factor = 2;
        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $sum += $body[$i] * $factor;
            $factor = $factor == 7 ? 2 : $factor + 1;
        }

        $dv = 11 - ($sum % 11);
        $dv = $dv == 11 ? '0' : ($dv == 10 ? 'K' : (string) $dv);

        if ($dv !== $vDV) {
            $fail('El RUT ingresado es incorrecto.');
        }
    }
}
