<?php

declare(strict_types=1);

namespace Modules\Common\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Support\HmacSigner;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class VerifySignature
{
    public function handle(Request $request, \Closure $next, string $secret = '', int $effectiveTime = 60, bool $checkRepeatRequest = true): mixed
    {
        if (! app()->isProduction()) {
            return $next($request);
        }

        $this->validateParameters($request, $effectiveTime);

        $this->validateSignature($request, $secret);

        $checkRepeatRequest and $this->validateRepeatRequest($request, $effectiveTime);

        return $next($request);
    }

    protected function validateParameters(Request $request, int $effectiveTime): void
    {
        Validator::make($request->headers(), [
            'signature' => 'required|string',
            'nonce' => 'required|string|size:16',
            'timestamp' => sprintf('required|int|max:%s|min:%s', $time = time(), $time - $effectiveTime),
        ])->validate();
    }

    protected function validateSignature(Request $request, string $secret): void
    {
        $parameters = array_merge($request->input(), [
            'timestamp' => $request->header('timestamp'),
            'nonce' => $request->header('nonce'),
        ]);

        /** @var HmacSigner $signer */
        $signer = app(HmacSigner::class, ['secret' => $secret]);
        if (! $signer->validate($request->header('signature'), $parameters)) {
            throw new InvalidSignatureException();
        }
    }

    protected function validateRepeatRequest(Request $request, int $effectiveTime): void
    {
        $cacheSignature = Cache::get($signature = $request->header('signature'));
        if ($cacheSignature) {
            throw new BadRequestException();
        }

        Cache::put($signature, spl_object_hash($request), $effectiveTime);
    }
}
