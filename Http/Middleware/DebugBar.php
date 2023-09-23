<?php

declare(strict_types=1);

namespace Modules\Common\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Support\HmacSigner;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class DebugBar
{
    /**
     * Handle an incoming request.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        return $next($request);
    }

    /**
     * @param  \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|Response  $response
     */
    public function terminate(Request $request, $response): void
    {
        if ($this->shouldDebugBar()) {
            $data = Arr::wrap($response->getData(true));
            Arr::set($data, 'debugbar', debugbar()->getData());

            // Update the new content and reset the content length
            $response->setData($data);
            $response->headers->remove('Content-Length');
        }
    }

    protected function shouldDebugBar(): bool
    {
        return ! app()->isProduction() && app()->hasDebugModeEnabled();
    }
}
