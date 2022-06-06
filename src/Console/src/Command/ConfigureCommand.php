<?php

declare(strict_types=1);

namespace Spiral\Console\Command;

use Psr\Container\ContainerInterface;
use Spiral\Console\Attribute\ConfirmationRequired;
use Spiral\Console\CommandManager;

#[ConfirmationRequired(
    class: ApplicationInProductionConfirmation::class,
    warningMessage: 'Application in production.'
)]
final class ConfigureCommand extends SequenceCommand
{
    protected const NAME = 'configure';
    protected const DESCRIPTION = 'Configure project';

    public function perform(CommandManager $manager, ContainerInterface $container): int
    {
        $this->info('Configuring project:');
        $this->newLine();

        return $this->runSequence($manager->getConfigureSequence(), $container);
    }
}
