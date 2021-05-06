<?php

namespace App\Http\Controllers;

use App\Application\TransferenciaExternaService;
use App\Application\UserService;
use App\Exceptions\ConflictException;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Cog\Laravel\Optimus\OptimusManager;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Jenssegers\Optimus\Optimus;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

  public function index(Request $request){
    $filter = $request->filter;
    $page =  $request->page;
    $query = User::query();

    if(Arr::has($filter, "username") && ($username = $filter["username"])){
      $query->where("username", $username."%");
    }
    if(Arr::has($filter, "estado")){
      $query->where("estado", $filter["estado"]);
    }

    if($page && Arr::has($page, "size")){
      $total = $query->count();
      $query->limit($page["size"]);
      if(Arr::has($page, "current")){
        $query->offset(($page["current"] - 1) * $page["size"]);
      }
      return response()->json($this->buildPaginatedResponseData($total, $query->get()));
    }
    if(Arr::has($page, "current")){
      $query->offset($page["current"]);
    }

    return response()->json($query->get());
  }

  public function show(Request $request, $id){
    $user =  User::with("roles")->find($id);
    if(!$user){
      throw new ModelNotFoundException();
    }
    $user->append("role_ids");
    $user->makeHidden("roles");
    return response()->json($user);
  }

  public function store(Request $request){
    $payload = $request->validate([
      "username" => "unique:users|required",
      "password" => "required",
      "ci" => "required",
      "ci_complemento" => "nullable",
      "apellido_paterno" => "required",
      "apellido_materno" => "required",
      "nombres" => "required",
      "role_ids" => "required|array",
      "role_ids.*" => "exists:".Role::class.",id"
    ]);

    $user = DB::transaction(function () use($payload) {
      $model = User::create([
        "username" => $payload["username"],
        "password" => Hash::make($payload["password"]),
        "ci" => $payload["ci"],
        "ci_complemento" => $payload["ci_complemento"],
        "apellido_paterno" => $payload["apellido_paterno"],
        "apellido_materno" => $payload["apellido_materno"],
        "nombres" => $payload["nombres"],
      ]);
      $model->syncRoles($payload["role_ids"]);
      return $model;
    });

      // $service = new UserService();
      // $user = $service->register(
      //   $payload['external_id'],
      //   $payload['username'],
      //   $payload['password'],
      //   $payload['role_ids']
      // );

      return response()->json($user);
  }
  
  public function update(Request $request, $id){
    /** @var User $user */
    $user = User::find($id);
    if(!$user){
      // abort(409, json_encode($user));
      throw new ModelNotFoundException();
    }
      $payload = $request->validate([
        "username" => ["required", Rule::unique("users")->whereNot("id", $id)],
        "role_ids" => "required|array",
        "role_ids.*" => "exists:".Role::class.",id"
      ]);

      // $service = new UserService();
      // $user = $service->register(
      //   $payload['external_id'],
      //   $payload['username'],
      //   $payload['password'],
      //   $payload['role_ids']
      // );
      DB::transaction(function() use($user, $payload) {
        $user->username = $payload["username"];
        $user->syncRoles($payload["role_ids"]);
        $user->save();
      });

      return response()->json([
        'username' => $user->username,
        'externalId' => $user->external_id,
        'roleIds' => $user->roles->map(function($role){return $role->id;})
      ]);
  }

  // public function authenticate(Request $request){
  //   $request->validate([
  //     'username' => 'required',
  //     'password' => 'required',
  //     'device_name' => 'required',
  //   ]);

  //   $user = User::where('username', $request->username)->first();

  //   if (! $user || ! Hash::check($request->password, $user->password_hash)) {
  //       throw ValidationException::withMessages([
  //           'username' => ['The provided credentials are incorrect.'],
  //       ]);
  //   }

  //   return $user->createToken($request->device_name)->plainTextToken;
  // }
}