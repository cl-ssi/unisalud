<?php

namespace App\Http\Livewire\Some;

use App\Models\Location;
use App\Models\MedicalProgrammer\Profession;
use App\Models\MedicalProgrammer\Specialty;
use App\Models\Practitioner;
use App\Models\Some\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class AsignAppointment extends Component
{
    public $run;
    public $name;
    public $user;
    public $appointments;
    public $appointmentsHistory;
    public $selectedAppointments = [];
    public $selectedOverbookingAppointments = [];
    public $profession_id;
    public $type;
    public $specialty_id;
    public $professions;
    public $specialties;
    public $practitioners;
    public $practitioner_id;
    public $appointments_from;
    public $appointments_to;
    public $dv;
    public $locations;
    public $selectedLocation;
    public $patientInstruction;

    protected $listeners = ['userSelected' => 'setUser',
    ];

    /**
     * @var Location[]|\Illuminate\Database\Eloquent\Collection|mixed
     */

    public function searchUser()
    {
        if ($this->run) {
            $this->user = User::getUserByRun($this->run);
        } elseif ($this->name) {
            $this->user = User::getUsersByName($this->name)->first();
        }

        if ($this->user) {
            $this->appointmentsHistory = $this->user->appointments()->withTrashed()->get();
        }
    }

    public function setDv()
    {
        $run = intval($this->run);
        $s = 1;
        for ($m = 0; $run != 0; $run /= 10)
            $s = ($s + $run % 10 * (9 - $m++ % 6)) % 11;
        $this->dv = chr($s ? $s + 47 : 75);
    }

    public function setUser($userId)
    {
        $this->user = User::find($userId);
    }

    public function searchAppointments()
    {
        //todo validar que this->specialty_id no sea nulo

        $userPractitioner = null;
        if ($this->practitioner_id) {
            $userPractitioner = Practitioner::find($this->practitioner_id)->user;
        }

        $query = Appointment::query();

        $query->when($this->appointments_from === null && $this->appointments_to === null, function ($q) {
            return $q->whereDate('start', '>=', Carbon::now()->toDateString());
        });

        $query->when($this->appointments_from != null && $this->appointments_to != null, function ($q) {
            return $q->whereDate('start', '>=', $this->appointments_from)
                ->whereDate('start', '<=', $this->appointments_to);
        });

        $query->when($this->appointments_from === null && $this->appointments_to != null, function ($q) {
            return $q->whereDate('start', '<=', $this->appointments_to);
        });

        $query->when($this->appointments_from != null && $this->appointments_to === null, function ($q) {
            return $q->whereDate('start', '<=', $this->appointments_from);
        });

        $query->whereHas('theoreticalProgramming', function ($q) {
            return $q->where('specialty_id', $this->specialty_id);
        });

        $query->when($userPractitioner != null, function ($q) use ($userPractitioner) {
            return $q->whereHas('theoreticalProgramming', function ($q) use ($userPractitioner) {
                return $q->where('user_id', $userPractitioner->id);
            });
        });

        $query->where('status', 'proposed')->orWhere('status', 'booked');

        $query->orderBy('start');

        $this->appointments = $query->get();

    }

    public function asignAppointment()
    {
        if (count($this->selectedAppointments) > 0) {
            $selectedAppointments = Appointment::whereIn('id', [$this->selectedAppointments]);

            foreach ($selectedAppointments->get() as $selectedAppointment) {
                $selectedAppointment->users()->save($this->user, ['required' => 'required', 'status' => 'accepted']);
                $selectedAppointment->practitioners()->save(Practitioner::find($this->practitioner_id), ['required' => 'required', 'status' => 'accepted']);
                $selectedAppointment->locations()->save($this->user, ['required' => 'required', 'status' => 'accepted']);
            }

            $selectedAppointments->update(
                ['status' => 'booked',
                 'patient_instruction' => $this->patientInstruction,
                ]

            );

            session()->flash('success', 'Cita asignada');
        }

        if (count($this->selectedOverbookingAppointments) > 0) {
            $selectedOverbookingAppointments = Appointment::whereIn('id', [$this->selectedOverbookingAppointments])->get();

            foreach ($selectedOverbookingAppointments as $selectedOverbookingAppointment) {
                $duplicateSelectedOverbookingAppointment = $selectedOverbookingAppointment->replicate();
                $duplicateSelectedOverbookingAppointment->cod_con_appointment_type_id = 6;
                $duplicateSelectedOverbookingAppointment->status = 'booked';
                $duplicateSelectedOverbookingAppointment->patient_instruction = $this->patientInstruction;
                $duplicateSelectedOverbookingAppointment->save();

                $duplicateSelectedOverbookingAppointment->users()->save($this->user, ['required' => 'required', 'status' => 'accepted']);
                $duplicateSelectedOverbookingAppointment->practitioners()->save(Practitioner::find($this->practitioner_id), ['required' => 'required', 'status' => 'accepted']);
                $duplicateSelectedOverbookingAppointment->locations()->save($this->user, ['required' => 'required', 'status' => 'accepted']);

            }

            session()->flash('success', 'Cita de sobrecupo asignada');
        }

        return redirect()->route('some.appointment');
    }

    public function getPractitioners()
    {
        $this->specialties = null;
        $this->professions = null;
        if ($this->type != null) {
            if ($this->type == "Médico") {
                $this->specialties = Specialty::orderBy('specialty_name', 'ASC')->get();
            } else {
                $this->professions = Profession::orderBy('profession_name', 'ASC')->get();
            }
        }

        $this->practitioners = null;
        if ($this->specialty_id != null) {

            $this->practitioners = Practitioner::where('specialty_id', $this->specialty_id)
                ->get();
        }

        if ($this->profession_id != null) {

            $this->practitioners = Practitioner::whereHas('user', function ($query) {
                return $query->whereHas('userProfessions', function ($query) {
                    return $query->where('profession_id', $this->profession_id);
                });
            })->get();
        }

        $this->locations = Location::all();
    }

    public function cancelAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);

        $duplicateAppointment = $appointment->replicate();
        $duplicateAppointment->status = 'proposed';
        $duplicateAppointment->save();

        $ids = $appointment->users()->allRelatedIds();
        foreach ($ids as $id) {
            $appointment->users()->updateExistingPivot($id, ['status' => 'declined',
            ]);
        }

        $ids = $appointment->practitioners()->allRelatedIds();
        foreach ($ids as $id) {
            $appointment->practitioners()->updateExistingPivot($id, ['status' => 'declined',
            ]);
        }

        $appointment->status = 'cancelled';
        $appointment->delete();
        $appointment->save();

        if ($this->user) {
            $this->user->refresh();
            $this->appointmentsHistory = $this->user->appointments()->withTrashed()->get();

        }
    }

    public function render()
    {
        return view('livewire.some.asign-appointment');
    }
}
