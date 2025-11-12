<?php

namespace App\Services\EmailTags;

use App\Enums\EmailTemplateType;

class MergeTagResolver
{
    protected array $providers = [];
    protected array $context = [];

    public function forEmailTemplate(int|EmailTemplateType $type): static
    {
        if ($type instanceof EmailTemplateType) {
            $type = $type->value;
        }

        $providerClasses = EmailTemplateTypeMap::getProvidersForType($type);
        $this->providers = array_map(fn($class) => app($class), $providerClasses);

        return $this;
    }

    public function forModels(array $providerClasses): static
    {
        $this->providers = array_map(fn($class) => app($class), $providerClasses);
        return $this;
    }

    public function context(array $context): static
    {
        $this->context = $context;
        return $this;
    }

    public function resolve(array $context = null): array
    {
        $context = $context ?? $this->context;
        $tags = [];

        foreach ($context as $model) {
            foreach ($this->providers as $provider) {
                if ($provider->supports($model)) {
                    $tags = array_merge($tags, $provider->resolve($model));
                }
            }
        }

        return $tags;
    }

    public function getAvailableTags(array $context = null): array
    {
        $tags = [];

        if (empty($context)) {
            foreach ($this->providers as $provider) {
                $tags = array_merge($tags, $provider::getAvailableTags());
            }
            return $tags;
        }

        foreach ($context as $model) {
            foreach ($this->providers as $provider) {
                if ($provider->supports($model)) {
                    $tags = array_merge($tags, $provider->getAvailableTags());
                }
            }
        }

        return $tags;
    }
}
