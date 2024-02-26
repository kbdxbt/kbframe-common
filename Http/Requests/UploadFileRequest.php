<?php

declare(strict_types=1);

namespace Modules\Common\Http\Requests;

use Illuminate\Validation\Rules\File;
use Modules\Core\Http\Requests\BaseRequest;

class UploadFileRequest extends BaseRequest
{
    protected int $fileMaxSize = 20480;

    public function authorize()
    {
        return true;
    }

    public function uploadImageRules()
    {
        return [
            'file' => ['required', File::image()->max($this->fileMaxSize)],
        ];
    }

    public function uploadFileRules()
    {
        return [
            'file' => ['required', 'max:'.$this->fileMaxSize],
        ];
    }

    public function excelRules()
    {
        return [
            'file' => [
                'required',
                File::types(['doc', 'xlsx', 'xls', 'docx', 'ppt', 'odt', 'ods', 'odp'])->max($this->fileMaxSize),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => '请选择上传文件',
            'file.image' => '只能上传图片类型的文件',
            'file.mimes' => '上传文件类型错误',
            'file.max' => '文件类型不能超过20M',
        ];
    }
}
