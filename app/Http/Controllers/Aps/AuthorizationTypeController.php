<?php

namespace App\Http\Controllers\Aps;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Aps\AuthorizationType;

class AuthorizationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authorizationTypes = AuthorizationType::all();
        return view('aps.authorization_types.index', compact('authorizationTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('aps.authorization_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $authorizationType = new AuthorizationType($request->All());
        $authorizationType->save();

        session()->flash('success', 'El tipo de autorización fue registrado.');
        return redirect()->route('aps.authorization_types.index');
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
    public function edit(AuthorizationType $authorizationType)
    {
        return view('aps.authorization_types.edit', compact('authorizationType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AuthorizationType $authorizationType)
    {
        $authorizationType->fill($request->all());
        $authorizationType->save();

        session()->flash('success', 'El tipo de autorización fue actualizado.');
        return redirect()->route('aps.authorization_types.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuthorizationType $authorizationType)
    {
        $authorizationType->delete();
        session()->flash('success', 'El tipo de autorización fue eliminado');
        return redirect()->route('aps.authorization_types.index');
    }
}
