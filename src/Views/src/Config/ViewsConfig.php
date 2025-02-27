<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\Exception\ConfigException;

final class ViewsConfig extends InjectableConfig
{
    public const CONFIG = 'views';

    /** @var array */
    protected $config = [
        'cache'        => [
            'enable'    => false,
            'directory' => '/tmp',
        ],
        'namespaces'   => [],
        'dependencies' => [],
        'engines'      => [
            NativeEngine::class,
        ],
    ];

    public function isCacheEnabled(): bool
    {
        return !empty($this->config['cache']['enable']) || !empty($this->config['cache']['enabled']);
    }

    public function getCacheDirectory(): string
    {
        return rtrim($this->config['cache']['directory'], '/') . '/';
    }

    /**
     * Return all namespaces and their associated directories.
     */
    public function getNamespaces(): array
    {
        return $this->config['namespaces'];
    }

    /**
     * Class names of all view dependencies.
     *
     * @return array<int, Autowire>
     *
     * @throws ConfigException
     */
    public function getDependencies(): array
    {
        $dependencies = [];
        foreach ($this->config['dependencies'] as $dependency) {
            $dependencies[] = $this->wire($dependency);
        }

        return $dependencies;
    }

    /**
     * Get all the engines associated with view component.
     *
     * @return array<int, Autowire>
     *
     * @throws ConfigException
     */
    public function getEngines(): array
    {
        $engines = [];
        foreach ($this->config['engines'] as $engine) {
            $engines[] = $this->wire($engine);
        }

        return $engines;
    }

    /**
     * @param Autowire|class-string $item
     *
     * @throws ConfigException
     */
    private function wire($item): Autowire
    {
        if ($item instanceof Autowire) {
            return $item;
        }

        if (is_string($item)) {
            return new Autowire($item);
        }

        throw new ConfigException('Invalid class reference in view config.');
    }
}
