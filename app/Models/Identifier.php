<?php

namespace App\Models;

use App\Models\CodConIdentifierType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\IdentifierUse;

class Identifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'use',
        'cod_con_identifier_type_id',
        'system',
        'value',
        'dv',
        'period_id',
        'organization',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
