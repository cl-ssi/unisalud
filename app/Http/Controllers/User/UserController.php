<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\HumanName;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $permissions = Permission::OrderBy('name')->get();
        return view('users.edit', compact('user','permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $user->fill($request->all());

        $user->syncPermissions( is_array($request->input('permissions')) ? $request->input('permissions') : array() );

        $user->save();

        session()->flash('success', 'El usuario '.$user->name.' ha sido actualizado.');

        return redirect()->back();
    }

    public function searchByName(Request $request){
      $term = $request->get('term');

      $querys = HumanName::where('text','LIKE','%'.$term.'%')
                         ->orwhere('fathers_family','LIKE','%'.$term.'%')
                         ->orwhere('mothers_family','LIKE','%'.$term.'%')
                         ->get();

      $data = [];

      foreach($querys as $query){
         $data[] = [
           'label' => $query->text . " " . $query->fathers_family . " " . $query->mothers_family,
           'id' => $query->user_id
        ];
      }

      return $data;
    }
}
