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
    $user = User::where("external_id", $request->external_id)->first();
    if($user){
      // abort(409, json_encode($user));
      throw ConflictException::withData($user);
    }
      $payload = $request->validate([
        "username" => "unique:users|required",
        "password" => "required",
        "external_id" => "required",
        "role_ids" => "required|array",
        "role_ids.*" => "exists:".Role::class.",id"
      ]);

      $service = new UserService();
      $user = $service->register(
        $payload['external_id'],
        $payload['username'],
        $payload['password'],
        $payload['role_ids']
      );

      return response()->json([
        'username' => $user->username,
        'externalId' => $user->external_id,
        'roleIds' => $payload['role_ids']
      ]);
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