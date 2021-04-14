<?php

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/users' ,[ \App\Http\Controllers\User::class , 'addUsers' ]);

Route::put('/users' ,[ \App\Http\Controllers\User::class , 'editUsers' ]);

Route::delete('/users' ,[ \App\Http\Controllers\User::class , 'deleteUsers' ]);

Route::patch('/users/{user}/verify' , [\App\Http\Controllers\User::class , 'verify']);

Route::get('/users' , [\App\Http\Controllers\User::class , 'listUsers']);

Route::post('/users/{user}/projects' , [\App\Http\Controllers\Project::class , 'addProjects']);

Route::post('/projects/link/users' , [\App\Http\Controllers\Project::class , 'linkUser']);

Route::middleware('auth:sanctum')->get('/projects' , [\App\Http\Controllers\Project::class , 'listProjects']);

Route::middleware('auth:sanctum')->delete('/projects' , [\App\Http\Controllers\Project::class , 'deleteProject']);

Route::post('users/{user}/labels' , [\App\Http\Controllers\Label::class , 'addLabels']);

Route::middleware('auth:sanctum')->get('/labels' , [\App\Http\Controllers\Label::class , 'filterLabels']);

Route::middleware('auth:sanctum')->delete('/labels' , [\App\Http\Controllers\Label::class , 'deleteLabels']);

Route::post('/labels/link/projects' , [\App\Http\Controllers\Label::class , 'linkLabels']);
