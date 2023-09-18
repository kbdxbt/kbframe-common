<?php

namespace Modules\Common\Support;

class ResponseFormat extends \Jiannei\Response\Laravel\Support\Format
{
    public function data(?array $data, ?string $message, int $code, $errors = null): array
    {
        $formatData = [
            'status' => $this->formatStatus($code),
            'code' => $code,
            'message' => $this->formatMessage($code, $message),
            'data' => $data ?: (object) $data,
            'error' => $errors ?: (object) [],
            'request_time' => request()->server('REQUEST_TIME'),
            'request_id' => app('request_id'),
        ];

        if (! app()->isProduction() && app()->hasDebugModeEnabled()) {
            $formatData['debug'] = debugbar()->getData();
        }

        return $this->formatDataFields($formatData, config('response.format.fields', []));
    }
}
