<?php

namespace Modules\Common\Support\Traits;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait HasHttpClient
{
    protected PendingRequest $httpClient;

    protected $httpOptions = [];

    public function setHttpOptions(array $httpOptions)
    {
        $this->httpOptions = array_merge($this->httpOptions, $httpOptions);

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }

    public function getHttpClient(array $config = [])
    {
        $config && $this->setHttpOptions($config);

        if ($config || ! $this->httpClient instanceof PendingRequest) {
            $this->httpClient = Http::withOptions($this->httpOptions);
        }

        return $this->httpClient;
    }
}
