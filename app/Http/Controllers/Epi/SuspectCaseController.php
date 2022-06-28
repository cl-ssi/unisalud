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
            //dd($suspectcases);
        } 
        else {
            $suspectcases = SuspectCase::all();
        }
        return view('epi.chagas.index', compact('suspectcases','tray'));
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
        $suspectCase->save();

        if ($request->chagas_result_screening == 'En Proceso') {
            Mail::to('marina.miranda@cormudesi.cl')
            // Mail::to('tebiccr@gmail.com')
            ->send(new DelegateChagasNotification($suspectCase));
        }
        

        session()->flash('success', 'Se añadieron los datos adicionales a Caso sospecha');
        return redirect()->back();
        //return redirect()->route('epi.chagas.index');
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
