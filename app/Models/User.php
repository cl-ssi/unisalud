<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Gender;
use App\Enums\Sex;
use App\Models\Identifier;
use App\Models\DependentUser;
use App\Models\DependentCareGiver;
use App\Models\Waitlist;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
// use App\Observers\UserObserver;

// #[ObservedBy([UserObserver::class])]
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
        'fhir_id',
        'email',
        'password',
        'claveunica', // Añadir el campo 'external'
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
        'birthday'          => 'date',
        'sex'               => Sex::class,
        'gender'            => Gender::class,
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Solo pueden acceder si tienen el permiso "be god"
        // TODO: Cuando esté listo para poducción, cambiar a return true;
        return true;
        // return auth()->user()->canAny(['be god','dependance:viewAny']);
    }

    public function canBeImpersonated()
    {
        // Let's prevent impersonating other users
        return auth()->user()->can('be god');
    }

    public function getFilamentName(): string
    {
        return "{$this->given} {$this->fathers_family}";
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(Identifier::class);
    }

    public function officialIdentifier(): HasOne
    {
        return $this->hasOne(Identifier::class, 'user_id')->orderBy('created_at');
    }

    public function OficialHumanName(): HasOne
    {
        // FIXME: where al oficial o actual
        return $this->hasOne(HumanName::class, 'user_id')->orderBy('created_at');
    }

    public function humanNames(): HasMany
    {
        return $this->hasMany(HumanName::class, 'user_id')->orderBy('created_at');
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
        // FIXME: OfficialAddress
        // FIXME: where de cual es la por defecto o en uso actual
        return $this->hasOne(Address::class);
    }

    public function codConMarital(): BelongsTo
    {
        return $this->belongsTo(CodConMarital::class);
    }

    public function dependentUser(): HasOne
    {
        return $this->HasOne(DependentUser::class);
    }

    public function dependentCaregiver(): HasOne
    {
        return $this->HasOne(DependentCareGiver::class);
    }

    public function contactPoints(): HasMany
    {
        return $this->hasMany(ContactPoint::class);
    }

    public function homeContactPoint(): HasOne
    {
        return $this->hasOne(ContactPoint::class)->where('use', 'home')->orderBy('created_at');
    }

    public function mobileContactPoint(): HasOne
    {
        return $this->hasOne(ContactPoint::class)->where('use', 'mobile')->orderBy('created_at');
    }

    public function emailContactPoint(): HasOne
    {
        return $this->hasOne(ContactPoint::class)->where('system', 'email')->orderBy('created_at');
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
                    ->withPivot('user_id', 'organization_id') // Atributos de la tabla pivote
                    ->withTimestamps(); // Si tienes timestamps en la tabla pivote
    }

    /*
    public function sexes(): belongsToMany
    {
        // return $this->belongsToMany(Sex::class)
        return $this->belongsToMany(Sex::class)
            ->withPivot('valid_from', 'valid_to')
            ->withTimestamps();
    }

    public function genders(): belongsToMany
    {
        // return $this->belongsToMany(Gender::class)
        return $this->belongsToMany(Gender::class)
            ->withPivot('valid_from', 'valid_to')
            ->withTimestamps();
    }
    */
}
