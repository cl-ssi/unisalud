<?php

namespace App\Models\Epi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'contact_id',
        'last_contact_at',
        'relationship',
        'live_together',
        'observation'
    ];

    protected $dates = [
        'last_contact_at',
    ];

    public function patient() {
        return $this->belongsTo('App\Models\User', 'contact_id');
    }

    public function self_patient() {
        return $this->belongsTo('App\Models\User', 'patient_id');
    }


    public function getRelationshipNameAttribute(){
        switch ($this->relationship) {
            case "grandfather":
                return 'Abuelo';
                break;
            case "grandmother":
                return 'Abuela';
                break;

            case "sister in law":
                return 'Cuñada';
                break;
            case "brother in law":
                return 'Cuñado';
                break;

            case "wife":
                return 'Esposa';
                break;
            case "husband":
                return 'Esposo';
                break;

            case "sister":
                return 'Hermana';
                break;
            case "brother":
                return 'Hermano';
                break;

            case "daughter":
                return 'Hija';
                break;
            case "son":
                return 'Hijo';
                break;

            case "mother":
                return 'Madre';
                break;
            case "father":
                return 'Padre';
                break;

            case "cousin":
                return 'Primo/a';
                break;

            case "niece":
                return 'Sobrina';
                break;
            case "nephew":
                return 'Sobrino';
                break;

            case "mother in law":
                return 'Suegra';
                break;
            case "father in law":
                return 'Suegro';
                break;

            case "aunt":
                return 'Tía';
                break;
            case "uncle":
                return 'Tío';
                break;

            case "grandchild":
                return 'Nieto/a';
                break;

            case "daughter in law":
                return 'Nuera';
                break;
            case "son in law":
                return 'Yerno';
                break;

            case "girlfriend":
                return 'Pareja';
                break;
            case "boyfriend":
                return 'Pareja';
                break;

            case "other":
                return 'Otro Parentesco u Relación';
                break;
        }
    }

    protected $table = 'epi_contacts';
}
