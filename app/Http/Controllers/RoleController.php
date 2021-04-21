<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->authorizeResource(Person::class, 'person');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Role::get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $payload = $request->validate([
        "name" => "required",
        "description" => "nullable",
        "permissions" => "required"
      ]);
      $role = DB::transaction(function() use($payload){
        $role = Role::create([
          "name" => $payload["name"],
          "description" => $payload["description"]
        ]);
        $role->givePermissionTo($payload["permissions"]);
        return $role;
      });
      return response()->json($role);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $role = Role::with("permissions")->find($id);
      if($role){
        $roleArray = $role->toArray();
        $roleArray["permissions"] = $role->permissions->pluck("name");
        return response()->json($roleArray);
      }
      else{
        throw new ModelNotFoundException();
      }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $payload = $request->validate([
        "name" => "required",
        "description" => "nullable",
        "permissions" => "required"
      ]);
      $role = Role::find($id);
      if(!$role){
        throw new ModelNotFoundException();
      }
      $role->name = $payload["name"];
      $role->description = $payload["description"];
      DB::transaction(function() use($role, $payload){
        $role->save();
        $role->givePermissionTo($payload["permissions"]);
      });
      return response()->json($role);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
