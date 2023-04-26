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
            $suspectcases = SuspectCase::where('organization_id', Auth::user()->practitioners->last()->organization->id)->paginate(100);
        }
        if ($tray === 'Pendientes de Recepción') {
            $suspectcases = SuspectCase::whereNull('reception_at')->orderBy('id', 'desc')->paginate(100);
        }

        if ($tray === 'Pendientes de Resultado') {
            $suspectcases = SuspectCase::whereNull('chagas_result_screening_at')->orderBy('id', 'desc')->whereNotNull('reception_at')->paginate(100);
        }

        if ($tray === 'Finalizadas') {
            $suspectcases = SuspectCase::whereNotNull('chagas_result_screening_at')->whereNotNull('reception_at')->paginate(100);
        }

        if ($tray === 'Todas las Solicitudes') {
            $suspectcases = SuspectCase::paginate(100);
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

        $mothers = SuspectCase::where('chagas_result_confirmation', 'Positivo')->get();

        return view('epi.chagas.create', compact('organizations', 'user', 'mothers'));
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
        $request->validate([
            'research_group' => 'required',
            'mother_id' => $request->research_group == 'Transmisión Vertical' ? 'required|exists:users,id' : '',
            'newborn_week' => $request->research_group == 'Gestante (+semana gestacional)' ? 'required|numeric|min:2|max:44' : '',
        ]);        

        $sc = new SuspectCase($request->All());
        $sc->save();
        session()->flash('success', 'Se creo caso sospecha exitosamente');

        if ($request->research_group == 'Transmisión Vertical') {
            Mail::to('claudia.caronna@redsalud.gob.cl')
                ->send(new DelegateChagasNotification($sc));
        }
        $request->flash();
        return redirect()->back();
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

    public function tutorials()
    {
        //
        return view('epi.chagas.tutorials');
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
        if ($request->hasFile('chagas_result_screening_file')) {
            $file_name = $suspectCase->id . '_screening';
            $file = $request->file('chagas_result_screening_file');
            $suspectCase->chagas_result_screening_file = $file->storeAs('/unisalud/chagas', $file_name . '.' . $file->extension(), ['disk' => 'gcs']);
        }

        if ($request->hasFile('chagas_result_confirmation_file')) {
            $file_name = $suspectCase->id . '_confirmation';
            $file = $request->file('chagas_result_confirmation_file');
            $suspectCase->chagas_result_confirmation_file = $file->storeAs('/unisalud/chagas', $file_name . '.' . $file->extension(), 'gcs');
        }

        if ($request->hasFile('direct_exam_file')) {
            $file_name = $suspectCase->id . '_direct_exam';
            $file = $request->file('direct_exam_file');
            $suspectCase->direct_exam_file = $file->storeAs('/unisalud/chagas', $file_name . '.' . $file->extension(), 'gcs');
        }

        if ($request->hasFile('pcr_first_file')) {
            $file_name = $suspectCase->id . '_primer_pcr';
            $file = $request->file('pcr_first_file');
            $suspectCase->pcr_first_file = $file->storeAs('/unisalud/chagas', $file_name . '.' . $file->extension(), 'gcs');
        }

        if ($request->hasFile('pcr_second_file')) {
            $file_name = $suspectCase->id . '_segunda_pcr';
            $file = $request->file('pcr_second_file');
            $suspectCase->pcr_second_file = $file->storeAs('/unisalud/chagas', $file_name . '.' . $file->extension(), 'gcs');
        }

        if ($request->hasFile('pcr_third_file')) {
            $file_name = $suspectCase->id . '_tercera_pcr';
            $file = $request->file('pcr_third_file');
            $suspectCase->pcr_third_file = $file->storeAs('/unisalud/chagas', $file_name . '.' . $file->extension(), 'gcs');
        }

        $suspectCase->save();

        if ($request->chagas_result_screening == 'En Proceso') {
            $organization = Organization::where('id', $suspectCase->organization_id)->first();
            $epi_mails = $organization->epi_mail;
            $emails = explode(',', $epi_mails);

            foreach ($emails as $email) {
                Mail::to(trim($email))->send(new DelegateChagasNotification($suspectCase));
            }
        }


        session()->flash('success', 'Se añadieron los datos adicionales a Caso sospecha');
        return redirect()->back();
    }
    public function downloadFile($fileName)
    {

        return Storage::disk('gcs')->download($fileName);
    }

    public function deleteFile(SuspectCase $suspectCase, $attribute)
    {
        $fileAttribute = $attribute . '_file';
        if ($suspectCase->$fileAttribute) {
            Storage::disk('gcs')->delete($suspectCase->$fileAttribute);
            $suspectCase->$fileAttribute = null;
            $suspectCase->save();
            session()->flash('info', 'Se ha eliminado el archivo correctamente.');
        }
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
        return view('epi.chagas.resultchagasnegative', compact('case'));
    }

    public function printresultchagasnegative(SuspectCase $suspectCase)
    {
        //
        $case = $suspectCase;
        $pdf = \PDF::loadView('epi.chagas.resultchagasnegative', compact('case'));
        return $pdf->stream();
    }

    public function delegateMail()
    {
        $organizations = Organization::where('id', Auth::user()->practitioners->last()->organization->id)->OrderBy('alias')->get();

        return view('epi.delegate_mail', compact('organizations'));
    }

    public function updateMail(Organization $organization, Request $request)
    {
        $organization->epi_mail = $request->epi_mail;
        $organization->save();
        return redirect()->back()->with('success', 'Correo electrónico actualizado correctamente.');
    }
}
