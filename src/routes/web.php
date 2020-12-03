<?php

use Codegaf\StorageManager\StorageManagerController;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('file/model', [StorageManagerController::class, 'filesByModel'])->name('file.model');
    Route::get('file/selected', [StorageManagerController::class, 'filesByIds'])->name('file.selected');
    Route::get('file/{media}', [StorageManagerController::class, 'file'])->name('file');
    Route::get('file/collection/{collection}', [StorageManagerController::class, 'filesByCollection'])->name('file.collection');
});

