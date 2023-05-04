<?php

namespace App\Http\Controllers\MedicalProgrammer;

use App\Models\MedicalProgrammer\MotherActivity;
use App\Models\MedicalProgrammer\Activity;
use App\Models\MedicalProgrammer\SubActivity;
use App\Models\MedicalProgrammer\Specialty;
use App\Models\MedicalProgrammer\Profession;
use App\Models\MedicalProgrammer\Service;
use App\Models\MedicalProgrammer\CalendarProgramming;
// use App\Models\MedicalProgrammer\Rrhh;
use App\Models\MedicalProgrammer\Contract;
use App\Models\MedicalProgrammer\UnscheduledProgramming;
use App\Models\MedicalProgrammer\TheoreticalProgramming;
use App\Models\MedicalProgrammer\UserSpecialty;
use App\Models\MedicalProgrammer\UserProfession;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// use Spatie\Permission\Traits\HasRoles;
// use Spatie\Permission\Models\Role;

use App\Models\MedicalProgrammer\OperatingRoomProgramming;

class TheoreticalProgrammingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //primer form
      if ($request->get('date')) {
        $date = $request->get('date');
        $year = date('Y', strtotime($date));
        $rut = $request->get('rut');
      }
      elseif ($request->get('date2')) {
        $date = $request->get('date2');
        $year = date('Y', strtotime($date));
        $rut= $request->get('rut');
      }
      else{
        $date = Carbon::now();
        if ($request->get('year')) {
            $year = $request->get('year');
        }else{$year = $date->get('year');}
        $rut = $request->get('rut');
      }


      $motherActivities = MotherActivity::where('id',2)->get();
      $ids_actividades = $motherActivities->first()->activities->pluck('id')->toArray(); //se obtienen actividades de pabellón teórico

      //obtengo usuario propio
      $users = User::find(Auth::id());

      // //obtengo rrhh segun especalidades asociadas al usuario logeado
      // $rrhhs = Rrhh::whereHas('contracts', function ($query) use ($year) {
      //                 return $query->where('year',$year);
      //             })
      //             // ->whereHas('unscheduled_programmings', function ($query) use ($users) {
      //             //     return $query->whereHas('specialty', function ($query) use ($users) {
      //             //         return $query->whereIn('specialty_id',$users->getSpecialtiesArray());
      //             //     });
      //             // })
      //             ->orderby('name','ASC')->get();

      // dd(UserSpecialty::select('user_id')->groupBy('user_id')->get()->toArray());

    //si es admin, se devuelve todo, si no, se devuelve lo configurado
    if (Auth::user()->hasPermissionTo('Mp: perfil administrador')) {
        $rrhhs = User::whereHas('contracts', function ($query) use ($year) {
                        return $query->where('year',$year);
                    })
                    //if - se devuelven solo usuarios que tengan asignada especialidad
                    ->when($request->get('tipo') == 1, function ($query) {
                        $query->whereIn('id',UserSpecialty::select('user_id')->groupBy('user_id')->get()->toArray());
                    })
                    //if - se devuelven solo usuarios que tengan asignada profesión
                    ->when($request->get('tipo') == 2, function ($query) {
                        $query->whereIn('id',UserProfession::select('user_id')->groupBy('user_id')->get()->toArray());
                    })
                    // ->orderby('name','ASC')
                    ->get();
                    // dd($rrhhs);
    }else{
        $rrhhs = User::whereHas('contracts', function ($query) use ($year) {
                        return $query->where('year',$year);
                    })
                    ->where('id',Auth::user()->id)
                    // ->orderby('name','ASC')
                    ->get();
    }

    //valida información
    // dd($rrhhs->count());
    if($rrhhs->count() == 0) {
        session()->flash('danger', 'No es posible programar semana seleccionada. No existe información para programar.');
        return redirect()->back();
    }

    //información para días contrato
    $contracts = Contract::where('user_id',$rut)->where('year',$year)->get();

    //obtiene contrato_id para obtener información
    $contract_id=null;
    $flag = 0;
    foreach ($contracts as $key => $contract) {
        if($contract->id == $request->get('contract_id')){
            $flag = 1;
        }
    }
    if ($flag == 1) {
        $contract_id = $request->get('contract_id');
    }else{
        if ($contracts->count() > 0) {
            $contract_id = $contracts->first()->id;
        }
    }

    // if ($request->get('contract_id') != null && $request->get('contract_id') != "--") {
    //     foreach ($contracts as $key => $contract) {
    //         if ($contract->id == $request->get('contract_id')) {
    //             $contract_id = $request->get('contract_id');
    //         }
    //     }
    //     if ($contract_id == null) {
    //         $contract_id = $contracts->first()->id;
    //     }
    // }else{
    //     if ($contracts->count()>0) {
    //         $contract_id = $contracts->first()->id;
    //     }
    // }

    //obtiene fechas límite
    $monday = Carbon::parse($date)->startOfWeek();
    $sunday = Carbon::parse($date)->endOfWeek();

    //encuentra actividades segun tipo de profesional
    $specialties = null;
    $professions = null;
    $activities = null;
    //si es programación médica
    if ($request->get('tipo') == 1) {

        // $specialties = Specialty::orderBy('specialty_name','ASC')->get();

        // //se verifica si tiene teorico, si es asi, se busca la primera especialidad y se selecciona en la vista
        // if ($rut != null) {
        //     if (TheoreticalProgramming::where('rut',$rut)->count() > 0) {
        //         $theoreticalProgramming = TheoreticalProgramming::where('rut',$rut)->first();
        //         if ($theoreticalProgramming->count() > 0) {
        //             // if ($specialties->first()->id == $request->get('specialty_id')) {
        //                 $request->merge([
        //                     'specialty_id' => $theoreticalProgramming->specialty_id,
        //                 ]);
        //             // }
        //         }
        //     }
        // }

        //obtiene especialidades ordenadas (si es que existe horario teorico, devuelve esa especialidad primero)
        $TheoreticalProgramming = TheoreticalProgramming::whereNotNull('specialty_id')->where('user_id',$rut)->first();
        if ($rut != null) {
            if ($TheoreticalProgramming!=null) {
                $collection1 = Specialty::where('id',$TheoreticalProgramming->specialty_id)->get();
                //si es admin, se devuelve todo, si no, se devuelve lo configurado
                if (Auth::user()->hasPermissionTo('Mp: perfil administrador')) {
                    $collection2 = Specialty::where('id','!=',$TheoreticalProgramming->specialty_id)->orderBy('specialty_name','ASC')->get();
                }else{
                    $collection2 = Specialty::whereIn('id',Auth::user()->getSpecialtiesArray())->orderBy('specialty_name','ASC')->get();
                }
                // $collection2 = Specialty::whereIn('id',Auth::user()->getSpecialtiesArray())->orderBy('specialty_name','ASC')->get();

                foreach ($collection2 as $key => $value) {
                    $collection1->push($value);
                }
                $specialties = $collection1;
                // $request->merge(['specialty_id' => $collection1->first()->id]);
            }else{

                //si es admin, se devuelve todo, si no, se devuelve lo configurado
                if (Auth::user()->hasPermissionTo('Mp: perfil administrador')) {
                    $specialties = Specialty::orderBy('specialty_name','ASC')->get();
                }else{

                    $specialties = Specialty::whereIn('id',Auth::user()->getSpecialtiesArray())->orderBy('specialty_name','ASC')->get();
                }
                // $specialties = Specialty::whereIn('id',Auth::user()->getSpecialtiesArray())->orderBy('specialty_name','ASC')->get();

            }
        }else{
            //si es admin, se devuelve todo, si no, se devuelve lo configurado
            if (Auth::user()->hasPermissionTo('Mp: perfil administrador')) {
                $specialties = Specialty::orderBy('specialty_name','ASC')->get();
            }else{
                $specialties = Specialty::whereIn('id',Auth::user()->getSpecialtiesArray())->orderBy('specialty_name','ASC')->get();
            }
            // $specialties = Specialty::whereIn('id',Auth::user()->getSpecialtiesArray())->orderBy('specialty_name','ASC')->get();
        }

        $var = $request->get('specialty_id');
        $activities = Activity::orderBy('activity_name','ASC')
                                ->where('activity_type_id',1) //ejemplo: actividades medicas
                                ->whereHas('specialties', function($q) use($var) {
                                    $q->where('specialty_id', $var);
                                })
                                ->where('programmable',1)
                                ->get();

        $non_programable_activities = Activity::orderBy('activity_name','ASC')
                                              ->where('activity_type_id',1) //ejemplo: actividades medicas
                                              ->whereHas('specialties', function($q) use($var) {
                                                  $q->where('specialty_id', $var);
                                              })
                                              ->where('programmable',0)
                                              ->get();

        //corresponde a la actividad no programable (que se guarda en el modelo UnscheduledProgramming)
        $programming = UnscheduledProgramming::where('user_id',$rut)
                                             ->where('year',$year)
                                             ->where('contract_id', $contract_id)
                                             ->where('specialty_id', $var)
                                             ->get();
                                         // dd($non_programable_activities);

        //obtiene operating operation_rooms
        $OperatingRoomProgrammings = OperatingRoomProgramming::where('specialty_id',$var)
                                                            ->whereBetween('start_date',[$monday,$sunday])
                                                            ->get();
                                                            // dd($OperatingRoomProgrammings);
    }
    //si es programación médica
    else{
        // $professions = Profession::orderBy('profession_name','ASC')->get();

        // //obtiene especialidades ordenadas (si es que existe horario teorico, devuelve esa especialidad primero)
        // if ($rut != null) {
        //     $collection1 = Profession::where('id',TheoreticalProgramming::where('rut',$rut)->get()->first()->profession_id)->get();
        //     $collection2 = Profession::where('id','!=',TheoreticalProgramming::where('rut',$rut)->get()->first()->profession_id)->orderBy('profession_name','ASC')->get();
        //     foreach ($collection2 as $key => $value) {
        //         $collection1->push($value);
        //     }
        //     $professions = $collection1;
        // }else{
        //     $professions = Profession::orderBy('profession_name','ASC')->get();
        // }

        //obtiene especialidades ordenadas (si es que existe horario teorico, devuelve esa especialidad primero)
        $TheoreticalProgramming = TheoreticalProgramming::whereNotNull('profession_id')->where('user_id',$rut)->first();
        if ($rut != null) {
            if ($TheoreticalProgramming!=null) {
                $collection1 = Profession::where('id',$TheoreticalProgramming->profession_id)->get();
                // if (Auth::user()->hasPermissionTo('Mp: perfil administrador')) {
                //     $collection2 = Profession::where('id','!=',$TheoreticalProgramming->profession_id)->orderBy('profession_name','ASC')->get();
                // }else{
                //     $collection2 = Profession::whereIn('id',Auth::user()->getProfessionsArray())->orderBy('profession_name','ASC')->get();
                // }
                $collection2 = Profession::whereIn('id',Auth::user()->getProfessionsArray())->orderBy('profession_name','ASC')->get();

                foreach ($collection2 as $key => $value) {
                    $collection1->push($value);
                }
                $professions = $collection1;
                // $request->merge(['profession_id' => $collection1->first()->id]);
            }else{
                // if (Auth::user()->hasPermissionTo('Mp: perfil administrador')) {
                //     $professions = Profession::orderBy('profession_name','ASC')->get();
                // }else{
                //     $professions = Profession::whereIn('id',Auth::user()->getProfessionsArray())->orderBy('profession_name','ASC')->get();
                // }
                $professions = Profession::whereIn('id',Auth::user()->getProfessionsArray())->orderBy('profession_name','ASC')->get();
            }
        }else{
            // if (Auth::user()->hasPermissionTo('Mp: perfil administrador')) {
            //     $professions = Profession::orderBy('profession_name','ASC')->get();
            // }else{
            //     $professions = Profession::whereIn('id',Auth::user()->getProfessionsArray())->orderBy('profession_name','ASC')->get();
            // }
            $professions = Profession::whereIn('id',Auth::user()->getProfessionsArray())->orderBy('profession_name','ASC')->get();
        }

        $var = $request->get('profession_id');
        $activities = Activity::orderBy('activity_name','ASC')
                                ->where('activity_type_id',2) //ejemplo: actividades medicas
                                ->whereHas('professions', function($q) use($var) {
                                    $q->where('profession_id', $var);
                                })
                                ->where('programmable',1)
                                ->get();

        $non_programable_activities = Activity::orderBy('activity_name','ASC')
                                              ->where('activity_type_id',2) //ejemplo: actividades medicas
                                              ->whereHas('specialties', function($q) use($var) {
                                                  $q->where('specialty_id', $var);
                                              })
                                              ->where('programmable',0)
                                              ->get();

        //corresponde a la actividad no programable (que se guarda en el modelo UnscheduledProgramming)
        $programming = UnscheduledProgramming::where('user_id',$rut)
                                             ->where('year',$year)
                                             ->where('contract_id', $contract_id)
                                             ->where('profession_id', $var)
                                             ->get();

        //obtiene operating operation_rooms
        $OperatingRoomProgrammings = OperatingRoomProgramming::where('profession_id',$var)
                                                            ->whereBetween('start_date',[$monday,$sunday])
                                                            ->get();
                                                            // dd($OperatingRoomProgrammings);
    }

    //obtiene horas teóricas
    $theoreticalProgrammings = TheoreticalProgramming::where('year',$year)
                                                  ->where('user_id',$rut)
                                                  ->whereNull('contract_day_type')
                                                  ->where('contract_id', $contract_id)
                                                  ->where('specialty_id', $request->get('specialty_id'))
                                                  ->whereBetween('start_date',[$monday,$sunday])
                                                  ->get();

                                                  // dd($theoreticalProgrammings);

      //se obtiene fechas de inicio y termino de cada isEventOverDiv
      foreach ($theoreticalProgrammings as $key => $theoricalProgramming) {
        $start  = new Carbon($theoricalProgramming->start_date);
        $end    = new Carbon($theoricalProgramming->end_date);
        $theoricalProgramming->duration_theorical_programming = $start->diffInMinutes($end)/60;
      }
      // dd($theoreticalProgrammings);

      //obtiene teóricos administrativos
      $theoreticalProgrammingsAdministrative = TheoreticalProgramming::where('year',$year)
                                                    ->where('user_id',$rut)
                                                    ->whereNotNull('contract_day_type')
                                                    ->where('contract_id', $contract_id)
                                                    // ->where('specialty_id', $request->get('specialty_id'))
                                                    ->whereBetween('start_date',[$monday,$sunday])
                                                    ->get();

                                                    // dd($theoreticalProgrammings);

        //se obtiene fechas de inicio y termino de cada isEventOverDiv
        foreach ($theoreticalProgrammingsAdministrative as $key => $theoricalProgramming) {
          $start  = new Carbon($theoricalProgramming->start_date);
          $end    = new Carbon($theoricalProgramming->end_date);
          $theoricalProgramming->duration_theorical_programming = $start->diffInMinutes($end)/60;
        }

        // dd($theoreticalProgrammingsAdministrative);

      //obtiene teoricos eliminados
      $theoreticalProgrammingDeleted = TheoreticalProgramming::where('year',$year)
                                                    ->where('user_id',$rut)
                                                    ->whereNull('contract_day_type')
                                                    ->where('contract_id', $contract_id)
                                                    ->where('specialty_id', $request->get('specialty_id'))
                                                    ->whereBetween('start_date',[$monday,$sunday])
                                                    ->onlyTrashed()->get();

        //se obtiene fechas de inicio y termino de cada isEventOverDiv
        foreach ($theoreticalProgrammingDeleted as $key => $theoricalProgramming) {
          $start  = new Carbon($theoricalProgramming->start_date);
          $end    = new Carbon($theoricalProgramming->end_date);
          $theoricalProgramming->duration_theorical_programming = $start->diffInMinutes($end)/60;
        }


    //obtiene información de programas médicos asignados
    $unscheduledProgrammings = UnscheduledProgramming::where('year',$year)
                                            ->where('user_id',$rut)
                                            ->whereIn('activity_id',$ids_actividades)
                                            ->get();
    $array = array();
    foreach ($unscheduledProgrammings as $key => $unscheduledProgramming) {
      $array[$unscheduledProgramming->contract->law] = $unscheduledProgrammings->where('contract_id',$unscheduledProgramming->contract_id);
    }

    //obtiene administrativos
    $permisos_administrativos = array();
    $contracts_administrative = Contract::find($contract_id);
    if ($request->get('specialty_id') != "--") {
      if ($contracts_administrative) {
        $permisos_administrativos['legal_holidays'] = $contracts_administrative->legal_holidays;
        // $permisos_administrativos['compensatory_rest'] = $contracts_administrative->compensatory_rest;
        $permisos_administrativos['administrative_permit'] = $contracts_administrative->administrative_permit;
        $permisos_administrativos['training_days'] = $contracts_administrative->training_days;
        // $permisos_administrativos['breastfeeding_time'] = $contracts_administrative->breastfeeding_time;
        // $permisos_administrativos['weekly_collation'] = $contracts_administrative->weekly_collation;
      }
    }


    // dd($permisos_administrativos);

    //se deja el obteo vacio temporalmente
    // $permisos_administrativos = array(); //temporalmente hasta que se empiece a utilizar

    // obtiene información de dias administrativos
    $contract_days = TheoreticalProgramming::where('user_id',$rut)
                                            ->whereNotNull('contract_day_type')
                                            ->where('year',$year)
                                            ->get();

    //se obtiene duración, ya sea 0.5 dias (medio dia administrativo) o 1 del dia completo
    foreach ($contract_days as $key => $contract_day) {
        if (date('H:i', strtotime($contract_day->end_date)) == '12:59' || date('H:i', strtotime($contract_day->start_date)) == '13:00') {
            $contract_day->duration = 0.5;
        }else{$contract_day->duration = 1;}
    }

    $monday = Carbon::parse($date)->startOfWeek();
    $sunday = Carbon::parse($date)->endOfWeek();

      return view('medical_programmer.management.theoretical_programmer', compact('request','array','activities','non_programable_activities','contract_days','date','theoreticalProgrammings','theoreticalProgrammingDeleted',
                                                                        'rrhhs','permisos_administrativos', 'specialties','professions','contracts',
                                                                        'programming','OperatingRoomProgrammings','theoreticalProgrammingsAdministrative'));
      // return view('medical_programmer.management.theoretical_programmer',compact('request','rrhhs','array','theoricalProgrammings','contracts','rut','contract_days'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage: se usa para guardar dias de contrato (feriados, etc.)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // ### Validaciones ###

        //### Validación 1: que no se topen los dias con ya registrados
        $count1 = CalendarProgramming::where('user_id',$request->rut)
                                     ->whereNotNull('contract_day_type')
                                     ->whereRaw('? between start_date and end_date', [$request->start_date])
                                     ->count();
        $count2 = CalendarProgramming::where('user_id',$request->rut)
                                     ->whereRaw('? between start_date and end_date', [$request->end_date])
                                     ->count();
        if (($count1 + $count2) != 0) {
            session()->flash('danger', 'Ya existe días administrativos registrados en esas fechas.');
            return redirect()->back();
        }


        //### Validacion 2: que no excedan los dias de los contratos -

        //obtiene días que se registrarán (request)
        $start  = new Carbon($request->start_date);
        $end    = new Carbon($request->end_date);
        $register_days = $start->diffInDays($end)+1;

        //se obtienen los permisos de contrato ya candelarizados
        $contract_days = CalendarProgramming::where('user_id',$request->rut)
                                            ->whereNotNull('contract_day_type')
                                            ->whereYear('start_date', $request->year)
                                            ->get();
        $matrix = [];
        foreach ($contract_days as $key => $contract_day) {
            $matrix[$contract_day->contract_day_type]['cantidad'] = 0;
        }

        //cantidad de días calendarizados por cada tipo de permiso
        foreach ($contract_days as $key => $contract_day) {
            $start  = new Carbon($contract_day->start_date);
            $end    = new Carbon($contract_day->end_date);
            $matrix[$contract_day->contract_day_type]['cantidad'] += floor($start->diffInDays($end)) + 1;
        }

        //se suma cantidad que viene en el request, para asi validar abajo si se peude ingresar o no
        foreach ($matrix as $key => $dd) {
            if ($key == $request->contract_day_type) {
                $matrix[$key]['cantidad'] += $register_days;
            }
        }

        //se valida si las cantidades están dentro del margen de los contratado

        //se obtiene infor de contratos
        $contract_matrix['legal_holidays'] = 0;
        $contract_matrix['compensatory_rest'] = 0;
        $contract_matrix['administrative_permit'] = 0;
        $contract_matrix['training_days'] = 0;

        $contracts = Contract::where('user_id',$request->rut)
                             ->where('year',$request->year)
                             ->get();
        foreach ($contracts as $key => $contract) {
            $contract_matrix['legal_holidays'] += $contract->legal_holidays;
            $contract_matrix['compensatory_rest'] += $contract->compensatory_rest;
            $contract_matrix['administrative_permit'] += $contract->administrative_permit;
            $contract_matrix['training_days'] += $contract->training_days;
        }

        //se verifica si exceden cantidades según sumatoria de contratos
        foreach ($matrix as $key => $value) {
            if($key == "legal_holidays"){
                if ($value['cantidad'] > $contract_matrix['legal_holidays']) {
                    session()->flash('danger', 'Se superó la cantidad de feriados legales.');
                    return redirect()->back();
                }
            }
            if($key == "compensatory_rest"){
                if ($value['cantidad'] > $contract_matrix['compensatory_rest']) {
                    session()->flash('danger', 'Se superó la cantidad de días descanzo compensatorio.');
                    return redirect()->back();
                }
            }
            if($key == "administrative_permit"){
                if ($value['cantidad'] > $contract_matrix['administrative_permit']) {
                    session()->flash('danger', 'Se superó la cantidad de días de permiso administrativos.');
                    return redirect()->back();
                }
            }
            if($key == "training_days"){
                if ($value['cantidad'] > $contract_matrix['training_days']) {
                    session()->flash('danger', 'Se superó la cantidad de días de congreso o capacitación.');
                    return redirect()->back();
                }
            }
        }




        //### Se ingresa información del evento
        $calendarProgramming = new CalendarProgramming($request->all());
        $calendarProgramming->end_date = $request->end_date . " 23:59:59";
        // $calendarProgramming->user_id = Auth::id();
        $calendarProgramming->save();

        session()->flash('info', 'El evento ha sido creado.');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function saveMyEvent(Request $request){
        try {
            // Storage::put('hola.txt', "asd");
            $year = $request->year;
            $first_date = new Carbon($request->start_date);
            $last_date = new Carbon($request->end_date);

            //registro permisos administrativos
            if ($request->tipo_evento != "teorico") {
                $theoreticalProgramming = new TheoreticalProgramming();
                $theoreticalProgramming->user_id = $request->rut;
                $theoreticalProgramming->contract_id = $request->contract_id;
                $theoreticalProgramming->specialty_id = $request->specialty_id;
                $theoreticalProgramming->profession_id = $request->profession_id;
                $theoreticalProgramming->contract_day_type = $request->tipo_evento;
                $theoreticalProgramming->start_date = $first_date;
                $theoreticalProgramming->end_date = $last_date;
                $theoreticalProgramming->year = $year;
                // $theoreticalProgramming->user_id = Auth::id();
                $theoreticalProgramming->save();
            }
            //registro de teoricos
            else {
                //obtiene rendimiento
                if ($request->specialty_id != null) {
                    $Specialty = Specialty::find($request->specialty_id);
                    $performance = $Specialty->activities->where('id',$request->activity_id)->first()->pivot->performance;
                }else{
                    $profession = Profession::find($request->profession_id);
                    $performance = $profession->activities->where('id',$request->activity_id)->first()->pivot->performance;
                }


                //solo se inserta en esta semana
                if ($request->tipo_ingreso == 1) {
                    $theoreticalProgramming = new TheoreticalProgramming();
                    $theoreticalProgramming->user_id = $request->rut;
                    $theoreticalProgramming->contract_id = $request->contract_id;
                    $theoreticalProgramming->specialty_id = $request->specialty_id;
                    $theoreticalProgramming->profession_id = $request->profession_id;
                    $theoreticalProgramming->activity_id = $request->activity_id;
                    $theoreticalProgramming->start_date = $first_date;
                    $theoreticalProgramming->end_date = $last_date;
                    $theoreticalProgramming->performance = $performance;
                    $theoreticalProgramming->year = $year;
                    // $theoreticalProgramming->user_id = Auth::id();
                    $theoreticalProgramming->save();
                }
                //se inserta desde esta semana hacia adelante
                elseif($request->tipo_ingreso == 2) {
                    // Storage::put('errores.txt',$year . " " . date('Y', strtotime($first_date)));
                    while (date('Y', strtotime($first_date)) == $year) {
                        $theoreticalProgramming = new TheoreticalProgramming();
                        $theoreticalProgramming->user_id = $request->rut;
                        $theoreticalProgramming->contract_id = $request->contract_id;
                        $theoreticalProgramming->specialty_id = $request->specialty_id;
                        $theoreticalProgramming->profession_id = $request->profession_id;
                        $theoreticalProgramming->activity_id = $request->activity_id;
                        $theoreticalProgramming->start_date = $first_date;
                        $theoreticalProgramming->end_date = $last_date;
                        $theoreticalProgramming->performance = $performance;
                        $theoreticalProgramming->year = $year;
                        // $theoreticalProgramming->user_id = Auth::id();
                        $theoreticalProgramming->save();

                        $first_date = $first_date->addWeek(1);
                        $last_date = $last_date->addWeek(1);
                    }
                }
                //semana volante
                elseif($request->tipo_ingreso == 3) {
                  while (date('Y', strtotime($first_date)) == $year) {
                      $theoreticalProgramming = new TheoreticalProgramming();
                      $theoreticalProgramming->user_id = $request->rut;
                      $theoreticalProgramming->contract_id = $request->contract_id;
                      $theoreticalProgramming->specialty_id = $request->specialty_id;
                      $theoreticalProgramming->profession_id = $request->profession_id;
                      $theoreticalProgramming->activity_id = $request->activity_id;
                      $theoreticalProgramming->start_date = $first_date;
                      $theoreticalProgramming->end_date = $last_date;
                      $theoreticalProgramming->performance = $performance;
                      $theoreticalProgramming->year = $year;
                      // $theoreticalProgramming->user_id = Auth::id();
                      $theoreticalProgramming->save();

                      $first_date = $first_date->addWeek(6);
                      $last_date = $last_date->addWeek(6);
                  }
                }
            }
        } catch (\Exception $e) {
            Storage::put('errores.txt', $e->getMessage());
        }
    }

    public function updateMyEvent(Request $request){
        try {

          // $this->buildXMLHeader();
          $year = $request->year;
          $start_date = new Carbon($request->start_date);
          $end_date = new Carbon($request->end_date);
          $start_date_start = new Carbon($request->start_date_start);
          $end_date_start = new Carbon($request->end_date_start);

          //obtiene rendimiento
          $performance = null;
          //se obtiene esta info para los que no son administrativos
          if ($request->tipo != 4) {
            if ($request->specialty_id != null) {
                $Specialty = Specialty::find($request->specialty_id);
                $performance = $Specialty->activities->where('id',$request->activity_id)->first()->pivot->performance;
            }else{
                $profession = Profession::find($request->profession_id);
                $performance = $profession->activities->where('id',$request->activity_id)->first()->pivot->performance;
            }

          }

          //solo se modifica el evento actual
          if ($request->tipo == 1) {
              $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                              ->where('activity_id',$request->activity_id)
                                                              ->where('contract_id',$request->contract_id)
                                                              ->where('specialty_id',$request->specialty_id)
                                                              ->where('profession_id',$request->profession_id)
                                                              ->where('start_date',$start_date_start)
                                                              ->where('end_date',$end_date_start)->first();
              $theoreticalProgramming->start_date = $start_date;
              $theoreticalProgramming->end_date = $end_date;
              $theoreticalProgramming->performance = $performance;
              $theoreticalProgramming->save();


          }
          //se modifican todos los eventos hacia adelante
          elseif($request->tipo == 2){
              while (date('Y', strtotime($start_date)) == $year) {
                  $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                                  ->where('activity_id',$request->activity_id)
                                                                  ->where('contract_id',$request->contract_id)
                                                                  ->where('specialty_id',$request->specialty_id)
                                                                  ->where('profession_id',$request->profession_id)
                                                                  ->where('start_date',$start_date_start)
                                                                  ->where('end_date',$end_date_start)->first();
                  if ($theoreticalProgramming != null) {
                    $theoreticalProgramming->start_date = $start_date;
                    $theoreticalProgramming->end_date = $end_date;
                    $theoreticalProgramming->performance = $performance;
                    $theoreticalProgramming->save();
                  }


                  $start_date = $start_date->addWeek(1);
                  $end_date = $end_date->addWeek(1);
                  $start_date_start = $start_date_start->addWeek(1);
                  $end_date_start = $end_date_start->addWeek(1);
              }
          }
          //se modifican eventos semana por medio
          elseif($request->tipo == 3){
              while (date('Y', strtotime($start_date)) == $year) {
                  $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                                  ->where('activity_id',$request->activity_id)
                                                                  ->where('contract_id',$request->contract_id)
                                                                  ->where('specialty_id',$request->specialty_id)
                                                                  ->where('profession_id',$request->profession_id)
                                                                  ->where('start_date',$start_date_start)
                                                                  ->where('end_date',$end_date_start)->first();
                  if ($theoreticalProgramming != null) {
                    $theoreticalProgramming->start_date = $start_date;
                    $theoreticalProgramming->end_date = $end_date;
                    $theoreticalProgramming->performance = $performance;
                    $theoreticalProgramming->save();
                  }

                  $start_date = $start_date->addWeek(6);
                  $end_date = $end_date->addWeek(6);
                  $start_date_start = $start_date_start->addWeek(6);
                  $end_date_start = $end_date_start->addWeek(6);
              }
          }
          //administrativos
          elseif ($request->tipo == 4) {
              $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                              ->where('activity_id',$request->activity_id)
                                                              ->where('contract_id',$request->contract_id)
                                                              ->where('specialty_id',$request->specialty_id)
                                                              ->where('profession_id',$request->profession_id)
                                                              ->where('start_date',$start_date_start)
                                                              ->where('end_date',$end_date_start)->first();
              $theoreticalProgramming->start_date = $start_date;
              $theoreticalProgramming->end_date = $end_date;
              $theoreticalProgramming->save();
          }

        } catch (\Exception $e) {

            // return $e->getMessage();
            Storage::put('errores.txt', $e->getMessage());
        }
    }

    //elimina el dato - queda respaldo de la eliminación
    public function deleteMyEvent(Request $request){
        try {

            $year = $request->year;
            $first_date = new Carbon($request->start_date);
            $last_date = new Carbon($request->end_date);

            //solo se elimina el evento actual
              if ($request->tipo == 1) {
                  $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                                  ->where('activity_id',$request->activity_id)
                                                                  ->where('contract_id',$request->contract_id)
                                                                  ->where('specialty_id',$request->specialty_id)
                                                                  ->where('profession_id',$request->profession_id)
                                                                  ->where('start_date',$first_date)
                                                                  ->where('end_date',$last_date)->first();
                  $theoreticalProgramming->delete();
              }
              //se elimina desde el evento actual
              elseif($request->tipo == 2){
                  while (date('Y', strtotime($first_date)) == $year) {
                      $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                                      ->where('activity_id',$request->activity_id)
                                                                      ->where('contract_id',$request->contract_id)
                                                                      ->where('specialty_id',$request->specialty_id)
                                                                      ->where('profession_id',$request->profession_id)
                                                                      ->where('start_date',$first_date)
                                                                      ->where('end_date',$last_date)->first();
                      if ($theoreticalProgramming != null) {
                        $theoreticalProgramming->delete();
                      }

                      $first_date = $first_date->addWeek(1);
                      $last_date = $last_date->addWeek(1);
                  }
              }
              //se elimina semana volante
              elseif($request->tipo == 3){
                  while (date('Y', strtotime($first_date)) == $year) {
                      $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                                      ->where('activity_id',$request->activity_id)
                                                                      ->where('contract_id',$request->contract_id)
                                                                      ->where('specialty_id',$request->specialty_id)
                                                                      ->where('profession_id',$request->profession_id)
                                                                      ->where('start_date',$first_date)
                                                                      ->where('end_date',$last_date)->first();
                      if ($theoreticalProgramming != null) {
                        $theoreticalProgramming->delete();
                      }

                      $first_date = $first_date->addWeek(6);
                      $last_date = $last_date->addWeek(6);
                  }
              }
              //administrativos
              elseif ($request->tipo == 4) {
                  $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                                  ->where('contract_id',$request->contract_id)
                                                                  ->where('specialty_id',$request->specialty_id)
                                                                  ->where('profession_id',$request->profession_id)
                                                                  ->where('start_date',$first_date)
                                                                  ->where('end_date',$last_date)->first();
                  $theoreticalProgramming->delete();
              }

        } catch (\Exception $e) {
            Storage::put('errores.txt', $e->getMessage());
        }
    }

    //elimina el dato (cuando son movimientos en el calendario) - no queda respaldo
    public function deleteMyEventForce(Request $request){
      $year = $request->year;
      $first_date = new Carbon($request->start_date);
      $last_date = new Carbon($request->end_date);
      while (date('Y', strtotime($first_date)) == $year) {
          $theoreticalProgramming = TheoreticalProgramming::where('user_id',$request->rut)
                                                          ->where('activity_id',$request->activity_id)
                                                          ->where('contract_id',$request->contract_id)
                                                          ->where('specialty_id',$request->specialty_id)
                                                          ->where('profession_id',$request->profession_id)
                                                          ->where('start_date',$first_date)
                                                          ->where('end_date',$last_date);
          $theoreticalProgramming->forceDelete();

          $first_date = $first_date->addWeek(1);
          $last_date = $last_date->addWeek(1);
      }
    }

    /*función que reemplaza espacios en blanco, puntos y parentesis por _ - además quita tildes **/
    public function formatear_cadena($cadena) {
      $no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
      $permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
      $texto = str_replace($no_permitidas, $permitidas ,$cadena);
      $texto = str_replace('}', '', str_replace('{', '', str_replace(',', '', str_replace('.', '', str_replace(')', '', str_replace('(', '', str_replace(' ', '_', $texto)))))));
      return $texto;
    }

    // function cmp_by_optionNumber($a, $b) {
    //   return $a['cant'] <=> $b['cant'];
    // }

    public function programed_professionals(Request $request){

        //filtro
        $tipo = 0;
        if ($request->filter) {
          $tipo = $request->filter;
        }

        //obtiene usuaros con especialidad
        // dd(Auth::hasRole('profesional'));
        $users = User::select('id')->whereHas('userSpecialties', function ($query) {
                                        return $query->where('principal',1);
                                    })
                    ->whereHas(
                    'roles', function($q){
                        $q->where('name', 'profesional');
                    })
                    ->get();
                    // dd($users);

        //busca usuarios en rrhh
        // $rrhhs = Rrhh::orderby('name','ASC')->get();
        $rrhhs = User::whereIn('rut',$users)->orderby('name','ASC')->whereHas(
            'contracts', function($q){
                $q->where('law', 'LEY 19.664');
            })->get();

        //ciclo
        $array = array();
        $total = 0;
        $total_with_theorical = 0;
        $total_withnot_theorical = 0;
        foreach ($rrhhs as $key => $rrhh) {
            if($rrhh->contracts)

            $user = User::where('id',$rrhh->rut)->first();
            $profesions = null;

            if ($user) {
                if ($user->specialties) {
                    foreach ($user->specialties as $key => $specialty) {
                        $profesions = $profesions . $specialty->specialty_name . ",";
                        if ($key == 2) {
                            break;
                        }
                    }
                }

                if ($user->professions) {
                    foreach ($user->professions as $key => $profession) {
                        $profesions = $profesions . $profession->profession_name . ",";
                        if ($key == 2) {
                            break;
                        }
                    }
                }
            }

            if ($tipo == 0) {
              if (TheoreticalProgramming::where('rut',$rrhh->rut)->count()>0) {
                  $array[$rrhh->getShortNameAttribute()][$profesions]['cant'] = 'Sí';
                  $total_with_theorical += 1;
              }else{
                  $array[$rrhh->getShortNameAttribute()][$profesions]['cant'] = 'No';
                  $total_withnot_theorical += 1;
              }
              $total += 1;
            }

            if ($tipo == 1) {
              if (TheoreticalProgramming::where('rut',$rrhh->rut)->count()>0) {
                  $array[$rrhh->getShortNameAttribute()][$profesions]['cant'] = 'Sí';
                  $total_with_theorical += 1;
              }
              $total += 1;
            }

            if ($tipo == 2) {
              if (TheoreticalProgramming::where('rut',$rrhh->rut)->count() == 0) {
                  $array[$rrhh->getShortNameAttribute()][$profesions]['cant'] = 'No';
                  $total_withnot_theorical += 1;
              }
              $total += 1;
            }

        }

        // dd(usort($array, "cmp_by_optionNumber"));
        // dd($array, usort($array,'cant'));
        // dd($total);

        return view('medical_programmer.management.reports.programed_professionals',compact('array','total','total_with_theorical','total_withnot_theorical','request'));
    }

    public function programed_specialties(){

        $specialties = Specialty::orderBy('specialty_name','DESC')->get();

        $array = array();
        foreach ($specialties as $key => $specialty) {

            $array[$specialty->specialty_name]['total'] = UserSpecialty::where('principal',1)->where('specialty_id',$specialty->id)->count();
            $array[$specialty->specialty_name]['con_teorico'] = TheoreticalProgramming::whereIn('rut',UserSpecialty::select('user_id')
                                                                                                                   ->where('specialty_id',$specialty->id)
                                                                                                                   ->where('principal',1)
                                                                                                                   // ->whereHas('users', function($q){
                                                                                                                   //     // $q->whereHas('roles', function($q){
                                                                                                                   //     //             $q->where('name', 'profesional');
                                                                                                                   //     //         });
                                                                                                                   //     return $q;
                                                                                                                   // })
                                                                                                                   ->get())
                                                                                      ->where('specialty_id',$specialty->id)
                                                                                      ->select('rut')
                                                                                      ->distinct('rut')
                                                                                      ->get()
                                                                                      ->count();
        }

        return view('medical_programmer.management.reports.programed_specialties',compact('array'));
    }

    public function programed_by_services(){

        $services = Service::orderBy('service_name','DESC')->get();

        $array = array();
        foreach ($services as $key => $service) {
          $profesionals = array();
          $profesionals[$service->service_name] = 0;
          foreach ($service->contracts as $key => $contract) {
            if ($contract->theoretical_programmings->count() > 0) {
              $profesionals[$service->service_name] += 1;
            }
          }
          $array[$service->service_name]['total'] = $service->contracts->groupBy('rut')->count();
          $array[$service->service_name]['con_teorico'] = $profesionals[$service->service_name];
        }

        return view('medical_programmer.management.reports.programed_by_services',compact('array'));
    }


    public function event_detail($rut, $activity_id,$contract_id,$specialty_id,$profession_id,$start_date,$end_date,$year){
      $theoreticalProgramming = TheoreticalProgramming::where('user_id',$rut)
                                                      ->where('activity_id',$activity_id)
                                                      ->where('contract_id',$contract_id)
                                                      // ->where('specialty_id',$specialty_id)
                                                      ->when($specialty_id != "undefined", function ($q) use ($specialty_id) {
                                                          return $q->where('specialty_id', $specialty_id);
                                                      })
                                                      // ->where('profession_id',$profession_id)
                                                      ->when($profession_id != "undefined", function ($q) use ($profession_id) {
                                                          return $q->where('profession_id', $profession_id);
                                                      })
                                                      ->where('start_date',Carbon::parse(date("m/d/Y H:i", $start_date/1000)))
                                                      ->where('end_date',Carbon::parse(date("m/d/Y H:i", $end_date/1000)))
                                                      ->first();

      $subactivities = SubActivity::where('specialty_id',$specialty_id)
                                  ->where('activity_id',$activity_id)
                                  ->get();
      // dd($subactivities);

      return view('medical_programmer.management.event_detail',compact('theoreticalProgramming','subactivities'));
    }

    public function editMyEvent(Request $request){

      $theoreticalProgramming = TheoreticalProgramming::find($request->id);
      $year = $theoreticalProgramming->year;
      $first_date = new Carbon($theoreticalProgramming->start_date);
      $last_date = new Carbon($theoreticalProgramming->end_date);

      if ($request->action == "Esta semana") {

        $theoreticalProgramming->sub_activity_id = $request->sub_activity_id;
        $theoreticalProgramming->save();

      }elseif($request->action == "Todas las semanas"){
        while (date('Y', strtotime($first_date)) == $year) {
              $theoreticalProgrammingUpdt = TheoreticalProgramming::where('user_id',$theoreticalProgramming->user_id)
                                                              ->where('activity_id',$theoreticalProgramming->activity_id)
                                                              ->where('contract_id',$theoreticalProgramming->contract_id)
                                                              ->where('specialty_id',$theoreticalProgramming->specialty_id)
                                                              ->where('profession_id',$theoreticalProgramming->profession_id)
                                                              ->where('start_date',$first_date)
                                                              ->where('end_date',$last_date)->first();
              if ($theoreticalProgrammingUpdt != null) {
                $theoreticalProgrammingUpdt->sub_activity_id = $request->sub_activity_id;
                $theoreticalProgrammingUpdt->save();
              }

              $first_date = $first_date->addWeek(1);
              $last_date = $last_date->addWeek(1);
          }
      }elseif($request->action == "Semana volante"){
        while (date('Y', strtotime($first_date)) == $year) {
              $theoreticalProgrammingUpdt = TheoreticalProgramming::where('user_id',$theoreticalProgramming->user_id)
                                                              ->where('activity_id',$theoreticalProgramming->activity_id)
                                                              ->where('contract_id',$theoreticalProgramming->contract_id)
                                                              ->where('specialty_id',$theoreticalProgramming->specialty_id)
                                                              ->where('profession_id',$theoreticalProgramming->profession_id)
                                                              ->where('start_date',$first_date)
                                                              ->where('end_date',$last_date)->first();
              if ($theoreticalProgrammingUpdt != null) {
                $theoreticalProgrammingUpdt->sub_activity_id = $request->sub_activity_id;
                $theoreticalProgrammingUpdt->save();
              }

              $first_date = $first_date->addWeek(6);
              $last_date = $last_date->addWeek(6);
          }
      }

      $parametros = explode("?", $request->url);
      return redirect()->route('medical_programmer.theoretical_programming.index', $parametros[1]);
    }

    public function deleteMyEventId(Request $request){

      $theoreticalProgramming = TheoreticalProgramming::find($request->id);
      $year = $theoreticalProgramming->year;
      $first_date = new Carbon($theoreticalProgramming->start_date);
      $last_date = new Carbon($theoreticalProgramming->end_date);

      if ($request->action == "Esta semana") {
        $theoreticalProgramming->delete();

      }elseif($request->action == "Todas las semanas"){
        while (date('Y', strtotime($first_date)) == $year) {
              $theoreticalProgrammingDelete = TheoreticalProgramming::where('user_id',$theoreticalProgramming->user_id)
                                                              ->where('activity_id',$theoreticalProgramming->activity_id)
                                                              ->where('contract_id',$theoreticalProgramming->contract_id)
                                                              ->where('specialty_id',$theoreticalProgramming->specialty_id)
                                                              ->where('profession_id',$theoreticalProgramming->profession_id)
                                                              ->where('start_date',$first_date)
                                                              ->where('end_date',$last_date)->first();
              if ($theoreticalProgrammingDelete != null) {
                $theoreticalProgrammingDelete->delete();
              }

              $first_date = $first_date->addWeek(1);
              $last_date = $last_date->addWeek(1);
          }
      }elseif($request->action == "Semana volante"){
        while (date('Y', strtotime($first_date)) == $year) {
              $theoreticalProgrammingDelete = TheoreticalProgramming::where('user_id',$theoreticalProgramming->user_id)
                                                              ->where('activity_id',$theoreticalProgramming->activity_id)
                                                              ->where('contract_id',$theoreticalProgramming->contract_id)
                                                              ->where('specialty_id',$theoreticalProgramming->specialty_id)
                                                              ->where('profession_id',$theoreticalProgramming->profession_id)
                                                              ->where('start_date',$first_date)
                                                              ->where('end_date',$last_date)->first();
              if ($theoreticalProgrammingDelete != null) {
                $theoreticalProgrammingDelete->delete();
              }

              $first_date = $first_date->addWeek(6);
              $last_date = $last_date->addWeek(6);
          }
      }



      $parametros = explode("?", $request->url);
      return redirect()->route('medical_programmer.theoretical_programming.index', $parametros[1]);
    }

}
