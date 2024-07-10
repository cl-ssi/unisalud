<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Identifier;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'active',
        'text',
        'given',
        'fathers_family',
        'mothers_family',
        'sex',
        'gender',
        'birthday',
        'deceased_datetime',
        'cod_con_marital_id',
        'nationality_id',
        'claveunica',
        'fhir_id',
        'password',
        'claveunica'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function getFilamentName(): string
    {
        return "{$this->given} {$this->fathers_family}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(Identifier::class);
    }

}
