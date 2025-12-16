<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\API\BeforeAfterController;
use App\Http\Controllers\API\LeadController;


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
    Route::get('/before-after', [BeforeAfterController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'create']);

    // Register y Login ADMIN
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Rutas admin
    Route::middleware('auth:sanctum')->group(function () {
        //Contenidos
        Route::post('/before-after', [BeforeAfterController::class, 'store']);
        Route::put('/before-after/{id}', [BeforeAfterController::class, 'update']);
        Route::delete('/before-after/{id}', [BeforeAfterController::class, 'destroy']);

        //Formularios
        Route::get('/leads', [LeadController::class, 'index']);
        Route::get('/leads/{id}', [LeadController::class, 'show']);
        Route::get('/leads/stats', [LeadController::class, 'stats']);
    });
});
