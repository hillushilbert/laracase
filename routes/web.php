<?php

use Illuminate\Support\Facades\Route;
use Hillus\Laracase\Http\Controllers\CrudController;

    Route::match(['post','get'],'/crud/new', [CrudController::class, 'index'])->name('crud.new');
    Route::match(['post','get'],'/crud/grid', [CrudController::class, 'grid'])->name('crud.grid');
    Route::match(['post','get'],'/crud/api', [CrudController::class, 'api'])->name('crud.api');
    Route::post('/crud/create', [CrudController::class, 'storage'])->name('crud.blade.storage');
    Route::get('/crud/create/{tabela}/ajax', [CrudController::class, 'ajax']);

    Route::post('/crud/createGrid', [CrudController::class, 'storageGrid'])->name('crud.grid.storage');
    Route::post('/crud/createApi', [CrudController::class, 'storageApi'])->name('crud.api.storage');