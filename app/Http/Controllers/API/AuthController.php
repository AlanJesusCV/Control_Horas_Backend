<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function App\Helpers\formatErrorResponse;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        try {
            $rulesValidation = [
                'email' => 'required',
                'password' => 'required',
            ];

            // Validar la solicitud
            $validator = Validator::make($request->all(), $rulesValidation);

            // Comprobar si la validación falló
            if ($validator->fails()) {
                return response()->json(formatErrorResponse(true, $validator->errors(), []), 400);
            }


            //$emailRequest = SodiumUtil::decryptData($request->email);
            //$passwordRequest = SodiumUtil::decryptData($request->password);
            $emailRequest = $request->email;
            $passwordRequest = $request->password;


            if (Auth::attempt(['email' => $emailRequest, 'password' => $passwordRequest])) {
                $user = DB::table('users')->where('email', $emailRequest)->first();
                if ($user->status == 'Activo') {
                    $user = Auth::user();
                    $user->email = SodiumUtil::encryptData($user->email);
                    //$user->tipo = SodiumUtil::encryptData($user->tipo);
                    $user->tipo = $user->tipo;
                    //$user->numero_empleado = SodiumUtil::encryptData($user->numero_empleado);
                    $user->numero_empleado = $user->numero_empleado;
                    $token = $user->createToken('authToken');
                    //$plainTextToken = $newAccessToken->plainTextToken;
                    $user->token = $token->plainTextToken;
                    return response()->json(formatErrorResponse(false, 'Inicio Correcto', $user));
                } else {
                    return response()->json(formatErrorResponse(true, 'El usuario se encuentra dado de baja.', []));
                }
            } else {
                return response()->json(formatErrorResponse(true, 'Las credenciales no son correctas, intente de nuevo.', []));
            }
        } catch (Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public function logout(Request $request)
    {
        // Revocar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json(formatErrorResponse(false, 'Cierre de sesion exitoso.', []));
    }
}
