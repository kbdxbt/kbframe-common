<?php

namespace Modules\Common\Support\Traits;

use Symfony\Component\OptionsResolver\OptionsResolver;

trait HasOptions
{
    /** @var array<array-key, mixed> */
    protected array $options = [];

    /**
     * @param  mixed  $option
     * @return array|mixed
     */
    public function __get($option)
    {
        return $this->get($option);
    }

    public function __set($option, $value): void
    {
        $this->set($option, $value);
    }

    /**
     * @param  mixed  $option
     */
    public function __isset($option): bool
    {
        return isset($this->options[$option]);
    }

    // protected $defined = [];
    // protected $required = [];
    // protected $deprecated = [];
    // protected $defaults = [];
    // protected $prototype = false;
    // protected $allowedValues = [];
    // protected $allowedTypes = [];
    // protected $normalizers = [];
    // protected $infos = [];

    public static function createOptionsResolver(): OptionsResolver
    {
        return new OptionsResolver();
    }

    /**
     * @param  mixed  $value
     * @return $this
     */
    public function addOption(string $option, $value)
    {
        $this->addOptions([$option => $value]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addOptions(array $options)
    {
        $resolver = $this->configureOptionsResolver(
            $this->preConfigureOptionsResolver(
                static::createOptionsResolver()
            )
        );

        $this->options = $resolver->resolve(array_merge($this->options, $options));

        return $this;
    }

    /**
     * @param  mixed  $value
     * @return $this
     */
    public function setOption(string $option, $value)
    {
        return $this->addOption($option, $value);
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        $resolver = $this->configureOptionsResolver(
            $this->preConfigureOptionsResolver(
                static::createOptionsResolver()
            )
        );

        $this->options = array_merge($this->options, $resolver->resolve($options));

        return $this;
    }

    /**
     * @param  null|mixed  $default
     * @return array|mixed
     */
    public function getOption(?string $option = null, $default = null)
    {
        if ($option === null) {
            return $this->options;
        }

        return $this->options[$option] ?? $default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  mixed  $value
     * @return $this
     */
    public function set(string $option, $value)
    {
        $this->setOption($option, $value);

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function get(?string $option = null)
    {
        return $this->getOption($option);
    }

    public function has(string $option): bool
    {
        return \array_key_exists($option, $this->options);
    }

    protected function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        // configure options resolver...
        return $optionsResolver;
    }

    protected function preConfigureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        property_exists($this, 'defined') and $optionsResolver->setDefined($this->defined);
        property_exists($this, 'required') and $optionsResolver->setRequired($this->required);
        property_exists($this, 'defaults') and $optionsResolver->setDefaults((array) $this->defaults);
        property_exists($this, 'prototype') and $optionsResolver->setPrototype((bool) $this->prototype);

        if (property_exists($this, 'deprecated')) {
            foreach ((array) $this->deprecated as $option => $deprecated) {
                array_unshift($deprecated, $option);
                $optionsResolver->setDeprecated(...$deprecated);
            }
        }

        if (property_exists($this, 'allowedValues')) {
            foreach ((array) $this->allowedValues as $option => $allowedValue) {
                $optionsResolver->setAllowedValues($option, $allowedValue);
            }
        }

        if (property_exists($this, 'allowedTypes')) {
            foreach ((array) $this->allowedTypes as $option => $allowedType) {
                $optionsResolver->setAllowedTypes($option, $allowedType);
            }
        }

        if (property_exists($this, 'normalizers')) {
            foreach ((array) $this->normalizers as $option => $normalizer) {
                $optionsResolver->setNormalizer($option, $normalizer);
            }
        }

        if (property_exists($this, 'infos')) {
            foreach ((array) $this->infos as $option => $info) {
                $optionsResolver->setInfo($option, $info);
            }
        }

        return $optionsResolver;
    }
}
