<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SurveyController;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
        ]);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::prefix('survey')->group(function () {
        Route::get('/', [SurveyController::class, 'index']);
        Route::post('/', [SurveyController::class, 'store']);
        Route::get('/{id}', [SurveyController::class, 'show']);
        Route::get('/public/{slug}', [SurveyController::class, 'showBySlug']);
        Route::put('/{id}', [SurveyController::class, 'update']);
        Route::delete('/{id}', [SurveyController::class, 'destroy']);
    });
    Route::get('/stats', [DashboardController::class, 'stats']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/survey/public/{slug}', [SurveyController::class, 'storeAnswer']);
