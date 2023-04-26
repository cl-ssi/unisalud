<?php

namespace App\Http\Controllers\Epi;

use App\Models\Epi\Tracing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Epi\SuspectCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;


class TracingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organizations = Organization::where('id', Auth::user()->practitioners->last()->organization->id)->orderBy('alias')->get();
        $organizationIds = $organizations->pluck('id')->toArray(); // Obtener un array de IDs de organizaciones
        $suspectcases = SuspectCase::whereIn('organization_id', $organizationIds)
            ->where('chagas_result_confirmation', 'positivo')
            ->get();
        return view('epi.tracings.index', compact('suspectcases'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(SuspectCase $suspectcase)
    {
        $cie10s = DB::select('select * from cie10 WHERE id IN (12791, 13800,12897,3559,3560,3561, 12862)');
        $organizations = Organization::where('id', Auth::user()->practitioners->last()->organization->id)->OrderBy('alias')->get();
        return view('epi.tracings.create', compact('cie10s', 'suspectcase', 'organizations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $trace = new Tracing($request->All());
        $trace->save();
        session()->flash('info', 'El Seguimiento ha sido almacenado exitosamente');
        return redirect()->route('epi.tracings.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tracing  $tracing
     * @return \Illuminate\Http\Response
     */
    public function show(Tracing $tracing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tracing  $tracing
     * @return \Illuminate\Http\Response
     */
    public function edit(Tracing $tracing)
    {
        //

        $organizations = Organization::where('id', Auth::user()->practitioners->last()->organization->id)->OrderBy('alias')->get();
        $cie10s = DB::select('select * from cie10 WHERE id IN (12791, 13800,12897,3559,3560,3561, 12862)');
        return view('epi.tracings.edit', compact('cie10s', 'tracing', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tracing  $tracing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tracing $tracing)
    {
        //        
        $tracing->fill($request->all());
        $tracing->save();
        session()->flash('info', 'El Seguimiento ha sido actualizado exitosamente');
        return redirect()->route('epi.tracings.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tracing  $tracing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tracing $tracing)
    {
        //
    }
}
