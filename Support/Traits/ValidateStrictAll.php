<?php

namespace Modules\Common\Support\Traits;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidateStrictAll
{
    /**
     * Run the validation routine against the given validator.
     *
     * @param  array|\Illuminate\Contracts\Validation\Validator  $validator
     * @param  ?Request  $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateStrictAllWith($validator, Request $request = null): array
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $request = $request ?: request();

        if (\is_array($validator)) {
            $validator = $this->getValidationStrictAllFactory()->make($request->strictAll(), $validator);
        }

        return $validator->validate();
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws ValidationException
     */
    public function validateStrictAll(
        Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): array {
        return $this->getValidationStrictAllFactory()->make(
            $request->strictAll(),
            $rules,
            $messages,
            $customAttributes
        )->validate();
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws ValidationException
     */
    public function validateStrictAllWithBag(
        string $errorBag,
        Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): array {
        try {
            return $this->validateStrictAll($request, $rules, $messages, $customAttributes);
        } catch (ValidationException $e) {
            $e->errorBag = $errorBag;

            throw $e;
        }
    }

    /**
     * Get a validation factory instance.
     */
    protected function getValidationStrictAllFactory(): Factory
    {
        return app(Factory::class);
    }
}
