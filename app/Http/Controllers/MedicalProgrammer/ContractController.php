<?php

namespace App\Http\Controllers\MedicalProgrammer;

use App\Models\MedicalProgrammer\Contract;
use App\Models\MedicalProgrammer\Service;
// use App\Models\MedicalProgrammer\Rrhh;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->get('year')) {
          $year = $request->get('year');
        }else{$year = Carbon::now()->format('Y');}

        $contracts = Contract::where('year',$year)->paginate(50);
        return view('medical_programmer.contracts.index', compact('contracts', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      // $rrhh = Rrhh::orderBy('name','ASC')->get();
      // $users = User::all();
      $services = Service::orderBy('service_name','ASC')->get();
      return view('medical_programmer.contracts.create', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $contract = new Contract($request->All());
      //$contract->user_id = Auth::id();
      $contract->save();

      session()->flash('info', 'El contrato ha sido creado.');
      return redirect()->route('medical_programmer.contracts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Pharmacies\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Pharmacies\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Contract $contract)
    {
      $users = User::where('id',$contract->user_id)->get();
      $services = Service::orderBy('service_name','ASC')->get();
      return view('medical_programmer.contracts.edit', compact('contract', 'users', 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Pharmacies\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contract $contract)
    {
        $contract->fill($request->all());
        //$contract->user_id = Auth::id();
        $contract->save();

        session()->flash('info', 'El contrato ha sido editado.');
        return redirect()->route('medical_programmer.contracts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Pharmacies\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
      //se elimina la cabecera y detalles
      $contract->delete();
      session()->flash('success', 'El contrato ha sido eliminado');
      return redirect()->route('medical_programmer.contracts.index');
    }
}
