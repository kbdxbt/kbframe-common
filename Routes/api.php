<?php

use Illuminate\Support\Facades\Route;

// 上传模块
Route::group(['prefix' => 'upload'], function () {
    Route::post('image', [Modules\Common\Http\Controllers\UploadController::class, 'image'])->name('upload.image');
});
