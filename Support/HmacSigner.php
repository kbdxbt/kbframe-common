<?php

namespace Modules\Common\Support;

use Modules\Common\Contracts\Signer;

class HmacSigner implements Signer
{
    public function __construct(
        private ?string $secret = '',
        private ?string $algo = 'sha256'
    ) {
    }

    public function sign(array $payload): string
    {
        return hash_hmac($this->algo, $this->transformToPreEncryptedData($payload), $this->secret);
    }

    public function validate(string $signature, array $payload): bool
    {
        return hash_equals($signature, $this->sign($payload));
    }

    protected function sort(array $payload): array
    {
        ksort($payload);

        foreach ($payload as &$item) {
            \is_array($item) and $item = $this->sort($item);
        }

        return $payload;
    }

    protected function transformToPreEncryptedData(array $payload): string
    {
        $sortedPayload = $this->sort($payload);

        return http_build_query($sortedPayload);
    }
}
