<?php

namespace Modules\Common\Http\Controllers;

use Modules\Common\Http\Requests\UploadFileRequest;
use Modules\Common\Support\Upload;
use Modules\Core\Http\Controllers\BaseController;

class UploadController extends BaseController
{
    public function image(UploadFileRequest $request)
    {
        $res = (new Upload($request->file('file'), 'public'))->upload('images');

        return $this->success(['url' => $res['url']]);
    }
}
