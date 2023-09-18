<?php

// 上传模块
Route::group(['prefix' => 'upload'], function () {
    Route::POST('image', [Modules\Common\Http\Controllers\UploadController::class, 'image'])->name('upload.image');
});
