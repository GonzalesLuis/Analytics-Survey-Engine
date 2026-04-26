<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PreSessionSurveyController;
use App\Http\Controllers\PostSessionSurveyController;
use App\Http\Controllers\TutorSurveyController;
use App\Http\Controllers\MetricResultsController;
use App\Http\Controllers\HomeController;

Route::get('/pre_session', [PreSessionSurveyController::class, 'show']);
Route::post('/pre_session', [PreSessionSurveyController::class, 'submit']);

Route::get('/post_session', [PostSessionSurveyController::class, 'show']);
Route::post('/post_session', [PostSessionSurveyController::class, 'submit']);

Route::get('/tutee_evaluation', [TutorSurveyController::class, 'show']);
Route::post('/tutee_evaluation', [TutorSurveyController::class, 'submit']);

Route::get('/survey_results', [MetricResultsController::class, 'show']);

Route::get('/', [HomeController::class, 'show']);
Route::post('/session/start', [HomeController::class, 'start']);
Route::post('/session/end', [HomeController::class, 'end']);










