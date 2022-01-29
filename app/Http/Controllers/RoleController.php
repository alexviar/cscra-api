<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{

    protected function appendFilters($query, $filter)
    {
        if (Arr::has($filter, "_busqueda") && ($texto = $filter["_busqueda"])) {
            $query->whereRaw("MATCH(`name`, `description`) AGAINST(? IN BOOLEAN MODE)", [$texto . '*']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->filter;
        $page =  $request->page;

        $this->authorize("ver-todo", [Role::class, $filter]);

        return $this->buildResponse(Role::query(), $filter, $page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize("registrar", Role::class);
        $payload = $request->validate([
            "name" => "required|unique:roles|max:125",
            "description" => "nullable|max:255",
            "permissions" => "array|required",
            "permissions.*" => "exists:".Permission::class.",name"
        ], [
            "name.unique" => "Ya existe un rol con el mismo nombre.",
            "permissions.required" => "Debe indicar al menos un permiso."
        ]);
        $role = DB::transaction(function () use ($payload) {
            $role = Role::create([
                "name" => $payload["name"],
                "description" => $payload["description"]??null
            ]);
            $role->givePermissionTo($payload["permissions"]);
            return $role;
        });

        $role->loadMissing("permissions");
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
        if (!$role) {
            throw new ModelNotFoundException();
        }

        $this->authorize("ver", $role);

        return response()->json($role);
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
        $role = Role::find($id);
        if(!$role){
            throw new ModelNotFoundException();
        }
        
        $payload = $request->validate([
            "name" => "required|unique:roles,name,$id|max:125",
            "description" => "nullable|max:255",
            "permissions" => "array|required",
            "permissions.*" => "exists:".Permission::class.",name"
        ], [
            "name.unique" => "Ya existe un rol con el mismo nombre.",
            "permissions.required" => "Debe indicar al menos un permiso."
        ]);

        $role->name = $payload["name"];
        $role->description = $payload["description"]??null;

        $this->authorize("editar", $role);

        DB::transaction(function () use ($role, $payload) {
            $role->save();
            $role->syncPermissions($payload["permissions"]);
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
        $rol = Role::find($id);
        if(!$rol){
            throw new ModelNotFoundException();
        }

        if(User::whereHas("roles", function($query) use($rol){
            $query->where("name", $rol->name);
        })->exists()){
            abort(409, "El rol no esta vacío.");
        }

        $this->authorize("eliminar", [Role::class, $rol]);
        
        $rol->delete();
        
        return response()->json();
    }
}
