<?php

namespace Modules\Common\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BadRequestException extends HttpException
{
    public function __construct(string $message = 'Bad request.')
    {
        parent::__construct(400, $message);
    }
}
