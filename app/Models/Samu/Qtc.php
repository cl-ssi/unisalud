<?php

namespace App\Models\Samu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Qtc extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table="samu_qtcs";

    protected $fillable = [
        
        'class_qtc',
        'hour',
        'call_reception',
        'telephone_information',
        'applicant',
        'direction',
        'telephone',
        'created_at'    

    ];

    public function follow()
    {
        return $this->hasOne('\App\Models\Samu\Follow');
    }
}