<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function App\Helpers\formatErrorResponse;

class UserController extends Controller
{

    public function getUsers()
    {
        try {
            $users = DB::table('users')
                ->orderBy('id', 'desc')
                ->get(['id', 'nombre', 'apellidos', 'email', 'tipo', 'status', 'numero_empleado', 'status']);
            return response()->json(formatErrorResponse(false, '', $users));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public function getUsersAutocomplete()
    {
        try {
            $users = DB::table('users')
            ->select(DB::raw("CONCAT(CONCAT(nombre, ' ', apellidos), ' (', numero_empleado, ')') AS empleado"))
            ->orderBy('id', 'desc')
            ->get();
            return response()->json(formatErrorResponse(false, '', $users));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }
    public static function getUsersManagers()
    {
        try {
            $usersManagers = DB::table('users')->where('tipo', '=', 'Gerente')->get(['id', 'nombre', 'apellidos', 'email', 'tipo', 'numero_empleado']);
            return response()->json(formatErrorResponse(false, '', $usersManagers));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public static function getUsersValidator()
    {
        try {
            $usersManagers = DB::table('users')->where('tipo', '=', 'Validador')->get(['id', 'nombre', 'apellidos', 'email', 'tipo', 'numero_empleado']);
            return response()->json(formatErrorResponse(false, '', $usersManagers));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public static function getUsersCatcher()
    {
        try {
            $usersSupervisor = DB::table('users')->where('tipo', '=', 'Capturador')->get(['id', 'nombre', 'apellidos', 'email', 'tipo', 'numero_empleado']);
            return response()->json(formatErrorResponse(false, '', $usersSupervisor));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public static function createUser(Request $request)
    {
        $rulesValidation = [
            'nombre' => ['required'],
            'apellidos' => ['required'],
            'tipo' => ['required'],
            'email' => ['required'],
            'password' => ['required'],
            'numero_empleado' => ['required']
        ];

        $validator = Validator::make($request->all(), $rulesValidation);
        if ($validator->fails()) {
            return response()->json(formatErrorResponse(true, $validator->errors(), []));
        }

        try {
            if (DB::table('users')->where('email', $request->email)->exists()) {
                return response()->json(formatErrorResponse(true, 'El usuario ya existe en el sistema, intente de nuevo con otro valor.', []));
            } else {
                DB::table('users')->insertGetId([
                    'nombre' => $request->nombre,
                    'apellidos' => $request->apellidos,
                    'tipo' => $request->tipo,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'status' => 'Activo',
                    'numero_empleado' => $request->numero_empleado,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                return response()->json(formatErrorResponse(false, 'Usuario creado correctamente', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public static function logicalDeleteUser($id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();
            if ($user) {
                if ($user->status == 'Activo') {
                    DB::table('users')
                        ->where('id', $id)
                        ->update([
                            'status' => 'Inactivo',
                            'updated_at' => Carbon::now()
                        ]);

                    //DB::table('usuario_grupo')->where('fk_id_user', '=', $id)->delete();
                    return response()->json(formatErrorResponse(false, 'Usuario dado de baja con exito.', []));
                } else {
                    DB::table('users')
                        ->where('id', $id)
                        ->update([
                            'status' => 'Activo',
                            'updated_at' => Carbon::now()
                        ]);
                    //DB::table('usuario_grupo')->where('fk_id_user', '=', $id)->delete();
                    return response()->json(formatErrorResponse(false, 'Usuario dado de baja con exito.', []));
                }
            } else {
                return response()->json(formatErrorResponse(true, 'El usuario no existe, verifique e intente de nuevo.', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode(json_encode(['mensaje' => $e->getMessage(), 'ruta' => $e->getFile(), 'clase' => get_class($e)]))));
        }
    }

    public static function updateUserMultiple(Request $request, $id)
    {
        try {
            $userData = $request->only(['name', 'last_name', 'tipo', 'user_scl']);
            // Filtra los campos que no están vacíos
            $filteredData = array_filter($userData, function ($value) {
                return $value !== null && $value !== '';
            });
            return response()->json(formatErrorResponse(false, 'Usuario actualizado con exito.', $filteredData));

            if (!empty($filteredData)) {
                DB::table('users')
                    ->where('id', $id)
                    ->update($filteredData);
                return response()->json(formatErrorResponse(false, 'Usuario actualizado con exito.', []));
            } else {
                return response()->json(formatErrorResponse(true, 'No existen datos que actualizar.', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode(json_encode(['mensaje' => $e->getMessage(), 'ruta' => $e->getFile(), 'clase' => get_class($e)]))));
        }
    }

    public static function updateUser(Request $request, $id)
    {
        try {
            $rulesValidation = [
                'nombre' => ['required'],
                'apellidos' => ['required'],
                'tipo' => ['required'],
            ];

            $validator = Validator::make($request->all(), $rulesValidation);
            if ($validator->fails()) {
                return response()->json(formatErrorResponse(true, $validator->errors(), []));
            }

            if (DB::table('users')->where('id', $id)->exists()) {
                DB::table('users')
                    ->where('id', $id)
                    ->update([
                        'nombre' => $request->nombre,
                        'apellidos' => $request->apellidos,
                        'tipo' => $request->tipo,
                        'updated_at' => Carbon::now()
                    ]);
                return response()->json(formatErrorResponse(false, 'Usuario actualizado con exito.', []));
            } else {
                return response()->json(formatErrorResponse(true, 'El usuario no existe, verifique e intente de nuevo.', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode(json_encode(['mensaje' => $e->getMessage(), 'ruta' => $e->getFile(), 'clase' => get_class($e)]))));
        }
    }

    public static function updatePassword(Request $request, $id)
    {
        try {
            $rulesValidation = [
                //'nueva_contrasena' => 'required|min:8',
                //'confirmar_contrasena' => 'required|same:nueva_contrasena',
                'nueva_contrasena' => 'required',
                'confirmar_contrasena' => 'required',
            ];

            $validator = Validator::make($request->all(), $rulesValidation);
            if ($validator->fails()) {
                return response()->json(formatErrorResponse(true, $validator->errors(), []));
            }

            DB::table('users')
                ->where('id', $id)
                ->update([
                    'password' => Hash::make($request->nueva_contrasena),
                    'updated_at' =>  Carbon::now()
                ]);

            return response()->json(formatErrorResponse(false, 'Contraseña actualizada correctamente.', []));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode(json_encode(['mensaje' => $e->getMessage(), 'ruta' => $e->getFile(), 'clase' => get_class($e)]))));
        }
    }

    public static function enableDeleteUser($id)
    {
        try {
            //$id = $id);
            $verifyUser = DB::table('users')->where('id', $id)->first();
            if ($verifyUser) {
                if ($verifyUser->status == 'Activo') {
                    DB::table('users')
                        ->where('id', $id)
                        ->update([
                            'status' => 'Inactivo',
                            'updated_at' => Carbon::now()
                        ]);
                } else {
                    DB::table('users')
                        ->where('id', $id)
                        ->update([
                            'status' => 'Activo',
                            'updated_at' => Carbon::now()
                        ]);
                }
                return response()->json(formatErrorResponse(false, 'Usuario actualizado con exito.', []));
            } else {
                return response()->json(formatErrorResponse(true, 'El usuario no existe, verifique e intente de nuevo.', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode(json_encode(['mensaje' => $e->getMessage(), 'ruta' => $e->getFile(), 'clase' => get_class($e)]))));
        }
    }
}
