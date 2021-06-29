<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize("ver-todo", Role::class);
        $filter = $request->filter;
        $page =  $request->page;
        $query = Role::query();

        if (Arr::has($filter, "texto") && ($texto = $filter["texto"])) {
            $query->whereRaw("MATCH(`name`, `description`) AGAINST(? IN BOOLEAN MODE)", [$texto . '*']);
        }

        if ($page && Arr::has($page, "size")) {
            $total = $query->count();
            $query->limit($page["size"]);
            if (Arr::has($page, "current")) {
                $query->offset(($page["current"] - 1) * $page["size"]);
            }
            return response()->json($this->buildPaginatedResponseData($total, $query->get()->map(function ($role) {
                return array_merge($role->toArray(), [
                    "permission_names" => $role->getPermissionNames()
                ]);
            })));
        }
        if (Arr::has($page, "current")) {
            $query->offset($page["current"]);
        }

        return response()->json($query->get()->map(function ($role) {
            return array_merge($role->toArray(), [
                "permission_names" => $role->getPermissionNames()
            ]);
        }));
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
            "name" => "required|unique:roles|max:50",
            "description" => "nullable|max:250",
            "permissions" => "array|required"
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
        return response()->json(array_merge($role->toArray(), [
            "permission_names" => $role->getPermissionNames()
        ]));
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

        return response()->json(array_merge($role->toArray(), [
            "permission_names" => $role->getPermissionNames()
        ]));
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
            "name" => "required|unique:roles,name,$id|max:50",
            "description" => "nullable|max:250",
            "permissions" => "array|required"
        ], [
            "name.unique" => "Ya existe un rol con el mismo nombre.",
            "permissions.required" => "Debe indicar al menos un permiso."
        ]);

        $role = Role::find($id);
        if (!$role) {
            throw new ModelNotFoundException();
        }

        $role->name = $payload["name"];
        $role->description = $payload["description"]??null;

        $this->authorize("editar", $role);

        DB::transaction(function () use ($role, $payload) {
            $role->save();
            $role->syncPermissions($payload["permissions"]);
        });
        return response()->json(array_merge($role->toArray(), [
            "permission_names" => $role->getPermissionNames()
        ]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $this->authorize("eliminar", Role::class);
        if (!Role::destroy($id))
            throw new \Exception("No se pudo eliminar el rol");
        return response()->json();
    }
}
