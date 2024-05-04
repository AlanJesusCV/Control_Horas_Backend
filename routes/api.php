<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DespachoController;
use App\Http\Controllers\API\GestionController;
use App\Http\Controllers\API\GrupoController;
use App\Http\Controllers\API\SeguimientoController;
use App\Http\Controllers\API\AvisoController;
use App\Http\Controllers\API\CalendarioController;
use App\Http\Controllers\API\CargaController;
use App\Http\Controllers\API\CargaControllerV2;
use App\Http\Controllers\API\CargaControllerV3;
use App\Http\Controllers\API\CargaPagosYConveniosController;
use App\Http\Controllers\API\CategoriaySubcategoriaController;
use App\Http\Controllers\API\DireccionController;
use App\Http\Controllers\API\DocumentacionController;
use App\Http\Controllers\API\EstadisticaController;
use App\Http\Controllers\API\GestionesYDireccionesMasivasController;
use App\Http\Controllers\API\MetasController;
use App\Http\Controllers\API\MinutaController;
use App\Http\Controllers\API\PDFControler;
use App\Http\Controllers\API\ReporteBatchSCLController;
use App\Http\Controllers\API\ReporteConvenioyLiquidacionController;
use App\Http\Controllers\API\ReporteDireccionController;
use App\Http\Controllers\API\ReporteGestionController;
use App\Http\Controllers\API\ReportePagosController;
use App\Http\Controllers\API\ReporteTelefonoController;
use App\Http\Controllers\API\TelefonoController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['api'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/encriptacion/{id}', [Controller::class, 'encriptar']);
    Route::get('/desencriptar/{id}', [Controller::class, 'desencriptar']);
});

// Temporal para encriptar cosas

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Users Inicio
    Route::get('/user/get-users-general', [UserController::class, 'getUsers']);
    Route::get('/user/get-manager-type', [UserController::class, 'getUsersManagers']);
    Route::get('/user/get-catcher-type', [UserController::class, 'getUsersCatcher']);
    Route::get('/user/get-validator-type', [UserController::class, 'getUsersValidator']);
    Route::post('/user', [UserController::class, 'createUser']);
    Route::post('/user/put-update/{id}', [UserController::class, 'updateUser']);
    Route::post('/user/put-update-password/{id}', [UserController::class, 'updatePassword']);
    Route::post('/user/put-delete/{id}', [UserController::class, 'logicalDeleteUser']);
    Route::post('/user/put-enable/{id}', [UserController::class, 'enableDeleteUser']);
    // Users Fin

    // Actividades Inicio

    // Actividades Fin

});
