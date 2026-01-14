<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\API\ClinicalImageController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\ProcedureController;
use App\Http\Controllers\API\MedicalEvaluationController;


/*|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/*-------------------------------------------------------*/
// Ruta de prueba simple 
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Cold Esthetic funcionando',
        'version' => 'v1'
    ]);
});
/*-------------------------------------------------------*/


Route::prefix('v1')->group(function () {

    // Rutas públicas
    Route::get('/before-after', [ClinicalImageController::class, 'index']);
    Route::get('/clinical-images', [ClinicalImageController::class, 'index']);

    // Register y Login ADMIN
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Rutas admin
    Route::middleware('auth:sanctum')->group(function () {
        //Imágenes Clínicas
        Route::post('/before-after', [ClinicalImageController::class, 'store']);
        Route::put('/before-after/{id}', [ClinicalImageController::class, 'update']);
        Route::delete('/before-after/{id}', [ClinicalImageController::class, 'destroy']);

        // Alias (más claro que before-after)
        Route::post('/clinical-images', [ClinicalImageController::class, 'store']);
        Route::put('/clinical-images/{id}', [ClinicalImageController::class, 'update']);
        Route::delete('/clinical-images/{id}', [ClinicalImageController::class, 'destroy']);

        // Pacientes
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);
        Route::post('/patients', [PatientController::class, 'store']);

        // Procedimientos + items (total calculado automáticamente)
        Route::get('/procedures', [ProcedureController::class, 'index']);
        Route::get('/procedures/{procedure}', [ProcedureController::class, 'show']);
        Route::post('/procedures', [ProcedureController::class, 'store']);
        Route::put('/procedures/{procedure}', [ProcedureController::class, 'update']);

        // Valoraciones (1-1 con procedures)
        Route::post('/medical-evaluations', [MedicalEvaluationController::class, 'store']);
        Route::put('/medical-evaluations/{medicalEvaluation}', [MedicalEvaluationController::class, 'update']);
    });
});
