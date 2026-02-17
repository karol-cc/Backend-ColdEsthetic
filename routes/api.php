<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleCalendarAuthController;
use App\Http\Controllers\API\ClinicalImageController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\ProcedureController;
use App\Http\Controllers\API\MedicalEvaluationController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\API\AppointmentController;
use Illuminate\Http\Request;


/*|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

    Route::prefix('v1')->group(function () {

    // ======================
    // Rutas públicas
    // ======================
        Route::get('/test', function () {
            return response()->json([
                'message' => 'API Cold Esthetic funcionando',
                'version' => 'v1'
            ]);
        });

        Route::get('/clinical-images', [ClinicalImageController::class, 'index']);

        // ======================
        // Auth
        // ======================
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        // ======================
        // Rutas protegidas
        // ======================
        Route::middleware('auth:sanctum')->group(function () {

            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);

            // Imágenes clínicas
            Route::post('/clinical-images', [ClinicalImageController::class, 'store']);
            Route::put('/clinical-images/{id}', [ClinicalImageController::class, 'update']);
            Route::delete('/clinical-images/{id}', [ClinicalImageController::class, 'destroy']);

            // Pacientes
            Route::get('/patients', [PatientController::class, 'index']);
            Route::get('/patients/{patient}', [PatientController::class, 'show']);
            Route::post('/patients', [PatientController::class, 'store']);

            // Valoraciones
            Route::get('/medical-evaluation/patient/{patient}',[MedicalEvaluationController::class, 'showByPatient']);
            Route::post('/medical-evaluations', [MedicalEvaluationController::class, 'store']);
            Route::put('/medical-evaluations/{medicalEvaluation}', [MedicalEvaluationController::class, 'update']);

            // Procedimientos
            Route::get('/procedures', [ProcedureController::class, 'index']);
            Route::get('/procedures/{procedure}', [ProcedureController::class, 'show']);
            Route::post('/procedures', [ProcedureController::class, 'store']);
            Route::put('/procedures/{procedure}', [ProcedureController::class, 'update']);

            // Google Calendar Authentication
            Route::prefix('google')->group(function () {
                Route::get('/auth', [GoogleCalendarAuthController::class, 'redirectToGoogle']);
                Route::get('/callback', [GoogleCalendarAuthController::class, 'handleCallback']);
                Route::get('/status', [GoogleCalendarAuthController::class, 'getStatus']);
                Route::post('/disconnect', [GoogleCalendarAuthController::class, 'disconnect']);
            });

            // Appointments (Citas)
            Route::prefix('appointments')->group(function () {
                Route::get('/', [AppointmentController::class, 'index']);
                Route::get('/upcoming', [AppointmentController::class, 'upcoming']);
                Route::get('/{appointment}', [AppointmentController::class, 'show']);
                Route::post('/', [AppointmentController::class, 'store']);
                Route::put('/{appointment}', [AppointmentController::class, 'update']);
                Route::delete('/{appointment}', [AppointmentController::class, 'destroy']);
                Route::post('/{appointment}/complete', [AppointmentController::class, 'completeAppointment']);
            });

            Route::prefix('stats')->group(function () {
            // Estadisticas
                Route::get('/summary', [StatsController::class, 'summary']);
                Route::get('/referrer-stats', [StatsController::class, 'referrerStats']);
                Route::get('/income-monthly', [StatsController::class, 'incomeMonthly']);
                Route::get('/income-weekly', [StatsController::class, 'incomeWeekly']);
                Route::get('/procedures/top-demand', [StatsController::class, 'topByDemand']);
                Route::get('/procedures/top-income', [StatsController::class, 'topByIncome']);
                Route::get('/income-by-procedure', [StatsController::class, 'incomeByProcedureType']);
            });
        });
});

