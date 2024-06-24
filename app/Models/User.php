<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Gender;
use App\Enums\Sex;
use App\Models\Identifier;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    // use \OwenIt\Auditing\Auditable;

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
        'sex'               => Sex::class,
        'gender'            => Gender::class,
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // TODO: usar propiedad "active" cuando se implementen los usarios activos
        return true;
        // return str_ends_with($this->email, '@redsalud.gob.cl');
    }

    public function canBeImpersonated()
    {
        // Let's prevent impersonating other users
        return auth()->user()->can('be god');
    }

    // protected function casts(): array
    // {
    //     return [
    //         'email_verified_at' => 'datetime',
    //         'password'          => 'hashed',
    //         'sex'               => Sex::class,
    //         'gender'            => Gender::class,
    //     ];
    // }

    public function identifiers(): HasMany
    {
        return $this->hasMany(Identifier::class);
    }



    public function getFilamentName(): string
    {
        return "{$this->given} {$this->fathers_family}";
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function codConMarital(): BelongsTo
    {
        return $this->belongsTo(CodConMarital::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class);
    }

    public function humanNames(): HasMany
    {
        return $this->hasMany(HumanName::class, 'user_id')->orderBy('created_at');
    }
}
