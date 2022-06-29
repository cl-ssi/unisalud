<?php

namespace App\Rules\Samu;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class EventTimestamp implements Rule
{
    // Stores the data of all timestamps
    public $data;

    // Name of the timestamp field to validate
    public $field;

    // Stores the name of timestamps
    public $fields;

    // TimestampI less than or equal to TimestampJ
    public $validateDate;

    // Position of the timestamps that are evaluated in validateDate
    public $validatePosition;

    // Timestamps name is spanish
    public $translate;

    /**
     * Initialize attributes
     *
     * @param  array $data
     * @param  string $field
     * @return void
     */
    public function __construct($data, $field)
    {
        $this->data['departure_at'] = $this->getDate($data['departure_at']);
        $this->data['mobile_departure_at'] = $this->getDate($data['mobile_departure_at']);
        $this->data['mobile_arrival_at'] = $this->getDate($data['mobile_arrival_at']);
        $this->data['route_to_healtcenter_at'] = $this->getDate($data['route_to_healtcenter_at']);
        $this->data['healthcenter_at'] = $this->getDate($data['healthcenter_at']);
        $this->data['patient_reception_at'] = $this->getDate($data['patient_reception_at']);
        $this->data['return_base_at'] = $this->getDate($data['return_base_at']);
        $this->data['on_base_at'] = $this->getDate($data['on_base_at']);

        $this->field = $field;
        $this->validationDate = collect([]);
        $this->validatePosition = collect([]);

        $this->fields = collect([
            "1" => 'departure_at',
            "2" => 'mobile_departure_at',
            "3" => 'mobile_arrival_at',
            "4" => 'route_to_healtcenter_at',
            "5" => 'healthcenter_at',
            "6" => 'patient_reception_at',
            "7" => 'return_base_at',
            "8" => 'on_base_at',
        ]);

        $this->translate = collect([
            "departure_at" => 'aviso de salida',
            "mobile_departure_at" => 'salida móvil',
            "mobile_arrival_at" => 'llegada al lugar',
            "route_to_healtcenter_at" => 'ruta al centro asistencial',
            "healthcenter_at" => 'centro asistencial',
            "patient_reception_at" => 'recepción paciente',
            "return_base_at" => 'retorno base',
            "on_base_at" => 'móvil en base',
        ]);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->validateDate();
        $this->msg = '';
        $passes = false;

        if($this->validationDate->count() == 0)
            $passes = true;
        else
        {
            if($this->validationDate->contains(false))
            {
                $passes = false;
                $index = $this->validationDate->search(function ($item) {
                    return $item == false;
                });

                if(isset($index))
                {
                    $dateI = $this->validatePosition[$index][0];
                    $dateJ = $this->validatePosition[$index][1];

                    if($dateI == $this->field)
                        $this->msg = ("El campo " . $this->translate[$dateI] . " debe ser menor o igual que " . $this->translate[$dateJ]);
                    elseif($dateJ == $this->field)
                        $this->msg = ("El campo " . $this->translate[$dateJ] . " debe ser mayor o igual que " . $this->translate[$dateI]);
                    else
                        $passes = true;
                }
            }
            else
                $passes = true;
        }

        return $passes;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->msg;
    }

    /**
     * Validate datetime timestamps
     *
     * @return void
     */
    public function validateDate()
    {
        // Current timestamp: TimestampI
        $i = 1;

        // The next non-null timestamp: TimestampJ
        $j = 2;

        while($i <= 7 && $j <= 8)
        {
            $dateI = $this->data[$this->fields[$i]];
            $dateJ = $this->data[$this->fields[$j]];

            if($dateJ != null)
            {
                $this->validatePosition->push([$this->fields[$i], $this->fields[$j]]);
                $i = $j;
                $j = $i + 1;
                $date = Carbon::parse($dateI);
                $dateNext = Carbon::parse($dateJ);
                $this->validationDate->push($date->lte($dateNext));
            }
            else
            {
                $j++;
            }
        }
    }

    /**
     * Create the date using Carbon
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDate($date)
    {
        $datetime = null;
        if($date)
        {
            if(preg_match('/^[0-9]{2}:[0-9]{2}$/', $date))
                $datetime = Carbon::parse(now()->format('Y-m-d ') . $date);
            else
                $datetime = Carbon::parse($date);
        }
        return $datetime;
    }
}
