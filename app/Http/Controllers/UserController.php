<?php

namespace App\Http\Controllers;

use App\Application\TransferenciaExternaService;
use App\Application\UserService;
use App\Exceptions\ConflictException;
use App\Models\Permisos;
use App\Models\Regional;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Cog\Laravel\Optimus\OptimusManager;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index(Request $request)
    {

        $filter = $request->filter;
        $page =  $request->page;

        $this->authorize("ver-todo", [User::class, $filter]);

        $query = User::query();

        if (Arr::has($filter, "username") && ($username = $filter["username"])) {
            $query->where("username", $username . "%");
        }
        if (Arr::has($filter, "estado")) {
            $query->where("estado", $filter["estado"]);
        }
        if (Arr::has($filter, "regional_id")) {
            $query->where("regional_id", $filter["regional_id"]);
        }

        if ($page && Arr::has($page, "size")) {
            $total = $query->count();
            $query->limit($page["size"]);
            if (Arr::has($page, "current")) {
                $query->offset(($page["current"] - 1) * $page["size"]);
            }
            $records = $query->get();
            $records->makeVisible("role_names");
            return response()->json($this->buildPaginatedResponseData($total, $records));
        }
        if (Arr::has($page, "current")) {
            $query->offset($page["current"]);
        }

        $records = $query->get();
        $records->makeVisible("role_names");
        return response()->json($records);
    }

    public function show(Request $request, $id)
    {
        $user =  User::with("roles")->find($id);

        if (!$user) {
            throw new ModelNotFoundException();
        }

        $this->authorize("ver", $user);

        $user->append("role_names");
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            "username" => "unique:users|required|min:6|max:32",
            "password" => ["required", "min:8", "max:256", Password::defaults()],
            "ci" => "required",
            "ci_complemento" => "nullable",
            "apellido_paterno" => "required_without:apellido_materno",
            "apellido_materno" => "required_without:apellido_paterno",
            "nombres" => "required",
            "regional_id" => "numeric|required|exists:" . Regional::class . ",id",
            "roles" => "required|array",
            "roles.*" => "exists:" . Role::class . ",name"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido"
        ]);

        $this->authorize("registrar", [User::class, $payload]);

        $user = User::where("ci_raiz", $payload["ci"])->where("ci_complemento", $payload["ci_complemento"] ?? null)->first();
        if ($user) {
            throw ConflictException::withData("ya existe un usuario con el carnet de identidad proporcionado", $user->id);
        }

        $user = DB::transaction(function () use ($payload) {
            $model = User::create([
                "username" => $payload["username"],
                "password" => Hash::make($payload["password"]),
                "ci_raiz" => $payload["ci"],
                "ci_complemento" => $payload["ci_complemento"] ?? null,
                "apellido_paterno" => $payload["apellido_paterno"] ?? null,
                "apellido_materno" => $payload["apellido_materno"] ?? null,
                "nombres" => $payload["nombres"],
                "regional_id" => $payload["regional_id"],
                "estado" => 1,
            ]);
            $model->syncRoles($payload["roles"]);
            return $model;
        });

        $user->append("role_names");
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
            // "username" => ["required", Rule::unique("users")->whereNot("id", $id)],
            "ci" => "required",
            "ci_complemento" => "nullable",
            "apellido_paterno" => "required_without:apellido_materno",
            "apellido_materno" => "required_without:apellido_paterno",
            "nombres" => "required",
            "regional_id" => "numeric|required|exists:" . Regional::class . ",id",
            "roles" => "required|array",
            "roles.*" => "exists:" . Role::class . ",name"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido"
        ]);

        $this->authorize("editar", [$user, $payload]);

        $user2 = User::where("ci_raiz", $payload["ci"])
            ->where("ci_complemento", $payload["ci_complemento"] ?? null)
            ->where("id", "<>", $user->id)
            ->first();
        if ($user2) {
            throw ConflictException::withData("ya existe un usuario con el carnet de identidad proporcionado", $user2->id);
        }

        DB::transaction(function () use ($user, $payload) {
            $user->ci_raiz = $payload["ci"];
            $user->ci_complemento = $payload["ci_complemento"] ?? null;
            $user->apellido_paterno = $payload["apellido_paterno"];
            $user->apellido_materno = $payload["apellido_materno"];
            $user->nombres = $payload["nombres"];
            $user->syncRoles($payload["roles"]);
            $user->save();
        });

        $user->append("role_names");
        return response()->json($user);
    }

    function changePassword(Request $request, $id)
    {
        /** @var User $user */
        $user = User::find($id);
        if (!$user) {
            // abort(409, json_encode($user));
            throw new ModelNotFoundException();
        }

        $payload = $request->validate([
            "old_password" => [Rule::requiredIf(function() use($request) {
                return !$request->user()->isSuperUser() && !$request->user()->hasPermissionTo(Permisos::CAMBIAR_CONTRASEÑA) && !$request->user()->hasPermissionTo(Permisos::CAMBIAR_CONTRASEÑA_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO);
            }), "password:sanctum"],
            "password" => ["required", "min:8", "max:256", Password::defaults()]
        ]);

        $this->authorize("cambiar-contrasena", [$user, $payload]);

        $user->password = Hash::make($payload["password"]);
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

        $user->update([
            "estado" => 1
        ]);

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

        $user->update([
            "estado" => 2
        ]);

        return response()->json();
    }
}
