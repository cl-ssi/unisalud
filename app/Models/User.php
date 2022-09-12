<?php

namespace App\Models;

use App\Models\Samu\Shift;
use App\Models\Some\Appointment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable, HasRoles;
    use SoftDeletes;

    use \OwenIt\Auditing\Auditable;


    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'active',
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
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'datetime'
    ];


    public function humanNames()
    {
        return $this->hasMany(HumanName::class, 'user_id')->orderBy('created_at');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id');
    }


    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class, 'user_id');
    }


    public function identifiers()
    {
        return $this->hasMany(Identifier::class, 'user_id');
    }

    public function practitioners()
    {
        return $this->hasMany(Practitioner::class, 'user_id');
    }

    public function congregations()
    {
        return $this->belongsToMany(Congregation::class, 'congregation_users')->withTimestamps();
    }

    public function congregationUsers()
    {
        return $this->hasMany(CongregationUser::class, 'user_id');
    }

    public function appointments()
    {
        return $this->morphToMany(Appointment::class, 'appointable');
    }

    public function maritalStatus()
    {
        return $this->belongsTo(CodConMarital::class, 'cod_con_marital_id');
    }

    public function nationality()
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    public function sexes()
    {
        return $this->belongsToMany(Sex::class)
            ->withPivot('valid_from', 'valid_to')
            ->withTimestamps();
    }

    public function genders()
    {
        return $this->belongsToMany(Gender::class)
            ->withPivot('valid_from', 'valid_to')
            ->withTimestamps();
    }



    // public function manager_shifts(): HasMany
    // {
    //     return $this->hasMany(Shift::class, 'manager_shift');
    // }

    // public function doctor_shifts(): HasMany
    // {
    //     return $this->hasMany(Shift::class, 'regulatory_doctor');
    // }

    // public function nurse_shifts(): HasMany
    // {
    //     return $this->hasMany(Shift::class, 'regulatory_nurse');
    // }

    public function shifts(): BelongsToMany
    {
        // return $this->belongsToMany(Shift::class, 'samu_shift_user')->withTimestamps()->withPivot('job_type','deleted_at')->whereNull('deleted_at');
        return $this->belongsToMany(Shift::class, 'samu_shift_user')->withTimestamps()->withPivot('job_type');
    }

    public function programmingProposals()
    {
        return $this->hasMany(MedicalProgrammer\ProgrammingProposal::class, 'user_id');
    }


    //HumanNames
    public function officialHumanNames()
    {
        return $this->humanNames();
    }

    public function getActualOfficialHumanNameAttribute()
    {
        return $this->officialHumanNames()
            ->where('use', 'official')
            ->whereNull('period_end')
            ->first();
    }

    // function getGivenArray(){
    //     return explode(' ', $this->given);
    // }

    public function getOfficialFullNameAttribute()
    {
        return ucwords(mb_strtolower($this->text));

    //   if ($this->actualOfficialHumanName) {
    //     return "{$this->actualOfficialHumanName->text} {$this->actualOfficialHumanName->fathers_family} {$this->actualOfficialHumanName->mothers_family}";
    //   }
    }

    public function getOfficialNameAttribute()
    {
        return "{$this->actualOfficialHumanName->given}";
    }

    public function getOfficialFathersFamilyAttribute()
    {
        return "{$this->actualOfficialHumanName->fathers_family}";
    }

    public function getOfficialMothersFamilyAttribute()
    {
        return "{$this->actualOfficialHumanName->mothers_family}";
    }


    //Identificadores solo RUN
    public function getIdentifierRunAttribute()
    {
        return $this->identifiers()
            ->where('cod_con_identifier_type_id', 1)
            ->latest()
            ->first();
    }

    //Identificadores solo RUN
    public function getIdentificationAttribute()
    {
        return $this->identifiers()            
            ->latest()
            ->first();
    }

    public static function getUserByRun($run)
    {
        return User::whereHas('identifiers', function ($query) use ($run) {
            return $query->where('value', $run)->where('cod_con_identifier_type_id', 1);
        })->first();
    }

    public static function getUsersByName($searchText)
    {
        $queryUser = User::query();
        $arraySearch = explode(' ', $searchText);

        foreach ($arraySearch as $word) {
            $queryUser->whereHas('humanNames', function ($q) use ($word) {
                $q->where('text', 'LIKE', '%' . $word . '%')
                    ->orwhere('fathers_family', 'LIKE', '%' . $word . '%')
                    ->orwhere('mothers_family', 'LKE', '%' . $word . '%');
            });
        }

        return $queryUser;
    }

    /**
     * Retorna Usuarios según contenido en $searchText
     * Búsqueda realizada en: nombres, apellidos, rut.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getUsersBySearch($searchText){
        $queryUser = User::query();
        $arraySearch = explode(' ', $searchText);
        foreach($arraySearch as $word){
            $queryUser->whereHas('humanNames',  function($q) use($word){
                $q->where('text', 'LIKE', '%' . $word . '%')
                    ->orwhere('fathers_family', 'LIKE', '%' . $word . '%')
                    ->orwhere('mothers_family', 'LIKE', '%' . $word . '%');
            })
            ->orwhereHas('identifiers', function ($q) use ($word) {
                $q->where('value', 'LIKE', '%' . $word . '%');
            });
        }
        return $queryUser;
    }

    public static function getUsersByIdentifier($searchText)
    {
        return User::whereHas('identifiers', function($query) use($searchText) {
            return $query->where('value', $searchText);
        });
    }

    //ContactPoints
    public function getOfficialPhoneAttribute()
    {
        $phone = $this->getOfficialContactPointPhoneAttribute();
        return ($phone) ? $phone->value : '';
    }

    public function getOfficialContactPointPhoneAttribute()
    {
        return $this->contactPoints()
            ->where('system', 'phone')
            ->latest()
            ->first();

    }

    public function getOfficialEmailAttribute()
    {
        $email = $this->getOfficialContactPointEmailAttribute();
        return ($email) ? $email->value : '';
    }


    public function getOfficialContactPointEmailAttribute()
    {
        $contactPointEmail = $this->contactPoints()
            ->where('system', 'email')
            ->latest()
            ->first();
        return $contactPointEmail;
    }

    //Addresses
    public function getOfficialFullAddressAttribute()
    {

        if ($this->addresses()->count() > 0) {
            $address = $this->addresses()
                ->first(['text', 'line', 'apartment']);
            return "$address->text $address->line $address->apartment";
        }else
            return '';

    }

    function getSexEspAttribute(){
        switch($this->sex) {
            case 'male': return 'Masculino'; break;
            case 'female': return 'Femenino'; break;
            case 'other': return 'Otro'; break;
            case 'unknown': return 'Desconocido'; break;
        }
    }

    function getAgeStringAttribute(){
        if($this->birthday){
            $age = $this->birthday->age;            
            $edad = $this->birthday->diff(now());            
            if($age > 0){                
                return $edad->format('%y años, %m meses y %d días');
            }elseif($this->birthday->diffInMonths(now()) > 0){
                return $this->birthday->diffInMonths(now()) . ' meses';
            }else{
                return $this->birthday->diffInDays(now()) . ' días';
            }
        }else{
            return '';
        }
    }

    public function actualSex()
    {
        return $this->sexes()
            ->wherePivotNull('valid_to')
            ->first();
    }

    public function actualGender()
    {
        return $this->genders()
            ->wherePivotNull('valid_to')
            ->first();
    }

    public function getActualSexAttribute()
    {
        if ($this->actualSex() === null) {
            return '';
        }

        return $this->actualSex()->text;
    }

    public function getActualGenderAttribute()
    {
        if ($this->actualGender() === null) {
            return '';
        }
        return $this->actualGender()->text;
    }

    //Scopes
    public function scopeGetByRun($query, $run)
    {
        $query->whereHas('identifiers', function ($query) use ($run) {
            return $query->where('value', $run)
                ->where('cod_con_identifier_type_id', 1);
        });
    }

    public function scopeGetByIdentifier($query, $value, $identifierType)
    {
        $query->orWhereHas('identifiers', function ($query) use ($value, $identifierType) {
            return $query->where('value', $value)
                ->where('cod_con_identifier_type_id', $identifierType);
        });
    }

    public function scopeGetByHumanName($query, $text, $fathers_family, $mothers_family)
    {
        $query->orWhereHas('humanNames', function ($query) use ($text, $fathers_family, $mothers_family) {
            return $query->where('text', $text)
                ->where('fathers_family', $fathers_family)
                ->where('mothers_family', $mothers_family);
        });
    }

    public function scopeGetByAddress($query, $text, $line, $apartment, $country_id, $commune_id, $region_id)
    {
        $query->orWhereHas('addresses', function ($query) use ($text, $line, $apartment, $country_id, $commune_id, $region_id) {
            return $query->where('text', $text)
                ->where('text', 'like', '%' . $text . '%')
                ->where('line', $line)
                ->when($apartment, function ($query, $apartment) {
                    return $query->where('apartment', $apartment);
                })
                ->where('country_id', $country_id)
                ->where('commune_id', $commune_id)
                ->where('region_id', $region_id);
        });

    }


    public function scopeGetByContactPoint($query, $value)
    {
        $query->orWhereHas('contactPoints', function ($query) use ($value) {
            return $query->where('value', $value);
        });
    }


    //Programador (relaciones)
    public function userSpecialties()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\UserSpecialty');
    }

    public function userProfessions()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\UserProfession');
    }

    public function userServices()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\UserService');
    }

    public function specialties()
    {
        return $this->belongsToMany('App\Models\MedicalProgrammer\Specialty', 'mp_user_specialties')
            ->wherePivot('deleted_at', null);
    }

    public function professions()
    {
        return $this->belongsToMany('App\Models\MedicalProgrammer\Profession', 'mp_user_professions')
            ->wherePivot('deleted_at', null);
    }

    public function services()
    {
        return $this->belongsToMany('App\Models\MedicalProgrammer\Service', 'mp_user_services')
            ->wherePivot('deleted_at', null);
    }

    public function userOperatingRooms()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\UserOperatingRoom');
    }

    public function unscheduledProgrammings()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\UnscheduledProgramming');
    }

    public function calendarProgrammings()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\CalendarProgramming');
    }

    public function operatingRoomProgrammings()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\OperatingRoomProgramming');
    }

    public function theoreticalProgrammings()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\TheoreticalProgramming');
    }

    public function contracts()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\Contract');
    }

    public function activities()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\Activity');
    }

    public function motherActivities()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\MotherActivity');
    }

    public function rrhhs()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\Rrhh');
    }

    public function operatingRooms()
    {
        return $this->hasMany('App\Models\MedicalProgrammer\OperatingRoom');
    }
    

    //programador (funciones)
    public function getSpecialtiesArray()
    {
        $array = array();
        foreach ($this->userSpecialties as $key => $userSpecialty) {
            $array[$key] = $userSpecialty->specialty_id;
        }
        return $array;
    }

    public function getProfessionsArray()
    {
        $array = array();
        foreach ($this->userProfessions as $key => $userProfession) {
            $array[$key] = $userProfession->profession_id;
        }
        return $array;
    }

    public function getOperatingRoomsArray()
    {
        $array = array();
        foreach ($this->userOperatingRooms as $key => $userOperatingRoom) {
            $array[$key] = $userOperatingRoom->operating_room_id;
        }
        return $array;
    }


    public function usersPatients()
    {
        return $this->hasMany(Fq\UserPatient::class, 'contact_user_id');
    }

    public function teleconsultationSurveys()
    {
        return $this->hasMany(Surveys\TeleconsultationSurvey::class, 'user_id');
    }

    public function scopeDontHavePermission($query, $permissionName)
    {
        return $query->whereDoesntHave('permissions', function($subquery) use ($permissionName) {
            return $subquery->where('name', $permissionName);
        });
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        self::creating(function (User $user): void {
            $user->given = trim($user->given);
            $user->fathers_family = trim($user->fathers_family);
            $user->mothers_family = trim($user->mothers_family);

            $user->text = $user->given.' '.$user->fathers_family.' '.$user->mothers_family;

            // $humanName = HumanName::create([
            //     'use' => 'official',
            //     'given' => $user->given,
            //     'fathers_family' => $user->fathers_family,
            //     'mothers_family' => $user->mothers_family,
            // ]);
            // $user->humanNames()->attach($humanName);
        });

        self::updating(function (User $user): void {
            $user->given = trim($user->given);
            $user->fathers_family = trim($user->fathers_family);
            $user->mothers_family = trim($user->mothers_family);

            $user->text = $user->given.' '.$user->fathers_family.' '.$user->mothers_family;
        });
    }


    
}
