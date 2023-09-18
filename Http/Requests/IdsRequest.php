<?php

declare(strict_types=1);

namespace Modules\Common\Http\Requests;

use Modules\Core\Http\Requests\BaseRequest;

class IdsRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function idRules()
    {
        return [
            'id' => ['required'],
        ];
    }

    public function idsRules()
    {
        return [
            'id' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '请选择操作项',
            'ids.required' => '请选择操作项',
            'ids.array' => '参数格式不正确',
        ];
    }
}
