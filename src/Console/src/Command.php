<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Attributes\Internal\AttributeReader;
use Spiral\Console\Attribute\ConfirmationRequired;
use Spiral\Console\Exception\CommandException;
use Spiral\Console\Signature\Parser;
use Spiral\Console\Traits\HelpersTrait;
use Spiral\Core\Exception\ScopeException;
use Spiral\Core\InvokerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides automatic command configuration and access to global container scope.
 */
abstract class Command extends SymfonyCommand
{
    use HelpersTrait;

    /** Command name. */
    protected const NAME = '';
    /** Short command description. */
    protected const DESCRIPTION = null;
    /** Command signature. */
    protected const SIGNATURE = null;
    /** Command options specified in Symfony format. For more complex definitions redefine getOptions() method. */
    protected const OPTIONS = [];
    /** Command arguments specified in Symfony format. For more complex definitions redefine getArguments() method. */
    protected const ARGUMENTS = [];

    protected ?ContainerInterface $container = null;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Pass execution to "perform" method using container to resolve method dependencies.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->container === null) {
            throw new ScopeException('Container is not set');
        }

        $method = method_exists($this, 'perform') ? 'perform' : '__invoke';

        $invoker = $this->container->get(InvokerInterface::class);

        try {
            [$this->input, $this->output] = [$input, $output];

            if (! $this->confirmToPerform()) {
                return Command::FAILURE;
            }

            // Executing perform method with method injection
            return (int)$invoker->invoke([$this, $method], \compact('input', 'output'));
        } finally {
            [$this->input, $this->output] = [null, null];
        }
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        if (static::SIGNATURE !== null) {
            $this->configureViaSignature((string)static::SIGNATURE);
        } else {
            $this->setName(static::NAME);
        }

        $this->setDescription((string)static::DESCRIPTION);

        foreach ($this->defineOptions() as $option) {
            \call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->defineArguments() as $argument) {
            \call_user_func_array([$this, 'addArgument'], $argument);
        }
    }

    protected function configureViaSignature(string $signature): void
    {
        $result = (new Parser())->parse($signature);

        $this->setName($result->name);

        foreach ($result->options as $option) {
            $this->getDefinition()->addOption($option);
        }

        foreach ($result->arguments as $argument) {
            $this->getDefinition()->addArgument($argument);
        }
    }

    /**
     * Define command options.
     */
    protected function defineOptions(): array
    {
        return static::OPTIONS;
    }

    /**
     * Define command arguments.
     */
    protected function defineArguments(): array
    {
        return static::ARGUMENTS;
    }

    /**
     * Will be redesigned in the future for using attributes with different types of confirmation.
     */
    private function confirmToPerform(): bool
    {
        if ($this->hasOption('force') && $this->option('force')) {
            return true;
        }

        $reader = $this->container->get(AttributeReader::class);

        // ConfirmationRequired
        if ($attribute = $reader->getClassMetadata(new \ReflectionClass($this), ConfirmationRequired::class)) {
            $definition = $this->container->get($attribute->class);
            if (! $definition instanceof ConfirmationDefinitionInterface) {
                throw new CommandException(
                    \sprintf(
                        'Confirmation definition `%s` should be instance of `%s`.',
                        $definition::class,
                        ConfirmationDefinitionInterface::class
                    )
                );
            }

            if (! $definition->shouldBeConfirmed()) {
                return true;
            }

            $warningMessage = $attribute->warningMessage !== null
                ? $attribute->warningMessage
                : $definition->getWarningMessage();

            $this->alert($warningMessage);
            $confirmed = $this->confirm(\sprintf('Do you really wish to run command [%s]?', $this->getName()));

            if (! $confirmed) {
                $this->comment('Command Canceled!');

                return false;
            }
        }

        return true;
    }
}
