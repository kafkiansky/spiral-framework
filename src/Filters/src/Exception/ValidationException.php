<?php

declare(strict_types=1);

namespace Spiral\Filters\Exception;

use Spiral\Filters\FilterBag;
use Spiral\Http\Exception\ClientException;

class ValidationException extends ClientException
{
    public function __construct(
        public readonly FilterBag $bag,
        public readonly array $errors,
        public readonly mixed $context = null
    ) {
        parent::__construct(422, 'The given data was invalid.');
    }
}
