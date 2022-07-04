<?php

declare(strict_types=1);

namespace Spiral\App\Filters;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\FilterInterface;

class SomeFilter implements FilterInterface
{
    #[Post]
    public string $foo;

    #[Query(key: 'baz')]
    public string $baz;
}
