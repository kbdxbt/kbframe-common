<?php

declare(strict_types=1);

namespace Modules\Common\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Modules\Common\Models\HttpLog;

class LogHttp
{
    protected $exceptMethods = [];

    protected $exceptPaths = [];

    protected $removedHeaders = [
        'Authorization',
    ];

    protected $removedInputs = [
        'password',
        'password_confirmation',
        'new_password',
        'old_password',
    ];

    protected static $skipCallbacks = [];

    /**
     * Handle an incoming request.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|Response
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
        $this->logHttp($request, $response);
    }

    public static function skipWhen(\Closure $callback): void
    {
        static::$skipCallbacks[] = $callback;
    }

    /**
     * @param  \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|Response  $response
     */
    protected function logHttp(Request $request, $response): void
    {
        if ($this->shouldLogHttp($request)) {
            HttpLog::query()->create($this->collectData($request, $response));
        }
    }

    protected function shouldLogHttp(Request $request): bool
    {
        return ! $this->shouldntLogHttp($request);
    }

    protected function shouldntLogHttp(Request $request): bool
    {
        if (\in_array($request->method(), array_map('strtoupper', $this->exceptMethods), true)) {
            return true;
        }

        foreach ($this->exceptPaths as $exceptPath) {
            $exceptPath === '/' or $exceptPath = trim($exceptPath, '/');
            if ($request->fullUrlIs($exceptPath) || $request->is($exceptPath)) {
                return true;
            }
        }

        return (bool) ($this->shouldSkip($request));
    }

    protected function shouldSkip(Request $request): bool
    {
        foreach (static::$skipCallbacks as $callback) {
            if ($callback($request)) {
                return true;
            }
        }

        return false;
    }

    protected function shouldntSkip(Request $request): bool
    {
        return ! $this->shouldSkip($request);
    }

    /**
     * @param  \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|Response  $response
     */
    protected function collectData(Request $request, $response): array
    {
        // MySQL mediumtext 类型最大 16MB (15 * 1024 * 1024)
        $maxLengthOfMediumtext = 15 * 1024 * 1024;

        return [
            'ip' => substr((string) $request->getClientIp(), 0, 16),
            'url' => substr($request->url(), 0, 128),
            'method' => substr($request->method(), 0, 10),
            'request_id' => substr(app('request_id'), 0, 50),
            'request_param' => substr($this->extractInput($request), 0, $maxLengthOfMediumtext),
            'request_header' => substr($this->extractHeader($request), 0, $maxLengthOfMediumtext),
            'request_time' => (int) LARAVEL_START,
            'response_code' => (int) $response->status(),
            'response_header' => substr($this->extractHeader($response), 0, $maxLengthOfMediumtext),
            'response_body' => substr((string) $response->getContent(), 0, $maxLengthOfMediumtext),
            'response_time' => (int) microtime(true),
            'duration' => substr($this->calculateDuration(), 0, 10),
            'ext' => [],
        ];
    }

    /**
     * @param  \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Request|Response  $requestOrResponse
     */
    protected function extractHeader($requestOrResponse): string
    {
        $header = Arr::except(
            $requestOrResponse->headers->all(),
            array_map('strtolower', $this->removedHeaders)
        );

        return (string) json_encode($header);
    }

    protected function extractInput(Request $request): string
    {
        return (string) json_encode($request->except($this->removedInputs));
    }

    protected function calculateDuration(): string
    {
        return number_format(microtime(true) - LARAVEL_START, 3);
    }
}
