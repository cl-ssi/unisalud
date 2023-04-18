<?php

namespace App\Http\Controllers\Epi;

use App\Models\Epi\SuspectCase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Mail;
use App\Mail\DelegateChagasNotification;
use Illuminate\Support\Facades\Storage;

class SuspectCaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tray)
    {
        if ($tray == 'Mi Organización') {
            // dd('soy organizacion');
            $suspectcases = SuspectCase::where('organization_id', Auth::user()->practitioners->last()->organization->id)->get();
        }
        if ($tray === 'Pendientes de Recepción') {
            $suspectcases = SuspectCase::whereNull('reception_at')->get();
        }
        if ($tray === 'Pendientes de Resultado') {
            $suspectcases = SuspectCase::whereNull('chagas_result_screening_at')->whereNotNull('reception_at')->get();
        }        

        if ($tray === 'Finalizadas') {
            $suspectcases = SuspectCase::whereNotNull('chagas_result_screening_at')->whereNotNull('reception_at')->get();
        }


        if ($tray === 'Todas las Solicitudes') {
            $suspectcases = SuspectCase::all();
        }         
        return view('epi.chagas.index', compact('suspectcases', 'tray'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(User $user)
    {
        //traigo la última organizacion
        $organizations = Organization::where('id', Auth::user()->practitioners->last()->organization->id)->OrderBy('alias')->get();
        return view('epi.chagas.create', compact('organizations', 'user'));
    }

    public function reception(SuspectCase $suspectcase)
    {
        $suspectcase->receptor_id = Auth::id();
        $suspectcase->reception_at = date('Y-m-d H:i:s');
        //TODO código duro, debería ser dinámico. ¿otra tabla solo para los labotatorios u ocupar organizatión?
        $suspectcase->laboratory_id = 4;
        $suspectcase->save();

        session()->flash('success', 'Se ha recepcionada la muestra: '
            . $suspectcase->id . ' en laboratorio: Hospital Ernesto Torres Galdames ');
            

        return redirect()->back();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $sc = new SuspectCase($request->All());
        $sc->save();
        session()->flash('success', 'Se creo caso sospecha exitosamente');
        if ($request->research_group == 'Tranmisión Vertical') {
            Mail::to('claudia.caronna@redsalud.gob.cl')
                ->send(new DelegateChagasNotification($sc));
        }

        return redirect()->back();
        //return redirect()->route('epi.chagas.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Epi\SuspectCase  $suspectCase
     * @return \Illuminate\Http\Response
     */
    public function show(SuspectCase $suspectCase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Epi\SuspectCase  $suspectCase
     * @return \Illuminate\Http\Response
     */
    public function edit(SuspectCase $suspectCase)
    {
        //
        $organizations = Organization::OrderBy('alias')->get();
        return view('epi.chagas.edit', compact('suspectCase', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Epi\SuspectCase  $suspectCase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SuspectCase $suspectCase)
    {
        //               
        $suspectCase->fill($request->all());
        if($request->hasFile('chagas_result_screening_file'))
        {            
            $file_name = $suspectCase->id.'_screening';
            $file = $request->file('chagas_result_screening_file');
            $suspectCase->chagas_result_screening_file = $file->storeAs('/unisalud/chagas', $file_name.'.'.$file->extension(), 'gcs');
        }

        if($request->hasFile('chagas_result_confirmation_file'))
        {            
            $file_name = $suspectCase->id.'_confirmation';
            $file = $request->file('chagas_result_confirmation_file');
            $suspectCase->chagas_result_confirmation_file = $file->storeAs('/unisalud/chagas', $file_name.'.'.$file->extension(), 'gcs');
        }


        $suspectCase->save();

        if ($request->chagas_result_screening == 'En Proceso') {
            Mail::to('sandra.rojas@cormudesi.cl')->send(new DelegateChagasNotification($suspectCase));
            
        }


        session()->flash('success', 'Se añadieron los datos adicionales a Caso sospecha');
        return redirect()->back();
    }

    public function downloadscreening(SuspectCase $suspectCase)
    {
        return Storage::disk('gcs')->download($suspectCase->chagas_result_screening_file);
    }

    public function downloadconfirmation(SuspectCase $suspectCase)
    {
        return Storage::disk('gcs')->download($suspectCase->chagas_result_confirmation_file);
    }
    

    public function fileDeletescreening(SuspectCase $suspectCase)
    {        
        Storage::disk('gcs')->delete($suspectCase->chagas_result_screening_file);
        $suspectCase->chagas_result_screening_file = false;
        $suspectCase->save();
        session()->flash('info', 'Se ha eliminado el archivo correctamente.');
        return redirect()->back();
    }

    public function fileDeleteconfirmation(SuspectCase $suspectCase)
    {        
        Storage::disk('gcs')->delete($suspectCase->chagas_result_confirmation_file);
        $suspectCase->chagas_result_confirmation_file = false;
        $suspectCase->save();
        session()->flash('info', 'Se ha eliminado el archivo correctamente.');
        return redirect()->back();
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Epi\SuspectCase  $suspectCase
     * @return \Illuminate\Http\Response
     */
    public function destroy(SuspectCase $suspectCase)
    {
        //
    }

    public function resultchagasnegative(SuspectCase $case)
    {
        //
        // dd(llegue);
        return view('epi.chagas.resultchagasnegative', compact('case'));
    }

    public function printresultchagasnegative(SuspectCase $suspectCase)
    {
        //
        $case = $suspectCase;
        $pdf = \PDF::loadView('epi.chagas.resultchagasnegative', compact('case'));
        return $pdf->stream();
    }
}
