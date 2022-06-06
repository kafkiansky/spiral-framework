<?php

declare(strict_types=1);

namespace Spiral\Console\Command;

use Psr\Container\ContainerInterface;
use Spiral\Console\CommandManager;

final class UpdateCommand extends SequenceCommand
{
    protected const NAME = 'update';
    protected const DESCRIPTION = 'Update project state';

    public function perform(CommandManager $manager, ContainerInterface $container): int
    {
        $this->info('Updating project state:');
        $this->newLine();

        return $this->runSequence($manager->getUpdateSequence(), $container);
    }
}
