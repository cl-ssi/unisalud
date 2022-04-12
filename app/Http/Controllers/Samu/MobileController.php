<?php

namespace App\Http\Controllers\Samu;

use App\Http\Controllers\Controller;
use App\Models\Samu\Mobile;
use App\Models\Samu\MobileType;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
           //busqueda de codigos de clave
           $search_mobile=$request->get('search_mobile');  
           $mobiles= mobile::when($search_mobile!=null, function ($query) use ($search_mobile){
               $query->where('name','like','%'.$search_mobile.'%');
           })->paginate(30);
           return view ('samu.mobile.index' , compact('mobiles','search_mobile')); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $types = MobileType::pluck('name','id');
        return view('samu.mobile.create',compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $mobile = new mobile($request->all());
        //consultando si esta o no clickeado un checkbox
        $mobile->managed = $request->has('managed') ? 1:0;
        $mobile->status = 1;
        $mobile->save();
        $mobiles = mobile::all();
        session()->flash('success', 'Se ha creado el móvil exitosamente');
        return redirect()->route ('samu.mobile.index', compact('mobiles'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Samu\Mobile  $mobile
     * @return \Illuminate\Http\Response
     */
    public function show(Mobile $mobile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Samu\Mobile  $mobile
     * @return \Illuminate\Http\Response
     */
    public function edit(Mobile $mobile)
    {
        $types = MobileType::pluck('name','id');
        return view('samu.mobile.edit', compact('mobile','types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Samu\Mobile  $mobile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mobile $mobile)
    {
        $mobile->fill($request->all());
        //consultando si esta o no clickeado un checkbox
        $mobile->managed = $request->has('managed') ? 1:0;
        $mobile->status = $request->has('status') ? 1:0;
        $mobile->save();

        session()->flash('info', 'El móvil ha sido editado.');

        return redirect()->route('samu.mobile.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Samu\Mobile  $mobile
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mobile $mobile)
    {
        $mobile->delete();
        return redirect()->route('samu.mobile.index')->with('danger', 'Eliminado satisfactoriamente');
    }
}
