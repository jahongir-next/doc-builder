<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate', [DocumentController::class, 'generate'])->name('document.generate');

//Route::post('/generate', [DocumentController::class, 'generate'])->name('document.generate');

