<?php

declare(strict_types=1);

namespace Spiral\Console\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class ConfirmationRequiredWhenApplicationInProduction
{
    public function __construct(
        public readonly string $warningMessage = 'Application in production.'
    ) {
    }

    public function __invoke(): bool
    {
    }
}
