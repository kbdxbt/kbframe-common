<?php

namespace Modules\Common\Support\Traits;

use Jiannei\Response\Laravel\Support\Facades\Response;

trait JsonResponseable
{
    public function accepted($data = [], string $message = '', string $location = '')
    {
        return Response::accepted($data, $message, $location);
    }

    public function created($data = [], string $message = '', string $location = '')
    {
        return Response::created($data, $message, $location);
    }

    public function noContent(string $message = ''): void
    {
        // return Response::noContent($message);
    }

    public function ok(string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        return Response::ok($message, $code, $headers, $option);
    }

    public function localize(int $code = 200, array $headers = [], int $option = 0)
    {
        return Response::localize($code, $headers, $option);
    }

    public function errorBadRequest(string $message = ''): void
    {
        Response::errorBadRequest($message);
    }

    public function errorUnauthorized(string $message = ''): void
    {
        Response::errorUnauthorized($message);
    }

    public function errorForbidden(string $message = ''): void
    {
        Response::errorForbidden($message);
    }

    public function errorNotFound(string $message = ''): void
    {
        Response::errorNotFound($message);
    }

    public function errorMethodNotAllowed(string $message = ''): void
    {
        Response::errorMethodNotAllowed($message);
    }

    public function errorInternal(string $message = ''): void
    {
        Response::errorInternal($message);
    }

    public function fail(string $message = '', int $code = 500, $errors = null, array $header = [], int $options = 0)
    {
        return Response::fail($message, $code, $errors, $header, $options);
    }

    public function success($data = [], string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        return Response::success($data, $message, $code, $headers, $option);
    }
}
