<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Common\Support\HmacSigner;

it('can upload file', function () {
    define('LARAVEL_START', microtime(true));

    $this->app['env'] = 'production';

    $header['nonce'] = Str::random();
    $header['timestamp'] = time();
    $header['signature'] = (new HmacSigner(config('services.signer.default.secret')))->sign([
        'timestamp' => $header['timestamp'],
        'nonce' => $header['nonce'],
    ]);

    $file = UploadedFile::fake()->create('test_file.jpg', 100);

    $this->postJson('upload_image', ['file' => $file], $header)->assertStatus(200);
});
