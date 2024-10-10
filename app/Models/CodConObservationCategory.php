<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodConObservationCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'cod_con_obs_categories_id',
        'coding_id',
        'text',
    ];

    public function getTextTranslateValueAttribute()
    {
        switch ($this->status) {
            case 'text':
                return 'procedimientos';
                return '';
        }
    }
}