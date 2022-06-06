<?php

declare(strict_types=1);

namespace Spiral\Console;

use Spiral\Console\Exception\ConfigException;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;
use Spiral\Core\Container\SingletonInterface;

final class CommandManager implements SingletonInterface
{
    /** @var class-string<\Symfony\Component\Console\Command\Command>[] */
    private array $commands = [];

    /** @var SequenceInterface[] */
    private array $sequences = [];

    public function register(string $command, bool $lowPriority = false): void
    {
        if ($lowPriority) {
            $this->commands[] = $command;

            return;
        }

        \array_unshift($this->commands, $command);
    }

    /**
     * User defined set of commands (to be used when auto-location is off).
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    public function registerSequence(
        string $name,
        string|array|\Closure $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->sequences[$name][] = \is_array($sequence) || \is_callable($sequence)
            ? new CallableSequence($sequence, $header, $footer)
            : new CommandSequence($sequence, $options, $header, $footer);
    }

    /**
     * Get list of sequences with given name.
     *
     * @return \Generator|SequenceInterface[]
     *
     * @throws ConfigException
     */
    public function getSequence(string $name): \Generator
    {
        $sequence = (array)($this->config['sequences'][$name] ?? []);

        foreach ($sequence as $item) {
            yield $this->parseSequence($item);
        }
    }

    /**
     * Get list of configure sequences.
     *
     * @return \Generator|SequenceInterface[]
     *
     * @throws ConfigException
     */
    public function getConfigureSequence(): \Generator
    {
        return $this->getSequence('configure');
    }

    /**
     * Get list of all update sequences.
     *
     * @return \Generator|SequenceInterface[]
     *
     * @throws ConfigException
     */
    public function getUpdateSequence(): \Generator
    {
        return $this->getSequence('update');
    }

    private function parseSequence(SequenceInterface|string|array $item): SequenceInterface
    {
        if ($item instanceof SequenceInterface) {
            return $item;
        }

        if (\is_callable($item)) {
            return new CallableSequence($item);
        }

        if (\is_array($item) && isset($item['command'])) {
            return new CommandSequence(
                $item['command'],
                $item['options'] ?? [],
                $item['header'] ?? '',
                $item['footer'] ?? ''
            );
        }

        if (\is_array($item) && isset($item['invoke'])) {
            return new CallableSequence(
                $item['invoke'],
                $item['header'] ?? '',
                $item['footer'] ?? ''
            );
        }

        throw new ConfigException(
            \sprintf('Unable to parse sequence `%s`.', \json_encode($item, JSON_THROW_ON_ERROR))
        );
    }
}
