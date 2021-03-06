<?php

namespace App\Http\Controllers\MedicalProgrammer;

use App\Models\MedicalProgrammer\MotherActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MotherActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $motherActivities = MotherActivity::all();
      return view('medical_programmer.mother_activities.index', compact('motherActivities'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('medical_programmer.mother_activities.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $motherActivity = new MotherActivity($request->All());
      //$motherActivity->user_id = Auth::id();
      $motherActivity->save();

      session()->flash('info', 'La actividad madre ha sido creada.');
      return redirect()->route('medical_programmer.mother_activities.index');
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
    public function edit(MotherActivity $motherActivity)
    {
        return view('medical_programmer.mother_activities.edit', compact('motherActivity'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MotherActivity $motherActivity)
    {
      $motherActivity->fill($request->all());
      //$motherActivity->user_id = Auth::id();
      $motherActivity->save();

      session()->flash('info', 'La actividad madre ha sido editada.');
      return redirect()->route('medical_programmer.mother_activities.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MotherActivity $motherActivity)
    {
      $motherActivity->delete();
      session()->flash('success', 'La actividad madre ha sido eliminada');
      return redirect()->route('medical_programmer.mother_activities.index');
    }
}
