<?php

declare(strict_types=1);

namespace Spiral\Console\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Command\CleanCommand;
use Spiral\Command\PublishCommand;
use Spiral\Console\CommandLocator;
use Spiral\Console\CommandManager;
use Spiral\Console\Console;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Console\LocatorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

/**
 * Bootloads console and provides ability to register custom bootload commands.
 */
final class ConsoleBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
    ];

    protected const SINGLETONS = [
        Console::class => Console::class,
        LocatorInterface::class => CommandLocator::class,
    ];

    public function __construct(
        private readonly CommandManager $manager
    ) {
    }

    public function init(AbstractKernel $kernel, FactoryInterface $factory): void
    {
        // Lowest priority
        $kernel->booted(static function (AbstractKernel $kernel) use ($factory): void {
            $kernel->addDispatcher($factory->make(ConsoleDispatcher::class));
        });

        $this->addCommand(CleanCommand::class);
        $this->addCommand(PublishCommand::class);
    }

    /**
     * @param class-string<\Symfony\Component\Console\Command\Command> $command
     * @param bool $lowPriority A low priority command will be overwritten in a name conflict case.
     *        The parameter might be removed in the next major update.
     */
    public function addCommand(string $command, bool $lowPriority = false): void
    {
        $this->manager->register($command, $lowPriority);
    }

    public function addConfigureSequence(
        string|array|\Closure $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->addSequence('configure', $sequence, $header, $footer, $options);
    }

    public function addUpdateSequence(
        string|array|\Closure $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->addSequence('update', $sequence, $header, $footer, $options);
    }

    public function addSequence(
        string $name,
        string|array|\Closure $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->manager->registerSequence($name, $sequence, $header, $footer, $options);
    }
}
