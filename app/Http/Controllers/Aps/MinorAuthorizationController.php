<?php

namespace App\Http\Controllers\Aps;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Aps\AuthorizationType;
use App\Models\Aps\MinorAuthorization;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MinorAuthorizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $minorAuthorizations = MinorAuthorization::all();
        $minorAuthorizations = MinorAuthorization::where('id',0)->get();
        
        return view('aps.minor_authorizations.index', compact('minorAuthorizations'));
    }

    public function parents_index(){
        $minorAuthorizations = MinorAuthorization::where('authorizer_id',Auth::id())->get();
        $array = array();
        foreach($minorAuthorizations as $key => $minorAuthorization){
            $array[$minorAuthorization->type->id][$key] = $minorAuthorization;
        }
        
        return view('aps.minor_authorizations.parents_index', compact('array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($type_id)
    {
        if($type_id != 0){
            $authorizationTypes = AuthorizationType::where('id',$type_id)->get();
        }else{
            $authorizationTypes = AuthorizationType::all();
        }
        
        return view('aps.minor_authorizations.create',compact('authorizationTypes','type_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $minorAuthorization = new MinorAuthorization($request->All());
        $minorAuthorization->authorizer_id = Auth::id();
        $minorAuthorization->authorization_date = Carbon::now();
        $minorAuthorization->save();

        session()->flash('success', 'La autorización fue registrada.');
        return redirect()->route('aps.minor_authorizations.index');
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
    public function edit(MinorAuthorization $minorAuthorization)
    {
        $authorizationTypes = AuthorizationType::all();
        return view('aps.minor_authorizations.edit', compact('authorizationTypes','minorAuthorization'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MinorAuthorization $minorAuthorization)
    {
        $minorAuthorization->fill($request->all());
        $minorAuthorization->save();

        session()->flash('success', 'La autorización fue actualizada.');
        return redirect()->route('aps.minor_authorizations.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MinorAuthorization $minorAuthorization)
    {
        $minorAuthorization->delete();
        session()->flash('success', 'La autorización fué eliminada.');
        return redirect()->route('aps.minor_authorizations.index');
    }
}
