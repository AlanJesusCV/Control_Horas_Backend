<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

use function App\Helpers\formatErrorResponse;
use function Laravel\Prompts\select;

class ActividadesController extends Controller
{

    public function createActivity(Request $request)
    {
        $rulesValidation = [
            'nombre_actividad' => ['required'],
            'descripcion' => ['required'],
            'tipo_actividad' => ['required'],
            'fecha_actividad' => ['required'],
            'horas_actividad' => ['required'],
            'id_usuario_asignado' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rulesValidation);
        if ($validator->fails()) {
            return response()->json(formatErrorResponse(true, $validator->errors(), []));
        }

        try {
            $timeUserActivity = DB::table('activities')
                ->selectRaw("EXTRACT(HOUR FROM COALESCE(SUM(horas_actividad), '00:00:00')) || ':' || EXTRACT(MINUTE FROM COALESCE(SUM(horas_actividad), '00:00:00')) as contador_horas")
                ->leftJoin('users_validate_activities', 'activities.id_actividad', '=', 'users_validate_activities.id_actividad')
                //->where('id_usuario_asignado', SodiumUtil::decryptData($request->id_usuario_asignado))
                ->where('id_usuario_asignado', $request->id_usuario_asignado)
                ->where('fecha_actividad', $request->fecha_actividad)
                ->where('users_validate_activities.validada', '!=', false)
                ->orWhereNull('users_validate_activities.validada')
                ->get();

            $intervaloParsed = date_create_from_format('G \h\o\u\r\s i \m\i\n\u\t\e\s', $request->horas_actividad);

            $formatoTiempoRequest = $intervaloParsed->format('H:i');
            $formatoTiempoQuery = $timeUserActivity[0]->contador_horas;

            // Convertir ambos tiempos al mismo formato (HH:MM)
            $formatoTiempoQuery = date('H:i', strtotime($formatoTiempoQuery));

            // Dividir la cadena en horas y minutos
            list($hours1, $minutes1) = explode(':', $formatoTiempoRequest);
            list($hours2, $minutes2) = explode(':', $formatoTiempoQuery);

            // Convertir las horas y los minutos a minutos
            $totalMinutesRequest = ($hours1 * 60) + $minutes1;
            $totalMinutesQuery = ($hours2 * 60) + $minutes2;

            // Sumar los tiempos en minutos
            $totalMinutes = $totalMinutesRequest + $totalMinutesQuery;

            // Convertir de minutos a horas y minutos
            $hoursTotal = floor($totalMinutes / 60);
            $minutesTotal = $totalMinutes % 60;

            // Formatear el resultado en formato HH:MM
            $formatSumHours = sprintf('%02d:%02d', $hoursTotal, $minutesTotal);

            if ($formatoTiempoRequest > '09:00' || $formatSumHours > '09:00') {
                return response()->json(formatErrorResponse(true, 'Revise la cantidad de horas asignadas para la actividad.', []));
            } else {
                $activityID = DB::table('activities')->insertGetId([
                    'nombre_actividad' => $request->nombre_actividad,
                    'descripcion' => $request->descripcion,
                    'tipo_actividad' => $request->tipo_actividad,
                    'fecha_actividad' => $request->fecha_actividad,
                    'horas_actividad' => $request->horas_actividad,
                    //'id_usuario_asignado' => SodiumUtil::decryptData($request->id_usuario_asignado),
                    'id_usuario_asignado' => $request->id_usuario_asignado,
                    'agregado_por' => auth()->user()->numero_empleado,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ], 'id_actividad');

                DB::table('users_validate_activities')->insert([
                    'id_actividad' => $activityID,
                    'validada' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                return response()->json(formatErrorResponse(false, 'Actividades creada correctamente', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public function editActivity($id, Request $request)
    {
        $rulesValidation = [
            'nombre_actividad' => ['required'],
            'descripcion' => ['required'],
            'tipo_actividad' => ['required'],
            'fecha_actividad' => ['required'],
            'horas_actividad' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rulesValidation);
        if ($validator->fails()) {
            return response()->json(formatErrorResponse(true, $validator->errors(), []));
        }

        try {
            //$id = SodiumUtil::decryptData($id);
            $verifyActivity = DB::table('activities')
                ->leftJoin('users_validate_activities', 'activities.id_actividad', '=', 'users_validate_activities.id_actividad')
                ->where('activities.id_actividad', $id)
                ->first();

            if ($verifyActivity->validada != true) {
                $timeUserActivity = DB::table('activities')
                    ->selectRaw("EXTRACT(HOUR FROM COALESCE(SUM(horas_actividad), '00:00:00')) || ':' || EXTRACT(MINUTE FROM COALESCE(SUM(horas_actividad), '00:00:00')) as contador_horas")
                    ->leftJoin('users_validate_activities', 'activities.id_actividad', '=', 'users_validate_activities.id_actividad')
                    //->where('id_usuario_asignado', SodiumUtil::decryptData($request->id_usuario_asignado))
                    ->where('id_usuario_asignado', $request->id_usuario_asignado)
                    ->where('fecha_actividad', $verifyActivity->fecha_actividad)
                    ->where('users_validate_activities.validada', '!=', false)
                    ->orWhereNull('users_validate_activities.validada')
                    ->where('activities.id_actividad', '!=', $id)
                    ->get();

                $intervaloParsed = date_create_from_format('G \h\o\u\r\s i \m\i\n\u\t\e\s', $request->horas_actividad);

                $formatoTiempoRequest = $intervaloParsed->format('H:i');
                $formatoTiempoQuery = $timeUserActivity[0]->contador_horas;

                // Convertir ambos tiempos al mismo formato (HH:MM)
                $formatoTiempoQuery = date('H:i', strtotime($formatoTiempoQuery));

                // Dividir la cadena en horas y minutos
                list($hours1, $minutes1) = explode(':', $formatoTiempoRequest);
                list($hours2, $minutes2) = explode(':', $formatoTiempoQuery);

                // Convertir las horas y los minutos a minutos
                $totalMinutesRequest = ($hours1 * 60) + $minutes1;
                $totalMinutesQuery = ($hours2 * 60) + $minutes2;

                // Sumar los tiempos en minutos
                $totalMinutes = $totalMinutesRequest + $totalMinutesQuery;

                // Convertir de minutos a horas y minutos
                $hoursTotal = floor($totalMinutes / 60);
                $minutesTotal = $totalMinutes % 60;

                // Formatear el resultado en formato HH:MM
                $formatSumHours = sprintf('%02d:%02d', $hoursTotal, $minutesTotal);

                if ($formatoTiempoRequest > '09:00' || $formatSumHours > '09:00') {
                    return response()->json(formatErrorResponse(true, 'Revise la cantidad de horas asignadas para la actividad.', []));
                } else {
                    DB::table('activities')->where('id_actividad', $id)->update([
                        'nombre_actividad' => $request->nombre_actividad,
                        'descripcion' => $request->descripcion,
                        'tipo_actividad' => $request->tipo_actividad,
                        'horas_actividad' => $request->horas_actividad,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    DB::table('users_validate_activities')->where('id_actividad', $id)->update([
                        'validada' => null,
                        'id_user' => null,
                        'updated_at' => Carbon::now()
                    ]);
                    return response()->json(formatErrorResponse(false, 'Actividad actualizada correctamente', []));
                }
            } else {
                return response()->json(formatErrorResponse(true, 'No se encontro la actividad o ya se encuentra validada, verifique e intente de nuevo', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public function deleteActivity($id)
    {
        try {
            //$id = SodiumUtil::decryptData($id);
            $verifyActivity = DB::table('activities')
                ->leftJoin('users_validate_activities', 'activities.id_actividad', '=', 'users_validate_activities.id_actividad')
                ->where('activities.id_actividad', $id)
                ->first();

            if ($verifyActivity->validada != true) {
                DB::table('activities')->where('id_actividad', $id)->delete();
                DB::table('users_validate_activities')->where('id_actividad', $id)->delete();
                return response()->json(formatErrorResponse(false, 'Actividad eliminada correctamente', []));
            } else {
                return response()->json(formatErrorResponse(true, 'La actividad ya fue validada, no se puede eliminar.', []));
            }
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public function validateActivity(Request $request)
    {
        $rulesValidation = [
            'id_actividad' => ['required'],
            'validada' => ['required'],
            'id_validador' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rulesValidation);
        if ($validator->fails()) {
            return response()->json(formatErrorResponse(true, $validator->errors(), []));
        }
        try {
            $verifyActivity = DB::table('activities')
                ->leftJoin('users_validate_activities', 'activities.id_actividad', '=', 'users_validate_activities.id_actividad')
                //->where('activities.id_actividad', SodiumUtil::decryptData($request->id_actividad))
                ->where('activities.id_actividad', $request->id_actividad)
                ->first();

            if ($verifyActivity->validada != true) {
                //DB::table('users_validate_activities')->where('id_actividad', SodiumUtil::decryptData($request->id_actividad))->update([
                DB::table('users_validate_activities')->where('id_actividad', $request->id_actividad)->update([
                    'validada' => $request->validada,
                    //'id_user' => SodiumUtil::decryptData($request->id_validador),
                    'id_user' => $request->id_validador,
                    'updated_at' => Carbon::now()
                ]);
            } else {
                return response()->json(formatErrorResponse(true, 'La actividad ya fue validada.', []));
            }
            return response()->json(formatErrorResponse(true, 'Actividad actualizada con exito.', []));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    // Consultas

    public function getActivitiesByUser($id, $fechaInicio, $fechaFin)
    {
        try {
            $fechas = [];
            $fechaInicioOriginal = new DateTime($fechaInicio);
            $fechaFinOriginal = new DateTime($fechaFin);
            $fechaInicio = clone $fechaInicioOriginal; // Clonamos las fechas originales para evitar modificarlas
            $fechaFin = clone $fechaFinOriginal;

            while ($fechaInicio <= $fechaFin) {
                $fechas[$fechaInicio->format('Y-m-d')] = null; // Inicializamos todas las fechas con valor null
                $fechaInicio->modify('+1 day');
            }

            // Consulta para obtener la suma de horas por usuario para cada día
            $getUser = DB::table('users')->where('numero_empleado', $id)->first();
            $getHoursByDay = DB::table('activities')
                ->selectRaw("DATE(fecha_actividad) as fecha,
                             COALESCE(EXTRACT(HOUR FROM SUM(horas_actividad)), 0) || ':' ||
                             COALESCE(EXTRACT(MINUTE FROM SUM(horas_actividad)), 0) as contador_horas")
                ->leftJoin('users_validate_activities', 'activities.id_actividad', '=', 'users_validate_activities.id_actividad')
                ->where('id_usuario_asignado', $getUser->id)
                ->where('users_validate_activities.validada', '=', true)
                ->whereBetween('fecha_actividad', [$fechaInicioOriginal->format('Y-m-d'), $fechaFinOriginal->format('Y-m-d')]) // Utilizamos las fechas originales
                ->groupBy('fecha_actividad')
                ->get();


            // Llenar los días con datos en el array de fechas
            foreach ($getHoursByDay as $result) {
                $contador_horas = $result->contador_horas;
                $horas = explode(":", $contador_horas)[0];
                $minutos = explode(":", $contador_horas)[1];
                $total_minutos = ($horas * 60) + $minutos;
                $diferencia_minutos = 540 - $total_minutos;

                // Calcular la diferencia en minutos
                $diferencia_minutos = 540 - $total_minutos;

                // Calcular horas y minutos de la diferencia
                $diferencia_horas = floor($diferencia_minutos / 60);
                $diferencia_minutos_restantes = $diferencia_minutos % 60;

                // Formatear la diferencia en formato H:i
                $diferencia_formato_Hi = sprintf('%02d:%02d', $diferencia_horas, $diferencia_minutos_restantes);

                $fechas[$result->fecha] = [
                    'fecha' => $result->fecha,
                    'contador_horas' => $contador_horas,
                    'excede_nueve_horas' => $total_minutos >= 540, // 9 horas en minutos
                    'diferencia_minutos' => $diferencia_formato_Hi
                ];
            }

            // Rellenar los días sin datos con 0:0
            foreach ($fechas as $fecha => $value) {
                if ($value === null) {
                    $fechas[$fecha] = [
                        'fecha' => $fecha,
                        'contador_horas' => '0:0',
                        'diferencia_minutos' => '9:0',
                        'excede_nueve_horas' => false
                    ];
                }
            }

            // Convertir el array asociativo a un array indexado
            $hoursByDay = array_values($fechas);
            return response()->json(formatErrorResponse(false, '', $hoursByDay));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }

    public function getActivitiesPendient()
    {
        // Por rol y excluyendose
    }

    public function getStatusByDays()
    {
        //Ver el estado de los dias en el rango de fechas establecido
    }

    public function getIndividualActivities($id, $fechaActividad)
    {
        try {
            $getActivities = DB::table('activities')
            ->select(
                'activities.*',
                'users_validate_activities.*',
                'users.numero_empleado as user_numero_empleado'
            )
            ->leftJoin('users_validate_activities', 'activities.id_actividad', '=', 'users_validate_activities.id_actividad')
            ->leftJoin('users', 'users_validate_activities.id_user', '=', 'users.id')
            ->where('activities.fecha_actividad', $fechaActividad)
            ->where('activities.id_usuario_asignado', $id)
            ->get();
            return response()->json(formatErrorResponse(false, '', $getActivities));
        } catch (\Exception $e) {
            return response()->json(formatErrorResponse(true, 'Ocurrio un error, intente de nuevo y si el problema persiste contacte con el departamento de TI.', base64_encode($e->getMessage())));
        }
    }
}
