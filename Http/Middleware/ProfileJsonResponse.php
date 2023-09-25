<?php

declare(strict_types=1);

namespace Modules\Common\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileJsonResponse
{
    private array $expectFullUrl = [];

    private array $exprectField = [];

    /**
     * Handle an incoming request.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);

        if (
            $response instanceof JsonResponse &&
            app()->bound('debugbar') &&
            app('debugbar')->isEnabled() &&
            is_object($response->getData())
        ) {
            $response->setData($response->getData(true) + [
                '_debugbar' => app('debugbar')->getData(),
            ]);
        }

        if ($request->fullUrlIs($this->getExpectFullUrl())) {
            $response->setData(Arr::except(Arr::wrap($response->getData(true)), $this->getExprectField()));
        }

        return $response;
    }

    protected function getExpectFullUrl(): array
    {
        return array_merge(['*request-docs*'], $this->expectFullUrl);
    }

    protected function getExprectField(): array
    {
        return array_merge(['debugbar', 'soar_scores'], $this->exprectField);
    }
}
