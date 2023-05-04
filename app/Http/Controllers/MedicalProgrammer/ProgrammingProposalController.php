<?php

namespace App\Http\Controllers\MedicalProgrammer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\MedicalProgrammer\Specialty;
use App\Models\MedicalProgrammer\ProgrammingProposal;
use App\Models\MedicalProgrammer\ProgrammingProposalDetail;
use App\Models\MedicalProgrammer\ProgrammingProposalSignatureFlow;
// use App\Models\MedicalProgrammer\TheoreticalProgramming;

use App\Models\User;

class ProgrammingProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        dd("");
      $type = $request->get('type');
      $specialty_id = $request->get('specialty_id');
      $profesion_id = $request->get('profesion_id');
      $user_id = $request->get('user_id');

      if (Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de CAE Médico') ||
          Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección Médica')) {
        $programmingProposals = ProgrammingProposal::whereNotNull('specialty_id')
                                                   ->when($type != null, function ($q) use ($type) {
                                                      return $q->when($type == "Médico", function ($q) {
                                                         return $q->whereNull('profession_id');
                                                      })->when($type == "No médico", function ($q) {
                                                         return $q->whereNull('specialty_id');
                                                      });
                                                   })
                                                   ->when($specialty_id != null, function ($q) use ($specialty_id) {
                                                      return $q->where('specialty_id',$specialty_id);
                                                   })
                                                   ->when($profesion_id != null, function ($q) use ($profesion_id) {
                                                      return $q->where('profesion_id',$profesion_id);
                                                   })
                                                   ->when($user_id != null, function ($q) use ($user_id) {
                                                      return $q->where('user_id',$user_id);
                                                   })
                                                   ->orderBy('id','DESC')
                                                   ->get();
      }elseif (Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de CAE No médico') ||
               Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección DGCP')) {
        $programmingProposals = ProgrammingProposal::whereNotNull('profession_id')
                                                    ->when($type != null, function ($q) use ($type) {
                                                       return $q->when($type == "Médico", function ($q) {
                                                          return $q->whereNull('profession_id');
                                                       })->when($type == "No médico", function ($q) {
                                                          return $q->whereNull('specialty_id');
                                                       });
                                                    })
                                                    ->when($specialty_id != null, function ($q) use ($specialty_id) {
                                                       return $q->where('specialty_id',$specialty_id);
                                                    })
                                                    ->when($profesion_id != null, function ($q) use ($profesion_id) {
                                                       return $q->where('profesion_id',$profesion_id);
                                                    })
                                                    ->when($user_id != null, function ($q) use ($user_id) {
                                                       return $q->where('user_id',$user_id);
                                                    })
                                                    ->orderBy('id','DESC')
                                                    ->get();
      }else{
        $programmingProposals = ProgrammingProposal::orderBy('id','DESC')
                                                    ->when($type != null, function ($q) use ($type) {
                                                       return $q->when($type == "Médico", function ($q) {
                                                          return $q->whereNull('profession_id');
                                                       })->when($type == "No médico", function ($q) {
                                                          return $q->whereNull('specialty_id');
                                                       });
                                                    })
                                                    ->when($specialty_id != null, function ($q) use ($specialty_id) {
                                                       return $q->where('specialty_id',$specialty_id);
                                                    })
                                                    ->when($profesion_id != null, function ($q) use ($profesion_id) {
                                                       return $q->where('profesion_id',$profesion_id);
                                                    })
                                                    ->when($user_id != null, function ($q) use ($user_id) {
                                                       return $q->where('user_id',$user_id);
                                                    })
                                                    ->get();
      }

      return view('medical_programmer.programming_proposals.index',compact('request','programmingProposals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('medical_programmer.programming_proposals.create',compact('request'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $programmingProposal = new ProgrammingProposal($request->All());
      $programmingProposal->type = $request->proposal_type;
      $programmingProposal->request_date = Carbon::now();
      $programmingProposal->status = "Creado";
      $programmingProposal->save();

      $programmingProposalSignatureFlow = new ProgrammingProposalSignatureFlow();
      $programmingProposalSignatureFlow->programming_proposal_id = $programmingProposal->id;
      $programmingProposalSignatureFlow->user_id = Auth::id();
      $programmingProposalSignatureFlow->sign_position = 1;
      $programmingProposalSignatureFlow->signature_date = Carbon::now();
      $programmingProposalSignatureFlow->status = "Solicitud creada";

      if (Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección Médica')) {
        $programmingProposalSignatureFlow->type = "Subdirección Médica";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección DGCP')){
        $programmingProposalSignatureFlow->type = "Subdirección DGCP";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de CAE Médico')){
        $programmingProposalSignatureFlow->type = "Jefatura CAE Médica";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de CAE No médico')){
        $programmingProposalSignatureFlow->type = "Jefatura CAE No médica";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de Servicio')){
        $programmingProposalSignatureFlow->type = "Jefe de Servicio";
      }else{
        $programmingProposalSignatureFlow->type = "Funcionario";
      }

      $programmingProposalSignatureFlow->save();

      session()->flash('success', 'Se ha registrado la información.');
      return redirect()->route('medical_programmer.programming_proposal.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_confirmation(Request $request, ProgrammingProposal $programmingProposal)
    {
      $last_position = $programmingProposal->signatureFlows->last()->sign_position;

      $programmingProposalSignatureFlow = new ProgrammingProposalSignatureFlow();
      $programmingProposalSignatureFlow->programming_proposal_id = $programmingProposal->id;
      $programmingProposalSignatureFlow->user_id = Auth::id();
      $programmingProposalSignatureFlow->sign_position = $last_position + 1;
      $programmingProposalSignatureFlow->signature_date = Carbon::now();

      if ($request->buttonaction == "accept_button") {
        $programmingProposalSignatureFlow->status = "Solicitud confirmada";

        if (Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección Médica') || Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección DGCP')) {
          $programmingProposal->status = "Confirmado";
          $programmingProposal->save();
        }
        else{
          $programmingProposal->status = "En proceso";
          $programmingProposal->save();
        }
      }
      else{
        $programmingProposalSignatureFlow->status = "Solicitud rechazada";

        $programmingProposal->status = "Rechazado";
        $programmingProposal->save();
      }

      if (Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección Médica')) {
        $programmingProposalSignatureFlow->type = "Subdirección Médica";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Subdirección DGCP')){
        $programmingProposalSignatureFlow->type = "Subdirección DGCP";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de CAE Médico')){
        $programmingProposalSignatureFlow->type = "Jefatura CAE Médica";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de CAE No médico')){
        $programmingProposalSignatureFlow->type = "Jefatura CAE No médica";
      }elseif(Auth::user()->hasPermissionTo('Mp: Proposal - Jefe de Servicio')){
        $programmingProposalSignatureFlow->type = "Jefe de Servicio";
      }else{
        $programmingProposalSignatureFlow->type = "Funcionario";
      }

      $programmingProposalSignatureFlow->observation = $request->observation;
      $programmingProposalSignatureFlow->save();

      session()->flash('success', 'Se ha registrado la información.');
      return redirect()->route('medical_programmer.programming_proposal.edit', $programmingProposal);
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
    public function edit(ProgrammingProposal $programmingProposal)
    {
        //obtiene detalles ingresados para mostrarlos en jcalendar
        $start_date = $programmingProposal->start_date;
        $end_date = $programmingProposal->end_date;
        $programmed_days = [];
        $count = 0;

        while ($start_date <= $end_date) {
          $dayOfWeek = $start_date->dayOfWeek;

          foreach ($programmingProposal->details->where('day',$dayOfWeek) as $key => $detail) {
            $programmed_days[$count]['start_date'] = $start_date->format('Y-m-d') . " " . $detail->start_hour;
            $programmed_days[$count]['end_date'] = $start_date->format('Y-m-d') . " " . $detail->end_hour;
            $programmed_days[$count]['data'] = $detail;
            $count+=1;
          }
          $start_date->addDays(1);
        }

        // dd($programmed_days);

        $total_hours = 0;
        foreach ($programmingProposal->details as $key => $detail) {
          $total_hours += Carbon::parse($detail->end_hour)->diffInMinutes(Carbon::parse($detail->start_hour))/60;
        }

        //obtiene teoricos para mostrar en jcalendar

        // $theoreticalProgrammings = TheoreticalProgramming::where('user_id',$programmingProposal->user_id)
        //                                                  ->where('contract_id',$programmingProposal->contract_id)
        //                                                  ->where('specialty_id',$programmingProposal->specialty_id)
        //                                                  ->whereBetween('start_date',[$programmingProposal->start_date,$programmingProposal->end_date])
        //                                                  ->get();

        // obtiene posibles programaciones anteriores para comparación
        $last_programmingProposal = ProgrammingProposal::where('user_id',$programmingProposal->user_id)
                                                       ->where('contract_id',$programmingProposal->contract_id)
                                                       ->where('specialty_id',$programmingProposal->specialty_id)
                                                       ->where('id','<',$programmingProposal->id)
                                                       ->where('status', 'Confirmado')
                                                       ->latest()->first();

       $last_programmed_days = [];
       if ($last_programmingProposal != null) {
         $start_date = $last_programmingProposal->start_date;
         $end_date = $last_programmingProposal->end_date;
         $count = 0;

         while ($start_date <= $end_date) {
           $dayOfWeek = $start_date->dayOfWeek;

           foreach ($last_programmingProposal->details->where('day',$dayOfWeek) as $key => $detail) {
             $last_programmed_days[$count]['start_date'] = $start_date->format('Y-m-d') . " " . $detail->start_hour;
             $last_programmed_days[$count]['end_date'] = $start_date->format('Y-m-d') . " " . $detail->end_hour;
             $last_programmed_days[$count]['data'] = $detail;
             $count+=1;
           }
           $start_date->addDays(1);
         }
       }

       //otras propuestas de la persona
       $other_contracts_info = [];
       foreach ($programmingProposal->user->programmingProposals->where('status','!=','Rechazado')->where('id','!=',$programmingProposal->id) as $key => $programmingProposal_) {
         if ($programmingProposal_ != null) {
           $start_date = $programmingProposal_->start_date;
           $end_date = $programmingProposal_->end_date;
           $count = 0;

           while ($start_date <= $end_date) {
             $dayOfWeek = $start_date->dayOfWeek;

             foreach ($programmingProposal_->details->where('day',$dayOfWeek) as $key => $detail) {
               $other_contracts_info[$count]['start_date'] = $start_date->format('Y-m-d') . " " . $detail->start_hour;
               $other_contracts_info[$count]['end_date'] = $start_date->format('Y-m-d') . " " . $detail->end_hour;
               $other_contracts_info[$count]['data'] = $detail;
               $count+=1;
             }
             $start_date->addDays(1);
           }
         }
       }
       // dd($other_contracts_info);

        return view('medical_programmer.programming_proposals.edit', compact('programmingProposal','last_programmed_days',
                                                                             'programmed_days','total_hours','other_contracts_info'));
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
    public function destroy(ProgrammingProposal $programmingProposal)
    {
        $programmingProposal->delete();
        session()->flash('success', 'Se ha eliminado la información.');
        return redirect()->route('medical_programmer.programming_proposal.index');
    }

    public function programming_by_practioner(Request $request)
    {
      $user_id = $request->get('user_id');
      $user = User::find($user_id);
      $programmingProposals = ProgrammingProposal::where('id',0)->get();
      if ($user != null) {

        $now = Carbon::now();
        $programmingProposals = ProgrammingProposal::where('user_id',$request->user_id)
                                                  // ->where('contract_id',$programmingProposal->contract_id)
                                                  ->where('specialty_id',$request->specialty_id)
                                                  ->where('status', 'Confirmado')
                                                  ->whereYear('request_date',$now->format('Y'))
                                                  ->get();


        foreach ($programmingProposals as $key => $programmingProposal) {
          $array = array();
          foreach ($programmingProposal->details as $key => $detail) {
            if($detail->subactivity) {
              $array[$detail->activity->activity_name . " - " . $detail->subactivity->sub_activity_name] = 0;
            }else{
              $array[$detail->activity->activity_name] = 0;
            }
          }
          foreach ($programmingProposal->details as $key => $detail) {
            if($detail->subactivity) {
              $array[$detail->activity->activity_name . " - " . $detail->subactivity->sub_activity_name] += Carbon::parse($detail->end_hour)->diffInMinutes(Carbon::parse($detail->start_hour))/60;
            }else{
              $array[$detail->activity->activity_name] += Carbon::parse($detail->end_hour)->diffInMinutes(Carbon::parse($detail->start_hour))/60;
            }
          }
          $programmingProposal->array = $array;
        }

        // dd($programmingProposals->first()->details);
      }

      return view('some.programming_by_practioner',compact('request','programmingProposals'));
    }

    public function consolidated_programmings(Request $request){
      // dd($request->start_date);

      set_time_limit(7200);
      ini_set('memory_limit', '2048M');

      $theoreticalProgrammings = null;
      $programmingProposals = null;
      $programmed_days = [];
      $array_medic_programmings = array();
      $array_non_medic_programmings = array();

      if ($request->date != null) {
          // if ($request->user_id != null) {
              $request_start_date = Carbon::parse($request->date)->startOfWeek();
              $request_end_date = Carbon::parse($request->date)->endOfWeek();
              $now = Carbon::now();
              $programmingProposals = ProgrammingProposal::with('details')
                                                        //   when($request->specialty_id != null, function ($query) use ($request) {
                                                        //     $query->where('specialty_id',$request->specialty_id);
                                                        // })
                                                        // ->when($request->profession_id != null, function ($query) use ($request) {
                                                        //     $query->where('profession_id',$request->profession_id);
                                                        // })
                                                        ->whereYear('request_date',$now->format('Y'))
                                                        ->where('start_date','<=',$request_start_date)
                                                        // ->where('status', 'Confirmado')
                                                        ->orderBy('user_id')
                                                        ->orderBy('request_date','ASC') //debe ir así, para que deje los primeros ingresados al principio, y aspi se puedan ordenar correctamente y no se pisen
                                                        ->get();

                                                        // dd($programmingProposals);

              // separa onthefly los días que se mostraran en fullcalendar
              $programmed_days = [];
              $count = 0;
              // ciclo para obtener fechas
              foreach ($programmingProposals as $key => $programmingProposal) {

                $start_date = clone $request_start_date;
                $end_date = clone $request_end_date;

                $programmingProposal->aux_OfficialFullName = $programmingProposal->user->OfficialFullName;
                $programmingProposal->aux_contract_id = $programmingProposal->contract->contract_id;
                if($programmingProposal->specialty){
                  $programmingProposal->aux_id_specialty = $programmingProposal->specialty->id_specialty;
                  $programmingProposal->aux_specialty_name = $programmingProposal->specialty->specialty_name;
                }
                if($programmingProposal->profession){
                  $programmingProposal->aux_id_profession = $programmingProposal->profession->id_profession;
                  $programmingProposal->aux_profession_name = $programmingProposal->profession->profession_name;
                }

                //se obtienen los del periodo actual
                while ($start_date <= $end_date) {
                  $dayOfWeek = $start_date->dayOfWeek;
                  // print_r($programmingProposal->id . "<<<<---\n");
                  foreach ($programmingProposal->details->where('day',$dayOfWeek) as $key2 => $detail) {
                    
                    //solo los que esten en el rango de fechas
                    if (Carbon::parse($start_date->format('Y-m-d'))->between($request_start_date, $request_end_date)) {
                      $programmed_days[$programmingProposal->user_id][$count]['start_date'] = $start_date->format('Y-m-d') . " " . $detail->start_hour;
                      $programmed_days[$programmingProposal->user_id][$count]['end_date'] = $start_date->format('Y-m-d') . " " . $detail->end_hour;
                      $programmed_days[$programmingProposal->user_id][$count]['data'] = $detail;
                      $programmed_days[$programmingProposal->user_id][$count]['fullName'] = $programmingProposal->aux_OfficialFullName;
                      $programmed_days[$programmingProposal->user_id][$count]['contractId'] = $programmingProposal->aux_contract_id;
                      $programmed_days[$programmingProposal->user_id][$count]['activity'] = $detail->activity->id_activity . ' - ' . $detail->activity->activity_name;
                      if($programmingProposal->aux_id_specialty){
                        $programmed_days[$programmingProposal->user_id][$count]['specialty'] = $programmingProposal->aux_id_specialty . ' - ' . $programmingProposal->aux_specialty_name;
                      }else{
                        $programmed_days[$programmingProposal->user_id][$count]['specialty'] = '';
                      }
                      if($programmingProposal->aux_id_profession){
                        $programmed_days[$programmingProposal->user_id][$count]['profession'] = $programmingProposal->aux_id_profession . ' - ' . $programmingProposal->aux_profession_name;
                      }else{
                        $programmed_days[$programmingProposal->user_id][$count]['profession'] = '';
                      }
                      
                      
                      $count+=1;
                    }
                  }
                  $start_date->addDays(1);
                }
              }

              // dd($programmed_days);

              // se obtiene array final
              foreach ($programmed_days as $key => $programmed_day) {

                foreach ($programmed_day as $key2 => $value) {
                  if ($value['specialty']!='') {
                    $specialty = $value['specialty'];
                    $array_medic_programmings[$value['fullName']][$value['contractId']][$value['specialty']][$value['activity']]['hours'] = 0;
                    $array_medic_programmings[$value['fullName']][$value['contractId']][$value['specialty']][$value['activity']]['performance'] = 0;
                  }
                  // profesiones
                  if ($value['data']->programmingProposal->profession) {
                    $profesion = $value['profession'];
                    $array_non_medic_programmings[$value['fullName']][$value['contractId']][$value['profession']][$value['activity']]['hours'] = 0;
                    $array_non_medic_programmings[$value['fullName']][$value['contractId']][$value['profession']][$value['activity']]['performance'] = 0;
                  }
                }

                foreach ($programmed_day as $key => $value) {

                  // especialidades
                  if ($value['specialty']!='') {
                    $start = Carbon::parse($value['start_date']);
                    $end = Carbon::parse($value['end_date']);
                    $array_medic_programmings[$value['fullName']][$value['contractId']][$value['specialty']][$value['activity']]['hours'] += $start->diffInMinutes($end)/60;
                    $array_medic_programmings[$value['fullName']][$value['contractId']][$value['specialty']][$value['activity']]['performance'] = $value['data']->activity->specialties->where('id',$value['data']->programmingProposal->specialty_id)->first()->pivot->performance;
                  }
                  // profesiones
                  if ($value['data']->programmingProposal->profession) {
                    $start = Carbon::parse($value['start_date']);
                    $end = Carbon::parse($value['end_date']);
                    $array_non_medic_programmings[$value['fullName']][$value['contractId']][$value['specialty']][$value['activity']]['hours'] += $start->diffInMinutes($end)/60;
                    $array_non_medic_programmings[$value['fullName']][$value['contractId']][$value['specialty']][$value['activity']]['performance'] = $value['data']->activity->specialties->where('id',$value['data']->programmingProposal->specialty_id)->first()->pivot->performance;
                  }
                }
              }

              // dd($array_medic_programmings, $array_non_medic_programmings);
      }

      return view('medical_programmer.management.reports.consolidated_programmings',compact('array_medic_programmings','array_non_medic_programmings','request'));
    }
}
