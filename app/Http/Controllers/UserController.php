<?php

namespace App\Http\Controllers;

use App\Models\Regional;
use App\Models\User;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected function appendFilters($query, $filter)
    {
        $query->with(["roles", "regional"]);
        if($busqueda = Arr::get($filter, "_busqueda")){
            $query->where(function($query) use($busqueda){
                $query->whereRaw("MATCH(`nombre`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$busqueda . "*"]);
                // $split = explode("-", $busqueda);
                // if(count($split) <= 2) {
                //     $query->orWhere(function($query) use($split){
                //         $query->where("ci", $split[0]);
                //         if(count($split) == 2) $query->where("ci_complemento", $split[0]);
                //     });
                // }
                $query->orWhere("username", $busqueda);
            });
        }
        else {
            if ($nombre = Arr::get($filter, "nombre")) {
                $query->whereRaw("MATCH(`nombre`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"]);
                // $query->where("nombre","like", Str::upper($nombre) . "%");
            }
            if (Arr::has($filter, "username") && ($username = $filter["username"])) {
                $query->where("username", "LIKE", $username . "%");
            }
        }
        if ($ci = Arr::get($filter, "ci.raiz")) {
            $query->where("ci", $ci);
            if($ciComplemento = Arr::get($filter, "ci.complemento")) $query->where("ci_complemento", $ciComplemento);
        }
        if ($estado = Arr::get($filter, "estado")) {
            $query->where("estado", $estado);
        }
        if ($regionalId = Arr::get($filter, "regional_id")) {
            $query->where("regional_id", $regionalId);
        }
    }

    public function index(Request $request)
    {
        $filter = $request->filter;
        $page =  $request->page;

        $this->authorize("ver-todo", [User::class, $filter]);

        return $this->buildResponse(User::query(), $filter, $page);
    }

    public function show(Request $request, $id)
    {
        $user =  User::with("roles")->find($id);

        if (!$user) {
            throw new ModelNotFoundException();
        }
        
        $this->authorize("ver", $user);

        $user->loadMissing(["regional", "roles"]);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            "username" => "unique:users|required|min:6|max:32",
            "password" => ["required", "min:8", Password::defaults()],
            "ci" => [function ($attribute, $value, $fail) use($request){
                $user = User::where("ci", $value["raiz"])
                    ->where("ci_complemento", $value["complemento"] ?? "")
                    ->where("regional_id", $request->regional_id)
                    ->first();
                if ($user) {
                    $fail("Ya existe un usuario registrado con este carnet de identidad.");
                }
            }],
            "ci.raiz" => "required|integer",
            "ci.complemento" => "nullable|regex:/^[1-9][A-Z]$/",
            "apellido_paterno" => "required_without:apellido_materno|max:25",
            "apellido_materno" => "required_without:apellido_paterno|max:25",
            "nombre" => "required|max:50",
            "regional_id" => "numeric|required|exists:" . Regional::class . ",id",
            "roles" => "required|array",
            "roles.*" => "exists:" . Role::class . ",name"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido",
            "ci.complemento.regex" => "Complemento invalido."
        ]);

        $this->authorize("registrar", [User::class, $payload]);

        $user = DB::transaction(function () use ($payload) {
            $model = User::create([
                "username" => $payload["username"],
                "password" => $payload["password"],
                "ci" => new CarnetIdentidad(Arr::get($payload, "ci.raiz"), Arr::get($payload, "ci.complemento") ?? ""),
                "apellido_paterno" => $payload["apellido_paterno"] ?? null,
                "apellido_materno" => $payload["apellido_materno"] ?? null,
                "nombre" => $payload["nombre"],
                "regional_id" => $payload["regional_id"],
                "estado" => 1,
            ]);
            $model->syncRoles($payload["roles"]);
            return $model;
        });
        $user->loadMissing(["regional", "roles"]);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        /** @var User $user */
        $user = User::find($id);
        if (!$user) {
            // abort(409, json_encode($user));
            throw new ModelNotFoundException();
        }

        $payload = $request->validate([
            "ci" => [function ($attribute, $value, $fail) use($request, $user){
                if (User::where("ci", $value["raiz"])
                    ->where("ci_complemento", $value["complemento"] ?? "")
                    ->where("regional_id", $request->regional_id)
                    ->where("id", "<>", $user->id)
                    ->exists()) {
                    $fail("Ya existe un usuario registrado con este carnet de identidad.");
                }
            }],
            "ci.raiz" => "required|integer",
            "ci.complemento" => "nullable|regex:/^[1-9][A-Z]$/",
            "apellido_paterno" => "required_without:apellido_materno|max:25",
            "apellido_materno" => "required_without:apellido_paterno|max:25",
            "nombre" => "required|max:50",
            "regional_id" => "numeric|required|exists:" . Regional::class . ",id",
            "roles" => "required|array",
            "roles.*" => "exists:" . Role::class . ",name"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido",
            "ci.complemento.regex" => "Complemento invalido."
        ]);

        $this->authorize("editar", [$user, $payload]);

        DB::transaction(function () use ($user, $payload) {
            $user->ci = new CarnetIdentidad(Arr::get($payload, "ci.raiz"), Arr::get($payload, "ci.complemento") ?? "");
            $user->apellido_paterno = $payload["apellido_paterno"];
            $user->apellido_materno = $payload["apellido_materno"];
            $user->nombre = $payload["nombre"];
            $user->regional_id = $payload["regional_id"];
            $user->syncRoles($payload["roles"]);
            $user->save();
        });

        return response()->json($user);
    }

    function changePassword(Request $request, $id)
    {
        /** @var User $user */
        $user = User::find($id);
        if (!$user) {
            throw new ModelNotFoundException();
        }

        $payload = $request->validate([
            "password" => ["required", "min:8", Password::defaults()],
            "old_password" => "nullable|password:sanctum"
        ]);

        $this->authorize("cambiar-contrasena", [$user, $payload]);

        $user->password = $payload["password"];
        $user->save();

        return response()->json();
    }

    function enable(Request $request, $id)
    {
        /** @var User $user */
        $user = User::find($id);
        if (!$user) {
            throw new ModelNotFoundException();
        }

        $this->authorize("enable", $user);

        $user->estado = 1;
        $user->save();

        return response()->json();
    }

    function disable(Request $request, $id)
    {
        /** @var User $user */
        $user = User::find($id);
        if (!$user) {
            throw new ModelNotFoundException();
        }

        $this->authorize("disable", $user);

        $user->estado = 2;
        $user->save();

        return response()->json();
    }
}
