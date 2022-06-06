<?php

declare(strict_types=1);

namespace Spiral\Console\Config;

use Spiral\Console\Exception\ConfigException;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;
use Spiral\Console\SequenceInterface;
use Spiral\Core\InjectableConfig;

final class ConsoleConfig extends InjectableConfig
{
    public const CONFIG = 'console';

    protected array $config = [
        'name' => null,
        'version' => null,
    ];

    public function getName(): string
    {
        return $this->config['name'] ?? 'Spiral Framework';
    }

    public function getVersion(): string
    {
        return $this->config['version'] ?? 'UNKNOWN';
    }
}
