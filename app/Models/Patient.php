<?php

namespace App\Models;

use App\Models\Commune;
use App\Models\Exam;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'mx_patients';

    protected $fillable = [
        'id',
        'run',
        'dv',
        'other_identification',
        'name',
        'fathers_family',
        'mothers_family',
        'gender',
        'birthday',
        'address',
        'telephone',
        'status',
        'deceased_at'
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'birthday'  => 'date:Y-m-d'
    ];

    protected $appends = [
        'fullname',
        'age'
    ];

    public function communes():HasMany
    {
        return $this->hasMany(Commune::class);
    }

    public function exams():HasMany
    {
        return $this->hasMany(Exam::class, 'patient_id', 'id');
    }

    public function getRun(): String
    {
        return "{$this->run}-{$this->dv}";
    }

    public function getAgeAttribute()
    {
        if($this->birthday) {
            return $this->birthday->diffInYears(\Carbon\Carbon::now());
        }
        else {
            return $this->birthday;
        }
    }

    public function getBirards(){
        // return $this->exams->where($this->age, '<', 35);
        return $this->exams->birards_mamografia;
    }

    public function getFullNameAttribute(): String
    {
        return "{$this->name} {$this->fathers_family} {$this->mothers_family}";
    }



}
