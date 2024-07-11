<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HumanName extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'use',
        'given',
        'fathers_family',
        'mothers_family',
        'prefix',
        'suffix',
        'period_start',
        'period_end',
        'user_id'
    ];

    /**
    * The attributes that should be mutated to dates.
    *
    * @var array
    */
    protected $dates = [
        'period_start',
        'period_end'
    ];

    public function getfullNameAttribute()
    {
        return "{$this->text}";
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        self::creating(function (HumanName $humanName): void {
            $humanName->given = trim($humanName->given);
            $humanName->fathers_family = trim($humanName->fathers_family);
            $humanName->mothers_family = trim($humanName->mothers_family);

            $humanName->text = $humanName->given.' '.$humanName->fathers_family.' '.$humanName->mothers_family;
            $humanName->period_start = Carbon::now()->addSeconds(1);
        });

        self::updating(function (HumanName $humanName): void {
            $humanName->given = trim($humanName->given);
            $humanName->fathers_family = trim($humanName->fathers_family);
            $humanName->mothers_family = trim($humanName->mothers_family);

            $humanName->text = $humanName->given.' '.$humanName->fathers_family.' '.$humanName->mothers_family;
        });
    }
}