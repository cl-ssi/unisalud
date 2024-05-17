<?php

namespace App\Models;

use App\Models\Identifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodConIdentifierType extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'cod_con_identifier_type_id',
        'coding_id',
        'text',
    ];

    // has many identifiers
    public function identifiers(): HasMany
    {
        return $this->hasMany(Identifier::class);
    }

    // belongs to CodConIdentifierTypes
    public function codConIdentifierType(): BelongsTo
    {
        return $this->belongsTo(CodConIdentifierType::class);
    }
}
