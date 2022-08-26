<?php

namespace App\Http\Controllers\Epi;

use App\Models\Epi\Tracing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Epi\SuspectCase;
use Illuminate\Support\Facades\DB;

class TracingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suspectcases = SuspectCase::where('chagas_result_confirmation','positivo')->get();
        return view('epi.tracings.index',compact('suspectcases'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //$cie10s = DB::table('cie10')->get();
        $cie10s = DB::select('select * from cie10 where name like  "%chag%"');
        return view('epi.tracings.create',compact('cie10s'));
        
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
