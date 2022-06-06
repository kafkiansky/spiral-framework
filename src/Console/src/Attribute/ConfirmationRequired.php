<?php

declare(strict_types=1);

namespace Spiral\Console\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Console\ConfirmationDefinitionInterface;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class ConfirmationRequired
{
    /**
     * @param class-string<ConfirmationDefinitionInterface> $classDefinition
     * @param non-empty-string $warningMessage
     */
    public function __construct(
        public readonly string $classDefinition,
        public readonly ?string $warningMessage = null
    ) {
    }
}
