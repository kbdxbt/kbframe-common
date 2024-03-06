<?php

namespace Modules\Common\Support\Traits;

use Illuminate\Http\UploadedFile;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Modules\Common\Http\Requests\UploadFileRequest;
use Modules\Common\Support\Upload;

trait UploadTrait
{
    protected string $disk = 'public';

    public function uploadImage(UploadFileRequest $request)
    {
        return $this->upload($request->file('file'), 'images');
    }

    public function uploadFile(UploadFileRequest $request)
    {
        return $this->upload($request->file('file'), 'files');
    }

    public function uploadRich(UploadFileRequest $request)
    {
        $file = $request->file('file')
            ?? $request->file('wangeditor-uploaded-image')
            ?? $request->file('wangeditor-uploaded-video');

        return $this->upload($file, 'rich');
    }

    protected function upload(UploadedFile $file, $path)
    {
        $res = (new Upload($file, $this->disk))->upload($path);

        return Response::success(['url' => $res['url']]);
    }
}
